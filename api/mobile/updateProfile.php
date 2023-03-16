<?php 
require_once(dirname(__FILE__).'/autoload.php');

$users = new Users();

$_POST['photo'] = SITE_URL.'assets/img/avatar/avatar1.jpg';

$user = $users->updateProfile($_POST);


if($user) {
    echo json_encode(['success' => true, 'message' => 'Profile Updated']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Nothing Change']);
}