<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$suppliers = new  Suppliers();
$data = ['success' => 200];
if(!empty($_SESSION['shopInfo'])) {
    print_r($_REQUEST);
    $data['lastId'] = $suppliers->createSupplier($_POST);
};
echo json_encode($data);
?>