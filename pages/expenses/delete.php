<?php 

$id = !empty($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    echo 'invalid id';
}

include_once dirname(__FILE__).'/../../include/settings.php';
print_r($_GET);
$categoryObj = new Expenses();
$categoryObj->deleteExpense($_GET);
echo '<script>window.close()</script>';
?>