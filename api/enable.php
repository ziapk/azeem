<?php 
include_once dirname(__FILE__).'/../include/settings.php';
if($userData['role'] == 'owner') {
    $products = new Store();
    $search = 0;
    $shopId = $_POST['id'];
    $enable = $_POST['enable'];
    if($userData['role'])
    if(!empty($shopId)) {
        try {
            $search = $products->enableStoreSale($shopId, $enable);
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    };
    echo json_encode($search);
}
else {
    echo json_encode([]);
}
?>