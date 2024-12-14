<?php
$totals = ['price' => 0, 'discount' => 0, 'paid' => 0, 'balance' => 0];

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
            <th>Customer</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>P.Price</th>
            <th>Discount</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1;
        foreach ($orders as $s) {

            $totals['price'] += $s['price'];
            $totals['discount'] += $s['discount'];
            $totals['paid'] += $s['paid_amount'];
            $totals['balance'] += ($s['price'] - $s['discount'] - $s['paid_amount']);
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo dateToSimple(date('Y-m-d', strtotime($s['order_date']))); ?></td>
                <td><?php echo $s['order_custom_id']; ?></td>
                <td><?php echo $s['product_id']; ?></td>
                <td><?php echo !empty($s['customer_name']) ? $s['customer_name'] : (!empty($s['full_name']) ? $s['full_name'] : $s['name']); ?></td>
                <td><?php echo !empty($s['productName']) ? $s['productName'] : (!empty($s['full_name']) ? $s['full_name'] : $s['productName']); ?></td>
                <td><?php echo $s['quantity']; ?></td>
                <td><?php echo $s['price']; ?></td>
                <td><?php echo $s['pprice']; ?></td>
                <td><?php echo $s['discount']; ?></td>
                <td><?php echo $s['tprice']; ?></td>
            </tr>
        <?php $count++;
        } ?>
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
        <tr>
            <th align="left">Total Paid</th>
            <td><?php echo $totals['paid']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Balance</th>
            <td><?php echo $totals['balance']; ?></td>
        </tr>
    </table>
</div>