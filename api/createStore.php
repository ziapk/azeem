<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$products = new Store();
if (!empty($_POST)) {
    $_POST['user_id'] = $userData['id'];
    $create = $products->createStore($_POST);
    var_dump($create);
    echo json_encode(["message" => "successfully created"]);
};
// echo json_encode($search);
