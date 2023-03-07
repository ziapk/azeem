<?php
$totals = ['price' => 0, 'qty' => 0];
?>
<center>
<?php if(!empty($inventoryReport)) { ?>
    <h2>Return to Inventory Products</h2>
<?php } elseif(!empty($faultyReport)) { ?>
    <h2>Return as Faulty Products</h2>
<?php }
elseif(!empty($lahoreReport)) { ?>
    <h2>Returned to Lahore Products</h2>
<?php }?>
<h4>Between <?php echo $from;?> and <?php echo $to;?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Product</th>
            <th>Qty.</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    <?php $count = 1; foreach ($orders as $s) {
        $totals['qty'] += $s['quantity'];
        $totals['price'] += $s['total'];
        ?>
        <tr>
            <td><?php echo $count;?></td>
            <td><?php echo $s['product_name'];?></td>
            <td><?php echo $s['quantity'];?></td>
            <td><?php echo $s['price'];?></td>
            <td><?php echo $s['total'];?></td>
        </tr>
	<?php $count++;} ?>
    </tbody>
</table>
<div style="width: 40%">
    <h3>Summery</h3>
    <table class="table">
        <tr>
            <th align="left">Total Products</th>
            <td><?php echo $totals['qty'];?></td>
        </tr>
        <tr>
            <th align="left">Total Price</th>
            <td><?php echo $totals['price'];?></td>
        </tr>
    </table>
</div>