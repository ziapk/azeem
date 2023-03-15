<?php
$totalDebit = 0;
$totalCredit = 0;


$incomeArray = [];
$costArray = [];
$expArray = [];

foreach ($reportData as $key => $value) {
  if($value['account_type'] == 4) {
    $incomeArray[] = $value;
  }
  elseif($value['account_type'] == 1) {
    $costArray[] = $value;
  }
  else {
    $expArray[] = $value;
  }
};

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
			<?php foreach ($headers as $value){ ?>
				<th><?php echo $value; ?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
        <tr>
            <th colspan="7">
                INCOME
            </th>
        </tr>
		<?php 
		
		$totals = [
			'debit' => [
				'sale' => 0
			],
			'credit' => [
				'sale' => 0
			]
		];
		
		$count = 1; foreach ($incomeArray as $s) {?>
			<tr>
			<?php if($srNo) { 
                
                ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>

				<?php 
                
            	$totals['debit']['sale'] += $s['debitAmount'];
                $totals['credit']['sale'] += $s['creditAmount'];
                
                foreach ($columns as $value){ 
                    $val = '';
            
                    
                    $val = $s[$value];

                    ?>

					<?php if($value == '_blank') { ?>

						<td width="300"><?php echo $val; ?></td>

					<?php } elseif($value == '_blank_normal2') { ?>
						<td width="170"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal3') { ?>
						<td width="370"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal') { ?>
						<td><?php echo $val; ?></td>
					<?php } elseif($value == 'transaction_date') { ?>
						<td><?php echo settings::dateToSimple($val); ?></td>
					<?php } else { ?>

						<td><?php echo $val; ?></td>

					<?php } ?>

				<?php } ?>
			</tr>
		<?php $count++;} ?>
        <tr>
            <th colspan="3">Total</th>
            <td><strong><?php echo $totals['debit']['sale'];?></strong></td>
            <td><strong><?php echo $totals['credit']['sale'];?></strong></td>
        </tr>
        <tr>
            <th colspan="7">
                Cost
            </th>
        </tr>
		<?php 
        
        
        $count = 1; foreach ($costArray as $s) {?>
			<tr>
			<?php if($srNo) { 
                
                ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>

				<?php 
                $totals['debit']['purchase'] += $s['debitAmount'];
                $totals['credit']['purchase'] += $s['creditAmount'];
                
                foreach ($columns as $value){ 
                    $val = '';
            
                    
                    $val = $s[$value];

                    ?>

					<?php if($value == '_blank') { ?>

						<td width="300"><?php echo $val; ?></td>

					<?php } elseif($value == '_blank_normal2') { ?>
						<td width="170"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal3') { ?>
						<td width="370"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal') { ?>
						<td><?php echo $val; ?></td>
					<?php } elseif($value == 'transaction_date') { ?>
						<td><?php echo settings::dateToSimple($val); ?></td>
					<?php } else { ?>

						<td><?php echo $val; ?></td>

					<?php } ?>

				<?php } ?>
			</tr>
		<?php $count++;} ?>
        <tr>
            <th colspan="3">Total</th>
            <td><strong><?php echo $totals['debit']['purchase'];?></strong></td>
            <td><strong><?php echo $totals['credit']['purchase'];?></strong></td>
        </tr>
        <tr>
			<th colspan="3" rowspan="2">Gross Sale</th>
            <th>INCOME TOTAL</th>
			<td><strong><?php echo $totals['debit']['sale'] - $totals['credit']['sale'];?></strong></td>
		</tr>
		<tr>
            <th>COST TOTAL</th>
            <td><strong><?php echo $totals['debit']['purchase'] - $totals['credit']['purchase'];?></strong></td>
        </tr>
        <?php if(sizeof($expArray)) {?>
        <tr>
            <th colspan="7">
                Expense
            </th>
        </tr>
        <?php } ?>

        <?php $count = 1; foreach ($expArray as $s) {?>
			<tr>
			<?php if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>

            <?php 
				$totals['debit']['expense'] += $s['debitAmount'];
                $totals['credit']['expense'] += $s['creditAmount'];


                foreach ($columns as $value){ 
                    $val = '';
                    $val = $s[$value];
    
                ?>

					<?php if($value == '_blank') { ?>

						<td width="300"><?php echo $val; ?></td>

					<?php } elseif($value == '_blank_normal2') { ?>
						<td width="170"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal3') { ?>
						<td width="370"><?php echo $val; ?></td>
					<?php } elseif($value == '_blank_normal') { ?>
						<td><?php echo $val; ?></td>
					<?php } elseif($value == 'transaction_date') { ?>
						<td><?php echo settings::dateToSimple($val); ?></td>
					<?php } else { ?>

						<td><?php echo $val; ?></td>

					<?php } ?>

				<?php } ?>
			</tr>
		<?php $count++;} ?>
	</tbody>
<tfoot>
    <tr>
        <th colspan="3">Total</th>
        <td><strong><?php echo $totalDebit;?></strong></td>
        <td><strong><?php echo $totalCredit;?></strong></td>
    </tr>
    <tr>
        <th colspan="3">Surplus/Deficit</th>
        <th>Credit - Debit</th>
        <td><strong><?php echo $totalCredit - $totalDebit;?></strong></td>
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

