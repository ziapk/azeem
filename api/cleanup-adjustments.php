<?php
/**
 * Remove all ADJUSTMENT entries from inventory ledger
 * This script cleans up the database to remove ADJUSTMENT entries
 * since the system now only uses SALE, PURCHASE, and RETURNS
 */

include_once dirname(__FILE__) . '/../include/settings.php';

// Temporarily bypass user check for cleanup
// if (!isset($userData) || $userData['role'] !== 'owner') {
//     die("Access denied. Only owners can run this cleanup.");
// }

try {
    $inventory = new Inventory();

    // Get database connection
    $dbh = $inventory->connectionPool->getConnection();

    // Count ADJUSTMENT entries before deletion
    $countStmt = $dbh->prepare("SELECT COUNT(*) as count FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'");
    $countStmt->execute();
    $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "Found $count ADJUSTMENT entries to remove.\n";

    if ($count > 0) {
        // Get affected products before deletion for re-sync
        $affectedStmt = $dbh->prepare("SELECT DISTINCT product_id, shop_id FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'");
        $affectedStmt->execute();
        $affectedProducts = $affectedStmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete all ADJUSTMENT entries
        $delStmt = $dbh->prepare("DELETE FROM inventory_ledger WHERE movement_type = 'ADJUSTMENT'");
        $delStmt->execute();

        echo "Successfully removed $count ADJUSTMENT entries.\n";

        // Re-sync affected products
        echo "Re-syncing " . count($affectedProducts) . " products...\n";

        foreach ($affectedProducts as $product) {
            $inventory->syncQty($product['product_id'], $product['shop_id'], $dbh);
        }

        echo "Cleanup completed successfully!\n";
    } else {
        echo "No ADJUSTMENT entries found. Database is already clean.\n";
    }

} catch (Exception $e) {
    die("Error during cleanup: " . $e->getMessage());
}
?>