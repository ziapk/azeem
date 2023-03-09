<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$products = new Store();
$search = 0;
$shopId = $userData['role'] == 'owner' ? $_POST['id'] : $shop['id'];
$shopDate = $userData['role'] == 'owner' ? $_POST['sale_date'] : $shop['sale_date'];
if(!empty($shopId)) {
    try {
        $date = date($shopDate);
        $next_date = ($_POST['sale_date'] == 'next') ? date('Y-m-d', strtotime($date . ' +1 day')) : $shopDate;
        $finalDate = $next_date;
        $search = $products->closeStoreSale($shopId, $finalDate);
        $shop['sale_date'] = $finalDate; 
        $_SESSION['shop'] = $shop;
        
    } catch (PDOException $e) {
        die("Error!: " . $e->getMessage() . "<br/>");
    }
};
echo json_encode($search);
?>