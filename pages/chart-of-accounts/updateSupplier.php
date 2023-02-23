<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$errors = false;
$response = array();
$data 	= array();
if(empty($_REQUEST['mtitle'])) {
	$errors = false;
	$response['title'] = 'please fill title';
} else {
	$data['title'] = $_REQUEST['mtitle'];
}

$data['id'] = !empty($_REQUEST['mid']) ? $_REQUEST['mid'] : null;
$data['short_title'] = !empty($_REQUEST['mshort_title']) ? $_REQUEST['mshort_title'] : null;
$data['phone'] = !empty($_REQUEST['mphone']) ? $_REQUEST['mphone'] : null;
$data['email'] = !empty($_REQUEST['memail']) ? $_REQUEST['memail'] : null;
$data['address'] = !empty($_REQUEST['maddress']) ? $_REQUEST['maddress'] : null;

if(!$errors && !empty($data['id'])) {
	$categories = new DoubleEntry();
	$category = $categories->updateSupplier($data);
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
