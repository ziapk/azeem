<?php 
include_once dirname(__FILE__).'/../include/settings.php';

$suppliers = new Suppliers();

if(empty($_POST['name'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
}
else {

    $data = [                
        'name' => $_POST['name'],
        'address' => !empty($_POST['address']) ? $_POST['address'] : "",
        'contact' => !empty($_POST['contact']) ? $_POST['contact'] : "",
    ];

    $create = $suppliers->createSupplier($data);

    if($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}