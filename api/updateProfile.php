<?php 
include_once dirname(__FILE__).'/../include/settings.php';

$users = new Users();

$user = $users->updateProfile($_POST);


if($user) {
    echo json_encode(['success' => true, 'message' => 'Profile Updated']);
    $_SESSION['user_credentials'] = $_POST;
}
else {
    echo json_encode(['success' => false, 'message' => 'Nothing Change']);
}