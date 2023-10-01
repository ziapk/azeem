<?php
include_once dirname(__FILE__) . '/../include/settings.php';
global $shop;
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
$siteUrl = 'https://azeem.reclinesolutions.com/';
if (empty($id)) {
    echo "Invalid data";
}
$details = !empty($_GET['detail']) && $_GET['detail'] == 'true' ? true : false;
$largeView = !empty($_GET['largeView']) && $_GET['largeView'] == 'large' ? true : false;
$ordersObj = new Supply();
$order = $ordersObj->getOrder($id, true);
$customers = new DoubleEntry();

if ($order['order']['supplier_type'] == 2) {
    $blc = $customers->getOpeningBalance($order['customer']['account_id'], 'c');
} else {
    $blc = $customers->getOpeningBalance($order['supplier']['account_id'], 's');
}


$gst = 0;
$service_charges = 0;
$price = 0;
$aprice = 0;

if (!empty($order['order']['price'])) {
    $gst = round($order['order']['price'] * ($order['order']['gst'] / 100));
    $service_charges = round($order['order']['price'] * ($order['order']['service_charges'] / 100));
    $price = $order['order']['price'] + $gst + $service_charges;
}

$currentBalance = $blc['balance'];
$balance = $price - $order['order']['payment_amount'] - $order['order']['payment_with_credit'];

?>
<link href="https://fonts.googleapis.com/css?family=Courgette&display=swap" rel="stylesheet">
<?php

$foodpanda = !empty($order['customer']) ? $order['customer'] : $order['supplier'];

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

    .mb-0 {
        margin-bottom: 0;
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

    @page {
        @bottom-left {
            content: counter(page) "/" counter(pages);
        }
    }
</style>
<div class="recipt large">
    <table width="100%" cellspading="0" cellspacing="0">
        <thead>
            <tr>
                <th style="padding: 0">
                    <?php $net = abs(($price)); ?>
                    <table class="table head text-left" style="width: 100%; margin: 0" cellspading="0" cellspacing="0">
                        <tr>
                            <td class="text-left" width="250">
                                <h3>
                                    <div style="padding-top: 10px"><?php echo strtoupper($shop['full_name']); ?>
                                        <p class="mt-0 mb-0"><?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
                                            <strong><small><?php echo implode(", ", $result); ?></small></strong>
                                        </p>
                                        <div>
                                </h3>
                            </td>
                            <td>
                                <h2 style="margin: 0 0 10px">Purchase Invoice <?php echo $order['order']['status'] == 1 ? '(Parked)' : null; ?></h2>
                                <span style="font-weight: bold; font-size: 14px;">Bill.# <?php echo $_GET['id'];
                                                                                            if ($order['order']['ref_no']) {
                                                                                            ?> | R:
                                    <?php echo $order['order']['ref_no'];
                                                                                            } ?></span>
                            </td>
                            <td class="text-right" width="250">
                                <img width="120" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo $siteUrl; ?>assets/clients/<?php echo $shop['image']; ?>" />
                            </td>
                        </tr>
                    </table>
                    <table class="table" style="width: 100%; margin: 0" cellspading="0" cellspacing="0">
                        <tr>
                            <td width="60" class="text-left">Name:</td>
                            <th><?php echo !empty($foodpanda['name']) ? $foodpanda['name'] : $foodpanda['full_name']; ?></th>
                            <td width="120" class="text-right">Date Time</td>
                            <td width="160" class="text-right"><?php echo dbDateToClient($order['order']['created_at']); ?></td>
                        </tr>
                    </table>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 0">
                    <table class="recipt-table" width="100%" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="40" class="text-left thead">Sr.#</th>
                                <th width="40" class="text-left thead">Code</th>
                                <th class="text-left thead">Item</th>
                                <th width="40" class="thead">Qty</th>
                                <!-- <th width="50" class="thead">U. Price</th> -->
                                <th width="50" class="thead">D. %</th>
                                <th width="50" class="thead">D. Price</th>
                                <th class="text-right thead" width="50">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['order_items'] as $key => $item) {
                                $cprice = $item['price'] / (100 / (100 - $item['discount']));
                                $discountTotal = (!empty($item['discount'])) ? $item['price'] / (100 / ($item['discount'])) : 0;
                            ?>
                                <tr>
                                    <td class="text-left"><?php echo $key + 1; ?></td>
                                    <td class="text-left" style="padding: 0 6px"><?php echo $item['product_id']; ?></td>
                                    <td class="text-left">
                                        <?php echo !empty($item['product_title']) ? $item['product_title'] : $item['full_name']; ?>
                                        <span style="float: right;"><?php echo !empty($item['pack_qty']) ? '<strong>Bundles: ' . $item['pack_qty'] . 'x' . $item['pack_size'] . (!empty($item['unpack_qty']) ? '+' . $item['unpack_qty'] : '') . '</strong>' : null; ?></span>
                                    </td>
                                    <td class="text-right"><?php echo abs(($item['quantity'])); ?></td>
                                    <!-- <td class="text-right"><?php echo number_format(abs(($item['price']))); ?></td> -->
                                    <td class="text-right"><?php echo number_format(abs($item['discount'])) . '%'; ?></td>
                                    <td class="text-right"><?php echo $cprice; ?></td>
                                    <td class="text-right"><?php
                                                            $aprice += $item['quantity'] * ($item['price']);
                                                            $distTotal += $item['quantity'] * $discountTotal;
                                                            $qty += $item['quantity'];
                                                            echo number_format(abs($item['quantity'] * $cprice)); ?></td>
                                </tr>
                                <?php if (!empty($item['description'])) { ?>
                                    <tr>
                                        <th colspan="2">--</th>
                                        <td colspan="6" style="text-align: left"><?php echo $item['description']; ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                            <tr class="no-border">
                                <td valign="top" style="border: 0" class="text-right" colspan="2">Total Quantity</td>
                                <td valign="top" style="border: 0" class="text-right"><strong><?php echo abs(($qty)); ?></strong></td>
                                <td class="text-right ref" style="border: 0" colspan="3">Invoice Total</td>
                                <th class="text-right ref"><?php echo number_format(abs(($aprice))); ?></th>
                            </tr>
                            <tr class="no-border">
                                <th rowspan="2" style="border: 0;" valign="middle" colspan="4">
                                    <!-- Important Note: Books once sold nerver be returned or exchanged. -->
                                </th>
                                <td class="text-right ref" style="border: 0" colspan="2">Additional Discount</td>
                                <th class="text-right ref"><?php echo number_format(abs(($order['order']['discount']))); ?></th>
                            </tr>
                            <tr class="no-border">
                                <td class="text-right ref" style="border: 0" colspan="2">Total Discount</td>
                                <th class="text-right ref"><?php echo number_format(abs(($order['order']['discount'] + $distTotal))); ?></th>
                            </tr>
                            <tr class="no-border">
                                <th style="border: 0;" valign="middle" colspan="4">
                                    <center>
                                        <table width="260" class="table" style="border-collapse: collapse;">
                                            <tr>
                                                <td width="130">Current Balance:</td>
                                                <th style="text-align: right"><?php echo number_format(abs(($currentBalance))); ?></th>
                                            </tr>
                                        </table>
                                    </center>
                                </th>
                                <td class="text-right ref" style="border: 0" colspan="2">Amount After Discount</td>
                                <th class="text-right ref"><?php echo number_format($net); ?></th>
                            </tr>
                            <tr class="no-border">
                                <th rowspan="4" style="border: 0;" valign="middle" colspan="4" class="text-left">
                                    Net in words: <?php echo convertNumberToWord($net); ?>
                                </th>
                                <th class="text-right ref" style="border: 0" colspan="2">Amount Paid</th>
                                <th class="text-right ref"><?php echo number_format(abs(($order['order']['payment_amount']))); ?></th>
                            </tr>
                            <?php if (!empty($order['order']['payment_amount'])) { ?>

                                <tr class="no-border">
                                    <th class="text-right ref" style="border: 0" colspan="2">Amount Exchanged</th>
                                    <th class="text-right ref"><?php echo number_format(abs(($order['order']['payment_with_credit']))); ?></th>
                                </tr>
                                <tr class="no-border">
                                    <td class="text-right ref" style="border: 0" colspan="2">Balance</td>
                                    <th class="text-right ref"><?php echo number_format(abs(($balance))); ?></th>
                                </tr>
                            <?php } ?>
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
            window.close();
        }
    </script>
<?php } ?>