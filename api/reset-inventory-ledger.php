<?php
include_once dirname(__FILE__) . '/../include/settings.php';

if (!isset($_SESSION['user_credentials'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$inventory = new Inventory();

try {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'reset_product':
            // Reset specific product ledger
            $product_id = (int)($_GET['product_id'] ?? 0);
            $shop_id = (int)($_GET['shop_id'] ?? 0);
            $movement_type = $_GET['movement_type'] ?? 'ADJUSTMENT'; // Default to ADJUSTMENT
            $delete_all = isset($_GET['delete_all']) && $_GET['delete_all'] === 'true';

            if (!$product_id || !$shop_id) {
                throw new Exception('product_id and shop_id are required');
            }

            $inventory->resetProductLedger($product_id, $shop_id, $movement_type, $delete_all);

            echo json_encode([
                'success' => true,
                'message' => "Reset ledger for product $product_id in shop $shop_id",
                'action' => $delete_all ? 'deleted_all' : "deleted_$movement_type"
            ]);
            break;

        case 'reset_all':
            // Reset ALL product ledgers (dangerous!)
            $shop_id = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;

            // Require confirmation
            $confirm = $_GET['confirm'] ?? '';
            if ($confirm !== 'YES_I_WANT_TO_RESET_ALL_LEDGER_ENTRIES') {
                throw new Exception('Confirmation required. Add ?confirm=YES_I_WANT_TO_RESET_ALL_LEDGER_ENTRIES');
            }

            $inventory->resetAllProductLedgers($shop_id);

            $scope = $shop_id ? "shop $shop_id" : "all shops";
            echo json_encode([
                'success' => true,
                'message' => "Reset ALL ledger entries for $scope",
                'warning' => 'All product quantities recalculated from scratch'
            ]);
            break;

        case 'reset_adjustments':
            // Reset only ADJUSTMENT entries for all products (safer)
            $shop_id = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;

            $dbh = $inventory->connectionPool->getConnection();

            // Get all products with ADJUSTMENT entries
            $where = $shop_id ? "AND shop_id = :shop_id" : "";
            $stmt = "SELECT DISTINCT product_id, shop_id FROM inventory_ledger
                     WHERE movement_type = 'ADJUSTMENT' $where";
            $prepare = $dbh->prepare($stmt);
            if ($shop_id) {
                $prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
            }
            $prepare->execute();
            $products = $prepare->fetchAll(PDO::FETCH_ASSOC);

            // Delete all ADJUSTMENT entries
            $delStmt = "DELETE FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'" .
                      ($shop_id ? " AND shop_id = :shop_id" : "");
            $del = $dbh->prepare($delStmt);
            if ($shop_id) {
                $del->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
            }
            $del->execute();

            // Re-sync all affected products
            foreach ($products as $product) {
                $inventory->syncQty($product['product_id'], $product['shop_id'], $dbh);
            }

            $inventory->connectionPool->releaseConnection($dbh);

            $scope = $shop_id ? "shop $shop_id" : "all shops";
            echo json_encode([
                'success' => true,
                'message' => "Deleted all ADJUSTMENT entries for $scope and re-synced quantities",
                'products_affected' => count($products)
            ]);
            break;

        default:
            echo json_encode([
                'error' => 'Invalid action',
                'available_actions' => [
                    'reset_product' => 'Reset ledger for specific product (?product_id=X&shop_id=Y&movement_type=ADJUSTMENT&delete_all=false)',
                    'reset_adjustments' => 'Delete all ADJUSTMENT entries and re-sync (?shop_id=X optional)',
                    'reset_all' => 'DANGER: Reset ALL ledger entries (?shop_id=X optional&confirm=YES_I_WANT_TO_RESET_ALL_LEDGER_ENTRIES)'
                ]
            ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>