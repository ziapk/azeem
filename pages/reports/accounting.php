<?php

include_once dirname(__FILE__) . '/../../include/settings.php';

$doubleEntry = new DoubleEntry();

$params = [];
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

		$customers = new  Customers();
		$page = !empty($_GET['page']) ? $_GET['page'] : 1;
		$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1000;
		$search = !empty($_GET['search']) ? $_GET['search'] : "";
		$account_type = !empty($_GET['account_type']) ? $_GET['account_type'] : 1;
		$balances = $customers->getCustomersPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'account_type' => $account_type, 'shopId' => $shop['id']]);

		$reportData = $doubleEntry->getClosingBalanceReport($params);
		$reportDataOther = $reportData['other'];
		$reportData = $reportData['records'];

		if (!empty($reportDataOther['opening_balance'])) {
			$ob = $reportDataOther['opening_balance']['amount'];
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
		// Without a date range every figure silently comes back zero, so stop
		// rather than hand back an empty statement that looks like real data.
		if (empty($params['fromDate']) || empty($params['toDate'])) {
			echo '<h3 style="font-family: Arial, sans-serif">Please select a date range for the Profit and Loss report.</h3>';
			exit;
		}

		// Sales, purchases and both kinds of return all post to $store['assets'],
		// so the breakdown splits them apart on transsaction_type.
		$params['trading_account_id']   = $store['assets'];
		$params['sale_discount_id']     = $store['sale_discount'];
		$params['sale_returns_id']      = $store['sale_returns'];
		$params['purchase_discount_id'] = $store['purchase_discount'];
		$params['purchase_returns_id']  = $store['purchase_returns'];
		$params['expense_root_ids']     = [$store['expense']];

		$pl          = $doubleEntry->getPLStatementBreakdown($params);
		$expenseRows = $doubleEntry->getPLExpenseRows($params);
		$productsObj = new Products();

		// 'purchases' = trading account: gross profit is net sales less net
		// purchases, using only movement inside the date range. 'stock' brings in
		// opening/closing stock valuation to derive a true COGS, which is only
		// meaningful once the stock records are trustworthy.
		$plBasis = (!empty($_POST['pl_basis']) && $_POST['pl_basis'] === 'stock')
			? 'stock'
			: 'purchases';

		if ($plBasis === 'stock') {
			// Opening stock is the closing position of the day before the period.
			$openingStock = $productsObj->getStockValuation(
				$params['shopId'],
				date('Y-m-d', strtotime($params['fromDate'] . ' -1 day'))
			);
			$closingStock = $productsObj->getStockValuation($params['shopId'], $params['toDate']);

			// Products whose stock records are wrong enough to move the bottom line.
			$attention = $productsObj->getStockAttentionRows(
				$openingStock,
				$closingStock,
				$params['shopId'],
				$params['fromDate'],
				$params['toDate'],
				20
			);
		} else {
			// Nothing outside the range is read at all.
			$openingStock = $closingStock = null;
			$attention    = $productsObj->getUnbackedSalesRows(
				$params['shopId'],
				$params['fromDate'],
				$params['toDate'],
				20
			);
		}

		$subtitle = ($plBasis === 'stock' ? 'Profit and Loss Account' : 'Trading Account') . $subtitle;

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
					<th align="right"><?php echo number_format($reportDataOther['footer'][$value], 0); ?></th>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php if ($reportType == '10') {
	if (!empty($reportDataOther['purchaseRecords'])) { ?>
		<h4 style="margin: 10px 0">Purchases</h4>
		<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
			<thead>
				<tr>
					<th width="30">S.#</th>
					<th>Account Code</th>
					<th>Account Title</th>
					<?php foreach ($modesList['records'] as $m) { ?>
						<th><?php echo $m['title']; ?></th>
					<?php } ?>
					<th>Credit Purchase</th>
				</tr>
			</thead>
			<tbody>
				<?php $pCount = 1;
				foreach ($reportDataOther['purchaseRecords'] as $row) { ?>
					<tr>
						<td width="10"><?php echo $pCount; ?></td>
						<td><?php echo $row['code']; ?></td>
						<td><?php echo $row['title']; ?></td>
						<?php foreach ($modesList['records'] as $m) { ?>
							<td align="right"><?php echo number_format($row[$m['id']], 0); ?></td>
						<?php } ?>
						<td align="right"><?php echo number_format($row['netCreditPurchases'], 0); ?></td>
					</tr>
				<?php $pCount++;
				} ?>
				<tr>
					<th></th>
					<th>Total</th>
					<th><?php echo number_format($reportDataOther['purchaseFooter']['totalCredit'], 0); ?></th>
					<?php foreach ($modesList['records'] as $m) { ?>
						<th align="right"><?php echo number_format($reportDataOther['purchaseFooter'][$m['id']], 0); ?></th>
					<?php } ?>
					<th align="right"><?php echo number_format($reportDataOther['purchaseFooter']['netCreditPurchases'], 0); ?></th>
				</tr>
			</tbody>
		</table>
	<?php }
	if (!empty($reportDataOther['expenses']['accounts'])) {
		$expenseData = $reportDataOther['expenses'];
		// One column per date is unreadable past a month, so switch to months.
		$periods = count($expenseData['dates']) > 31 ? $expenseData['months'] : $expenseData['dates'];
		$periodLabel = count($expenseData['dates']) > 31 ? 'Month' : 'Date';
	?>
		<h4 style="margin: 10px 0">Expenses Summery</h4>
		<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
			<thead>
				<tr>
					<th width="30">S.#</th>
					<th align="left">Expense Account</th>
					<?php foreach ($modesList['records'] as $rr) { ?>
						<th align="right"><?php echo $rr['title']; ?></th>
					<?php } ?>
					<th align="right">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php $eCount = 1;
				foreach ($expenseData['accounts'] as $account) { ?>
					<tr>
						<td width="10"><?php echo $eCount; ?></td>
						<td align="left"><?php echo $account['title']; ?></td>
						<?php foreach ($modesList['records'] as $rr) {
							$tag = ($cashModeId == $rr['id']) ? 'th' : 'td';
						?>
							<<?php echo $tag; ?> align="right"><?php echo number_format(!empty($account['modes'][$rr['id']]) ? $account['modes'][$rr['id']] : 0, 0); ?></<?php echo $tag; ?>>
						<?php } ?>
						<th align="right"><?php echo number_format($account['total'], 0); ?></th>
					</tr>
				<?php $eCount++;
				} ?>
				<tr>
					<th></th>
					<th align="left">Total</th>
					<?php foreach ($modesList['records'] as $rr) { ?>
						<th align="right"><?php echo number_format(!empty($expenseData['total'][$rr['id']]) ? $expenseData['total'][$rr['id']] : 0, 0); ?></th>
					<?php } ?>
					<th align="right"><?php echo number_format($expenseData['grandTotal'], 0); ?></th>
				</tr>
			</tbody>
		</table>

		<h5 style="margin: 5px 0">Expenses by <?php echo $periodLabel; ?></h5>
		<table class="table" id="resultTable" width="100%" style="border-collapse: collapse" border="1">
			<thead>
				<tr>
					<th align="left"><?php echo $periodLabel; ?></th>
					<?php foreach ($modesList['records'] as $rr) { ?>
						<th align="right"><?php echo $rr['title']; ?></th>
					<?php } ?>
					<th align="right">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($periods as $period => $amounts) { ?>
					<tr>
						<td align="left" style="white-space: nowrap;"><?php echo $period; ?></td>
						<?php foreach ($modesList['records'] as $rr) {
							$tag = ($cashModeId == $rr['id']) ? 'th' : 'td';
						?>
							<<?php echo $tag; ?> align="right"><?php echo number_format(!empty($amounts[$rr['id']]) ? $amounts[$rr['id']] : 0, 0); ?></<?php echo $tag; ?>>
						<?php } ?>
						<th align="right"><?php echo number_format($amounts['total'], 0); ?></th>
					</tr>
				<?php } ?>
				<tr>
					<th align="left">Total</th>
					<?php foreach ($modesList['records'] as $rr) { ?>
						<th align="right"><?php echo number_format(!empty($expenseData['total'][$rr['id']]) ? $expenseData['total'][$rr['id']] : 0, 0); ?></th>
					<?php } ?>
					<th align="right"><?php echo number_format($expenseData['grandTotal'], 0); ?></th>
				</tr>
			</tbody>
		</table>
	<?php } ?>
	<h5 style="margin: 5px 0">Other Totals</h5>
	<table id="resultTable" class="table" style="border-collapse: collapse;" border="1">
		<tr>
			<th rowspan="2" align="left" style="width: 50%">Account</th>
			<?php foreach (array_keys($reportDataOther['otherTotals']['totals']) as $key) { ?>
				<th align="left" colspan="<?php echo count($modesList['records']); ?>"><?php echo $key; ?></th>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach (array_keys($reportDataOther['otherTotals']['totals']) as $key) { ?>
				<?php foreach ($modesList['records'] as $rr) {
					$tag = ($cashModeId == $rr['id']) ? 'th' : 'td';
				?>
					<<?php echo $tag; ?> align="left"><?php echo $rr['title']; ?></<?php echo $tag; ?>>
			<?php }
			} ?>
		</tr>
		<?php foreach ($reportDataOther['otherList'] as $accountId => $value) {
		?>
			<tr>
				<th align="left"><?php echo array_values(array_values($value)[0])[0]['title']; ?></th>
				<?php foreach (array_keys($reportDataOther['otherTotals']['totals']) as $key) {
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
			<?php

			foreach ($reportDataOther['otherTotals']['totals'] as $val) {
				foreach ($modesList['records'] as $row) {
					$tag = ($cashModeId == $row['id']) ? 'th' : 'td';
			?>
					<<?php echo $tag; ?> align="left"><?php echo $val[$row['id']]; ?></<?php echo $tag; ?>>
			<?php }
			} ?>
		</tr>

	</table>
	<?php


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
			<th align="right"><?php echo number_format($reportDataOther['sale_returns'], 0); ?></td>
		</tr>
		<tr>
			<th align="left">Sale via CASH</th>
			<th align="right"><?php echo number_format($reportDataOther['cashSale'], 0); ?></th>
			<th align="left">Purchase via CASH</th>
			<th align="right"><?php echo number_format($reportDataOther['purchasePayments'], 0); ?></th>
		</tr>
		<tr>
			<th align="left">Receivings</th>
			<th align="right"><?php echo number_format($reportDataOther['receivings'], 0); ?></th>
			<th align="left">Payments</th>
			<th align="right"><?php echo number_format($reportDataOther['payments'], 0); ?></th>
		</tr>
		<tr>
			<th align="left">Purchase Returns</th>
			<th align="right"><?php echo number_format($reportDataOther['purchase_returns'], 0); ?></th>
			<th align="left">Expenses</th>
			<th align="right"><?php echo number_format($reportDataOther['texpense'], 0); ?></th>
		</tr>
		<tr>
			<th align="left">Pay via locker or Cash out</th>
			<th align="right"><?php echo number_format($reportDataOther['withdrawal'], 0); ?></th>
			<th align="left">Deposit In Locker</td>
			<th align="right"><?php echo number_format($reportDataOther['deposit'], 0); ?></td>
		</tr>

		<tr>
			<th align="left">Total</th>
			<th align="right"><?php echo number_format($reportDataOther['totalSale'], 0); ?></th>
			<th align="left">Total</th>
			<th align="right"><?php echo number_format($reportDataOther['deduction'], 0); ?></th>
		</tr>
	</table>

	<h5 style="margin: 5px 0">Totals</h5>
	<table id="resultTable" class="table" style="border-collapse: collapse;" border="1">
		<tr>
			<th align="left">Total Sale</th>
			<th align="right"><?php echo number_format($reportDataOther['totalSale'], 0); ?></th>
		</tr>
		<tr>
			<th align="left">Total Deduction</th>
			<th align="right"><?php echo number_format($reportDataOther['deduction'], 0); ?></th>
		</tr>
		<tr>
			<th align="left">Net Total</th>
			<th align="right"><?php echo number_format($reportDataOther['totalNetSale'], 0); ?></th>
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