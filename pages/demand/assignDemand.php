<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $shop['owner_id'];

$demandObj = new Demands();



if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
    header('location: ' . SITE_URL . '');
}


$demand = $demandObj->getStoreDemand($_POST['id'], $ownerId);


if (empty($demand)) {
    header('location: ' . SITE_URL . '');
}

if ($_POST['flag'] == 2) { // cancel requeset
    $assign = $demandObj->cancelDemand($_POST);

    echo json_encode(['status' => 200, 'message' => 'Successfully Done!']);
    exit;
}




if (empty($_POST['assign_date'])) {
    $error = "Please fill all fields";
    echo json_encode(['status' => 400, 'message' => $error]);
} else {

    $assign = $demandObj->assignDemand($_POST);

    echo json_encode(['status' => 200, 'message' => 'Successfully Done!']);
}
exit;
