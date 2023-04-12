<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
if (empty($id)) {
    echo "Invalid data";
}
$details = !empty($_GET['detail']) && $_GET['detail'] == 'true' ? true : false;
$largeView = !empty($_GET['largeView']) && $_GET['largeView'] == 'large' ? true : false;
$ordersObj = new Orders();
$order = $ordersObj->getOrder($id);
?>
<link href="https://fonts.googleapis.com/css?family=Courgette&display=swap" rel="stylesheet">
<?php

$foodpanda = $order['customer'];

$gst = 0;
$service_charges = 0;
$price = 0;
$aprice = 0;

if (!empty($order['order']['price'])) {
    $gst = round($order['order']['price'] * ($order['order']['gst'] / 100));
    $service_charges = round($order['order']['price'] * ($order['order']['service_charges'] / 100));
    $price = $order['order']['price'] + $gst + $service_charges;
}

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
$balance = ($price - $order['order']['discount']) - $order['order']['paid_amount'];
if ($largeView) {
    $distTotal = 0;
    $qty = 0; ?>
    <style>
        body {
            margin: 0;
        }

        .recipt {
            font-family: Tahoma;
            text-align: center;
            line-height: 1.5;
            padding-top: 0;
            width: 260px;
            margin: 0 auto;
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
            padding: 8px 0;
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

        .tiny {
            margin: 0.5em 0 .5in
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
            height: 30px;
        }

        .table {
            text-align: left;
            margin-bottom: 20px;
        }
    </style>
    <div class="recipt large">
        <div class="head">
            <h3>
                <div style="padding-top: 10px"><?php echo strtoupper($shop['full_name']); ?>
                    <p class="mt-0"><?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
                        <strong><small><?php echo implode(", ", $result); ?></small></strong>
                    </p>
                    <div>
            </h3>
            <h2>Credit / Cash Sales Invoice</h2>

        </div>
        <table class="table" style="width: 100%">
            <tr>
                <th width="140">Customer Name:</th>
                <th class="border"><?php echo !empty($order['order']['customer_name']) ? $order['order']['customer_name'] : $foodpanda['full_name']; ?></th>
                <th width="120">Date Time:</th>
                <th class="border"><?php echo date('d/m/Y H:i', strtotime($order['order']['created_at'])); ?></th>
            </tr>
            <tr>
                <th>Bill Ref.</th>
                <th class="border">0000<?php echo $_GET['id']; ?></th>
                <th>Contact No.</th>
                <th class="border"><?php if (!empty($foodpanda['phoneNumber'])) { ?><?php echo $foodpanda['phoneNumber']; ?><?php } ?></th>
            </tr>
            <span class="ref"><strong></strong> </span>
            <span class="date"></span>
            <span class="ref"><strong></strong> </span>

        </table>
        <table class="recipt-table" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <th class="text-left thead">Sr.#</th>
                <th class="text-left thead">Code</th>
                <th class="text-left thead">Item</th>
                <th width="80" class="thead">Qty</th>
                <th width="80" class="thead">U. Price</th>
                <th width="80" class="thead">D. Price</th>
                <th width="80" class="thead">D. Total</th>
                <th class="text-right thead" width="100">Total</th>
            </tr>
            <?php foreach ($order['order_items'] as $key => $item) { ?>
                <tr>
                    <td class="text-left"><?php echo $key + 1; ?></td>
                    <td class="text-left" style="padding: 0 6px"><?php echo $item['product_id']; ?></td>
                    <td class="text-left"><?php echo $item['product_title']; ?> <br /><?php echo $item['description']; ?></td>
                    <td><?php echo number_format($item['quantity'], 1); ?></td>
                    <td><?php echo number_format($item['price'], 1); ?></td>
                    <td><?php echo number_format($item['price'] - $item['discount'], 1); ?></td>
                    <td class="text-right"><?php
                                            $aprice += $item['quantity'] * ($item['price']);
                                            $distTotal += $item['quantity'] * ($item['discount']);
                                            $qty += $item['quantity'];
                                            echo number_format($item['quantity'] * ($item['price'] - $item['discount']), 2); ?></td>
                    <td class="text-right"><?php echo number_format($item['quantity'] * ($item['price']), 2); ?></td>
                </tr>
            <?php } ?>
            <tr class="no-border">
                <td rowspan="7" style="line-height: 25px" valign="top" colspan="3">Total Qty</td>
                <td rowspan="7" style="line-height: 25px" valign="top"><strong><?php echo number_format($qty, 1); ?></strong></td>
                <td class="text-right ref" colspan="3">Invoice Total</td>
                <th class="text-right ref"><?php echo number_format($aprice, 2); ?></th>
            </tr>
            <!-- <tr class="no-border">
            <td class="text-right ref" colspan="3">Invoice Total</td>
            <th class="text-right ref"><?php echo number_format($aprice, 2); ?></th>
        </tr> -->
            <tr class="no-border">
                <td class="text-right ref" colspan="3">Additional Discount</td>
                <th class="text-right ref"><?php echo number_format($order['order']['discount'], 2); ?></th>
            </tr>
            <tr class="no-border">
                <td class="text-right ref" colspan="3">Total Discount</td>
                <th class="text-right ref"><?php echo number_format($order['order']['discount'] + $distTotal, 2); ?></th>
            </tr>
            <tr class="no-border">
                <td class="text-right ref" colspan="3">Net Total</td>
                <th class="text-right ref"><?php echo number_format($price - $order['order']['discount'], 2); ?></th>
            </tr>
            <?php if (!empty($order['order']['paid_amount'])) { ?>

                <tr class="no-border">
                    <td class="text-right ref" colspan="3">Deposit</td>
                    <th class="text-right ref"><?php echo number_format($order['order']['paid_amount'], 2); ?></th>
                </tr>
                <tr class="no-border">
                    <td class="text-right ref" colspan="3">Balance 1</td>
                    <th class="text-right ref"><?php echo number_format($balance, 2); ?></th>
                </tr>
            <?php } ?>
        </table>
        <p class="tiny">Power by: Zia ur Rehman Ph.# <strong>03245120412</strong></p>
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

        .tiny {
            font-size: 10px;
            margin: 0.5em 0 .5in
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
                <img width="60" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo strtoupper($shop['image']); ?>" />
                <div style="padding-top: 10px"><?php echo strtoupper($shop['full_name']); ?>
                    <p class="mt-0"><?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
                        <strong><small><?php echo implode(", ", $result); ?></small></strong>
                    </p>
                    <div>
            </h3>

        </div>
        <strong class="pull-left ref">Customer Name: <?php echo !empty($order['order']['customer_name']) ? $order['order']['customer_name'] : $foodpanda['full_name']; ?></strong>
        <div style="clear: both;"></div>
        <span class="pull-left ref">Ref. RSV0<?php echo $_GET['id']; ?></span>
        <span class="pull-right date"><?php echo date('d/m/Y H:i', strtotime($order['order']['created_at'])); ?></span>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <th class="text-left thead">Desciption</th>
                <th width="45" class="thead">Price</th>
                <th width="45" class="thead">D.Price</th>
                <th width="20" class="thead">Qty</th>
                <th class="text-right thead" width="45">Total</th>
                <th class="text-right thead" width="45">D.Total</th>
            </tr>
            <?php
            $totalDist = $order['order']['discount'];
            foreach ($order['order_items'] as $item) {
                if (!empty($order['order']['show_discount'])) {
                    $totalDist += $item['discount'] * $item['quantity'];
                    $aprice += $item['quantity'] * ($item['price']);
                }
            ?>
                <tr style="border: 0">
                    <td class="text-left" colspan="6" style="font-size: 10px"><?php echo $item['product_title']; ?></td>
                </tr>
                <tr style="font-weight: bold">
                    <td class="text-left"></td>
                    <td style="padding: 0 3px"><?php echo ($item['price']); ?></td>
                    <td style="padding: 0 3px"><?php echo ($item['price'] - $item['discount']); ?></td>
                    <td style="padding: 0 3px"><?php echo $item['quantity']; ?></td>
                    <td style="padding: 0 3px" class="text-right"><?php echo number_format($item['quantity'] * ($item['price']), 0); ?></td>
                    <td style="padding: 0 3px" class="text-right"><?php echo number_format($item['quantity'] * ($item['price'] - $item['discount']), 0); ?></td>
                </tr>
            <?php } ?>
            <tr class="no-border">
                <td class="text-right ref" colspan="5">Gross Total</td>
                <td class="text-right ref"><?php echo number_format($aprice, 0); ?></td>
            </tr>
            <tr class="no-border">
                <th class="text-right ref" colspan="5">Disc</th>
                <th class="text-right ref"><?php echo number_format($totalDist, 0); ?></th>
            </tr>
            <tr class="no-border">
                <td class="text-right ref" colspan="5">Net Total</td>
                <th class="text-right ref"><?php echo number_format($price - $order['order']['discount'], 0); ?></th>
            </tr>
            <?php if (!empty($order['order']['paid_amount'])) { ?>

                <tr class="no-border">
                    <td class="text-right ref" colspan="5">Deposit</td>
                    <th class="text-right ref"><?php echo number_format($order['order']['paid_amount'], 0); ?></th>
                </tr>
                <tr class="no-border">
                    <td class="text-right ref" colspan="5">Balance</td>
                    <th class="text-right ref"><?php echo number_format($balance, 0); ?></th>
                </tr>
            <?php } ?>
        </table>
        <p class="tiny">Power by: Zia ur Rehman Ph.# <strong>03245120412</strong></p>
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