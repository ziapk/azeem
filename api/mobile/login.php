<?php
require_once(dirname(__FILE__).'/autoload.php');
if(empty($_POST['email']) || empty($_POST['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Please fill all fields"]);
}
else {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $usersObj = new Users();

    $userData = $usersObj->login($email,$password);
    if(!empty($userData)) {
        if(is_array($userData)) {
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
