<?php
$totalDebit = 0;
$totalCredit = 0;


$incomeArray = [];
$costArray = [];
$expArray = [];

ob_start();
?>
<style>
	table {
		font-size: 12px;
	}
	body {
		font: 16pt Arial, sans-serif;
		line-height: 1.3;
	}
	@page {
		margin-top: 25px;
		margin-left: 100px;
		margin-bottom: 25px;
		margin-right: 25px;
		<?php if(!$params['pdf']) { ?>
		size: Legal;
		<?php } ?>
	}

	td {
		padding: 5px 5px;
		<?php if($report == 'dataCollection' || $report == 'idcards_list') { ?>
			height: 32px;
			white-space: nowrap;
		<?php } ?>
	}
	<?php if($largeFont) { ?>
		td {
			font-size: 25px;
			padding: 7px 15px;
			font-family: Calibri, sans-serif;
			white-space: nowrap;
		}

	<?php } ?>
	<?php if($mediumFont) { ?>
		td {
			font-size: 15px;
			padding: 4px 5px;
			font-family: Calibri, sans-serif;
			white-space: nowrap;
		}

	<?php } ?>
	h1 {
		font-size: 20pt;
	}
	<?php if($orientation == 'L' && !$params['pdf']) { ?>
		@page {
			size: Legal landscape;			
		}
	<?php } ?>

</style>

<h1 style="margin: 0; text-align: center;"><?php echo $reportTitle; ?></h1>
<h4 style="margin-top: 10px; text-align: center; font-weight: bold"><?php echo $subtitle; ?></h4>
<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
	<thead>
		<tr>
			<?php if($srNo) { ?>
				<th width="30">S.#</th>
			<?php } ?>
			<th>Account</th>
			<th>Amount</th>
		</tr>
	</thead>
	<tbody>
        <tr>
            <th colspan="3">
                INCOME
            </th>
        </tr>
		<?php 
		
		$totals = [
			'sale' => 0,
			'purchase' => 0,
			'expense' => 0
		];
		
		$count = 1; foreach ($reportData as $s) {
			if($shop['expense'] === $s['parent_id']) {
				$expArray[] = $s;
			}
			?>
			<tr>
			<?php 
                if($shop['assets'] === $s['account_id'] && !empty($s['creditAmount'])) {
					$totals['sale'] += $s['creditAmount'];
					if($srNo) { ?>
						<td width="10"><?php echo $count; ?></td>
					<?php } ?>
						<td><?php echo $s['title']; ?></td>
						<td><?php echo $s['creditAmount']; ?></td>
					<?php
					$count++;
				}
				else if($shop['sale_discount'] === $s['account_id'] && !empty($s['debitAmount'])) {
					$totals['sale'] -= $s['debitAmount'];
					if($srNo) { ?>
						<td width="10"><?php echo $count; ?></td>
					<?php }
					?>
						<td><?php echo $s['title']; ?></td>
						<td>(<?php echo $s['creditAmount']; ?>)</td>
					<?php
					$count++;
				} 
				
				else if($shop['sale_returns'] === $s['account_id'] && !empty($s['debitAmount'])) {
					$totals['sale'] -= $s['debitAmount'];
					if($srNo) { ?>
						<td width="10"><?php echo $count; ?></td>
					<?php }
					?>
						<td><?php echo $s['title']; ?></td>
						<td>(<?php echo $s['debitAmount']; ?>)</td>
					<?php
					$count++;} 
				} ?>
			</tr>
		<?php ?>
        <tr>
            <th colspan="2">Total</th>
            <td><strong><?php echo $totals['sale'];?></strong></td>
        </tr>
        <tr>
            <th colspan="3">
                Purchases
            </th>
        </tr>
		<?php 
        $count = 1; foreach ($reportData as $s) {
		if($shop['assets'] === $s['account_id'] && !empty($s['debitAmount'])) {
			$totals['purchase'] += $s['debitAmount'];
			if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>
				<td><?php echo $s['title']; ?></td>
				<td><?php echo $s['debitAmount']; ?></td>
			<?php
			$count++;
			?> </tr><?php
		}
		else if($shop['purchase_discount'] === $s['account_id'] && !empty($s['creditAmount'])) {
			$totals['purchase'] -= $s['creditAmount'];
			if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php }
			?>
				<td><?php echo $s['title']; ?></td>
				<td>(<?php echo $s['creditAmount']; ?>)</td>
			<?php $count++; ?> </tr><?php
		}
		else if($shop['purchase_returns'] === $s['account_id'] && !empty($s['creditAmount'])) {
			$totals['purchase'] -= $s['creditAmount'];
			if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php }
			?>
				<td><?php echo $s['title']; ?></td>
				<td>(<?php echo $s['creditAmount']; ?>)</td>
			<?php $count++; ?> </tr><?php
		}
		
	}?>
        
            <th colspan="2">Total</th>
            <td><strong><?php echo $totals['purchase'];?></strong></td>
        </tr>
		<!-- <tr>
            <th colspan="2">Gross Sale</th>
            <td><strong><?php echo ($totals['sale'] - $totals['purchase']);?></strong></td>
        </tr> -->
        <tr>
            <th colspan="3">
                Expense
            </th>
        </tr>

        <?php $count = 1; foreach ($expArray as $s) {?>
			<tr>
			<?php if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>
			<td><?php echo $s['title']; ?></td>
			<td><?php echo $s['debitAmount']; ?></td>
            <?php 
				$totals['expense'] += $s['debitAmount'];
 ?>
			</tr>
		<?php $count++;} ?>
	</tbody>
<tfoot>
    <tr>
        <th colspan="2">Total</th>
        <td><strong><?php echo $totals['expense'];?></strong></td>
    </tr>
    <tr>
        <th colspan="2">Profit and Loss Account</th>
        <td><strong><?php 
		
		$totalExpense = $totals['expense'];
		$totalIncome = $totals['sale'];
		$totalPurchase = $totals['purchase'];

		$total = $totalIncome - $totalPurchase - $totalExpense;
		echo $total > 0 ? $total : '('. (-1 * $total).')'
		?></strong></td>
    </tr>
</tfoot>
<?php if(!$params['pdf']) { ?>
<script>
	// window.print();
</script>

<?php 
}

	$html = ob_get_contents();
	ob_clean();



if(!$params['pdf']) {
	echo $html;

}
else {

	

// if($orientation == 'L') {

// 	$mpdf=new mPDF('c','Legal-L');

// }else {

// 	$mpdf=new mPDF('c','Legal');
// }

echo $html;
exit;


	$mpdf->WriteHTML($html);

	$mpdf->Output();
	exit;


}	

?>

