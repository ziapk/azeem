<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$s = new DoubleEntry();
$search = !empty($_GET['term']) ? $_GET['term'] : "";
$shopId = !empty($_GET['shopId']) ? $_GET['shopId'] : "";
$data = $s->searchAccounts($userData['role'] == 'owner' ? null : $shop['id'], $search);
echo json_encode($data);
