<?php 
include_once dirname(__FILE__).'/../include/settings.php';

$orders = new Expenses();

$status = 9;
if(!empty($_POST['form'])) {
    foreach($_POST['form'] as $data) {
        $insert[] = $orders->createExpense($data);

    }
    
}
echo json_encode(['status' => 200, 'message' => 'successfully done', 'order' => [ 'id'=> $insert ]]);

?>