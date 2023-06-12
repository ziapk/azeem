<?php

?>
<center>
    <h2>Inventory Report</h2>
    <h2><?php echo $selectShop['full_name']; ?></h2>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Product ID</th>
            <th>Product</th>
            <th>In Hand</th>
            <th>Price</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = 1;
        foreach ($orders as $s) {
            $in_hand = $s['qty'] - $s['stock_out'];
            $totals['detail'][$s['details']] = !empty($totals['detail'][$s['details']]) ? $totals['detail'][$s['details']] : 0;
            $totals['detail'][$s['details']] += $s['price'];
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo $s['full_name']; ?>
                    <?php echo $s['author'] . " - " . $s['group'] . " - " . $s['publisherName']; ?>
                </td>
                <td><?php echo $in_hand; ?></td>
                <td><?php echo $s['price']; ?></td>
                <td><?php echo $s['location']; ?></td>
            </tr>
        <?php $count++;
        } ?>
    </tbody>
</table>