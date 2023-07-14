<?php
include_once dirname(__FILE__) . '/../include/settings.php';
try {


    $emp = new Employees();
    $shopAccounts = new ShopAccounts();
    $doubleEntry = new DoubleEntry();
    $accountsData = $shopAccounts->getSAs($shop['id']);
    $storeAccounts = [];
    foreach ($accountsData as $a) {
        $storeAccounts[$a['key_value']] = $a['account_id'];
    }

    $employees = $emp->getCurrentEmployees($shop['id'], ['flag' => 1, 'salary_month' => $_POST['salary_month']]);
    $storeObj = new Store();
    $storeDATA = $storeObj->getStore($shop['id']);
    $defaultMode = $doubleEntry->getDefaultPaymentMode(['shopId' => $shop['id']]);

    if (!empty($employees)) {
        foreach ($employees as $value) {
            $data = ['employee_id' => $value['id'], 'amount' => $value['salary'], 'shop_id' => $value['shop_id'], 'owner_id' => $value['owner_id'], 'flag' => 1, 'salary_month' => $_POST['salary_month']];
            $salary_id = $emp->createSalary($data);
            $makeTransaction = [
                'description' => "SALAY GENERATED FOR " . strtoupper($_POST['salary_month']),
                'transaction_date' => $storeDATA['sale_date'],
                'transaction_type' => 'SALAY_GENERATED',
                'reference' => $salary_id,
                'shopId' => $storeDATA['id'],
                'created_by' => $userData['id'],
                'salary_ref' => $salary_id
            ];

            $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

            // payable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['payable_salary'],
                'entry_type' => 'D',
                'description' => $makeTransaction['description'],
                'amount' => $data['amount'],
                'payment_mode' => $defaultMode['id'],
                'user_id' => $userData['id'],
            ];
            $a[] = $doubleEntry->makeEntry($entry);
            // payable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $value['account_id'],
                'entry_type' => 'C',
                'description' => $makeTransaction['description'],
                'amount' => $data['amount'],
                'payment_mode' => $defaultMode['id'],
                'user_id' => $userData['id'],
            ];
            $a[] = $doubleEntry->makeEntry($entry);
        }
        echo json_encode(['status' => 200, 'message' => 'Successfully Done', 'transaction' => ['id' => $makeTransactionId]]);
    } else {

        echo json_encode(['status' => 200, 'message' => 'Opss! Not Found. May be already generated', 'transaction' => ['id' => $makeTransactionId]]);
    }
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
