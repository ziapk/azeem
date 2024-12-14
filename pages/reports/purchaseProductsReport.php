<?php
$totals = ['price' => 0, 'samples_qty' => 0, 'samples' => 0, 'discount' => 0, 'paid' => 0, 'balance' => 0];

$groupOrders = [];
foreach($orders['rows'] as $row) {
    $c = !empty($row['customerName']) ? $row['customerName'] : $row['supplierName'];
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
            <th>Product ID</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Discount</th>
            <th>P.Price</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1;
        foreach ($groupOrders as $name => $rows) {
            ?>
            <tr>
                <th colspan="7"><?php echo $name; ?></th>
            </tr>
            <?php
            foreach ($rows as $s) {
                    
                $totals['discount'] += $s['discount'];
                $totals['price'] += $s['price'];
                $totals['pprice'] += $s['pprice'];
                $totals['qty'] += $s['quantity'];

            ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $s['product_id']; ?></td>
                    <td><?php echo $s['full_name']; ?></td>
                    <td><?php echo $s['quantity']; ?></td>
                    <td><?php echo $s['price']; ?></td>
                    <td><?php echo $s['discount']; ?></td>
                    <td><?php echo $s['pprice']; ?></td>
                </tr>
            <?php $count++;
            }
        } ?>
    </tbody>
</table>


<div style="width: 40%">
    <h3>Summery</h3>
    <table class="table">
        <tr>
            <th align="left">Total Orders</th>
            <td align="right"><?php echo $orders['summery']['total']; ?></td>
        </tr>
        <tr>
            <th align="left">Total Price</th>
            <td align="right"><?php echo number_format($totals['price']); ?></td>
        </tr>
        <tr>
            <th align="left">Products</th>
            <td align="right"><?php echo sizeof($orders['rows']); ?></td>
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
            <td align="right"><?php echo number_format($totals['pprice']); ?></td>
        </tr>
    </table>
</div>