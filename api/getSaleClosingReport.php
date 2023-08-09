<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$doubleEntry = new DoubleEntry();
$reportData = $doubleEntry->getClosingBalanceReport($_POST);
echo safe_json_encode($reportData);
