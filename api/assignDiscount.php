<?php 
include_once dirname(__FILE__).'/../include/settings.php';
global $shop;
$programs = new Customers();
if(!empty($_POST['customer_id'])) {
    $programs->deleteCustomerDiscounts(['customer_id' => $_POST['customer_id'], 'shopId' => $shop['id'] ]);
    if(!empty($_POST['books'])) {
        foreach($_POST['books'] as $book) {
            $created = $programs->createCustomerDiscounts(['customer_id' => $_POST['customer_id'], 'publisher_id' => $book['publisher_id'], 'discount_type' => $book['discount_type'], 'discount_value' => $book['discount_value'], 'user_id' => $userData['id'], 'shopId' => $shop['id']]);
            echo $created;
        }
    }
}
?>