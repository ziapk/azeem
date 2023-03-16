<?php
require_once(dirname(__FILE__).'/autoload.php');
$_POST = json_decode(file_get_contents('php://input'), true);

if(empty($_POST['email']) || empty($_POST['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Please fill all fields", 'data' => $_POST]);
}
else {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $usersObj = new Users();

    $userData = $usersObj->login($email,$password);
    if(!empty($userData)) {
        if(is_array($userData)) {
            http_response_code(200);
            echo json_encode(["data" => $userData, "message" => "Successfully loggedin!"]);
        }
        else {
            http_response_code(400);
            echo json_encode(["message" => $userData]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => 'Invalid email or password.']);
    }
}
