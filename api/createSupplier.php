<?php
include_once dirname(__FILE__) . '/../include/settings.php';

$suppliers = new Suppliers();

if (empty($_POST['name'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill all requried fields']);
} else {

    $data = [
        'name' => $_POST['name'],
        'address' => !empty($_POST['address']) ? $_POST['address'] : "",
        'contact' => !empty($_POST['contact']) ? $_POST['contact'] : "",
        'email' => !empty($_POST['email']) ? $_POST['email'] : "",
        'wallet' => !empty($_POST['wallet']) ? $_POST['wallet'] : 0,
        'company' => !empty($_POST['company']) ? $_POST['company'] : "",
        'title' => !empty($_POST['title']) ? $_POST['title'] : "",
        'user_id' => $userData['id'],
        'type' => !empty($_POST['type']) ? $_POST['type'] : 1,
        'shopId' => $shop['id'],
    ];


    $de = new DoubleEntry();
    $shopAccounts = new ShopAccounts();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $settings = ['account' => $storeAccounts['payable'], 'type' => 'Supplier - '];

    if (!empty($data['type']) && $data['type'] == 2) {
        $settings = ['account' => $storeAccounts['royalty'], 'type' => 'Author - '];
    }

    $payableAccount = $de->getAccount($settings['account']);

    $accountData = [
        'title' => $settings['type'] . $_POST['name'] . ' - ' . $_POST['company'],
        'code' => $payableAccount['code'],
        'account_type' => $payableAccount['account_type'],
        'group_id' => $payableAccount['group_id'],
        'status' => $payableAccount['status'],
        'parent_id' => $payableAccount['id'],
        'created_by' => $userData['id'],
        'shopId' => $shop['id'],
        'opening_balance' => $_POST['opening_balance'],
    ];


    $accountId = $de->insertAccount($accountData);
    $data['account_id'] = $accountId;

    $create = $suppliers->createSupplier($data);

    if ($create) {
        echo json_encode(["success" => true, "message" => "Successfully created!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Check form carefully!"]);
    }
}
