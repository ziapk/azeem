<?php 
require_once(dirname(__FILE__).'/autoload.php');
$customers = new  Demands();
$result = $customers->getUserDemands($_GET['shop_id'], $_GET['user_id']);
echo json_encode($result);
?>