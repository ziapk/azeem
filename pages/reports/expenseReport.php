<?php
$totals = ['price' => 0];

?>
<center>
    <h2>Expense Report</h2>
    <h4>Between <?php echo $from;?> and <?php echo $to;?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Product</th>
            <th>Description</th>
            <th>Group</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    <?php $count = 1; foreach ($expenses as $s) {
        $totals['price'] += $s['price'];
        $totals['detail'][$s['details']] = !empty($totals['detail'][$s['details']]) ? $totals['detail'][$s['details']] : 0;
        $totals['detail'][$s['details']] += $s['price'];
        ?>
        <tr>
            <td><?php echo $count;?></td>
            <td><?php echo $s['title'];?></td>
            <td><?php echo $s['description'];?></td>
            <td><?php echo $s['details'];?></td>
            <td><?php echo $s['price'];?></td>
            <td><?php echo $s['exp_date'];?></td>
        </tr>
	<?php $count++;} ?>
    </tbody>
</table>
<div style="width: 40%">
    <h3>Summery</h3>
    <table class="table">
        <tr>
            <th align="left">Total Price</th>
            <td><?php echo $totals['price'];?></td>
        </tr>
        <?php foreach($totals['detail'] as $key => $val) {?>
          <tr>
            <th align="left"><?php echo $key;?></th>
            <td><?php echo $val;?></td>
          </tr>
        <?php }?>
    </table>
</div>