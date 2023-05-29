<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
$errors = false;
$response = array();
$data 	= array();
if (empty($_REQUEST['purchase_date'])) {
	$errors = false;
	$response['purchase_date'] = 'please fill purchase_date';
} else {
	$data['purchase_date'] = $_REQUEST['purchase_date'];
}
if (empty($_REQUEST['account_id'])) {
	$errors = false;
	$response['account_id'] = 'please fill account_id';
} else {
	$data['account_id'] = $_REQUEST['account_id'];
}

if (empty($_REQUEST['unit_price'])) {
	$errors = false;
	$response['unit_price'] = 'please fill unit_price';
} else {
	$data['unit_price'] = $_REQUEST['unit_price'];
}

if (empty($_REQUEST['quantity'])) {
	$errors = false;
	$response['quantity'] = 'please fill quantity';
} else {
	$data['quantity'] = $_REQUEST['quantity'];
}

if (empty($_REQUEST['total_amount'])) {
	$errors = false;
	$response['total_amount'] = 'please fill total_amount';
} else {
	$data['total_amount'] = $_REQUEST['total_amount'];
}

if (empty($_REQUEST['warranty_expiration'])) {
	$errors = false;
	$response['warranty_expiration'] = 'please fill warranty_expiration';
} else {
	$data['warranty_expiration'] = $_REQUEST['warranty_expiration'];
}

if (empty($_REQUEST['description'])) {
	$errors = false;
	$response['description'] = 'please fill description';
} else {
	$data['description'] = $_REQUEST['description'];
}

if (empty($_REQUEST['warranty_docs'])) {
	$errors = false;
	$response['warranty_docs'] = 'please fill warranty_docs';
} else {
	$data['warranty_docs'] = $_REQUEST['warranty_docs'];
}

if (empty($_REQUEST['description'])) {
	$errors = false;
	$response['description'] = 'please fill description';
} else {
	$data['description'] = $_REQUEST['description'];
}

if (empty($_REQUEST['docs'])) {
	$errors = false;
	$response['docs'] = 'please fill docs';
} else {
	$data['docs'] = $_REQUEST['docs'];
}




if (empty($_REQUEST['docs'])) {
	$errors = false;
	$response['docs'] = 'please fill docs';
} else {
	$data['docs'] = $_REQUEST['docs'];
}


// if(empty($_REQUEST['group_id'])) {
// 	$errors = false;
// 	$response['group_id'] = 'please fill group_id';
// } else {
// 	$data['group_id'] = $_REQUEST['group_id'];
// }

$data['status'] = $_REQUEST['status'];
$data['shopId'] = $shop['id'];
$data['parent_id'] = !empty($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : null;
$data['created_by'] = $_SESSION['user_credentials']['id'];


if (!$errors) {
	$categories = new DoubleEntry();
	$category = $categories->insertAccount($data);
	if ($category) {
		echo json_encode($category);
	} else {
		header('HTTP/1.1 500 ServerError');
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode('internal server');
	}
} else {
	header('HTTP/1.1 400 Form');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($response);
}
