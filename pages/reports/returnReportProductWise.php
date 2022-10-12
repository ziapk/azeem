
<?php

$array = [];
$types = [];
$total = 0;
$qty = 0;
foreach ($orders as $key => $value) {
    $array[$value['product_id']][$value['type']] = $value;
    $types[$value['type']] = !empty($types[$value['type']]) ? $types[$value['type']] : [];
    $types[$value['type']]['qty'] = !empty($types[$value['type']]['qty']) ? $types[$value['type']]['qty'] : 0;
    $types[$value['type']]['total'] = !empty($types[$value['type']]['total']) ? $types[$value['type']]['total'] : 0;
    $types[$value['type']]['qty'] += $value['quantity'];
    $types[$value['type']]['total'] += $value['quantity'] * $value['price'];
    $qty += $value['quantity'];
    $total += $value['quantity'] * $value['price'];
}
?>
<center>
<h2>Sales Orders Product Wise</h2>
<h4>Between <?php echo $from;?> and <?php echo $to;?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th width="60">Sr.#</th>
            <th width="190">Product ID</th>
            <th>Product Title</th>
            <?php foreach($types as $row => $quantity) {?>
                <th><?php echo $returnArray[$row]['title'];?></th>
            <?php } ?>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
    <?php $count = 1; foreach ($array as $id => $rows) { $keys = array_keys($rows);?>
        <tr>
            <td><?php echo $count;?></td>
            <td><?php echo $rows[$keys[0]]['product_id'];?></td>
            <td><?php echo $rows[$keys[0]]['full_name'];?></td>
            <?php foreach ($types as $type => $quantity) { ?>
                <th><?php if(!empty($rows[$type])) { echo $rows[$type]['quantity']; } else { echo '--'; }?></th>
            <?php } ?>
            <th><?php echo $rows[$keys[0]]['price'];?></th>
        </tr>
	<?php $count++;} ?>
    </tbody>
</table>

<div style="width: 45%">
    <h3>Summery</h3>
    <table class="table">
        <?php foreach ($types as $type => $row) { ?>
            <tr>
                <th align="left" width="260"><?php echo $returnArray[$type]['title'];?></th>
                <th><?php echo $row['qty'];?></th>
                <th><?php echo $row['total'];?></th>
            </tr>
        <?php } ?>
        <tr>
            <th align="left">Grand Total</th>
            <th><?php echo $qty;?></th>
            <th><?php echo $total;?></th>
        </tr>
    </table>
</div>