<?php 
print_r($_GET);

$id = !empty($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    echo 'invalid id';
}

include_once dirname(__FILE__).'/../../include/settings.php';
print_r($_GET);
$categoryObj = new Categories();
$categoryObj->deleteCategory($_GET);
echo '<script>window.close()</script>';
?>