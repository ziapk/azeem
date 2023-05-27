<?php
include_once dirname(__FILE__) . '/../include/settings.php';

$products = new Products();

$id = !empty($_GET['id']) ? $_GET['id'] : "";
$type = !empty($_GET['action']) ? 0 : 1;
if (!empty($id)) {
    $result = $products->setInactive($id);
    echo $result;
}
