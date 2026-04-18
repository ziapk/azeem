<?php
include_once dirname(__FILE__) . '/../include/settings.php';

if (!isset($_SESSION['user_credentials'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$inventory = new Inventory();

// Debug output if requested
if (isset($_GET['debug'])) {
    echo "DEBUG: API started, user authenticated\n";
    echo "DEBUG: userData: " . json_encode($userData) . "\n";
    echo "DEBUG: action: " . ($_GET['action'] ?? 'none') . "\n";
    flush();
}

try {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'find_duplicates':
            // Find duplicate inventory ledger entries
            $shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

            $duplicates = $inventory->findDuplicateLedgerEntries($shopId, $productId);

            echo json_encode([
                'success' => true,
                'total_groups' => count($duplicates),
                'duplicates' => $duplicates
            ]);
            break;

        case 'delete_duplicates':
            // Delete duplicate inventory ledger entries (dry run by default)
            $shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
            $dryRun = !isset($_GET['confirm']) || $_GET['confirm'] !== 'yes';

            // First find duplicates
            $duplicates = $inventory->findDuplicateLedgerEntries($shopId, $productId);

            if (empty($duplicates)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'No duplicate inventory ledger entries found'
                ]);
                break;
            }

            // Delete duplicates
            $results = $inventory->deleteDuplicateLedgerEntries($duplicates, $dryRun);

            echo json_encode([
                'success' => true,
                'dry_run' => $dryRun,
                'results' => $results,
                'message' => $dryRun ?
                    "DRY RUN: Would delete " . count($results['deleted_entries']) . " duplicate entries, keeping " . count($results['kept_entries']) . " original entries" :
                    "Deleted " . count($results['deleted_entries']) . " duplicate entries, kept " . count($results['kept_entries']) . " original entries. Re-synced " . count($results['affected_products']) . " products"
            ]);
            break;

        case 'find_by_ref':
            // Find all ledger entries for a specific reference
            $refType = $_GET['ref_type'] ?? '';
            $refId = isset($_GET['ref_id']) ? (int)$_GET['ref_id'] : 0;

            if (empty($refType) || !$refId) {
                throw new Exception('ref_type and ref_id parameters required');
            }

            $entries = $inventory->findLedgerEntriesByRef($refType, $refId);

            echo json_encode([
                'success' => true,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'total_entries' => count($entries),
                'entries' => $entries
            ]);
            break;

        case 'delete_by_ref':
            // Delete all ledger entries for a specific reference
            $refType = $_GET['ref_type'] ?? '';
            $refId = isset($_GET['ref_id']) ? (int)$_GET['ref_id'] : 0;
            $dryRun = !isset($_GET['confirm']) || $_GET['confirm'] !== 'yes';

            if (empty($refType) || !$refId) {
                throw new Exception('ref_type and ref_id parameters required');
            }

            $entries = $inventory->findLedgerEntriesByRef($refType, $refId);

            if (empty($entries)) {
                echo json_encode([
                    'success' => true,
                    'message' => "No ledger entries found for $refType #$refId"
                ]);
                break;
            }

            $affectedProducts = [];
            $deletedIds = [];

            if (!$dryRun) {
                $dbh = $inventory->connectionPool->getConnection();
                try {
                    $dbh->beginTransaction();

                    foreach ($entries as $entry) {
                        $stmt = "DELETE FROM inventory_ledger WHERE id = :id";
                        $prepare = $dbh->prepare($stmt);
                        $prepare->bindParam(':id', $entry['id'], PDO::PARAM_INT);
                        $prepare->execute();
                        $deletedIds[] = $entry['id'];

                        $affectedProducts[$entry['product_id'] . '_' . $entry['shop_id']] = [
                            'product_id' => $entry['product_id'],
                            'shop_id' => $entry['shop_id']
                        ];
                    }

                    $dbh->commit();

                    // Re-sync affected products
                    foreach ($affectedProducts as $product) {
                        $inventory->syncQty($product['product_id'], $product['shop_id']);
                    }

                } catch (Exception $e) {
                    $dbh->rollBack();
                    throw $e;
                } finally {
                    $inventory->connectionPool->releaseConnection($dbh);
                }
            } else {
                $deletedIds = array_column($entries, 'id');
                foreach ($entries as $entry) {
                    $affectedProducts[$entry['product_id'] . '_' . $entry['shop_id']] = [
                        'product_id' => $entry['product_id'],
                        'shop_id' => $entry['shop_id']
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'dry_run' => $dryRun,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'deleted_entries' => $deletedIds,
                'affected_products' => array_values($affectedProducts),
                'message' => $dryRun ?
                    "DRY RUN: Would delete " . count($deletedIds) . " ledger entries for $refType #$refId" :
                    "Deleted " . count($deletedIds) . " ledger entries for $refType #$refId. Re-synced " . count($affectedProducts) . " products"
            ]);
            break;

        case 'cleanup_adjustments':
            // Remove all ADJUSTMENT entries from inventory ledger
            $dryRun = !isset($_GET['confirm']) || $_GET['confirm'] !== 'yes';

            $dbh = $inventory->connectionPool->getConnection();
            try {
                // Count ADJUSTMENT entries
                $countStmt = $dbh->prepare("SELECT COUNT(*) as count FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'");
                $countStmt->execute();
                $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

                if ($count == 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'No ADJUSTMENT entries found in inventory ledger'
                    ]);
                    break;
                }

                // Get affected products for re-sync
                $affectedStmt = $dbh->prepare("SELECT DISTINCT product_id, shop_id FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'");
                $affectedStmt->execute();
                $affectedProducts = $affectedStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$dryRun) {
                    // Delete ADJUSTMENT entries
                    $delStmt = $dbh->prepare("DELETE FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'");
                    $delStmt->execute();

                    // Re-sync affected products
                    foreach ($affectedProducts as $product) {
                        $inventory->syncQty($product['product_id'], $product['shop_id'], $dbh);
                    }
                }

                echo json_encode([
                    'success' => true,
                    'dry_run' => $dryRun,
                    'adjustment_entries_count' => $count,
                    'affected_products' => $affectedProducts,
                    'message' => $dryRun ?
                        "DRY RUN: Would delete $count ADJUSTMENT entries and re-sync " . count($affectedProducts) . " products" :
                        "Deleted $count ADJUSTMENT entries and re-synced " . count($affectedProducts) . " products"
                ]);

            } finally {
                $inventory->connectionPool->releaseConnection($dbh);
            }
            break;

        case 'reset_order_ledger':
            // Reset ledger for a specific order and rebuild from transaction data
            $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            $shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;
            $dryRun = !isset($_GET['confirm']) || $_GET['confirm'] !== 'yes';

            if (isset($_GET['debug'])) {
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
            }

            if (!$orderId || !$shopId) {
                throw new Exception('order_id and shop_id parameters required');
            }

            if ($dryRun) {
                // Count what would be affected - use inventory class method instead of direct DB access
                $entries = $inventory->findLedgerEntriesByRef('order', $orderId);
                $count = count($entries);

                echo json_encode([
                    'success' => true,
                    'dry_run' => true,
                    'order_id' => $orderId,
                    'shop_id' => $shopId,
                    'entries_to_delete' => $count,
                    'message' => "DRY RUN: Would delete $count ledger entries for order #$orderId and rebuild from transaction data"
                ]);
                break;
            }

            // Debug: Check if we get here
            if (isset($_GET['debug'])) {
                echo "DEBUG: About to call resetOrderLedger\n";
                flush();
            }

            // Perform the actual reset
            $ownerId = $userData['id'] ?? 1; // Default to 1 if not set
            $result = $inventory->resetOrderLedger($orderId, $shopId, $ownerId);

            // Debug: Check if we get here
            if (isset($_GET['debug'])) {
                echo "DEBUG: resetOrderLedger returned\n";
                var_dump($result);
                flush();
            }

            // Check if there was an error
            if (isset($result['error'])) {
                echo json_encode([
                    'success' => false,
                    'error' => $result['error'],
                    'order_id' => $orderId,
                    'shop_id' => $shopId,
                    'partial_result' => $result
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'dry_run' => false,
                'order_id' => $orderId,
                'shop_id' => $shopId,
                'result' => $result,
                'message' => "Reset complete: Deleted {$result['deleted_entries']} entries, inserted {$result['inserted_entries']} entries, re-synced {$result['affected_products']} products"
            ]);
            break;

        default:
            echo json_encode([
                'error' => 'Invalid action',
                'available_actions' => [
                    'find_duplicates' => 'Find duplicate ledger entries (?shop_id=X&product_id=Y)',
                    'delete_duplicates' => 'Delete duplicate ledger entries (?shop_id=X&product_id=Y&confirm=yes)',
                    'find_by_ref' => 'Find ledger entries by reference (?ref_type=order&ref_id=123)',
                    'delete_by_ref' => 'Delete ledger entries by reference (?ref_type=order&ref_id=123&confirm=yes)',
                    'cleanup_adjustments' => 'Remove all ADJUSTMENT entries from ledger (&confirm=yes)',
                    'reset_order_ledger' => 'Reset ledger for order and rebuild (?order_id=X&shop_id=Y&confirm=yes)'
                ]
            ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>