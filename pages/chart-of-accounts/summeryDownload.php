<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
include_once dirname(__FILE__) . '/../../../portal/mpdf/mpdf.php';


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
ob_start();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/bootstrap.min.css">
<style>
    td,
    th {
        padding: 3px 10px
    }
</style>
<table width="100%" border="1">
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
<?php
$html = ob_get_contents();
ob_clean();

ob_start();
?>
<table width="100%">
    <tr>
        <td style="padding-top: 10px">
            <span style="font-size: 20px; font-weight: bold"><?php echo strtoupper($shop['full_name']); ?></span><br />
            <?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
            <strong><small><?php echo implode(", ", $result); ?></small></strong>
        </td>
        <td style="text-align: right"><img width="120" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shop['image']; ?>" /></td>
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
                    <td style="text-align: right; font-weight: bold" width="140"><?php echo number_format($user['account']['opening_balance'], 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Total Amount:</td>
                    <td style="text-align: right; font-weight: bold"><?php echo number_format($amount, 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Total Paid:</td>
                    <td style="text-align: right; font-weight: bold"><?php echo number_format($paid, 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Closing Balance:</td>
                    <td style="text-align: right; font-weight: bold"><?php echo number_format($balance, 0); ?></td>
                </tr>
            </table>

        </td>
    </tr>
</table>
<?php
$header = ob_get_contents();
ob_clean();
// echo $html;
$footer = 'Today: ' . date('d-m-Y h:i:s');
$mpdf = new mPDF('c', 'A4', null, 10, 10, 10, '65');
$mpdf->setHeader($header);
$mpdf->setFooter($footer);
$mpdf->WriteHTML($html);

$mpdf->Output($user['full_name'] . '-Ledger.pdf', 'I');
exit;
?>