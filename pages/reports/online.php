<?php

include_once dirname(__FILE__) . '/../../include/settings.php';
include_once dirname(__FILE__) . '/../../../portal/mpdf/mpdf.php';
$doubleEntry = new DoubleEntry();

$params = array();
$from = $_POST['from'];
$to = $_POST['to'];

$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shop['id']);
$storeAccounts = [];
foreach ($accountsData as $a) {
    $storeAccounts[$a['key_value']] = $a['account_id'];
}

$_POST['ids'][] = $storeAccounts['cash'];
$_POST['ids'][] = $storeAccounts['expense'];
$_POST['ids'][] = $storeAccounts['receiving'];
$entries = $doubleEntry->getOnlineLedgerByAccounts($_POST);


$entiresFinal = [];
$modes = [];
foreach ($entries['rows'] as $key => $value) {
    $modes[] = $value['modeTitle'];
    $value[$value['modeTitle']]['credit'] += $value['creditAmount'];
    $value[$value['modeTitle']]['debit'] += $value['debitAmount'];
    $entiresFinal[date('F-Y', strtotime($value['transaction_date']))]['rows'][] = $value;
    $entiresFinal[date('F-Y', strtotime($value['transaction_date']))]['totals'][$value['modeTitle']]['credit'] += $value['creditAmount'];
    $entiresFinal[date('F-Y', strtotime($value['transaction_date']))]['totals'][$value['modeTitle']]['debit'] += $value['debitAmount'];
}


$headers         = array();
$columns         = array();
$orientation     = 'P';
$largeFont         = false;
$srNo             = true;
$mediumFont        = false;

$reportTitle = $shop['full_name'] . ' - ' . $shop['city'];

$subtitle = "Payment Details";
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
</style>
<?php
foreach ($entiresFinal as $key => $rows) { ?>
    <table id="resultTable" width="100%" style="border-collapse: collapse" border="0">
        <thead>
            <tr>
                <th style="border-width: 0; font-size: 14px; font-style: italic; font-family: 'Times New Roman', Times, serif; line-height: 1.8" colspan="9">Online Payment for the month of <?php echo $key; ?></th>
            </tr>
            <tr>
                <th rowspan="2" style="border: 1px solid">Sr.#</th>
                <th rowspan="2" style="border: 1px solid">Date</th>
                <th width="170" rowspan="2" style="border: 1px solid">Narration</th>
                <th rowspan="2" style="border: 1px solid">Ref.</th>
                <th rowspan="2" style="border: 1px solid">Code</th>
                <?php foreach (array_unique($modes) as $key => $value) { ?>
                    <th colspan="2" style="border: 1px solid"><?php echo $value; ?></th>
                <?php } ?>
            </tr>
            <tr>
                <th style="border: 1px solid">Debit</th>
                <th style="border: 1px solid">Credit</th>
                <th style="border: 1px solid">Debit</th>
                <th style="border: 1px solid">Credit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows['rows'] as $k => $v) { ?>
                <tr>
                    <td style="border: 1px solid"><?php echo ($k + 1); ?></td>
                    <td style="border: 1px solid"><?php echo $v['transaction_date']; ?></td>
                    <td style="border: 1px solid"><?php echo $v['v_description']; ?></td>
                    <td style="border: 1px solid"><?php
                                                    if (!empty($v['order_ref'])) {
                                                        echo $v['order_ref'];
                                                    }
                                                    if (!empty($v['supply_ref'])) {
                                                        echo $v['supply_ref'];
                                                    }
                                                    if (!empty($v['return_ref'])) {
                                                        echo $v['return_ref'];
                                                    }
                                                    if (!empty($v['reference'])) {
                                                        echo $v['reference'];
                                                    }
                                                    ?></td>
                    <td style="border: 1px solid"><?php echo $v['transsaction_type']; ?></td>
                    <?php foreach (array_unique($modes) as $mode) { ?>
                        <?php
                        if (empty($v[$mode])) { ?>
                            <td style="border: 1px solid"></td>
                            <td style="border: 1px solid"></td>
                        <?php } else { ?>
                            <td style="text-align: right; border: 1px solid"><?php echo !empty($v[$mode]['debit']) ? number_format($v[$mode]['debit']) : ''; ?></td>
                            <td style="text-align: right; border: 1px solid"><?php echo !empty($v[$mode]['credit']) ? number_format($v[$mode]['credit']) : ''; ?></td>
                    <?php }
                    } ?>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th style="font-size: 14px; font-style: italic; font-family: 'Times New Roman', Times, serif; text-align: right; padding-right: 10px" colspan="5">Total</th>
                <?php foreach (array_unique($modes) as $mode) { ?>
                    <th style="border: 1px solid" align="right"><?php echo number_format($rows['totals'][$mode]['debit']); ?></th>
                    <th style="border: 1px solid" align="right"><?php echo number_format($rows['totals'][$mode]['credit']); ?></th>
                <?php } ?>
            </tr>
        </tfoot>
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