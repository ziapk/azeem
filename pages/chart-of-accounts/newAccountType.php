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
if(empty($_REQUEST['code'])) {
	$errors = false;
	$response['code'] = 'please fill code';
} else {
	$data['code'] = $_REQUEST['code'];
}
if(!$errors) {
	$categories = new DoubleEntry();
	$category = $categories->insertAccountType($data);
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
