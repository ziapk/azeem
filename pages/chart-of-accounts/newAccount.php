<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$errors = false;
$response = array();
$data 	= array();
if(empty($_REQUEST['title'])) {
	$errors = false;
	$response['title'] = 'please fill title';
} else {
	$data['title'] = $_REQUEST['title'];
}
if(empty($_REQUEST['parent_id'])) {
	$errors = false;
	$response['parent_id'] = 'please fill parent';
} else {
	$data['parent_id'] = $_REQUEST['parent_id'];
}

if(empty($_REQUEST['account_type'])) {
	$errors = false;
	$response['account_type'] = 'please fill account_type';
} else {
	$data['account_type'] = $_REQUEST['account_type'];
}

$data['status'] = $_REQUEST['status'];
$data['created_by'] = $_SESSION['user_credentials']['id'];

if(!$errors) {
	try {
	$categories = new DoubleEntry();
	$getSiblings = $categories->getAccountSiblings($data['parent_id']);
	$count = $getSiblings['total'];
	if(!empty($getSiblings) && $count > 0) {
		$data['code'] = $_REQUEST['code'];
		if($count < 9) {
			$data['code'] .= '0'.($count+1);
		}
		else {
			$data['code'] .= ($count+1);
		}
	}
	else {
		$data['code'] = $_REQUEST['code'].'01';
	}
	$category = $categories->insertAccount($data);
	if($category) {
		echo json_encode($category);
	}else {
		header('HTTP/1.1 500 ServerError');
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode('internal server');	
	}
}
catch (PDOException $e) {
	die("Error!: " . $e->getMessage() . "<br/>");
}
}else {
	header('HTTP/1.1 400 Form');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($response);	
}
