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

$modesList = $doubleEntry->getPaymentModes(['page' => 1, 'perPage' => 10000, 'search' => '', 'shopId' => $params['shopId']]);
$amodesList = [];
foreach ($modesList['records'] as $key => $value) {
	$amodesList[$value['id']] = $value;
}

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
		$params['parent_ids'][] = $store['payable'];
		$params['account_ids'][] = $store['sale_discount'];
		$params['account_ids'][] = $store['receiving'];
		$params['parent_ids'][] = $store['expense'];
		
		$reportDataRaw = $doubleEntry->getClosingBalanceReport($params);
		// print_r($reportDataRaw);

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
		$payments = 0;
		$receivings = 0;
		$final = [];
		$modes = [];
		foreach ($reportDataRaw as $key => $value) {
			if($store['payable'] != $value['parent_id']) {
				if($value['account_id'] != $exp && $value['parent_id'] != $expHead) {
					if($value['entry_type'] == 'D') {
						if($value['account_id'] == $shop['receiving']) {
							$receivings += $value['amount'];
							$rows[$value['transaction_date']][$value['transaction_id']]['isReceving'] = true;
						}
						else {
							$rows[$value['transaction_date']][$value['transaction_id']]['row'] = $value;
							$rows[$value['transaction_date']][$value['transaction_id']]['totalCredit'] += $value['amount'];
						}
					}
					else {
						if(empty($rows[$value['transaction_date']][$value['transaction_id']]['isReceving'])) {
							$rows[$value['transaction_date']][$value['transaction_id']]['row'] = $value;
							$modes[$value['payment_mode']] += $value['amount'];
							$rows[$value['transaction_date']][$value['transaction_id']]['totalPaid'] += $value['amount'];
						}
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
			} else {
				if($value['entry_type'] == 'D') {
					$payments += $value['amount'];
				}
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
				// if(empty($transactions['isReceiving'])) {
					$final[$date][$transactions['row']['account_id']]['id'] = $transactions['row']['transaction_id'];
					$final[$date][$transactions['row']['account_id']]['code'] = $transactions['row']['code'];
					$final[$date][$transactions['row']['account_id']]['title'] = $transactions['row']['title'];
					$final[$date][$transactions['row']['account_id']]['grossCredit'] += $transactions['totalCredit'];
					$final[$date][$transactions['row']['account_id']]['totalCredit'] += $transactions['totalCredit'];
					$final[$date][$transactions['row']['account_id']]['totalPaid'] += $transactions['totalPaid'];
					$final[$date][$transactions['row']['account_id']]['totalDiscount'] += $transactions['discount'];
					$final[$date][$transactions['row']['account_id']]['transaction_date'] = $date;
				// }
			}
			if(!empty($final[$date])) {
				$reportData1 = array_values($final[$date]);
			}
		}
		$reportData = [];

		$count = 0;
		$footer = [];
		$finalSummeryDateWise = [];
		foreach ($reportData1 as $key => $value) {
			if(empty($value['title'])) {
				unset($reportData1);
			}
			else {
				$reportData[$count] = $value;
				$reportData[$count]['grossCreditSales'] = $value['grossCredit'] - $value['totalPaid'];
				$reportData[$count]['grossCashSales'] = $value['totalPaid'] + $value['totalDiscount'];
				$reportData[$count]['discount'] = $value['totalDiscount'];
				$reportData[$count]['netCreditSales'] = $value['totalCredit'] - $value['totalPaid'];
				$reportData[$count]['netCashSales'] = $value['totalPaid'];
				$reportData[$count]['finalCashSales'] = $value['totalPaid'];
	
				$footer['grossCreditSales'] += $value['grossCredit'] - $value['totalPaid'];
				$footer['grossCashSales'] += $value['totalPaid'] + $value['totalDiscount'];
				$footer['discount'] += $value['totalDiscount'];
				$footer['netCreditSales'] += $value['totalCredit'] - $value['totalPaid'];
				$footer['netCashSales'] += $value['totalPaid'];
				$footer['finalCashSales'] += $value['totalPaid'];
	
				$finalSummeryDateWise[$value['transaction_date']] += $value['totalPaid'];
				$count++;
			}
		}



        $subtitle = 'Closing Balance'.$subtitle;
        $headers = ['Date', 'Account Code', 'Account Title', 'Gross Credit Sales','Gross Cash Sales', 'Discount', 'Net Credit Sales', 'Net Cash Sales'];
		$columns = ['transaction_date','code', 'title', 'grossCreditSales','grossCashSales', 'discount', 'netCreditSales', 'netCashSales'];

		$hasFooter = true;
		$footerCols = ['','Date', 'Account Code', 'Account Title'];
		$summerCols = ['Gross Credit Sales','Gross Cash Sales', 'Discount', 'Net Credit Sales', 'Net Cash Sale'];
		$footerVals = ['grossCreditSales','grossCashSales', 'discount', 'netCreditSales', 'netCashSales'];

	break;
	
	case '11':
		$reportData = $doubleEntry->getTrialBalanceReport($params);
        // print_r($reportData);exit;
        $subtitle = 'Trial Balance'.$subtitle;
        $headers = ['Account Code', 'Account Title', 'Date', 'Debit', 'Credit'];
		$columns = ['accountCode', 'accountTitle', 'transaction_date', 'finalDebitAmount', 'finalCreditAmount'];

	break;

    case '12':
		// $params['parent_ids'][] = $store['receivable'];
		$params['parent_ids'][] = $store['payable'];
		$params['account_ids'][] = $store['sale_discount'];
		$params['account_ids'][] = $store['sale_returns'];
		$params['account_ids'][] = $store['purchase_discount'];
		$params['account_ids'][] = $store['purchase_returns'];
		$params['account_ids'][] = $store['receiving'];
		$params['account_ids'][] = $store['cash'];
		$params['account_ids'][] = $store['assets'];
		$params['parent_ids'][] = $store['expense'];
		$reportData = $doubleEntry->getPLStatementReport($params);
		$subtitle = 'Profit and Loss Statement'.$subtitle;
        $headers = ['Account Code', 'Account Title', 'Debit', 'Credit'];
		$columns = ['code', 'title', 'debitAmount', 'creditAmount'];

        include_once dirname(__FILE__).'/plstatement.php';
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

<h1 style="text-align: center; margin-bottom: 10px"><?php echo $reportTitle; ?></h1>
<h5 style="text-align: center; margin-top: 10px"><?php echo $subtitle; ?></h5>
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

						<td <?php echo is_numeric($val) ? 'align="right"' : null;?>><?php echo is_numeric($val) ? number_format($val, 2) : $val; ?></td>

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
					<th align="right"><?php echo number_format($footer[$value], 2); ?></th>
				<?php } ?>
			</tr>
		<?php }?>
	</tbody>
</table>
<?php if($reportType == '10') {
if(!empty($expenses['rows'])) { ?>
<h3>Expenses Summery</h3>
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
		</tr>
		<tr>
			<td><?php echo $date;?></td>
			<?php foreach ($value['row'] as $row) { $total+=$row['amount']?>
				<td align="right"><?php echo number_format($row['amount'], 2);?></td>
			<?php };?>
			<th align="right"><?php echo number_format($total, 2);?></th>
		</tr>
	<?php }?>
</table>
<?php } ?>
<h3>Final Summery</h3>
<?php 

$tsale = empty($footer['netCashSales']) ? 0 : $footer['netCashSales'];
$creditsale = empty($footer['netCreditSales']) ? 0 : $footer['netCreditSales'];
$texpense = empty($expenses['total']) ? 0 : $expenses['total'];
$netCash = ($tsale + $receivings) - ($texpense - $payments);
?>
<table id="resultTable" style="border-collapse: collapse; width: 400px" border="1">
	<?php foreach ($summerCols as $index => $value){ 
		$key = $footerVals[$index];
		if($key != 'netCashSales') {
		
		?>
		<tr>
			<th align="left"><?php echo $value; ?></th>
			<th align="right"><?php echo number_format($footer[$key], 2); ?></th>
		</tr>
		<?php }} ?>
		<?php foreach ($modes as $id => $value) {?>
			<tr>
				<th align="left">Sale via <?php echo $amodesList[$id]['title'];?></td>
				<th align="right"><?php echo number_format($value, 2);?></td>
			</tr>
		<?php } ?>
		<tr>
			<th align="left">Payments</td>
			<th align="right"><?php echo number_format($payments, 2);?></td>
		</tr>
		<tr>
			<th align="left">Expenses</td>
			<th align="right"><?php echo number_format($texpense, 2); ?></td>
		</tr>
		<tr>
			<th align="left">Net Cash</td>
			<th align="right"><?php echo number_format($netCash, 2); ?></td>
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

