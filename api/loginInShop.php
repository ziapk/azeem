<?php
// session_start();
include_once dirname(__FILE__) . '/../include/settings.php';
include_once dirname(__FILE__) . '/../classes/users.php';

// try {
$usersObj = new Users();
$userData = $usersObj->refreshSession($_GET['id']);
print_r($userData);
// } catch (Exception $e) {
//     print_r($e);
// }
$_SESSION['user_credentials'] = $userData['user'];
$_SESSION['shopInfo'] = $userData['client'];
$_SESSION['shop'] = $userData['shop'];
$_SESSION['user_logged_in'] = true;
header('Location: ' . SITE_URL . 'index.php');
