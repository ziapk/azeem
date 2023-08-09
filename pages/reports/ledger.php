<?php

include_once dirname(__FILE__) . '/../../include/settings.php';
include_once dirname(__FILE__) . '/../../../portal/mpdf/mpdf.php';
$doubleEntry = new DoubleEntry();

$params = array();
$from = $_POST['from'];
$to = $_POST['to'];
$account_id = $_POST['account_id'];
$entries = $doubleEntry->getLedgerByAccount($_POST);
// $entiresFinal = $entries['rows'];
foreach ($entries['rows'] as $key => $value) {
    $entiresFinal[date('F-Y', strtotime($value['transaction_date']))]['rows'][] = $value;
    $entiresFinal[date('F-Y', strtotime($value['transaction_date']))]['totals']['credit'] += $value['creditAmount'];
    $entiresFinal[date('F-Y', strtotime($value['transaction_date']))]['totals']['debit'] += $value['debitAmount'];
}

$headers         = array();
$columns         = array();
$orientation     = 'P';
$largeFont         = false;
$srNo             = true;
$mediumFont        = false;

$store = [];
foreach ($accountsData as $a) {
    $store[$a['key_value']] = $a['account_id'];
}

$reportTitle = $shop['full_name'] . ' - ' . $shop['city'];

$subtitle = "Payment Details";

//$time = new datetime('Y');
$d = new DateTime('Y');


ob_start();
?>
<h1 style="text-align: center; margin-bottom: 10px"><?php echo $reportTitle; ?></h1>
<h2 style="text-align: center; margin-top: 10px; margin-bottom: 10px"><?php echo $subtitle; ?></h2>
<?php
$header = ob_get_contents();
ob_clean();
ob_start();
?>
<style>
    table {
        font-size: 12px;
    }

    body {
        font: 16pt Arial, sans-serif;
        line-height: 1.3;
    }

    td {
        padding: 5px 5px;
    }

    <?php if ($report == 'dataCollection' || $report == 'idcards_list') { ?>td {
        height: 32px;
        white-space: nowrap;
    }

    <?php }
    if ($largeFont) { ?>td {
        font-size: 25px;
        padding: 7px 15px;
        font-family: Calibri, sans-serif;
        white-space: nowrap;
    }

    <?php }
    if ($mediumFont) { ?>td {
        font-size: 15px;
        padding: 4px 5px;
        font-family: Calibri, sans-serif;
        white-space: nowrap;
    }

    <?php } ?>h1 {
        font-size: 20pt;
    }

    <?php if ($orientation == 'L' && !$params['pdf']) { ?>@page {
        size: Legal landscape;
    }

    <?php } ?>
</style>
<table width="100%" style="border-collapse: collapse" border="0">
    <thead>
        <tr>
            <th style="border: 1px solid">Opening Balance</th>
            <th style="border: 1px solid"><?php echo number_format($entries['first']['balance']); ?></th>
        </tr>
    </thead>
</table>

<?php

foreach ($entiresFinal as $key => $rows) {
?>
    <table id="resultTable" width="100%" style="border-collapse: collapse" border="0">
        <thead>
            <tr>
                <th style="border-width: 0; font-size: 14px; font-style: italic; font-family: 'Times New Roman', Times, serif; line-height: 1.8" colspan="5">Payment Details for the month of <?php echo $key; ?></th>
            </tr>
            <tr>
                <th style="border: 1px solid">Sr.#</th>
                <th style="border: 1px solid">Date</th>
                <th style="border: 1px solid">Narration</th>
                <th style="border: 1px solid">Debit</th>
                <th style="border: 1px solid">Credit</th>
                <th style="border: 1px solid">Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows['rows'] as $k => $v) { ?>
                <tr>
                    <td style="border: 1px solid"><?php echo ($k + 2); ?></td>
                    <td style="border: 1px solid"><?php echo $v['transaction_date']; ?></td>
                    <td style="border: 1px solid"><?php echo $v['v_description']; ?></td>
                    <td style="border: 1px solid" align="right"><?php echo number_format($v['debitAmount']); ?></td>
                    <td style="border: 1px solid" align="right"><?php echo number_format($v['creditAmount']); ?></td>
                    <td style="border: 1px solid" align="right"><?php echo number_format($v['balance']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php
}
$html = ob_get_contents();
ob_clean();

$footer = 'Today: ' . date('d-m-Y h:i:s');
$mpdf = new mPDF('c', 'A4', null, 10, 10, 10, '35');
$mpdf->setHeader($header);
$mpdf->setFooter($footer);
$mpdf->WriteHTML($html);

$mpdf->Output($shop['full_name'] . '-Balances.pdf', 'I');
exit;



?>