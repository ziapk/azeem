<?php 
print_r($_GET);

$id = !empty($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    echo 'invalid id';
}

include_once dirname(__FILE__).'/../../include/settings.php';
print_r($_GET);
$customerObj = new Customers();
$customerObj->deleteCustomer($_GET);
echo '<script>window.close()</script>';
?>