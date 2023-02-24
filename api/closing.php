<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$products = new Store();
$search = 0;
if(!empty($shop['id'])) {
    try {
        $date = date($shop['sale_date']);
        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
        $shop['sale_date'] = $next_date;
        $search = $products->closeStoreSale($shop);
        $_SESSION['shop'] = $shop;
    } catch (PDOException $e) {
        die("Error!: " . $e->getMessage() . "<br/>");
    }
};
echo json_encode($search);
?>