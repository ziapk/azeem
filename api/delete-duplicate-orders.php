<?php
include_once dirname(__FILE__) . '/../include/settings.php';

if (!isset($_SESSION['user_credentials'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$orders = new Orders();

try {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'find_duplicates':
            // Find duplicate orders
            $shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;
            $criteria = $_GET['criteria'] ?? 'ref_no'; // 'ref_no' or 'customer_date'

            $duplicates = $orders->findDuplicateOrders($shopId, $criteria);

            echo json_encode([
                'success' => true,
                'criteria' => $criteria,
                'total_groups' => count($duplicates),
                'duplicates' => $duplicates
            ]);
            break;

        case 'delete_duplicates':
            // Delete duplicate orders (dry run by default)
            $shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;
            $criteria = $_GET['criteria'] ?? 'ref_no';
            $dryRun = !isset($_GET['confirm']) || $_GET['confirm'] !== 'yes';

            // First find duplicates
            $duplicates = $orders->findDuplicateOrders($shopId, $criteria);

            if (empty($duplicates)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'No duplicate orders found',
                    'criteria' => $criteria
                ]);
                break;
            }

            // Delete duplicates
            $results = $orders->deleteDuplicateOrders($duplicates, $dryRun);

            echo json_encode([
                'success' => true,
                'dry_run' => $dryRun,
                'criteria' => $criteria,
                'results' => $results,
                'message' => $dryRun ?
                    "DRY RUN: Would delete " . count($results['deleted_orders']) . " orders, keeping " . count($results['kept_orders']) . " most recent ones" :
                    "Deleted " . count($results['deleted_orders']) . " duplicate orders, kept " . count($results['kept_orders']) . " most recent ones"
            ]);
            break;

        case 'delete_specific':
            // Delete specific order IDs
            $orderIds = $_GET['order_ids'] ?? '';
            if (empty($orderIds)) {
                throw new Exception('order_ids parameter required (comma-separated)');
            }

            $ids = array_map('intval', explode(',', $orderIds));
            $dryRun = !isset($_GET['confirm']) || $_GET['confirm'] !== 'yes';

            $deleted = [];
            $errors = [];

            foreach ($ids as $orderId) {
                if (!$dryRun) {
                    try {
                        $orders->deleteOrder($orderId);
                        $deleted[] = $orderId;
                    } catch (Exception $e) {
                        $errors[] = "Order $orderId: " . $e->getMessage();
                    }
                } else {
                    $deleted[] = $orderId;
                }
            }

            echo json_encode([
                'success' => true,
                'dry_run' => $dryRun,
                'requested_deletion' => $ids,
                'deleted' => $deleted,
                'errors' => $errors,
                'message' => $dryRun ?
                    "DRY RUN: Would delete " . count($deleted) . " orders" :
                    "Deleted " . count($deleted) . " orders" . (count($errors) ? " with " . count($errors) . " errors" : "")
            ]);
            break;

        default:
            echo json_encode([
                'error' => 'Invalid action',
                'available_actions' => [
                    'find_duplicates' => 'Find duplicate orders (?shop_id=X&criteria=ref_no|customer_date)',
                    'delete_duplicates' => 'Delete duplicate orders (?shop_id=X&criteria=ref_no&confirm=yes)',
                    'delete_specific' => 'Delete specific orders (?order_ids=1,2,3&confirm=yes)'
                ]
            ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>