<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
global $shop;
$newsletter = new Newsletter();

$from = !empty($_POST['from']) ? $_POST['from'] : date('Y-m-d', strtotime(date('Y-m', strtotime("-6 months")) . '-' . '01'));
$to = !empty($_POST['to']) ? $_POST['to'] : date('Y-m-d');
$type = !empty($_POST['type']) ? $_POST['type'] : 'c';
$account_ids = $_POST['account_id'];
$customer = [];

$customers = new Customers();
$suppliers = new Suppliers();
$employees = new Employees();
$expenses = new Categories();


if (!empty($account_ids)) {

    try {

        foreach ($account_ids as $key => $account_id) {

            if ($type == 'c') {
                $customer = $customers->getUserByAccount($account_id);
            } elseif ($type == 's') {
                $customer = $suppliers->getUserByAccount($account_id);
            } elseif ($type == 'emp') {
                $customer = $employees->getUserByAccount($account_id);
            } elseif ($type == 'e') {
                $customer = $expenses->expenseByAccount($account_id);
            }

            $emails = [];

            $addressArr = explode(',', $customer['email']);

            if (!empty($addressArr)) {
                foreach ($addressArr as $key => $email) {
                    if (!empty($email)) {
                        $emails[] = ['email' => $email, 'name' => $customer['full_name']];
                    } else {
                        $emails[] = ['email' => 'zia.pccr@yahoo.com', 'name' => $customer['full_name']];
                    }
                }
            } else {
                $emails[] = ['email' => 'zia.pccr@yahoo.com', 'name' => $customer['full_name']];
            }

            $newsletter->send([
                'subject' => "Ledger Summery for " . $customer['full_name'],
                'body' => $newsletter->drawLedger($account_id, $type, $from, $to),
                'sentTo' => $emails,
                'ccEmails' => [['email' => $shop['company_ledger_inbox'], 'name' => $shop['full_name']]],
                'client' => $shop['full_name'],
                'labels' => ['Ledger Summery']
            ]);
        }

        echo json_encode(['success' => true, 'message' => "Email Sent Successfully!"]);
    } catch (Exception $e) {
        print_r($e);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Incomplete Data"]);
}
