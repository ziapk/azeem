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

$data['short_title'] = !empty($_REQUEST['short_title']) ? $_REQUEST['short_title'] : null;
$data['phone'] = !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : null;
$data['email'] = !empty($_REQUEST['email']) ? $_REQUEST['email'] : null;
$data['address'] = !empty($_REQUEST['address']) ? $_REQUEST['address'] : null;

if(!$errors) {
	$categories = new DoubleEntry();
	$category = $categories->insertSupplier($data);
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
