<center>
    <h2>Audit Report Product Wise</h2>
    <h3><?php echo !empty($reportTitle) ? $reportTitle : 'Overall Result'; ?></h3>
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

        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $s['product_id']; ?></td>
                <td><?php echo $s['full_name']; ?></td>
                <td><?php echo $s['purchase_qty']; ?></td>
                <td><?php echo $s['purchase_return']; ?></td>
                <td><?php echo $s['purchase_balance']; ?></td>
                <td><?php echo $s['sale_qty']; ?></td>
                <td><?php echo $s['sale_return']; ?></td>
                <td><?php echo $s['sale_balance']; ?></td>
                <td><?php echo $s['audit_qty']; ?></td>
                <td><?php echo $s['in_hand']; ?></td>
            </tr>
        <?php $count++;
        } ?>
    </tbody>
</table>