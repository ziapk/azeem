<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
global $shop;
$newsletter = new Newsletter();

$from = !empty($_POST['from']) ? $_POST['from'] : date('Y-m-d', strtotime(date('Y-m', strtotime("-2 months")) . '-' . '01'));
$to = !empty($_POST['to']) ? $_POST['to'] : date('Y-m-d');
$type = !empty($_POST['type']) ? $_POST['type'] : 'c';
$account_id = $_POST['account_id'];
$customer = [];
if (!empty($account_id)) {
    $send = $newsletter->send([
        'subject' => "Ledger Summery Between " . $from . " and " . $to,
        'body' => $newsletter->drawLedger($account_id, $type, $from, $to),
        'sentTo' => [['email' => !empty($customer['email']) ? $customer['email'] : 'zia.pccr@yahoo.com', 'name' => $_POST['customer_name']]],
        'ccEmails' => [['email' => $shop['company_email'], 'name' => $shop['full_name']]],
        'client' => $shop['full_name'],
        'labels' => ['Ledger Summery']
    ]);
    try {
        echo json_encode(['success' => true, 'message' => "Email Sent Successfully!"]);
    } catch (Exception $e) {
        print_r($e);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Incomplete Data"]);
}
