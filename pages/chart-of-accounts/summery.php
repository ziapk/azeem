<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
$dentry = new DoubleEntry();
$user = [];
$type = 's';
if ($_GET['t'] == 'c') {
    $type = 'c';
    $customers = new Customers();
    $user = $customers->getUserByAccount($_GET['id']);
} elseif ($_GET['t'] == 's') {
    $suppliers = new Suppliers();
    $user = $suppliers->getUserByAccount($_GET['id']);
} elseif ($_GET['t'] == 'e') {
    $expenses = new Categories();
    $user = $expenses->expenseByAccount($_GET['id']);
}

$journel = $dentry->getLedgerByAccount(['account_id' => $_GET['id'], 'type' => $type]);
$summery = $journel['summery'];

if ($_GET['t'] == 'c') {
    $summery['debit'] += $user['account']['opening_balance'];
} else {
    $summery['credit'] += $user['account']['opening_balance'];
}
$paid = $_GET['t'] == 's' ? $summery['debit'] : $summery['credit'];
$amount = $_GET['t'] == 's' ? $summery['credit'] : $summery['debit'];
// $amount = ($user['account']['opening_balance'] + $amount);
$balance = ($amount - $paid);

$url = '?';

foreach ($_GET as $key => $value) {
    $url .= $key . "=" . $value . "&";
}

mainHeader();
?>
<div class="container">
    <table width="100%">
        <tr>
            <td>
                <h2>Account Summary <a class="btn btn-primary" href="<?php echo SITE_URL . '/pages/chart-of-accounts/summeryDownload.php' . $url; ?>" target="_blank">Generate PDF</a></h2>
                <p><?php echo $user['full_name']; ?></p>
                <p><?php echo $user['address']; ?> (<?php echo $user['company']; ?>) </p>
                <p>Contact No: <?php echo $user['phoneNumber']; ?></p>
            </td>
            <td width="300">
                <table width="100%">
                    <tr>
                        <td>Opening Balance:</td>
                        <td width="140"><?php echo number_format($user['account']['opening_balance'], 2); ?><br /></td>
                    </tr>
                    <tr>
                        <td>Total Invoices:</td>
                        <td><?php echo $summery['total']; ?><br /></td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td><?php echo number_format($amount, 2); ?><br /></td>
                    </tr>
                    <tr>
                        <td>Total Paid:</td>
                        <td><?php echo number_format($paid, 2); ?><br /></td>
                    </tr>
                    <tr>
                        <td>Closing Balance:</td>
                        <td><?php echo number_format($balance, 2); ?></td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
    <table width="100%" class="table table-striped">
        <thead>
            <tr>
                <th>T.ID</th>
                <th>Date</th>
                <th>Order ID</th>
                <th>Description</th>
                <th>Entry Type</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Running Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($journel['rows'] as $key => $value) { ?>
                <tr>
                    <td><?php echo $value['transaction_id']; ?></td>
                    <td><?php echo $value['transaction_date']; ?></td>
                    <td><?php echo $value['order_ref']; ?></td>
                    <td><?php echo $value['v_description']; ?></td>
                    <td><?php echo $value['transsaction_type']; ?></td>
                    <td><?php echo number_format($value['debitAmount'], 2); ?></td>
                    <td><?php echo number_format($value['creditAmount'], 2); ?></td>
                    <td style="<?php if ($value['balance'] < 0) {
                                    echo "color: red";
                                } ?>"><?php echo number_format($value['balance'], 2); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>