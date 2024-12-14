<?php

$array = [];
$types = [];
$total = 0;
$qty = 0;

$customerDateWise = [];

$initialValue = [
    "total_amount" => 0,
    "total_paid" => 0,
    "total_discount" => 0,
    "total_balance" => 0,
    "orders" => []
];

foreach ($orders as $key => $value) {
    $customerDateWise[$value['customer_id']] = empty($customerDateWise[$value['customer_id']]) ? $initialValue : $customerDateWise[$value['customer_id']];
    $customerDateWise[$value['customer_id']][$value['return_date']] = empty($customerDateWise[$value['customer_id']][$value['return_date']]) ? [] : $customerDateWise[$value['customer_id']][$value['return_date']];


    $customerDateWise[$value['customer_id']][$value['return_date']][] = $value;
    
    
    $array[$value['product_id']][$value['type']] = $value;
    $types[$value['type']] = !empty($types[$value['type']]) ? $types[$value['type']] : [];
    $types[$value['type']]['discount'] = !empty($types[$value['type']]['discount']) ? $types[$value['type']]['discount'] : 0;
    $types[$value['type']]['qty'] = !empty($types[$value['type']]['qty']) ? $types[$value['type']]['qty'] : 0;
    $types[$value['type']]['total'] = !empty($types[$value['type']]['total']) ? $types[$value['type']]['total'] : 0;
    $types[$value['type']]['qty'] += $value['quantity'];
    $types[$value['type']]['discount'] += $value['discount'];
    $types[$value['type']]['total'] += $value['quantity'] * $value['price'];
    $qty += $value['quantity'];
    $total += ($value['quantity'] * $value['price']);
}
?>
<center>
    <h2>Sales Orders Product Wise</h2>
    <h4>Between <?php echo $from; ?> and <?php echo $to; ?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th width="60">Sr.#</th>
            <th width="190">R.Order ID</th>
            <th width="190">Product ID</th>
            <th>Product Title</th>
            <th>Customer</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Disc.</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1; foreach ($orders as $id => $row) { ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['full_name']; ?></td>
                <td><?php echo $row['customer_name']; ?></td>
                <th><?php echo $row['price']; ?></th>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['discount']; ?></td>
                <th><?php echo $row['quantity'] * $row['price'] - $row['discount']; ?></th>
            </tr>
        <?php $count++;
        } ?>
    </tbody>
</table>

<div style="width: 45%">
    <h3>Summery</h3>
    <table class="table">
        <?php foreach ($types as $type => $row) { ?>
            <tr>
                <th align="left" width="260"><?php echo $returnArray[$type]['title']; ?></th>
                <th><?php echo $row['qty']; ?></th>
                <th><?php echo $row['total']; ?></th>
            </tr>
        <?php } ?>
        <tr>
            <th align="left">Grand Total</th>
            <th><?php echo $qty; ?></th>
            <th><?php echo $total; ?></th>
        </tr>
    </table>
</div>