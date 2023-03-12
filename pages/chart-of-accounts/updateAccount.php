<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$errors = false;
$response = array();
$data 	= array();


// title

if(empty($_REQUEST['mtitle'])) {
	$errors = false;
	$response['title'] = 'please fill title';
} else {
	$data['title'] = $_REQUEST['mtitle'];
}

// code 

if(empty($_REQUEST['mcode'])) {
	$errors = false;
	$response['code'] = 'please fill code';
} else {
	$data['code'] = $_REQUEST['mcode'];
}

$data['parent_id'] = $_REQUEST['mparent_id'];
$data['status'] = $_REQUEST['mstatus'];
$data['created_by'] = $_SESSION['user_credentials']['id'];





// id

if(empty($_REQUEST['mid'])) {
	$errors = false;
	$response['id'] = 'please fill id';
} else {
	$data['id'] = $_REQUEST['mid'];
}




if(!$errors) {
	$categories = new DoubleEntry();
	$category = $categories->updateAccount($data);
	if($category) {
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($category);
	}else {
		header('HTTP/1.1 500 ServerError');
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode('internal server');	
	}
}else {
	header('HTTP/1.1 400 Form');
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($response);	
}
