<?php 
require_once(dirname(__FILE__).'/autoload.php');
$customers = new  Demands();
$result['demands'] = $customers->getUserDemands($_GET['shop_id'], $_GET['user_id']);
$ids = [];
foreach ($result['demands'] as $key => $value) {
    $ids[] = $value['id'];
}
$result['items'] = $customers->getDemandsItems($ids);

echo json_encode($result);
?>