<?php

include_once dirname(__FILE__) . '/../../include/settings.php';

$doubleEntry = new DoubleEntry();

$params = array();
$from = $_POST['from'];
$to = $_POST['to'];
$params['shopId'] = isset($_POST['shopId']) && !empty(trim($_POST['shopId'])) ? $_POST['shopId'] : $shop['id'];
$params['fromDate'] = isset($from) && !empty(trim($from)) ? $from : '';
$params['toDate'] = isset($to) && !empty(trim($to)) ? $to : '';
$report 		= isset($_GET['report']) && !empty(trim($_GET['report'])) ? $_GET['report'] : '';
$cashTotals = [];
$modesList = $doubleEntry->getPaymentModes(['page' => 1, 'perPage' => 10000, 'search' => '', 'shopId' => $params['shopId']]);
$amodesList = [];
$cashModeId = 0;
foreach ($modesList['records'] as $key => $value) {
	$amodesList[$value['id']] = $value;
	if ($value['code'] == 'CASH') {
		$cashModeId = $value['id'];
	}
}
$ob = !empty($opening_balance) ? $opening_balance : 0;

$headers 		= array();
$columns 		= array();
$orientation 	= 'P';
$largeFont 		= false;
$srNo 			= true;
$mediumFont		= false;

$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($params['shopId']);
$store = [];
foreach ($accountsData as $a) {
	$store[$a['key_value']] = $a['account_id'];
}

$reportTitle = $shop['full_name'] . ' - ' . $shop['city'];

$subtitle = " Between " . $params['fromDate'] . " and " . $params['toDate'];

//$time = new datetime('Y');
$d = new DateTime('Y');

$reportData = [];

switch ($reportType) {
	case '10':
		$params['parent_ids'][] = $store['receivable'];
		$params['parent_ids'][] = $store['payable'];
		$params['account_ids'][] = $store['sale_discount'];
		$params['account_ids'][] = $store['receiving'];
		$params['account_ids'][] = $store['sale_returns'];
		$params['account_ids'][] = $store['purchase_returns'];
		$params['parent_ids'][] = $store['expense'];
		$customers = new  Customers();
		$page = !empty($_GET['page']) ? $_GET['page'] : 1;
		$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1000;
		$search = !empty($_GET['search']) ? $_GET['search'] : "";
		$balances = $customers->getCustomersPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shopId' => $shop['id']]);
		$reportDataRaw = $doubleEntry->getClosingBalanceReport($params);
		if (!empty($reportDataRaw['opening_balance'])) {
			$ob = $reportDataRaw['opening_balance']['amount'];
		}
		$rows = [];
		$exp = $store['sale_discount'];
		$expHead = $store['expense'];
		$count = 0;
		$expenses = ['total' => 0, 'rows' => []];
		$otherList = [];
		$otherTotals = [];
		$purchaseReturnsList = [];
		$exchange = 0;
		$payments = 0;
		$receivings = 0;
		$cashSale = 0;
		$sale_returns = 0;
		$purchase_returns = 0;
		$receivingList = 0;
		$final = [];
		$modes = [];
		foreach ($reportDataRaw['rows'] as $key => $value) {
			if ($value['transsaction_type'] == 'SALE' || $value['transsaction_type'] == 'EXPENSE') {
				if ($value['parent_id'] == $store['receivable']) { // exclude expense
					if ($value['entry_type'] == 'D') {
						$rows[$value['account_id']]['row'] = $value;
						$rows[$value['account_id']]['totalCredit'] += $value['amount'];
					} else {
						$rows[$value['account_id']]['row'] = $value;
						$modes[$value['payment_mode']] += $value['amount'];
						$rows[$value['account_id']]['totalPaid'] += $value['amount'];
						$rows[$value['account_id']]['paid'][$value['payment_mode']] += $value['amount'];
					}
				} else if ($value['parent_id'] == $expHead) {
					if (empty($expenses['rows'][$value['transaction_date']]['row'][$value['account_id']][$value['payment_mode']])) {
						$expenses['rows'][$value['transaction_date']]['row'][$value['account_id']][$value['payment_mode']] = $value;
					} else {
						$expenses['rows'][$value['transaction_date']]['row'][$value['account_id']][$value['payment_mode']]['amount'] += $value['amount'];
					}
					$expenses['rows'][$value['transaction_date']]['total'][$value['payment_mode']] += $value['amount'];
					$expenses['total'][$value['payment_mode']] += $value['amount'];
				}
			} elseif (in_array($value['transsaction_type'], ['DIRECT_RECEIVING', 'CASH_RECEIVED'])) {
				if ($store['receivable'] == $value['parent_id']) {

					$k = "RECEIVING";

					$m = $value['payment_mode'];

					$otherTotals['accounts'][$value['account_id']][$k][$m] += $value['amount'];
					$otherTotals['totals'][$k][$m] += $value['amount'];

					if (empty($otherList[$value['account_id']][$k][$m])) {
						$otherList[$value['account_id']][$k][$m] = $value;
					} else {
						$otherList[$value['account_id']][$k][$m]['amount'] += $value['amount'];
					}

					if ($cashModeId == $value['payment_mode']) {
						$receivings += $value['amount'];
					}
				}
			} else {
				$consider = true;

				if (in_array($value['transsaction_type'], ['PURCHASE_PAYMENT', 'DIRECT_PAYMENT'])) {
					if ($cashModeId == $value['payment_mode']) {
						$payments += $value['amount'];
					}
				}
				if (in_array($value['transsaction_type'], ['EXCHANGE'])) {
					$exchange += $value['amount'];
				}
				if (in_array($value['transsaction_type'], ['SALE_RETURN'])) {
					if ($store['receivable'] == $value['parent_id'] && $value['entry_type'] == 'D') {
						if ($cashModeId == $value['payment_mode']) {
							$sale_returns += $value['amount'];
						}
					} else {
						$consider = false;
					}
				}

				if (in_array($value['transsaction_type'], ['PURCHASE'])) {
					if ($value['entry_type'] == 'D') {
						if ($cashModeId == $value['payment_mode']) {
							$payments += $value['amount'];
						}
					} else {
						$consider = false;
					}
				}

				if ($consider) {
					$otherTotals['accounts'][$value['account_id']][$value['transsaction_type']][$value['payment_mode']] += $value['amount'];
					$otherTotals['totals'][$value['transsaction_type']][$value['payment_mode']] += $value['amount'];
					if (empty($otherList[$value['account_id']][$value['transsaction_type']][$value['payment_mode']])) {
						$otherList[$value['account_id']][$value['transsaction_type']][$value['payment_mode']] = $value;
					} else {
						$otherList[$value['account_id']][$value['transsaction_type']][$value['payment_mode']]['amount'] += $value['amount'];
					}
				}
			}

			$count++;
		}


		// $rows2 = [];
		$totals = ['expense' => 0, 'gross' => 0, 'net' => 0];
		$count = 0;
		$reportData1 = [];
		foreach ($rows as $accountId => $transaction) {
			// if(empty($transactions['isReceiving'])) {
			$final[$accountId]['id'] = $transaction['row']['transaction_id'];
			$final[$accountId]['code'] = $transaction['row']['code'];
			$final[$accountId]['title'] = $transaction['row']['title'];
			$final[$accountId]['grossCredit'] += $transaction['totalCredit'];
			$final[$accountId]['totalCredit'] += $transaction['totalCredit'];
			foreach ($modesList['records'] as $m) {
				if ($m['code'] == 'CASH') {
					$cashSale += $transaction['paid'][$m['id']];
				}
				$final[$accountId][$m['id']] += $transaction['paid'][$m['id']];
			}


			$final[$accountId]['totalPaid'] += $transaction['totalPaid'];
			$final[$accountId]['totalDiscount'] += $transaction['discount'];
			// }
			if (!empty($final)) {
				$reportData1 = array_values($final);
			}
		}
		$reportData = [];

		// echo '<pre>';
		// var_dump($cashSale);
		// exit;

		$count = 0;
		$footer = [];
		$finalSummeryDateWise = [];
		foreach ($reportData1 as $key => $value) {
			if (empty($value['title'])) {
				unset($reportData1);
			} else {
				$reportData[$count] = $value;
				$reportData[$count]['grossCreditSales'] = !empty($value['grossCredit'] - $value['totalPaid']) ? $value['grossCredit'] - $value['totalPaid'] + $value['totalDiscount'] : 0;
				$reportData[$count]['grossCashSales'] = !empty($value['totalPaid']) ? $value['totalPaid'] + $value['totalDiscount'] : 0;
				// $reportData[$count]['discount'] = $value['totalDiscount'];
				$reportData[$count]['netCreditSales'] = $value['totalCredit'] - $value['totalPaid'];
				$reportData[$count]['netCashSales'] = $value['totalPaid'];
				$reportData[$count]['finalCashSales'] = $value['totalPaid'];

				foreach ($modesList['records'] as $m) {
					$reportData[$count][$m['id']] = $value[$m['id']];
					$footer[$m['id']] += $value[$m['id']];
				}

				$footer['grossCreditSales'] += !empty($value['grossCredit'] - $value['totalPaid']) ? $value['grossCredit'] - $value['totalPaid'] + $value['totalDiscount'] : 0;
				$footer['grossCashSales'] += !empty($value['totalPaid']) ? $value['totalPaid'] + $value['totalDiscount'] : 0;
				$footer['discount'] += $value['totalDiscount'];
				$footer['netCreditSales'] += $value['totalCredit'] - $value['totalPaid'];
				$footer['netCashSales'] += $value['totalPaid'];
				$footer['finalCashSales'] += $value['totalPaid'];

				$finalSummeryDateWise[$value['transaction_date']] += $value['totalPaid'];
				$count++;
			}
		}



		$subtitle = 'Closing Balance' . $subtitle;
		$kkk = [];
		$titles = [];
		foreach ($modesList['records'] as $m) {
			$kkk[] = $m['id'];
			$titles[] = $m['title'];
		}

		$headers = ['Date', 'Account Code', 'Account Title', ...$titles, 'Credit Sales'];
		$columns = ['transaction_date', 'code', 'title', ...$kkk, 'netCreditSales'];

		$hasFooter = true;
		$footerCols = ['', 'Date', 'Account Code', 'Account Title'];
		$summerCols = [...$titles, 'Credit Sales', 'Cash Sale'];
		$footerVals = [...$kkk, 'netCreditSales'];

		break;

	case '11':
		$reportData = $doubleEntry->getTrialBalanceReport($params);
		// print_r($reportData);exit;
		$subtitle = 'Trial Balance' . $subtitle;
		$headers = ['Account Code', 'Account Title', 'Date', 'Debit', 'Credit'];
		$columns = ['accountCode', 'accountTitle', 'transaction_date', 'finalDebitAmount', 'finalCreditAmount'];

		break;

	case '12':
		$params['account_ids'][] = $store['sale_discount'];
		$params['account_ids'][] = $store['sale_returns'];
		$params['account_ids'][] = $store['purchase_discount'];
		$params['account_ids'][] = $store['purchase_returns'];
		$params['account_ids'][] = $store['assets'];
		$params['parent_ids'][] = $store['expense'];
		$reportData = $doubleEntry->getPLStatementReport($params);
		$subtitle = 'Profit and Loss Account' . $subtitle;
		$headers = ['Account Code', 'Account Title', 'Debit', 'Credit'];
		$columns = ['code', 'title', 'debitAmount', 'creditAmount'];

		include_once dirname(__FILE__) . '/plstatement.php';
		exit;

		break;

	default:
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

	td {
		padding: 5px 5px;
	}

	<?php if ($report == 'dataCollection' || $report == 'idcards_list') { ?>td {
		height: 32px;
		white-space: nowrap;
	}

	<?php }
	if ($largeFont) { ?>td {
		font-size: 25px;
		padding: 7px 15px;
		font-family: Calibri, sans-serif;
		white-space: nowrap;
	}

	<?php }
	if ($mediumFont) { ?>td {
		font-size: 15px;
		padding: 4px 5px;
		font-family: Calibri, sans-serif;
		white-space: nowrap;
	}

	<?php } ?>h1 {
		font-size: 20pt;
	}

	<?php if ($orientation == 'L' && !$params['pdf']) { ?>@page {
		size: Legal landscape;
	}

	<?php } ?>
</style>

<h1 style="text-align: center; margin-bottom: 10px"><?php echo $reportTitle; ?></h1>
<h5 style="text-align: center; margin-top: 10px"><?php echo $subtitle; ?></h5>
<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
	<thead>
		<tr>
			<?php if ($srNo) { ?>
				<th width="30">S.#</th>
			<?php } ?>
			<?php foreach ($headers as $value) { ?>
				<th><?php echo $value; ?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php $count = 1;
		foreach ($reportData as $s) { ?>
			<tr>
				<?php if ($srNo) { ?>
					<td width="10"><?php echo $count; ?></td>
				<?php } ?>

				<?php foreach ($columns as $value) {
					$val = '';
					if ($value == 'ifDebitAmount' || $value == 'ifCreditAmount') {
						if ($value == 'ifDebitAmount') {
							$val = $s['entry_type'] == 'D' ? $s['amount'] : 0;
						} else if ($value == 'ifCreditAmount') {
							$val = $s['entry_type'] == 'C' ? $s['amount'] : 0;
						}
					} else if ($value == 'finalDebitAmount' || $value == 'finalCreditAmount') {
						$debit = !empty($s['debitAmount']) ? $s['debitAmount'] : 0;
						$credit = !empty($s['creditAmount']) ? $s['creditAmount'] : 0;

						if ($debit > $credit && $value == 'finalDebitAmount') {
							$val = $s['debitAmount'] - $s['creditAmount'];
						} elseif ($debit > $credit && $value == 'finalCreditAmount') {
							$val = null;
						} elseif ($credit > $debit && $value == 'finalCreditAmount') {
							$val = $s['creditAmount'] - $s['debitAmount'];
						} elseif ($credit > $debit && $value == 'finalDebitAmount') {
							$val = null;
						}
					} else {
						$val = $s[$value];
					}

				?>

					<?php if ($value == '_blank') { ?>

						<td width="300"><?php echo $val; ?></td>

					<?php } elseif ($value == '_blank_normal2') { ?>
						<td width="170"><?php echo $val; ?></td>
					<?php } elseif ($value == '_blank_normal3') { ?>
						<td width="370"><?php echo $val; ?></td>
					<?php } elseif ($value == '_blank_normal') { ?>
						<td><?php echo $val; ?></td>
					<?php } elseif ($value == 'transaction_date') { ?>
						<td><?php echo $val; ?></td>
					<?php } else { ?>

						<td <?php echo is_numeric($val) ? 'align="right"' : null; ?>><?php echo is_numeric($val) ? number_format($val, 0) : $val; ?></td>

					<?php } ?>

				<?php } ?>
			</tr>
		<?php $count++;
		} ?>
		<?php if (!empty($hasFooter)) { ?>
			<tr>
				<?php foreach ($footerCols as $value) { ?>
					<th><?php echo $value; ?></th>
				<?php } ?>
				<?php foreach ($footerVals as $value) { ?>
					<th align="right"><?php echo number_format($footer[$value], 0); ?></th>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php if ($reportType == '10') {
	if (!empty($expenses['rows'])) { ?>
		<h4 style="margin: 10px 0">Expenses Summery</h4>
		<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
			<?php foreach ($expenses['rows'] as $date => $value) {
				$total = 0;
			?>
				<tr>
					<th rowspan="2">Date</th>
					<?php foreach ($value['row'] as $row) {
						$title = array_values($row)[0]['title'];
					?>
						<th colspan="<?php echo count($modesList['records']); ?>"><?php echo $title; ?></th>
					<?php }; ?>
				</tr>
				<tr>
					<?php foreach ($value['row'] as $row) { ?>
						<?php foreach ($modesList['records'] as $rr) {
							$tag = ($cashModeId == $rr['id']) ? 'th' : 'td';

						?>
							<<?php echo $tag; ?> style="text-align: center"><?php echo $rr['title']; ?></<?php echo $tag; ?>>
						<?php }; ?>
					<?php }; ?>
				</tr>
				<tr>
					<td><?php echo $date; ?></td>
					<?php
					foreach ($value['row'] as $id => $row3) {
						$cashTotals["exp"] += $row3[$cashModeId]['amount'];
						foreach ($modesList['records'] as $rr) {

							$tag = ($cashModeId == $rr['id']) ? 'th' : 'td';
					?>
							<<?php echo $tag; ?> align="right"><?php echo number_format($row3[$rr['id']]['amount'], 0); ?></<?php echo $tag; ?>>
						<?php };
						?>
					<?php } ?>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
	<h5 style="margin: 5px 0">Other Totals</h5>
	<table id="resultTable" class="table" style="border-collapse: collapse;" border="1">
		<tr>
			<th rowspan="2" align="left" style="width: 50%">Account</th>
			<?php foreach (array_keys($otherTotals['totals']) as $key) { ?>
				<th align="left" colspan="<?php echo count($modesList['records']); ?>"><?php echo $key; ?></th>
			<?php } ?>
			<!-- <th align="right"><?php echo number_format($cash + $ob, 0); ?></th> -->
		</tr>
		<tr>
			<?php foreach (array_keys($otherTotals['totals']) as $key) { ?>
				<?php foreach ($modesList['records'] as $rr) {
					$tag = ($cashModeId == $rr['id']) ? 'th' : 'td';
				?>
					<<?php echo $tag; ?> align="left"><?php echo $rr['title']; ?></<?php echo $tag; ?>>
			<?php }
			} ?>
			<!-- <th align="right"><?php echo number_format($cash + $ob, 0); ?></th> -->
		</tr>
		<?php foreach ($otherList as $accountId => $value) {
		?>
			<tr>
				<th align="left"><?php echo array_values(array_values($value)[0])[0]['title']; ?></th>
				<?php foreach (array_keys($otherTotals['totals']) as $key) {
				?>
					<?php foreach ($modesList['records'] as $row) {
						$tag = ($cashModeId == $row['id']) ? 'th' : 'td';
					?>

						<<?php echo $tag; ?> align="left"><?php echo $value[$key][$row['id']]['amount']; ?></<?php echo $tag; ?>>
				<?php }
				} ?>
			</tr>
		<?php } ?>
		<tr>
			<th align="left">Total</th>
			<?php foreach ($otherTotals['totals'] as $val) {
				foreach ($modesList['records'] as $row) {
					$tag = ($cashModeId == $row['id']) ? 'th' : 'td';
			?>
					<<?php echo $tag; ?> align="left"><?php echo $val[$row['id']]; ?></<?php echo $tag; ?>>
			<?php }
			} ?>
		</tr>

	</table>
	<?php

	$tsale = $cashSale;
	$creditsale = empty($footer['netCreditSales']) ? 0 : $footer['netCreditSales'];
	$texpense = $cashTotals["exp"];
	$cash = ($tsale + $receivings + $purchase_returns);
	$deduction = ($sale_returns + $texpense + $payments);
	$netCash = ($tsale + $receivings + $purchase_returns) - $deduction;
	?>

	<h5 style="margin: 5px 0"></h5>
	<table id="resultTable" class="table" style="border-collapse: collapse;" border="1">
		<tr>
			<th colspan="2" style="width: 50%">
				<h5 style="margin: 5px 0; font-size: 1.4em">Sale</h5>
			</th>
			<th colspan="2" style="width: 50%">
				<h5 style="margin: 5px 0; font-size: 1.4em">Deductions</h5>
			</th>
		</tr>
		<tr>
			<th align="left">Opening Balance</th>
			<th align="right"><?php echo number_format($ob); ?></th>
			<th align="left">Sale Return</td>
			<th align="right"><?php echo number_format($sale_returns, 0); ?></td>
		</tr>
		<tr>
			<th align="left">Sale via CASH
				</td>
			<th align="right"><?php echo number_format($cashSale, 0); ?></td>
			<th align="left">Payments</td>
			<th align="right"><?php echo number_format($payments, 0); ?></td>
		</tr>
		<tr>
			<th align="left">Receivings</th>
			<th align="right"><?php echo number_format($receivings, 0); ?></th>
			<th align="left">Expenses</td>
			<th align="right"><?php echo number_format($texpense, 0); ?></td>
		</tr>
		<tr>
			<th align="left">Purchase Returns</th>
			<th align="right"><?php echo number_format($purchase_returns, 0); ?></th>
		</tr>

		<tr>
			<th align="left">Total</th>
			<th align="right"><?php echo number_format($cash + $ob, 0); ?></th>
			<th align="left">Total</th>
			<th align="right"><?php echo number_format($deduction, 0); ?></th>
		</tr>
	</table>

	<h5 style="margin: 5px 0">Totals</h5>
	<table id="resultTable" class="table" style="border-collapse: collapse;" border="1">
		<tr>
			<th align="left">Total Sale</th>
			<th align="right"><?php echo number_format($cash + $ob, 0); ?></th>
		</tr>
		<tr>
			<th align="left">Total Deduction</th>
			<th align="right"><?php echo number_format($deduction, 0); ?></th>
		</tr>
		<tr>
			<th align="left">Net Total</td>
			<th align="right"><?php echo number_format(($cash + $ob) - $deduction, 0); ?></th>
		</tr>
	</table>


	<h5 style="margin: 5px 0">Customers's Balances</h5>
	<table id="resultTable" class="table" style="border-collapse: collapse;" border="1">
		<?php foreach ($balances['records'] as $key => $value) {
			if ($value['type'] == 1) {
		?>
				<tr>
					<th align="left"><?php echo $value['full_name']; ?></th>
					<th align="right"><?php echo number_format($value['closing_balance']); ?></th>
				</tr>
		<?php }
		} ?>
	</table>
<?php }
if (empty($params['pdf'])) { ?>
	<script>
		// window.print();
	</script>

<?php
}

$html = ob_get_contents();
ob_clean();



echo $html;



?>