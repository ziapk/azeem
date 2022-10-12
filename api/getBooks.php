<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$products = new Programs();
if(!empty($_SESSION['shopInfo']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $search = $products->getProgramBooks(['program_id'=> $id]);
};
echo json_encode($search);
?>