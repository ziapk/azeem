<?php 
require_once(dirname(__FILE__).'/autoload.php');
$customers = new  Products();
$result = $customers->getProduct($_GET['id'], $_GET['shop_id']);
echo json_encode($result);
?>