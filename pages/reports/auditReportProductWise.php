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
            <th>S. Qty</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1;
        foreach ($orders['rows'] as $s) {
            $saleQty = !empty($s['sale_qty']) ? $s['sale_qty'] : 0;
            $purchaseQty = !empty($s['purchase_qty']) ? $s['purchase_qty'] : 0;
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $s['product_id']; ?></td>
                <td><?php echo $s['full_name']; ?></td>
                <td><?php echo $s['purchase_qty']; ?></td>
                <td><?php echo $s['sale_qty']; ?></td>
                <td><?php echo ($purchaseQty - $saleQty); ?></td>
            </tr>
        <?php $count++;
        } ?>
    </tbody>
</table>