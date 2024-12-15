<?php
$totals = ['price' => 0, 'discount' => 0, 'paid' => 0, 'balance' => 0];

$groupOrders = [];
foreach($orders as $row) {
    $c = !empty($row['customer_name']) ? $row['customer_name'] : (!empty($row['full_name']) ? $row['full_name'] : $row['name']);
    $groupOrders[$c][] = $row;
}

?>
<center>
    <h2><?php echo !empty($reportTitle) ? $reportTitle : "Sales Orders"; ?></h2>
    <h4>Between <?php echo $from; ?> and <?php echo $to; ?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Date</th>
            <th>Order #</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Discount</th>
            <th>P.Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
         <?php 
        foreach ($groupOrders as $name => $rows) {
            $count = 1;
            ?>
            <tr>
                <th colspan="8" style="text-align: left"><?php echo $name; ?></th>
            </tr>
            <?php
            foreach ($rows as $s) {

            $totals['price'] += $s['tpprice'];
            $totals['discount'] += $s['tdiscount'];
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo dateToSimple(date('Y-m-d', strtotime($s['order_date']))); ?></td>
                <td><?php echo $s['order_custom_id']; ?></td>
                <td><?php echo $s['product_id']; ?></td>
                <td><?php echo !empty($s['productName']) ? $s['productName'] : (!empty($s['full_name']) ? $s['full_name'] : $s['productName']); ?></td>
                <td><?php echo $s['quantity']; ?></td>
                <td><?php echo $s['price']; ?></td>
                <td><?php echo $s['discount']; ?></td>
                <td><?php echo $s['pprice']; ?></td>
                <td><?php echo $s['tpprice']; ?></td>
            </tr>
        <?php $count++;
        }} ?>
    </tbody>
</table>
<div style="width: 40%">
    <h3>Summery</h3>
    <table class="table">
        <tr>
            <th align="left">Total Orders</th>
            <td><?php echo sizeof($orders); ?></td>
        </tr>
        <tr>
            <th align="left">Total Price</th>
            <td><?php echo $totals['price']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Discount</th>
            <td><?php echo $totals['discount']; ?></td>
        </tr>
    </table>
</div>