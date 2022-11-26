<?php
include_once dirname(__FILE__).'/../../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $shop['owner_id'];
if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);exit;
}
$demandObj = new Demands();

$demand = $demandObj->getStoreDemand($_GET['id'], $ownerId);

if(empty($demand)) {
    echo json_encode(['status' => 404, 'message' => 'Not Found']);exit;
}
$data = [
    'assign_date' => date('Y-m-d'),
    'flag' => 3,
    'id' => $_GET['id'],
];
$assign = $demandObj->cancelDemand($data);
    
echo json_encode(['status' => 200, 'message' => 'Successfully Done!']);

exit;
