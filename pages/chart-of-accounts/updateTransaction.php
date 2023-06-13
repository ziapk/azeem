<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
$errors = false;
$response = array();
$data 	= array();

// title

if (empty($_REQUEST['amount'])) {
	$errors = false;
	$response['amount'] = 'please fill amount';
} else {
	$data['amount'] = $_REQUEST['amount'];
}



// code 

if (empty($_REQUEST['transaction_id'])) {
	$errors = false;
	$response['transaction_id'] = 'please fill transaction_id';
} else {
	$data['transaction_id'] = $_REQUEST['transaction_id'];
}


if (!$errors) {
	$categories = new DoubleEntry();
	$category = $categories->updateTransactions($data);
	if ($category) {
		echo json_encode(["success" => true, "message" => "Successfully updated!"]);
	} else {
		echo json_encode(["success" => false, "message" => "nothing change!"]);
	}
} else {
	header('HTTP/1.1 400 Form');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($response);
}
