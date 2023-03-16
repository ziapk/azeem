<?php 
require_once(dirname(__FILE__).'/autoload.php');
$customers = new Users();
$result = $customers->getUser($_GET['id']);
echo json_encode($result);
?>