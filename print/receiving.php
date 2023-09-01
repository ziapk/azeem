<?php
include_once dirname(__FILE__) . '/../include/settings.php';
global $shop;
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
if (empty($id)) {
    echo "Invalid data";
}
$siteUrl = 'https://azeem.reclinesolutions.com/';
$details = !empty($_GET['detail']) && $_GET['detail'] == 'true' ? true : false;
$largeView = !empty($_GET['largeView']) && $_GET['largeView'] == 'large' ? true : false;
$customers = new Customers();
$de = new DoubleEntry();
$id = $_GET['id'];

$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shop['id']);
$storeAccounts = [];
foreach ($accountsData as $a) {
    $storeAccounts[$a['key_value']] = $a['account_id'];
}

$recevingEntry = $de->getLedgerByTID($id);
$userEntry = [];
$blc = [];
$configs = ['title' => 'Receiving Invoice', 'label' => 'Customer\'s Name', 'sign_label' => 'Customer\'s Sign'];
foreach ($recevingEntry as $row) {
    if ($row['entry_type'] == 'C' && $row['transsaction_type'] == 'DIRECT_RECEIVING') {
        $userEntry = $row;
        $blc = $de->getOpeningBalance($row['account_id'], 'c');
    } else {
        if (in_array($row['parent_id'], [$storeAccounts['payable'], $storeAccounts['receivable']])) {
            $configs['title'] = 'Payment Invoice';
            $configs['label'] = 'Supplier\'s Name';
            $configs['sign_label'] = 'Supplier\'s Sign';
            $userEntry = $row;
            $userEntry['creditAmount'] = $row['amount'];
            $blc = $de->getOpeningBalance($row['account_id'], 's');
        }
    }
}
$gst = 0;
$service_charges = 0;
$price = $userEntry['creditAmount'];
$aprice = 0;
$largeView = true;
$currentBalance = $blc['balance'];
$balance = $price - $order['order']['payment_amount'] - $order['order']['payment_with_credit'];
?>
<link href="https://fonts.googleapis.com/css?family=Courgette&display=swap" rel="stylesheet">
<?php

$foodpanda = $order['customer'];

$a = [];
if (!empty($shop['phoneNumber1'])) {
    array_push($a, $shop['phoneNumber1']);
}
if (!empty($shop['phoneNumber2'])) {
    array_push($a, $shop['phoneNumber2']);
}
if (!empty($shop['phoneNumber3'])) {
    array_push($a, $shop['phoneNumber3']);
}
$result = array_filter($a, 'strlen');
$distTotal = 0;
$qty = 0; ?>
<style>
    body {
        margin: 0;
    }

    .recipt>table {
        font-size: 12px;
        font-family: inherit;
    }

    .recipt {
        font-family: Tahoma;
        text-align: center;
        line-height: 1.5;
        padding-top: 0;
        width: 260px;
        margin: 0 auto;
        font-size: 12px;
    }

    .recipt.large {
        width: auto;
    }

    h3 {
        margin: 0;
        line-height: 1
    }

    .recipt-table {
        border-collapse: collapse;
        font-family: Tahoma;
        font-size: inherit;
    }

    .recipt-table th,
    .recipt-table td {
        border: 1px solid;
    }

    p {
        font-family: Tahoma;
        font-style: normal;
        line-height: 1.3
    }

    .no-border {
        border: 0;
    }

    th {
        padding: 4px 5px;
    }

    td {
        padding: 4px 5px;
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .thead {
        font-weight: 600
    }

    footer {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 0;
        font-size: 12px;
    }

    .mt-5 {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .mt-0 {
        margin-top: 0;
    }

    .head h3 {
        font-family: 'Courgette', cursive;
    }

    .ref,
    .date,
    .head p {}

    .head {
        margin: 0;
    }

    .pull-left {
        float: left;
    }

    .pull-right {
        float: right;
    }

    .border {
        border-bottom: 1px solid #333;
    }

    .table th {
        padding-right: 10px;
        /* height: 30px; */
    }

    .table {
        text-align: left;
        margin-bottom: 20px;
        font-size: inherit;
    }
</style>
<div class="recipt large">
    <table width="100%">
        <thead>
            <tr>
                <th>
                    <?php $net = abs(($price)); ?>
                    <table class="table head text-left" style="width: 100%; margin: 0 0 10px">
                        <tr>
                            <td class="text-left" width="250">
                                <h3>
                                    <div style="padding-top: 10px"><?php echo strtoupper($shop['full_name']); ?>
                                        <p class="mt-0"><?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
                                            <strong><small><?php echo implode(", ", $result); ?></small></strong>
                                        </p>
                                        <div>
                                </h3>
                            </td>
                            <td>
                                <h2 style="margin: 0 0 10px"><?php echo $configs['title']; ?></h2>
                                <span style="font-weight: bold; font-size: 14px;">Bill Ref. <?php echo $_GET['id']; ?></span>
                            </td>
                            <td class="text-right" width="250">
                                <img width="120" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo $siteUrl; ?>assets/clients/<?php echo $shop['image']; ?>" />
                            </td>
                        </tr>
                    </table>
                    <table class="table" style="width: 100%; margin: 0">
                        <tr>
                            <td width="140" class="text-right"><?php echo $configs['label']; ?>:</td>
                            <th><?php echo $userEntry['title']; ?></th>
                            <td width="120" class="text-right">Date Time</td>
                            <td width="120" class="text-right"><?php echo date('d/m/Y h:m A', strtotime($userEntry['datetime'])); ?></td>
                        </tr>
                    </table>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <table class="recipt-table" width="100%" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="40" class="text-left thead">Sr.#</th>
                                <th class="text-left thead">Description</th>
                                <th width="40" class="thead">MODE</th>
                                <th width="40" class="thead">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-left"><?php echo 1; ?></td>
                                <td class="text-left" style="padding: 0 6px"><?php echo $userEntry['v_description']; ?></td>
                                <td class="text-left"><?php echo $userEntry['pay_via']; ?></td>
                                <td class="text-right" style="font-size: 20px;"><?php echo number_format(($userEntry['creditAmount'])); ?></td>
                            </tr>
                            <tr class="no-border">
                                <th style="border: 0;" valign="middle" colspan="4" class="text-left">
                                    Net in words: <?php echo convertNumberToWord($userEntry['creditAmount']); ?>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="4" style="border: 0; height: 50px;">
                                    <table class="table" style="border-collapse: collapse; margin: 0 auto;">
                                        <tr>
                                            <?php if (empty($foodpanda['is_default'])) { ?>
                                                <td width="120">Current Balance:</td>
                                                <th width="60" style="text-align: right"><?php echo number_format(($currentBalance), 0); ?></th>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="4" style="border: 0">
                                    <table border="0" class="table" width="100%" style="border-collapse: collapse; border: 0">
                                        <tr>
                                            <td width="40%" style="border: 0; text-align: left" align="left">
                                                Owner's Sign

                                            </td>
                                            <td style="border: 0"></td>
                                            <td style="border: 0; text-align: left">
                                                <?php echo $configs['sign_label']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: 0">
                                            </td>
                                            <td style="border: 0"></td>
                                            <td style="border: 0; text-align: left">
                                                <strong><?php echo $userEntry['title']; ?></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            </tfoot>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <footer>
        Powered by: Zia ur Rehman Ph.# <strong>03245120412</strong>
    </footer>
</div>
<?php if (!$details) { ?>
    <script>
        window.print();
        window.onafterprint = function() {
            // window.close();
        }
    </script>
<?php } ?>