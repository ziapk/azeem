<?php 

include_once dirname(__FILE__).'/../../include/settings.php';
// include_once dirname(__FILE__).'/../../mpdf/mpdf.php';



$doubleEntry = new DoubleEntry();

$params = array();
$from = $_POST['from'];
$to = $_POST['to'];
$params['shopId'] = isset($_POST['shopId']) && !empty(trim($_POST['shopId'])) ? $_POST['shopId'] : $shop['id'];
$params['fromDate'] = isset($from) && !empty(trim($from)) ? $from : '';
$params['toDate'] = isset($to) && !empty(trim($to)) ? $to : '';
$report 		= isset($_GET['report']) && !empty(trim($_GET['report'])) ? $_GET['report'] : '';

$headers 		= array();
$columns 		= array();
$orientation 	= 'P';
$largeFont 		= false;
$srNo 			= true;
$mediumFont		= false;
$storeObj = new Store();
$store = $storeObj->getStore($params['shopId']);
$reportTitle = $shop['full_name'] . ' - ' . $shop['city'];

$subtitle = " Between ".$params['fromDate']." and ".$params['toDate'];

//$time = new datetime('Y');
$d = new DateTime('Y');

$reportData = [];



switch ($reportType) {
	case '10':
		$params['parent_ids'][] = $store['receivable'];
		$params['account_ids'][] = $store['sale_discount'];
		$params['parent_ids'][] = $store['expense'];
		
		$reportDataRaw = $doubleEntry->getClosingBalanceReport($params);

		// $rece = [];
		// foreach ($reportData as $key => $value) {
		// 	if($value['parent_id'] === $store['receivable']) {
		// 		$rece[$value['id']][] = $value;
		// 	}
		// }
        // // print_r($reportData);exit;
		$rows = [];
		$exp = $store['sale_discount'];
		$expHead = $store['expense'];
		$count = 0;
		$expenses = ['total' => 0, 'rows' => []];
		$final = [];
		// print_r($shop['expense']);
		// print_r($reportDataRaw);
		foreach ($reportDataRaw as $key => $value) {
			if($value['account_id'] != $exp && $value['parent_id'] != $expHead) {
				if($value['entry_type'] == 'D') {
					$rows[$value['transaction_date']][$value['transaction_id']]['row'] = $value;
					$rows[$value['transaction_date']][$value['transaction_id']]['totalCredit'] += $value['amount'];
				}
				else {
					$rows[$value['transaction_date']][$value['transaction_id']]['row'] = $value;
					$rows[$value['transaction_date']][$value['transaction_id']]['totalPaid'] += $value['amount'];
				}
			}
			else if($value['account_id'] == $exp) {
				$rows[$value['transaction_date']][$value['transaction_id']]['discount'] += $value['amount'];
			}
			else if($value['parent_id'] == $expHead) {
				$expenses['rows'][$value['transaction_date']]['row'][$value['account_id']] = $value;
				$expenses['rows'][$value['transaction_date']]['total'] += $value['amount'];
				$expenses['total'] += $value['amount'];
			}
			
			// $rows[$value['transaction_date']][$value['transaction_id']][$value['entry_type']]['total'] += $value['amount'];
			$count++;
		}
		// $rows2 = [];
		$totals = ['expense' => 0, 'gross' => 0, 'net' => 0];
		$count = 0;
		$reportData1 = [];
		foreach ($rows as $date => $transactions) {
			foreach ($transactions as $transactionId => $transactions) {
				$final[$date][$transactions['row']['account_id']]['id'] = $transactions['row']['transaction_id'];
				$final[$date][$transactions['row']['account_id']]['code'] = $transactions['row']['code'];
				$final[$date][$transactions['row']['account_id']]['title'] = $transactions['row']['title'];
				$final[$date][$transactions['row']['account_id']]['grossCredit'] += ($transactions['totalCredit'] + $transactions['discount']);
				$final[$date][$transactions['row']['account_id']]['totalCredit'] += $transactions['totalCredit'];
				$final[$date][$transactions['row']['account_id']]['totalPaid'] += $transactions['totalPaid'];
				$final[$date][$transactions['row']['account_id']]['totalDiscount'] += $transactions['discount'];
				$final[$date][$transactions['row']['account_id']]['transaction_date'] = $date;
			}
			$reportData1 = array_values($final[$date]);
		}

		$reportData = [];

		// echo '<pre>';
		// print_r($reportData1);
		// echo '</pre>';
		$count = 0;
		$footer = [];
		$finalSummeryDateWise = [];
		foreach ($reportData1 as $key => $value) {
			$reportData[$count] = $value;
			$reportData[$count]['grossCreditSales'] = number_format($value['grossCredit'] - $value['totalPaid'], 2);
			$reportData[$count]['grossCashSales'] = number_format($value['totalPaid'] + $value['totalPaid'], 2);
			$reportData[$count]['discount'] = number_format($value['totalDiscount'], 2);
			$reportData[$count]['netCreditSales'] = number_format($value['totalCredit'] - $value['totalPaid'], 2);
			$reportData[$count]['finalCreditSales'] = number_format($value['totalCredit'] - $value['totalPaid'], 2);
			$reportData[$count]['netCashSales'] = number_format($value['totalPaid'], 2);
			$reportData[$count]['finalCashSales'] = number_format($value['totalPaid'], 2);
			

			$footer['grossCreditSales'] += $value['grossCredit'] - $value['totalPaid'];
			$footer['grossCashSales'] += $value['totalPaid'] + $value['totalPaid'];
			$footer['discount'] += $value['totalDiscount'];
			$footer['netCreditSales'] += $value['totalCredit'] - $value['totalPaid'];
			$footer['finalCreditSales'] += $value['totalCredit'] - $value['totalPaid'];
			$footer['netCashSales'] += $value['totalPaid'];
			$footer['finalCashSales'] += $value['totalPaid'];

			$finalSummeryDateWise[$value['transaction_date']] += $value['totalPaid'];

			

			$count++;
		}



        $subtitle = 'Closing Balance'.$subtitle;
        $headers = ['Date', 'Account Code', 'Account Title', 'Gross Credit Sales','Gross Cash Sales', 'Discount', 'Net Credit Sales', 'Final Credit Sales','Net Cash Sales', 'Final Cash Sales'];
		$columns = ['transaction_date','code', 'title', 'grossCreditSales','grossCashSales', 'discount', 'netCreditSales', 'finalCreditSales','netCashSales', 'finalCashSales'];

		$hasFooter = true;
		$footerCols = ['','Date', 'Account Code', 'Account Title'];
		$footerVals = ['grossCreditSales','grossCashSales', 'discount', 'netCreditSales', 'finalCreditSales','netCashSales', 'finalCashSales'];

	break;
	
	case '11':
		$reportData = $doubleEntry->getTrialBalanceReport($params);
        // print_r($reportData);exit;
        $subtitle = 'Trial Balance'.$subtitle;
        $headers = ['Account Code', 'Account Title', 'Date', 'Debit', 'Credit'];
		$columns = ['accountCode', 'accountTitle', 'transaction_date', 'finalDebitAmount', 'finalCreditAmount'];

	break;

    case '12':
		$reportData = $doubleEntry->getPLStatementReport($params);
        // print_r($reportData);exit;
        $subtitle = 'Profit and Loss Statement'.$subtitle;
        $headers = ['Account Code', 'Account Title', 'Debit', 'Credit'];
		$columns = ['code', 'title', 'debitAmount', 'creditAmount'];

        include_once dirname(__FILE__).'/print/plstatement.php';
        exit;

	break;
	
	default :
		break;
}
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
		<?php $count = 1; foreach ($reportData as $s) { ?>
			<tr>
			<?php if($srNo) { ?>
				<td width="10"><?php echo $count; ?></td>
			<?php } ?>

				<?php foreach ($columns as $value){ 
                    $val = '';
                    if($value == 'ifDebitAmount' || $value == 'ifCreditAmount') {
                        if($value == 'ifDebitAmount') {
                            $val = $s['entry_type'] == 'D' ? $s['amount'] : 0;
                        }
                        else if($value == 'ifCreditAmount') {
                            $val = $s['entry_type'] == 'C' ? $s['amount'] : 0;
                        }
                    }
                    else if($value == 'finalDebitAmount' || $value == 'finalCreditAmount') {
                        $debit = !empty($s['debitAmount']) ? $s['debitAmount'] : 0;
                        $credit = !empty($s['creditAmount']) ? $s['creditAmount'] : 0;
                        
                        if($debit > $credit && $value == 'finalDebitAmount') {
                            $val = $s['debitAmount'] - $s['creditAmount'];
                        }
                        elseif ($debit > $credit && $value == 'finalCreditAmount') {
                            $val = null;
                        }

                        elseif($credit > $debit && $value == 'finalCreditAmount') {
                            $val = $s['creditAmount'] - $s['debitAmount'];
                        }
                        elseif ($credit > $debit && $value == 'finalDebitAmount') {
                            $val = null;
                        }
                    }
                    else {
                        $val = $s[$value];
                    }

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
						<td><?php echo $val; ?></td>
					<?php } else { ?>

						<td><?php echo $val; ?></td>

					<?php } ?>

				<?php } ?>
			</tr>
		<?php $count++;} ?>
		<?php if(!empty($hasFooter)) { ?>
			<tr>
				<?php foreach ($footerCols as $value){ ?>
					<th><?php echo $value; ?></th>
				<?php } ?>
				<?php foreach ($footerVals as $value){ ?>
					<th align="left"><?php echo number_format($footer[$value], 2); ?></th>
				<?php } ?>
			</tr>
		<?php }?>
	</tbody>
</table>
<?php if($reportType == '10') {?>
<h3>Final Summery</h3>
<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
	<?php foreach ($expenses['rows'] as $date => $value) {
	$total = 0;
	?>
		<tr>
			<th>Date</th>
			<?php foreach ($value['row'] as $row) {?>
				<th><?php echo $row['title'];?></th>
			<?php };?>
			<th>Total Expenses</th>
			<th>Total Sale</th>
			<th>Net Total</th>
		</tr>
		<tr>
			<td><?php echo $date;?></td>
			<?php foreach ($value['row'] as $row) { $total+=$row['amount']?>
				<td><?php echo $row['amount'];?></td>
			<?php };?>
			<td><?php echo $total;?></td>
			<td><?php echo $footer['finalCashSales'];?></td>
			<td><?php echo $footer['finalCashSales'] - $total;?></td>
		</tr>
	<?php }?>
</table>
<br>
<?php 

$tsale = empty($footer['finalCashSales']) ? 0 : $footer['finalCashSales'];
$texpense = empty($expenses['total']) ? 0 : $expenses['total'];

?>
<table id="resultTable" style="border-collapse: collapse; width: 400px" border="1">
		<tr>
			<th align="left">Grand Sale</td>
			<th align="left"><?php echo $tsale;?></td>
		</tr>
		<tr>
			<th align="left">Total Expenses</td>
			<th align="left"><?php echo $texpense;?></td>
		</tr>
		<tr>
			<th align="left">Net Total</td>
			<th align="left"><?php echo $tsale  - $texpense;?></th>
		</tr>
</table>

<?php }
	if(empty($params['pdf'])) { ?>
<script>
	window.print();
</script>

<?php 
}

	$html = ob_get_contents();
	ob_clean();



	echo $html;



?>

