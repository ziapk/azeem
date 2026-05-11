<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {
    $supply = new Supply();
    $orders = new Orders();
    if($_GET['debug']) {
        print_r($_POST);
        exit;
    }

    $response = $orders->prepareOrder($_POST);

    if ($response['status'] === 200) {
        // $order = [];
        // if ($response['order']['linked_shop']) {
        //     $order = $orders->getOrder($response['order']['id']);
        //     $supply->prepareSupplyAgainstOrder($order, ['payment_with' => $_POST['payment_with']]);
        // }
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode($response);
    }
} catch (PDOException $e) {
    http_response_code(400);
    error_log($e->getMessage());
    echo json_encode($e->getMessage());
    // die("Error!: " . $e->getMessage() . "<br/>");
}
