<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$programs = new Customers();
if(!empty($_POST['customer_id'])) {
    $programs->deleteCustomerDiscounts(['customer_id' => $_POST['customer_id'], 'shopId' => $_SESSION['shopInfo']['id'] ]);
    if(!empty($_POST['books'])) {
        foreach($_POST['books'] as $book) {
            $created = $programs->createCustomerDiscounts(['customer_id' => $_POST['customer_id'], 'publisher_id' => $book['id'], 'discount_type' => $book['discount_type'], 'discount_value' => $book['discount_value'], 'user_id' => $userData['id'], 'shopId' => $_SESSION['shopInfo']['id']]);
            echo $created;
        }
    }
}
?>