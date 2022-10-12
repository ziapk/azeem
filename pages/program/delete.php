<?php 
$id = !empty($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    echo 'invalid id';
}

include_once dirname(__FILE__).'/../../include/settings.php';
$categoryObj = new Programs();
$categoryObj->deleteProgram($_GET['id']);
echo '<script>window.close()</script>';
?>