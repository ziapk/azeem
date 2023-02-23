<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$errors = false;
$response = array();
$data 	= array();
if(empty($_POST['id'])) {
	$errors = false;
	$response['id'] = 'please fill id';
} else {
	$data['id'] = $_REQUEST['id'];
}

if(!$errors) {
	
	$categories = new DoubleEntry();
	
	$category = $categories->deleteAccount($data);
	if($category) {
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
