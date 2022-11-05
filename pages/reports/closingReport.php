<?php
$totals = ['price' => 0];

$final = [];


foreach ($orders['rows'] as $value) {
    $final[$value['order_date']]['paid'] += $value['paid_amount'];
    $final[$value['order_date']]['discount'] += $value['discount'];
    $final[$value['order_date']]['amount'] += $value['price'];
}
foreach ($expenses as $value) {
    $final[$value['exp_date']]['expense'] += $value['price'];
}
?>
<center>
    <h2>Closing Balance Report (Date Wise)</h2>
    <h4>Between <?php echo $from;?> and <?php echo $to;?></h4>
</center>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Date</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Paid</th>
            <th>Expense</th>
            <th>Closing Balance</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    $totals = [];
    $count = 1; foreach ($final as $date => $row) { 
        
        $expense = !empty($row['expense']) ? $row['expense'] : 0;
        $paid = !empty($row['paid']) ? $row['paid'] : 0;
        $discount = !empty($row['discount']) ? $row['discount'] : 0;
        $amount = !empty($row['amount']) ? $row['amount'] : 0;

        $totals['expense'] += $expense;
        $totals['paid'] += $paid;
        $totals['discount'] += $discount;
        $totals['amount'] += $amount;
        
        ?>
        <tr>
            <td><?php echo $count;?></td>
            <td><?php echo $date;?></td>
            <td align="center"><?php echo !empty($amount) ? $amount : '-';?></td>
            <td align="center"><?php echo !empty($discount) ? $discount : '-';?></td>
            <td align="center"><?php echo !empty($paid) ? $paid : '-';?></td>
            <td align="center"><?php echo !empty($expense) ? $expense : '-';?></td>
            <td align="center"><?php echo $paid - $expense;?></td>
        </tr>
	<?php $count++;} ?>
    <tr>
        <th colspan="2">Total</th>
        <th><?php echo $totals['amount'];?></th>
        <th><?php echo $totals['discount'];?></th>
        <th><?php echo $totals['paid'];?></th>
        <th><?php echo $totals['expense'];?></th>
        <th><?php echo $totals['paid'] - $totals['expense'];?></th>
    </tr>
    </tbody>
</table>