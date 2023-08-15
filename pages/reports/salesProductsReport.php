<?php
$totals = ['price' => 0, 'discount' => 0, 'paid' => 0, 'balance' => 0];
?>
<center>
    <h2>Sales Orders</h2>
    <h4>Between <?php echo $from; ?> and <?php echo $to; ?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Date</th>
            <th>Order #</th>
            <th>Customer</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Discount</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1;
        foreach ($orders as $s) {

            $totals['qty'] += $s['quantity'];
            $totals['price'] += $s['price'];
            $totals['discount'] += $s['discount'];

        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php
                    print_r($s['order_date']);
                    echo dateToSimple(date('Y-m-d', strtotime($s['order_date']))); ?></td>
                <td><?php echo $s['order_custom_id']; ?></td>
                <td><?php echo !empty($s['full_name']) ? $s['full_name'] : $s['name']; ?></td>
                <td><?php echo $s['productName']; ?></td>
                <td><?php echo $s['quantity']; ?></td>
                <td><?php echo $s['price']; ?></td>
                <td><?php echo $s['discount']; ?></td>
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
            <th align="left">Total Products</th>
            <td><?php echo $totals['qty']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Discount</th>
            <td><?php echo $totals['discount']; ?></td>
        </tr>
    </table>
</div>