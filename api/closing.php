<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$products = new Store();
$search = 0;
$shopId = $userData['role'] == 'owner' ? $_POST['id'] : $shop['id'];
$shopDate = $userData['role'] == 'owner' ? $_POST['sale_date'] : $shop['sale_date'];
if($userData['role'])
if(!empty($shopId)) {
    try {
        $date = date($shopDate);
        $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
        $finalDate = $next_date;
        $search = $products->closeStoreSale($shopId, $finalDate);
    } catch (PDOException $e) {
        die("Error!: " . $e->getMessage() . "<br/>");
    }
};
echo json_encode($search);
?>