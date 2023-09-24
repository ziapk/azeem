<center>
    <h2>Sales Orders Product Wise</h2>
    <h4>Between <?php echo $from; ?> and <?php echo $to; ?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Product ID</th>
            <th>Product Title</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1;
        foreach ($orders['rows'] as $s) {
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $s['product_id']; ?></td>
                <td><?php echo $s['full_name']; ?></td>
                <td><?php echo $s['price']; ?></td>
                <td><?php echo $s['quantity']; ?></td>
                <td><?php echo $s['price'] * $s['quantity']; ?></td>
            </tr>
        <?php $count++;
        } ?>
    </tbody>
</table>

<?php
$summery = $orders['summery'];
?>
<div style="width: 40%">
    <h3>Summery</h3>
    <table class="table">
        <tr>
            <th align="left">Total Orders</th>
            <td><?php echo $summery['total']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Price</th>
            <td><?php echo $summery['gross']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Discount</th>
            <td><?php echo $summery['dist']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Paid</th>
            <td><?php echo $summery['paid']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Balance</th>
            <td><?php echo $summery['balance']; ?></td>
        </tr>
    </table>
</div>