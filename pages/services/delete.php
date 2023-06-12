<?php

$id = !empty($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo 'invalid id';
}

include_once dirname(__FILE__) . '/../../include/settings.php';
$categoryObj = new Services();
$categoryObj->deleteService($_GET);
echo '<script>window.close()</script>';
