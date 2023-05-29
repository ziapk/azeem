<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$categoryObj = new Categories();

$error = "";
$message = "";
$error = "";

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];


$userId = $userData['id'];

if (empty($_POST['full_name'])) {

    $error = "Please fill all fields";
} else {

    $photo = $_FILES['image'];
    $uploaded = false;
    $image = "";
    if (isset($photo) && count($photo)) {
        if ($photo['error'] == 0) {
            $img = explode('.', $photo['name']);
            $photo['dst_path']     = dirname(__FILE__) . '/../../uploads/products/';

            $image = time() . '.' . $img[1];

            if (!file_exists($photo['dst_path'])) {

                mkdir($photo['dst_path'], 0777, true);
            }

            $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'] . $image);
            if ($moved) {
                $uploaded = true;
            }
        }
    }

    $data = [
        'image' => $uploaded ? $image : "",
        'full_name' => $_POST['full_name'],
        'cat_type' => $_POST['cat_type'],
        'groupName' => !empty($_POST['groupName']) ? $_POST['groupName'] : 'General',
        'owner_id' => $ownerId,
        'created_by' => $userId
    ];

    if ($_POST['cat_type'] == 1) {

        $de = new DoubleEntry();

        $receivableAccount = $de->getAccount($shop['expense']);

        $accountData = [
            'title' => 'Expense - ' . $_POST['full_name'] . ' - ' . $data['groupName'],
            'code' => $receivableAccount['code'],
            'account_type' => $receivableAccount['account_type'],
            'group_id' => $receivableAccount['group_id'],
            'status' => $receivableAccount['status'],
            'parent_id' => $receivableAccount['id'],
            'shopId' => $shop['id'],
            'opening_balance' => empty($_POST['opening_balance']) ? 0 : $_POST['opening_balance'],
            'created_by' => $userId
        ];


        $accountId = $de->insertAccount($accountData);
        $data['account_id'] = $accountId;
    } else {
        $data['account_id'] = null;
    }

    $create = $categoryObj->createCategory($data);

    if ($create) {
        $message = "Successfully created!";
    } else {
        $error = "Check form carefully!";
    }
}

if (!empty($error)) {
    echo json_encode(['success' => false, 'error' => $error]);
}
if (!empty($message)) {
    echo json_encode(['success' => true, 'message' => $message]);
}
