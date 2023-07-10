<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
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
    $blc = $customers->getOpeningBalance($order['customer']['account_id'], 's');
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
if ($largeView) {
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

        @page {
            @bottom-left {
                content: counter(page) "/" counter(pages);
            }
        }
    </style>
    <div class="recipt large">
        <table width="100%">
            <thead>
                <tr>
                    <th>
                        <div class="head text-left">
                            <span class="pull-right">
                                <img width="120" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shop['image']; ?>" />
                            </span>
                            <h3>
                                <div style="padding-top: 10px"><?php echo strtoupper($shop['full_name']); ?>
                                    <p class="mt-0"><?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
                                        <strong><small><?php echo implode(", ", $result); ?></small></strong>
                                    </p>
                                    <div>
                            </h3>
                            <h2 style="text-align: center">Purchase Invoice <?php echo $order['order']['status'] == 1 ? '(Parked Invoice)' : null ?></h2>
                        </div>
                        <?php $net = abs(($price)); ?>
                        <table class="table" style="width: 100%">
                            <tr>
                                <td width="140" class="text-right">Supplier Name:</td>
                                <th><?php echo $foodpanda['full_name']; ?></th>
                                <td width="120" class="text-right">Bill Ref.</td>
                                <th>0000<?php echo $_GET['id'];
                                        if ($order['order']['ref_no']) {
                                        ?> | R:
                                <?php echo $order['order']['ref_no'];
                                        } ?>
                                </th>
                            </tr>
                            <tr>
                                <td class="text-right">Contact No.</td>
                                <th><?php if (!empty($foodpanda['phoneNumber'])) { ?><?php echo $foodpanda['phoneNumber']; ?><?php } ?></th>
                                <td width="120" class="text-right">Date Time:</td>
                                <th><?php echo date('d/m/Y H:i', strtotime($order['order']['created_at'])); ?></th>
                            </tr>
                            <span class="ref"><strong></strong> </span>
                            <span class="date"></span>
                            <span class="ref"><strong></strong> </span>
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
                                    <th width="40" class="text-left thead">Code</th>
                                    <th class="text-left thead">Item</th>
                                    <th width="40" class="thead">Qty</th>
                                    <th width="50" class="thead">U. Price</th>
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
                                        <td class="text-left"><?php echo $item['full_name']; ?></td>
                                        <td class="text-right"><?php echo abs(($item['quantity'])); ?></td>
                                        <td class="text-right"><?php echo abs(($item['price'])); ?></td>
                                        <td class="text-right"><?php echo $item['discount'] . '%'; ?></td>
                                        <td class="text-right"><?php echo $cprice; ?></td>
                                        <td class="text-right"><?php
                                                                $aprice += $item['quantity'] * ($item['price']);
                                                                $distTotal += $item['quantity'] * $discountTotal;
                                                                $qty += $item['quantity'];
                                                                echo abs($item['quantity'] * $cprice); ?></td>
                                    </tr>
                                    <?php if (!empty($item['description'])) { ?>
                                        <tr>
                                            <th colspan="2">--</th>
                                            <td colspan="6" style="text-align: left"><?php echo $item['description']; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                                <tr class="no-border">
                                    <td valign="top" style="border: 0" class="text-right" colspan="3">Total Quantity</td>
                                    <td valign="top" style="border: 0" class="text-right"><strong><?php echo abs(($qty)); ?></strong></td>
                                    <td class="text-right ref" style="border: 0" colspan="3">Invoice Total</td>
                                    <th class="text-right ref"><?php echo abs(($aprice)); ?></th>
                                </tr>
                                <tr class="no-border">
                                    <th rowspan="2" style="border: 0;" valign="middle" colspan="4">
                                        <!-- Important Note: Books once sold nerver be returned or exchanged. -->
                                    </th>
                                    <td class="text-right ref" style="border: 0" colspan="3">Additional Discount</td>
                                    <th class="text-right ref"><?php echo abs(($order['order']['discount'])); ?></th>
                                </tr>
                                <tr class="no-border">
                                    <td class="text-right ref" style="border: 0" colspan="3">Total Discount</td>
                                    <th class="text-right ref"><?php echo abs(($order['order']['discount'] + $distTotal)); ?></th>
                                </tr>
                                <tr class="no-border">
                                    <th style="border: 0;" valign="middle" colspan="4">
                                        <center>
                                            <table width="260" class="table" style="border-collapse: collapse;">
                                                <tr>
                                                    <td width="130">Current Balance:</td>
                                                    <th style="text-align: right"><?php echo abs(($currentBalance)); ?></th>
                                                </tr>
                                            </table>
                                        </center>
                                    </th>
                                    <td class="text-right ref" style="border: 0" colspan="3">Amount After Discount</td>
                                    <th class="text-right ref"><?php echo $net; ?></th>
                                </tr>
                                <tr class="no-border">
                                    <th rowspan="4" style="border: 0;" valign="middle" colspan="4" class="text-left">
                                        Net in words: <?php echo convertNumberToWord($net); ?>
                                    </th>
                                    <th class="text-right ref" style="border: 0" colspan="3">Amount Paid</th>
                                    <th class="text-right ref"><?php echo abs(($order['order']['payment_amount'])); ?></th>
                                </tr>
                                <?php if (!empty($order['order']['payment_amount'])) { ?>

                                    <tr class="no-border">
                                        <th class="text-right ref" style="border: 0" colspan="3">Amount Exchanged</th>
                                        <th class="text-right ref"><?php echo abs(($order['order']['payment_with_credit'])); ?></th>
                                    </tr>
                                    <tr class="no-border">
                                        <td class="text-right ref" style="border: 0" colspan="3">Balance</td>
                                        <th class="text-right ref"><?php echo abs(($balance)); ?></th>
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
<?php
} else {
?>
    <style>
        body {
            margin: 0;
        }

        .recipt {
            font-family: Tahoma;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
            padding-top: 0;
            width: 260px;
            margin: 0 auto;
        }

        h3 {
            margin: 0;
            line-height: 1
        }

        table {
            border-collapse: collapse;
            font-size: 12px;
            font-family: Tahoma;
        }

        tr {
            border-bottom: 1px dashed
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
            padding: 0 0 3px;
        }

        td {
            padding: 3px 0;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .thead {
            font-size: 10px;
            font-weight: 600
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
        .head p {
            font-size: 12px;
        }

        .head {
            margin: 0;
        }

        .pull-left {
            float: left;
        }

        .pull-right {
            float: right;
        }
    </style>
    <div class="recipt">
        <div class="head">
            <h3 style="display: flex; text-align: left;">
                <img width="60" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shop['image']; ?>" />
                <div style="padding-top: 10px"><?php echo strtoupper($shop['full_name']); ?>
                    <p class="mt-0"><?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
                        <strong><small><?php echo implode(", ", $result); ?></small></strong>
                    </p>
                    <div>
            </h3>

        </div>
        <span class="pull-left ref"><span style="font-size: 10px">Customer Name:</span> <strong><?php echo !empty($order['order']['customer_name']) ? $order['order']['customer_name'] : $foodpanda['full_name']; ?></strong></span>
        <div style="clear: both;"></div>
        <span class="pull-left ref">Ref. RSV0<?php echo $_GET['id']; ?></span>
        <span class="pull-right date"><?php echo date('d/m/Y H:i', strtotime($order['order']['created_at'])); ?></span>
        <table width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-left thead"></th>
                    <th width="45" class="thead">Price</th>
                    <th width="45" class="thead">D.Price</th>
                    <th width="20" class="thead">Qty</th>
                    <th class="text-right thead" width="45">Total</th>
                    <th class="text-right thead" width="45">D.Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalDist = $order['order']['discount'];
                foreach ($order['order_items'] as $item) {
                    if (!empty($order['order']['show_discount'])) {
                        $totalDist += $item['discount'] * $item['quantity'];
                        $aprice += $item['quantity'] * ($item['price']);
                    }
                ?>
                    <tr style="border: 0">
                        <td class="text-left" colspan="6" style="font-size: 10px"><strong style="font-weight: 700"><?php echo $item['product_id']; ?></strong> | <?php echo !empty($item['description']) ? $item['description'] : $item['full_name']; ?></td>
                    </tr>
                    <tr style="font-weight: bold">
                        <td class="text-left"></td>
                        <td style="padding: 0 3px"><?php echo abs($item['price']); ?></td>
                        <td style="padding: 0 3px"><?php echo abs($item['price'] - $item['discount']); ?></td>
                        <td style="padding: 0 3px"><?php echo abs($item['quantity']); ?></td>
                        <td style="padding: 0 3px" class="text-right"><?php echo abs(($item['quantity'] * ($item['price']))); ?></td>
                        <td style="padding: 0 3px" class="text-right"><?php echo abs(($item['quantity'] * ($item['price'] - $item['discount']))); ?></td>
                    </tr>
                <?php } ?>
                <tr class="no-border">
                    <td class="text-right ref" colspan="5">Gross Total</td>
                    <td class="text-right ref"><?php echo abs(($aprice)); ?></td>
                </tr>
                <tr class="no-border">
                    <th class="text-right ref" colspan="5">Disc</th>
                    <th class="text-right ref"><?php echo abs(($totalDist)); ?></th>
                </tr>
                <tr class="no-border">
                    <td class="text-right ref" colspan="5">Net Total</td>
                    <th class="text-right ref"><?php echo abs(($price - $order['order']['discount'])); ?></th>
                </tr>
                <?php if (!empty($order['order']['payment_amount'])) { ?>

                    <tr class="no-border">
                        <td class="text-right ref" colspan="5">Deposit</td>
                        <th class="text-right ref"><?php echo abs(($order['order']['payment_amount'])); ?></th>
                    </tr>
                    <tr class="no-border">
                        <td class="text-right ref" colspan="5">Balance</td>
                        <th class="text-right ref"><?php echo abs(($balance)); ?></th>
                    </tr>
                <?php } ?>
                </tfoot>
        </table>
        <footer>
            Powered by: Zia ur Rehman Ph.# <strong>03245120412</strong>
        </footer>
    </div>
<?php } ?>
<?php if (!$details) { ?>
    <script>
        window.print();
        window.onafterprint = function() {
            window.close();
        }
    </script>
<?php } ?>