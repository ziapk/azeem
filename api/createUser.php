<?php
include_once dirname(__FILE__) . '/../include/settings.php';

$users = new Users();

$user = $users->createProfile($_POST);


if ($user) {
    echo json_encode(['success' => true, 'message' => 'Profile Created']);
} else {
    echo json_encode(['success' => false, 'message' => 'Nothing Change']);
}
