<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';

global $shop;

$siteUrl = 'https://azeem.reclinesolutions.com/';
$dentry = new DoubleEntry();
$user = [];
$from = !empty($from) ? $from : date('Y-m-d', strtotime(date('Y-m') . '-' . '01'));
$to = !empty($to) ? $to : date('Y-m-d');

if ($type == 'c') {
    $type = 'c';
    $customers = new Customers();
    $user = $customers->getUserByAccount($id);
} elseif ($type == 's') {
    $suppliers = new Suppliers();
    $user = $suppliers->getUserByAccount($id);
} elseif ($type == 'emp') {
    $employees = new Employees();
    $user = $employees->getUserByAccount($id);
} elseif ($type == 'e') {
    $expenses = new Categories();
    $user = $expenses->expenseByAccount($id);
}

$journel = $dentry->getLedgerByAccount(['account_id' => $id, 'type' => $type, 'from' => $from, 'to' => $to, 'user' => $user['account']]);

$summery = $journel['first'];
$end = $journel['last'];
?>
<table width="100%">
    <tr>
        <td style="padding-top: 10px">
            <span style="font-size: 20px; font-weight: bold"><?php echo strtoupper($shop['full_name']); ?></span><br />
            <?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
            <strong><small><?php echo implode(", ", $result); ?></small></strong>
        </td>
        <td style="text-align: right"><img width="120" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo $siteUrl; ?>assets/clients/<?php echo $shop['image']; ?>" /></td>
    </tr>
    <tr>
        <th colspan="2" style="font-size: 1.5em;">
            Account Summary <?php echo $order['order']['status'] == 1 ? '(Parked Invoice)' : null ?>
        </th>
    </tr>
</table>
<table width="100%">
    <tr>
        <td valign="top" style="padding-top: 10px">
            <?php echo $user['full_name']; ?> <br />
            <?php echo $user['address']; ?>
        </td>
        <td width="300">
            <table width="100%">
                <tr>
                    <td>Opening Balance:</td>
                    <td style="text-align: right; font-weight: bold" width="140"><?php echo number_format($summery['balance'], 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Closing Balance:</td>
                    <td style="text-align: right; font-weight: bold"><?php echo number_format($end['balance'], 0); ?></td>
                </tr>
            </table>

        </td>
    </tr>
</table>
<table width="100%" border="1" cellpadding="4" cellspacing="0" style="border: 1px solid;">
    <thead>
        <tr>
            <th>T.ID</th>
            <th>Date</th>
            <th>Order ID</th>
            <th>Ref.#</th>
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
                <td><?php echo $value['reference']; ?></td>
                <td><?php echo $value['v_description']; ?></td>
                <td><?php echo $value['transsaction_type']; ?></td>
                <td style="text-align: right;"><?php echo number_format($value['debitAmount'], 0); ?></td>
                <td style="text-align: right;"><?php echo number_format($value['creditAmount'], 0); ?></td>
                <td style="text-align: right; <?php if ($value['balance'] < 0) {
                                                    echo "color: red";
                                                } ?>"><?php echo number_format($value['balance'], 0); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>