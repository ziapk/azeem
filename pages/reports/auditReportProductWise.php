<center>
    <h2>Audit Report Product Wise</h2>
    <h4>Between <?php echo $from; ?> and <?php echo $to; ?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Product ID</th>
            <th>Product Title</th>
            <th>P. Qty</th>
            <th>P. Return</th>
            <th>Balance</th>
            <th>S. Qty</th>
            <th>S. Return</th>
            <th>Balance</th>
            <th>Audit Qty</th>
            <th>In Stock</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1;
        foreach ($orders['rows'] as $s) {
            $saleQty = !empty($s['sale_qty']) ? $s['sale_qty'] : 0;
            $saleReturn = !empty($s['sale_return']) ? $s['sale_return'] : 0;
            $purchaseQty = !empty($s['purchase_qty']) ? $s['purchase_qty'] : 0;
            $purchaseReturn = !empty($s['purchase_return']) ? $s['purchase_return'] : 0;
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $s['product_id']; ?></td>
                <td><?php echo $s['full_name']; ?></td>
                <td><?php echo $s['purchase_qty']; ?></td>
                <td><?php echo $s['purchase_return']; ?></td>
                <td><?php echo $purchaseQty - $purchaseReturn; ?></td>
                <td><?php echo $s['sale_qty']; ?></td>
                <td><?php echo $s['sale_return']; ?></td>
                <td><?php echo $saleQty - $saleReturn; ?></td>
                <td><?php echo (($purchaseQty - $purchaseReturn) - ($saleQty - $saleReturn)); ?></td>
                <td><?php echo $s['in_hand']; ?></td>
            </tr>
        <?php $count++;
        } ?>
    </tbody>
</table>