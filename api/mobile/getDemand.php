<?php 
require_once(dirname(__FILE__).'/autoload.php.php');
$customers = new  Demands();
$result = $customers->getDemandDetail($_GET['id'], $_GET['shop_id']);
echo json_encode($result);
?>