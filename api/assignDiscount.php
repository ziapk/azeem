<?php
include_once dirname(__FILE__) . '/../include/settings.php';
global $shop;
$programs = new Customers();
if (!empty($_POST['customer_id'])) {
    $programs->deleteCustomerDiscounts(['customer_id' => $_POST['customer_id'], 'shopId' => $shop['id']]);
    if (!empty($_POST['books'])) {
        foreach ($_POST['books'] as $book) {
            $dv = !empty($book['discount_value']) ? $book['discount_value'] : 0;
            $created[] = $programs->createCustomerDiscounts(['customer_id' => $_POST['customer_id'], 'publisher_id' => $book['id'], 'discount_type' => $book['discount_type'], 'discount_value' =>  $dv, 'user_id' => $userData['id'], 'shopId' => $shop['id']]);
        }
        echo json_encode($created);
    }
}
