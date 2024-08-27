<?php
include_once dirname(__FILE__) . '/../include/settings.php';

$users = new Users();


if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'superadmin') {
} else {
    unset($_POST['password']);
}


$user = $users->updateProfile($_POST);


if ($user) {
    echo json_encode(['success' => true, 'message' => 'Profile Updated']);
    if (!empty($_SESSION['user_credentials']) && $_SESSION['user_credentials']['role'] == 'superadmin') {
    } else {
    $_SESSION['user_credentials'] = $_POST;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nothing Change']);
}
