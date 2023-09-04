<?php
$totals = ['price' => 0, 'samples_qty' => 0, 'samples' => 0, 'discount' => 0, 'paid' => 0, 'balance' => 0];
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
            $tt = $s['price'] - $s['discount'];

            if ($tt == 0) {
                $totals['samples'] += $s['price'] * $s['quantity'];
                $totals['samples_qty'] += $s['quantity'];
            } else {
                $totals['discount'] += $s['discount'] * $s['quantity'];
                $totals['price'] += $s['price'] * $s['quantity'];
                $totals['qty'] += $s['quantity'];
            }

        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo dateToSimple(date('Y-m-d', strtotime($s['order_date']))); ?></td>
                <td><?php echo $s['order_custom_id']; ?></td>
                <td><?php echo !empty($s['full_name']) ? $s['full_name'] : $s['name']; ?></td>
                <td><?php echo $s['productName']; ?></td>
                <td><?php echo $s['quantity']; ?></td>
                <td><?php echo $s['price']; ?></td>
                <td><?php echo $s['discount']; ?></td>
                <td><?php echo ($s['price'] - $s['discount']) * $s['quantity']; ?></td>
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
            <td align="right"><?php echo sizeof($orders); ?></td>
        </tr>
        <tr>
            <th align="left">Total Price</th>
            <td align="right"><?php echo number_format($totals['price']); ?></td>
        </tr>
        <tr>
            <th align="left">Total Products</th>
            <td align="right"><?php echo $totals['qty']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Discount</th>
            <td align="right"><?php echo number_format($totals['discount']); ?></td>
        </tr>
        <tr>
            <th align="left">Net Total</th>
            <td align="right"><?php echo number_format($totals['price'] - $totals['discount']); ?></td>
        </tr>
        <tr style="color: red">
            <th align="left">Samples Total</th>
            <td align="right"><?php echo number_format($totals['samples']); ?></td>
        </tr>
        <tr style="color: red">
            <th align="left">Samples Qty</th>
            <td align="right"><?php echo $totals['samples_qty']; ?></td>
        </tr>
    </table>
</div>