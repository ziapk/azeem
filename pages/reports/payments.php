<?php

include_once dirname(__FILE__) . '/../../include/settings.php';
include_once dirname(__FILE__) . '/../../../portal/mpdf/mpdf.php';
$doubleEntry = new DoubleEntry();
$params = array();
$from = $_POST['from'];
$to = $_POST['to'];
$account_id = $_POST['account_id'];

$entries = $doubleEntry->getPaymentsByAccounts($_POST);
$reportTitle = $shop['full_name'] . ' - ' . $shop['city'];

$subtitle = "Payment Details for " . $entries[0]['title'] . '<br />Between ' . date('d-m-Y', strtotime($from)) . ' to ' . date('d-m-Y', strtotime($to));

//$time = new datetime('Y');
$d = new DateTime('Y');


ob_start();
$total = 0;
?>

<h2 style="text-align: center; margin-bottom: 10px"><?php echo $reportTitle; ?></h2>
<h4 style="text-align: center; margin-top: 10px; margin-bottom: 10px"><?php echo $subtitle; ?></h4>
<table id="resultTable" width="100%" style="border-collapse: collapse" border="0">
    <thead>
        <tr>
            <th style="border: 1px solid">Sr.#</th>
            <th style="border: 1px solid">Date</th>
            <th style="border: 1px solid">Narration</th>
            <th style="border: 1px solid">Via</th>
            <th style="border: 1px solid">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $k => $v) {
            $total += $v['amount'];
        ?>
            <tr>
                <td style="border: 1px solid"><?php echo ($k + 2); ?></td>
                <td style="border: 1px solid"><?php echo $v['transaction_date']; ?></td>
                <td style="border: 1px solid"><?php echo $v['v_description']; ?></td>
                <td style="border: 1px solid"><?php echo $v['mode']; ?></td>
                <td style="border: 1px solid" align="right"><?php echo number_format($v['amount']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4">TOTAL</th>
            <th style="text-align: right;"><?php echo number_format($total); ?></th>
        </tr>
    </tfoot>
</table>
<?php

$html = ob_get_contents();
ob_clean();
echo $html;
?>