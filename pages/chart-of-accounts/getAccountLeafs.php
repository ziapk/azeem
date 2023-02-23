<?php
session_start();
include_once dirname(__FILE__).'/../../include/settings.php';
$s = new DoubleEntry();
$id = !empty($_GET['term']) ? $_GET['term'] : "";
$data = $s->searchAccountLeafs($id);
echo json_encode($data);
