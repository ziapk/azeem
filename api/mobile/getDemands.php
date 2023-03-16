<?php 
require_once(dirname(__FILE__).'/autoload.php');
$customers = new  Demands();
$result = $customers->getStoreDemands($_GET['shop_id']);
echo json_encode($result);
?>