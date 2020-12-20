<?php
include_once dirname(__FILE__).'/../include/settings.php';
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
if(empty($id)) {
    echo "Invalid data";
}
$details = !empty($_GET['detail']) && $_GET['detail'] == 'true' ? true : false;
 $ordersObj = new Orders();
$order = $ordersObj->getOrder($id);
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
    h4 {
        margin: 0; 
    }
    table {
        border-collapse: collapse;
        font-size: 12px;
        font-family: Tahoma;
}
tr {
    border-bottom: 1px dashed
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
    margin: 0.5em 0
}
.mt-5 {
    margin-top: 5px;
    margin-bottom: 5px;
}
.head h4 {
    font-family: 'Courgette', cursive;
}
.ref, .date,
.head p {
    font-size: 11px;
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
<link href="https://fonts.googleapis.com/css?family=Courgette&display=swap" rel="stylesheet">
<?php

    $foodpanda = [];
    if($order['customer']['type'] == 2) {
        $foodpanda = $order['customer'];
    }

    

    $a = [];
    array_push($a, $shopData['phone_1'], $shopData['phone_2']);
    $result = array_filter( $a, 'strlen' );
?>
<div class="recipt">
    <div class="head">
        <h4><?php echo strtoupper($shopData['product_title']);?></h4>
        <p class="mt-5"><?php echo $shopData['address'];?> <br> 
            <strong><small><?php echo implode(", ", $result);?></small></strong>
        </p>
    </div>
    <span class="pull-left ref">Ref. TT0<?php echo $_GET['id'];?></span>
    <span class="pull-right date"><?php echo date('d/m/Y H:i', strtotime($order['order']['created_at']) );?></span>
    <?php if(!empty($foodpanda)) { ?>
    <div style="clear: both">
        <span class="pull-left ref">Name: <?php echo $foodpanda['full_name'];?></span>
        <?php if(!empty($foodpanda['phoneNumber'])) { ?> <span class="pull-right date">Phone: <?php echo $foodpanda['phoneNumber'];?></span> <?php } ?>
    </div>
    <div style="clear: both">
        <?php if(!empty($foodpanda['address'])) { ?> <span class="pull-left ref">Address: <?php echo $foodpanda['address'];?></span> <?php } ?>
    </div>
    <?php } ?>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <th class="text-left thead">Desciption</th>
            <th width="20" class="thead">Qty</th>
            <th width="45" class="thead">Price</th>
            <th class="text-right thead" width="45">Total</th>
        </tr>
        <?php foreach($order['order_items'] as $item) { ?>
        <tr>
            <td class="text-left"><?php echo $item['product_title']; ?></td>
            <td><?php echo number_format($item['quantity'], 2); ?></td>
            <td><?php echo $item['price']; ?></td>
            <td class="text-right"><?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
        </tr>
        <?php } ?>
        <tr class="no-border">
            <td class="text-right ref" colspan="3">Gross Total</td>
            <td class="text-right ref"><?php echo number_format($order['order']['price'], 2);?></td>
        </tr>
        <tr class="no-border">
            <th class="text-right ref" colspan="3">Disc</th>
            <th class="text-right ref"><?php echo number_format($order['order']['discount'], 2);?></th>
        </tr>
        <tr class="no-border">
            <td class="text-right ref" colspan="3">Net Total</td>
            <th class="text-right ref"><?php echo number_format($order['order']['price'] - $order['order']['discount'], 2);?></th>
        </tr>
        <?php if($order['order']['paid_amount']) {?>
            <tr class="no-border">
                <td class="text-right ref" colspan="3">Deposit</td>
                <th class="text-right ref"><?php echo number_format($order['order']['deposit'], 2);?></th>
            </tr>
            <tr class="no-border">
                <td class="text-right ref" colspan="3">Balance</td>
                <th class="text-right ref"><?php echo number_format( $order['order']['deposit'] - $order['order']['price'] - $order['order']['discount'], 2);?></th>
            </tr>
        <?php } ?>
    </table>
    <p class="tiny">Power by:  Zia ur Rehman  Ph.# <strong>03245120412</strong></p>
</div>
<?php if(!$details) {?>
<script>
 window.print();
 window.onafterprint = function(){
    window.close();
}
</script>
<?php }?>