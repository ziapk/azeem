<?php
/**
 * Informal Profit Summary.
 *
 * Profit is taken straight off each order line -- (price - discount - pprice)
 * per unit -- rather than from the double-entry ledger. That makes it a quick
 * operational read, not a statutory statement: it ignores stock movement, and
 * any product with no purchase price counts its whole sale value as profit.
 *
 * Expects from print.php:
 *   $margin, $byPublisher, $byCustomer, $returnMargin, $expenseRows,
 *   $cashMovement, $reportTitle, $subtitle
 */

$money = function ($n) {
	return number_format(round((float) $n));
};
$neg = function ($n) use ($money) {
	return '(' . $money(abs($n)) . ')';
};
$pct = function ($num, $den) {
	return $den != 0 ? number_format(($num / $den) * 100, 2) . '%' : '-';
};

$saleValue   = (float) $margin['sale_value'];
$costValue   = (float) $margin['cost_value'];
$salesProfit = (float) $margin['profit'];

$returnProfit = (float) $returnMargin['profit'];
$returnValue  = (float) $returnMargin['sale_value'];

$netSalesProfit = $salesProfit - $returnProfit;

$totalExpenses = 0.0;
foreach ($expenseRows as $e) {
	$totalExpenses += (float) $e['amount'];
}

// Samples, donations and promotions cost the shop their purchase price but
// earned nothing, so they come off the profit on their own line instead of
// hiding inside the sales margin as loss-making sales.
$giveawayCost = (float) $giveaways['totals']['cost_value'] - (float) $giveaways['totals']['billed_value'];

$netProfit = $netSalesProfit - $giveawayCost - $totalExpenses;

$noCostUnits = (float) $margin['units_without_cost'];
$noCostValue = (float) $margin['profit_without_cost'];

// Balances are stated as at the end of the period, not for the period.
$closingAsAt = date('d-m-Y', strtotime($to));

// Totals for the final summary. Receivables are debit-positive (owed to the
// shop); payables are flipped so a positive figure is owed by the shop.
$fsReceivable = 0.0;
foreach ($receivables as $r) {
	$fsReceivable += (float) $r['closing'];
}
$fsPayable = 0.0;
foreach ($payables as $r) {
	$fsPayable -= (float) $r['closing'];
}

ob_start();
?>
<style>
	body {
		font: 11pt Arial, sans-serif;
		line-height: 1.3;
	}

	@page {
		margin: 25px 25px 25px 40px;
		size: Legal;
	}

	h1 {
		font-size: 18pt;
		margin: 0;
		text-align: center;
	}

	h4 {
		margin: 6px 0 14px;
		text-align: center;
		font-weight: bold;
	}

	h3 {
		margin: 20px 0 6px;
		font-size: 13pt;
	}

	table {
		width: 100%;
		border-collapse: collapse;
		font-size: 11pt;
	}

	td,
	th {
		padding: 5px 8px;
		border: 1px solid #000;
	}

	th {
		text-align: left;
		background: #eee;
	}

	.section th {
		background: #ddd;
		text-transform: uppercase;
	}

	.amt {
		text-align: right;
		width: 150px;
		white-space: nowrap;
	}

	.num {
		text-align: right;
		white-space: nowrap;
	}

	.subtotal td,
	.subtotal th {
		font-weight: bold;
		background: #f6f6f6;
	}

	.grand td,
	.grand th {
		font-weight: bold;
		background: #e4e4e4;
		font-size: 12pt;
	}

	.loss {
		color: #a00;
	}

	.note {
		margin-top: 14px;
		font-size: 9pt;
		border: 1px solid #999;
		padding: 8px 10px;
	}

	.note h5 {
		margin: 0 0 6px;
		font-size: 10pt;
	}

	.note ul {
		margin: 0;
		padding-left: 18px;
	}

	table.brk {
		font-size: 9.5pt;
	}

	table.brk td {
		text-align: right;
		white-space: nowrap;
	}

	table.brk td.l,
	table.brk th.l {
		text-align: left;
		white-space: normal;
	}

	table.brk th {
		text-align: right;
	}

	table.brk tfoot td,
	table.brk tfoot th {
		font-weight: bold;
		background: #f6f6f6;
	}
	table.fs td.sign {
		width: 26px;
		text-align: center;
		font-weight: bold;
	}
</style>

<h1><?php echo $reportTitle; ?></h1>
<h4><?php echo $subtitle; ?></h4>

<table>
	<tbody>
		<tr class="section">
			<th colspan="2">Sales Profit &mdash; price &minus; discount &minus; purchase price</th>
		</tr>
		<tr>
			<td>Sale Value <small>(after discount)</small></td>
			<td class="amt"><?php echo $money($saleValue); ?></td>
		</tr>
		<tr>
			<td>Less: Purchase Cost of those items</td>
			<td class="amt"><?php echo $neg($costValue); ?></td>
		</tr>
		<tr class="subtotal">
			<th>Total Sales Profit</th>
			<td class="amt"><?php echo $money($salesProfit); ?></td>
		</tr>
		<tr>
			<td>Margin on sales</td>
			<td class="amt"><?php echo $pct($salesProfit, $saleValue); ?></td>
		</tr>

		<?php if ($nonStockSales['sale_value'] > 0) { ?>
			<tr>
				<td>
					Memo: Sales through amount-entry items
					<small>(<?php echo $money($nonStockSales['products']); ?> row(s), no cost recorded &mdash; not included in profit)</small>
				</td>
				<td class="amt"><?php echo $money($nonStockSales['sale_value']); ?></td>
			</tr>
		<?php } ?>

		<tr class="section">
			<th colspan="2">What was sold</th>
		</tr>
		<tr>
			<td>Total products sold <small>(distinct items)</small></td>
			<td class="amt"><?php echo $money($margin['products']); ?></td>
		</tr>
		<tr>
			<td>Total units sold</td>
			<td class="amt"><?php echo $money($margin['units']); ?></td>
		</tr>
		<tr>
			<td>Total bills</td>
			<td class="amt"><?php echo $money($margin['orders']); ?></td>
		</tr>

		<?php if ($returnValue != 0 || $returnProfit != 0) { ?>
			<tr class="section">
				<th colspan="2">Returns</th>
			</tr>
			<tr>
				<td>Returned sale value <small>(<?php echo $money($returnMargin['units']); ?> units on
						<?php echo $money($returnMargin['returns_count']); ?> returns)</small></td>
				<td class="amt"><?php echo $neg($returnValue); ?></td>
			</tr>
			<tr class="subtotal">
				<th>Less: Profit given back on returns</th>
				<td class="amt"><?php echo $neg($returnProfit); ?></td>
			</tr>
			<tr class="subtotal">
				<th>Net Sales Profit</th>
				<td class="amt"><?php echo $money($netSalesProfit); ?></td>
			</tr>
		<?php } ?>

		<?php if ($giveaways['totals']['units'] > 0) { ?>
			<tr class="section">
				<th colspan="2">Samples &amp; Donations &mdash; goods given away</th>
			</tr>
			<?php foreach ($giveaways['customers'] as $g) { ?>
				<tr>
					<td>
						<?php echo htmlspecialchars($g['label']); ?>
						<small>(<?php echo $money($g['units']); ?> units on <?php echo $money($g['bills']); ?> bill(s), net cost)</small>
					</td>
					<td class="amt"><?php echo $neg($g['cost_value'] - $g['billed_value']); ?></td>
				</tr>
			<?php } ?>
			<tr class="subtotal">
				<th>Less: Cost of goods given away</th>
				<td class="amt"><?php echo $neg($giveawayCost); ?></td>
			</tr>
		<?php } ?>

		<tr class="section">
			<th colspan="2">Expenses</th>
		</tr>
		<?php if (empty($expenseRows)) { ?>
			<tr>
				<td colspan="2"><em>No expenses recorded in this period.</em></td>
			</tr>
		<?php } else {
			foreach ($expenseRows as $e) { ?>
				<tr>
					<td><?php echo htmlspecialchars($e['title']); ?></td>
					<td class="amt"><?php echo $money($e['amount']); ?></td>
				</tr>
			<?php }
		} ?>
		<tr class="subtotal">
			<th>Total Expenses</th>
			<td class="amt"><?php echo $neg($totalExpenses); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr class="grand">
			<th><?php echo $netProfit < 0 ? 'Net Loss' : 'Net Profit'; ?></th>
			<td class="amt <?php echo $netProfit < 0 ? 'loss' : ''; ?>">
				<?php echo $netProfit < 0 ? $neg($netProfit) : $money($netProfit); ?>
			</td>
		</tr>
	</tfoot>
</table>

<h3>Money Movement <small style="font-weight: normal">&mdash; cash in and out, not part of the profit above</small></h3>
<table>
	<tbody>
		<tr>
			<td>Total Receivings <small>(from customers)</small></td>
			<td class="amt"><?php echo $money($cashMovement['received_from_customers']); ?></td>
		</tr>
		<tr>
			<td>Total Payments to Suppliers</td>
			<td class="amt"><?php echo $money($cashMovement['paid_to_suppliers']); ?></td>
		</tr>
		<?php if ($cashMovement['paid_to_customers'] != 0) { ?>
			<tr>
				<td>Paid to Customers <small>(clearing money the shop owed them &mdash; not refunds for returns)</small></td>
				<td class="amt"><?php echo $money($cashMovement['paid_to_customers']); ?></td>
			</tr>
		<?php } ?>
		<tr class="subtotal">
			<th>Net Cash Movement</th>
			<td class="amt">
				<?php
				$netCash = $cashMovement['received_from_customers']
					- $cashMovement['paid_to_suppliers']
					- $cashMovement['paid_to_customers'];
				echo $netCash < 0 ? $neg($netCash) : $money($netCash);
				?>
			</td>
		</tr>
	</tbody>
</table>

<?php
$breakdowns = [
	'Profit by Supplier / Publisher' => ['rows' => $byPublisher, 'head' => 'Supplier / Publisher'],
	'Profit by Customer'             => ['rows' => $byCustomer,  'head' => 'Customer'],
];
foreach ($breakdowns as $heading => $bd) {
	if (empty($bd['rows'])) {
		continue;
	} ?>
	<h3><?php echo $heading; ?> <small style="font-weight: normal">&mdash; top <?php echo count($bd['rows']); ?> by profit</small></h3>
	<table class="brk">
		<thead>
			<tr>
				<th class="l">#</th>
				<th class="l"><?php echo $bd['head']; ?></th>
				<th>Products</th>
				<th>Units Sold</th>
				<th>Sale Value</th>
				<th>Cost</th>
				<th>Profit</th>
				<th>Margin</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
			$tProfit = $tSale = $tCost = $tUnits = 0;
			foreach ($bd['rows'] as $r) {
				$tProfit += $r['profit'];
				$tSale   += $r['sale_value'];
				$tCost   += $r['cost_value'];
				$tUnits  += $r['units']; ?>
				<tr>
					<td class="l"><?php echo $i; ?></td>
					<td class="l"><?php echo htmlspecialchars($r['label']); ?></td>
					<td><?php echo $money($r['products']); ?></td>
					<td><?php echo $money($r['units']); ?></td>
					<td><?php echo $money($r['sale_value']); ?></td>
					<td><?php echo $money($r['cost_value']); ?></td>
					<td class="<?php echo $r['profit'] < 0 ? 'loss' : ''; ?>">
						<?php echo $r['profit'] < 0 ? $neg($r['profit']) : $money($r['profit']); ?>
					</td>
					<td><?php echo $pct($r['profit'], $r['sale_value']); ?></td>
				</tr>
			<?php $i++;
			} ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="l" colspan="3">Total of rows listed</th>
				<td><?php echo $money($tUnits); ?></td>
				<td><?php echo $money($tSale); ?></td>
				<td><?php echo $money($tCost); ?></td>
				<td><?php echo $money($tProfit); ?></td>
				<td><?php echo $pct($tProfit, $tSale); ?></td>
			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php
// Receivable / payable summaries. Payables are held credit-positive, so the
// stored debit-positive figures are flipped for display.
$balanceBlocks = [
	[
		'heading'  => 'Receivable Summary &mdash; Customers',
		'party'    => 'Customer',
		'rows'     => $receivables,
		'sign'     => 1,
		'up'       => 'Billed in Period',
		'down'     => 'Received in Period',
		'owed'     => 'owed to the shop',
	],
	[
		'heading'  => 'Payable Summary &mdash; Suppliers',
		'party'    => 'Supplier',
		'rows'     => $payables,
		'sign'     => -1,
		'up'       => 'Purchased in Period',
		'down'     => 'Paid in Period',
		'owed'     => 'owed by the shop',
	],
];

foreach ($balanceBlocks as $b) {
	if (empty($b['rows'])) {
		continue;
	}

	$sign = $b['sign'];
	// Totals cover every party; the table itself shows the largest balances.
	$tOpening = $tUp = $tDown = $tClosing = 0;
	foreach ($b['rows'] as $r) {
		$tOpening += $sign * $r['opening'];
		$tUp      += $sign > 0 ? $r['period_debit']  : $r['period_credit'];
		$tDown    += $sign > 0 ? $r['period_credit'] : $r['period_debit'];
		$tClosing += $sign * $r['closing'];
	}

	$shown = $b['rows'];
	usort($shown, function ($x, $y) use ($sign) {
		return abs($sign * $y['closing']) <=> abs($sign * $x['closing']);
	});
	$shown = array_slice($shown, 0, 25);
	?>
	<h3><?php echo $b['heading']; ?>
		<small style="font-weight: normal">
			&mdash; balance <?php echo $b['owed']; ?> as at <?php echo $closingAsAt; ?>;
			top <?php echo count($shown); ?> of <?php echo count($b['rows']); ?>
		</small>
	</h3>
	<table class="brk">
		<thead>
			<tr>
				<th class="l">#</th>
				<th class="l"><?php echo $b['party']; ?></th>
				<th>Opening Balance</th>
				<th><?php echo $b['up']; ?></th>
				<th><?php echo $b['down']; ?></th>
				<th>Closing Balance</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
			foreach ($shown as $r) {
				$opening = $sign * $r['opening'];
				$up      = $sign > 0 ? $r['period_debit']  : $r['period_credit'];
				$down    = $sign > 0 ? $r['period_credit'] : $r['period_debit'];
				$closing = $sign * $r['closing']; ?>
				<tr>
					<td class="l"><?php echo $i; ?></td>
					<td class="l"><?php echo htmlspecialchars($r['title']); ?></td>
					<td class="<?php echo $opening < 0 ? 'loss' : ''; ?>">
						<?php echo $opening < 0 ? $neg($opening) : $money($opening); ?>
					</td>
					<td><?php echo $money($up); ?></td>
					<td><?php echo $money($down); ?></td>
					<td class="<?php echo $closing < 0 ? 'loss' : ''; ?>">
						<strong><?php echo $closing < 0 ? $neg($closing) : $money($closing); ?></strong>
					</td>
				</tr>
			<?php $i++;
			} ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="l" colspan="2">Total &mdash; all <?php echo count($b['rows']); ?> parties</th>
				<td><?php echo $tOpening < 0 ? $neg($tOpening) : $money($tOpening); ?></td>
				<td><?php echo $money($tUp); ?></td>
				<td><?php echo $money($tDown); ?></td>
				<td><?php echo $tClosing < 0 ? $neg($tClosing) : $money($tClosing); ?></td>
			</tr>
		</tfoot>
	</table>
	<p style="font-size:9pt; margin:4px 0 0">
		A bracketed figure is the reverse of the usual direction &mdash;
		<?php echo $sign > 0
			? 'an advance held from a customer rather than money they owe.'
			: 'an overpayment sitting with a supplier rather than money the shop owes.'; ?>
	</p>
<?php } ?>

<?php if (!empty($giveaways['products'])) { ?>
	<h3>Samples &amp; Donations <small style="font-weight: normal">&mdash; what was given away, top <?php echo count($giveaways['products']); ?> by cost</small></h3>
	<p style="font-size:9.5pt; margin:0 0 8px">
		Bills for sample, donation and promotion customers are not sales, so they
		are kept out of the sales profit and the loss lists. The stock still left
		the shop: <?php echo $money($giveaways['totals']['units']); ?> units costing
		<?php echo $money($giveaways['totals']['cost_value']); ?><?php if ($giveaways['totals']['billed_value'] > 0) { ?>,
		less <?php echo $money($giveaways['totals']['billed_value']); ?> that was billed<?php } ?> &mdash;
		<strong><?php echo $money($giveawayCost); ?></strong> deducted from the profit above.
	</p>
	<table class="brk">
		<thead>
			<tr>
				<th class="l">#</th>
				<th class="l">Product</th>
				<th>Units</th>
				<th>Value at Sale Price</th>
				<th>Cost</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
			foreach ($giveaways['products'] as $r) { ?>
				<tr>
					<td class="l"><?php echo $i; ?></td>
					<td class="l">
						<?php echo htmlspecialchars($r['full_name']); ?>
						<small>(#<?php echo $r['product_id']; ?>)</small>
					</td>
					<td><?php echo $money($r['units']); ?></td>
					<td><?php echo $money($r['value_at_price']); ?></td>
					<td><?php echo $money($r['cost_value']); ?></td>
				</tr>
			<?php $i++;
			} ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="l" colspan="2">Total (all giveaways in period)</th>
				<td><?php echo $money($giveaways['totals']['units']); ?></td>
				<td><?php echo $money($giveaways['totals']['value_at_price']); ?></td>
				<td><?php echo $money($giveaways['totals']['cost_value']); ?></td>
			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php if (!empty($writeOffs['rows'])) { ?>
	<h3>Stock Write-offs <small style="font-weight: normal">&mdash; zero-value bills, excluded from the profit above</small></h3>
	<p style="font-size:9.5pt; margin:0 0 8px">
		These bills were fully discounted to nothing, so no sale took place &mdash;
		they are stock being written out of the system. They are kept out of the
		sales margin, but the goods did leave the shop, so their cost is shown here.
		<?php echo $money($writeOffs['totals']['orders']); ?> bill(s),
		<?php echo $money($writeOffs['totals']['units']); ?> units,
		<strong><?php echo $money($writeOffs['totals']['cost_value']); ?></strong> at cost.
	</p>
	<table class="brk">
		<thead>
			<tr>
				<th class="l">#</th>
				<th class="l">Bill</th>
				<th class="l">Reason / Customer</th>
				<th>Date</th>
				<th>Units</th>
				<th>Value at Sale Price</th>
				<th>Cost</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
			foreach ($writeOffs['rows'] as $r) { ?>
				<tr>
					<td class="l"><?php echo $i; ?></td>
					<td class="l">#<?php echo $r['order_id']; ?></td>
					<td class="l"><?php echo htmlspecialchars($r['reason']); ?></td>
					<td><?php echo date('d-m-Y', strtotime($r['order_date'])); ?></td>
					<td><?php echo $money($r['units']); ?></td>
					<td><?php echo $money($r['gross_value']); ?></td>
					<td><?php echo $money($r['cost_value']); ?></td>
				</tr>
			<?php $i++;
			} ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="l" colspan="4">Total (all write-offs in period)</th>
				<td><?php echo $money($writeOffs['totals']['units']); ?></td>
				<td><?php echo $money($writeOffs['totals']['gross_value']); ?></td>
				<td><?php echo $money($writeOffs['totals']['cost_value']); ?></td>
			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php
$fixLink = SITE_URL . 'pages/product/fix-pprice.php?shopId=' . (int) $shopId
	. '&amp;from=' . urlencode($from) . '&amp;to=' . urlencode($to);

$lossGroups = [
	'cost_above_list' => [
		'heading' => 'Needs Attention &mdash; Purchase Price at or above List Price',
		'intro'   => 'The recorded purchase price is at or above the list price, so these
			lose money on every sale whatever the discount. That is almost always a data
			error &mdash; typically a pack price recorded against a per-piece selling
			price. Correct them in <a href="' . $fixLink . '" target="_blank"><strong>Fix
			Purchase Prices</strong></a>.',
	],
	'discounted' => [
		'heading' => 'Sold Below Cost after Discount',
		'intro'   => 'The list price covers the cost, but the price actually received
			&mdash; after line and bill discounts &mdash; fell below it. These are genuine
			losses from discounting, not data errors: compare <strong>Avg Sold At</strong>
			with <strong>Purchase Price</strong>. <strong>Below List</strong> is how far
			the price received sat under the list price, after every discount.',
	],
];

foreach ($lossGroups as $key => $grp) {
	$lg = $lossMakers[$key];
	if (empty($lg['rows'])) {
		continue;
	} ?>
	<h3 style="color:#a00"><?php echo $grp['heading']; ?>
		<small style="font-weight: normal">&mdash; top <?php echo count($lg['rows']); ?> of <?php echo $lg['count']; ?></small>
	</h3>
	<p style="font-size:9.5pt; margin:0 0 8px"><?php echo $grp['intro']; ?></p>
	<table class="brk">
		<thead>
			<tr>
				<th class="l">#</th>
				<th class="l">Product</th>
				<th>List Price</th>
				<th>Purchase Price</th>
				<th>Avg Sold At</th>
				<th>Below List</th>
				<th>Units Sold</th>
				<th>Sale Value</th>
				<th>Cost</th>
				<th>Loss</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
			foreach ($lg['rows'] as $r) { ?>
				<tr>
					<td class="l"><?php echo $i; ?></td>
					<td class="l">
						<?php echo htmlspecialchars($r['full_name']); ?>
						<small>(#<?php echo $r['product_id']; ?>)</small>
					</td>
					<td><?php echo $money($r['list_price']); ?></td>
					<td><?php echo $money($r['pprice']); ?></td>
					<td class="loss"><?php echo number_format($r['avg_sold_at'], 2); ?></td>
					<td><?php echo $r['below_list_pct'] === null ? '&mdash;' : number_format($r['below_list_pct'], 1) . '%'; ?></td>
					<td><?php echo $money($r['units']); ?></td>
					<td><?php echo $money($r['sale_value']); ?></td>
					<td><?php echo $money($r['cost_value']); ?></td>
					<td class="loss"><?php echo $neg($r['profit']); ?></td>
				</tr>
			<?php $i++;
			} ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="l" colspan="9">Total loss &mdash; all <?php echo $lg['count']; ?> product(s) in this group</th>
				<td class="loss"><?php echo $neg($lg['loss']); ?></td>
			</tr>
		</tfoot>
	</table>
<?php } ?>

<div class="note">
	<h5>How to read this report</h5>
	<ul>
		<li>
			Profit is taken from each bill line as
			<strong>(price &minus; discount &minus; purchase price) &times; quantity</strong>.
			It does not use opening or closing stock, and it is not a statutory
			Profit &amp; Loss &mdash; use the Trading Account report for that.
		</li>
		<?php if ($nonStockSales['sale_value'] > 0) { ?>
			<li>
				Amount-entry rows (products flagged as non-stock) are kept out of the
				margin calculation &mdash; their quantity holds a rupee amount, not a
				unit count, so a purchase price against them is meaningless. Their
				<strong><?php echo $money($nonStockSales['sale_value']); ?></strong> of
				sales is shown on its own line with no cost attached.
			</li>
		<?php } ?>
		<?php if ($giveaways['totals']['units'] > 0) { ?>
			<li>
				Bills for customers marked <strong>Giveaway Account</strong> on the Update
				Customer page (samples, donations, promotions) are treated as goods given
				away, not sales. Tick that box on any new account of this kind.
			</li>
		<?php } ?>
		<?php if ($noCostUnits > 0) { ?>
			<li>
				<strong><?php echo $money($noCostUnits); ?> units</strong> were sold on products
				with no purchase price set. Their full sale value of
				<strong><?php echo $money($noCostValue); ?></strong> is counted as profit here,
				so the figures above are overstated by up to that amount.
			</li>
		<?php } ?>
		<li>
			<strong>Returns</strong> are goods a customer brought back. A return reduces what
			that customer owes; it never pays cash out by itself.
			<strong>Paid to Customers</strong> is cash the shop actually paid a customer &mdash;
			usually to clear a credit balance, where the customer had paid in advance or
			the shop also buys from them. The two are unrelated and never overlap.
		</li>
		<li>
			Money Movement is cash in and out during the period. It does not belong in
			the profit calculation &mdash; a receipt may settle a bill from an earlier
			period, and a bill raised now may be paid later.
		</li>
	</ul>
</div>

<?php
/*
 * Final Summary: every figure above that moves money, one line each with its
 * sign, so the bottom lines can be checked by adding down the column.
 */
// Subtracting a negative figure is an addition -- show it as one, rather than
// as "minus (x)".
$fsLine = function ($op, $label, $amount) use ($money) {
	$amount = (float) $amount;
	if ($amount < 0) {
		$sign   = $op === '+' ? '&minus;' : '+';
		$amount = -$amount;
	} else {
		$sign = $op === '+' ? '+' : '&minus;';
	}
	return '<tr><td class="sign">' . $sign . '</td><td>' . $label . '</td><td class="amt">'
		. $money($amount) . '</td></tr>';
};
$fsTotal = function ($label, $amount, $class = 'subtotal') use ($money, $neg) {
	$amount = (float) $amount;
	return '<tr class="' . $class . '"><td class="sign">=</td><th>' . $label . '</th><td class="amt'
		. ($amount < 0 ? ' loss' : '') . '">' . ($amount < 0 ? $neg($amount) : $money($amount)) . '</td></tr>';
};

$fsNetCash = (float) $cashMovement['received_from_customers']
	- (float) $cashMovement['paid_to_suppliers']
	- (float) $cashMovement['paid_to_customers'];
?>

<h3>Final Summary</h3>
<table class="fs">
	<tbody>
		<tr class="section">
			<th colspan="3">Profit &mdash; <?php echo $from; ?> to <?php echo $to; ?></th>
		</tr>
		<?php
		echo $fsLine('+', 'Sale value (after discount)', $saleValue);
		echo $fsLine('-', 'Purchase cost of items sold', $costValue);
		echo $fsTotal('Sales Profit', $salesProfit);
		if ($returnProfit != 0) {
			echo $fsLine('-', 'Profit given back on returns', $returnProfit);
		}
		if ($giveawayCost != 0) {
			echo $fsLine('-', 'Cost of goods given away (samples &amp; donations)', $giveawayCost);
		}
		echo $fsLine('-', 'Expenses', $totalExpenses);
		echo $fsTotal($netProfit < 0 ? 'Net Loss' : 'Net Profit', $netProfit, 'grand');
		?>

		<tr class="section">
			<th colspan="3">Cash &mdash; <?php echo $from; ?> to <?php echo $to; ?></th>
		</tr>
		<?php
		echo $fsLine('+', 'Received from customers', $cashMovement['received_from_customers']);
		echo $fsLine('-', 'Paid to suppliers', $cashMovement['paid_to_suppliers']);
		if ($cashMovement['paid_to_customers'] != 0) {
			echo $fsLine('-', 'Paid to customers', $cashMovement['paid_to_customers']);
		}
		echo $fsTotal('Net Cash Movement', $fsNetCash);
		?>

		<tr class="section">
			<th colspan="3">Balances &mdash; as at <?php echo $closingAsAt; ?></th>
		</tr>
		<?php
		echo $fsLine('+', 'Owed to the shop by customers', $fsReceivable);
		echo $fsLine('-', 'Owed by the shop to suppliers', $fsPayable);
		echo $fsTotal('Net Position', $fsReceivable - $fsPayable);
		?>

		<?php if ($nonStockSales['sale_value'] > 0 || (float) $writeOffs['totals']['cost_value'] != 0) { ?>
			<tr class="section">
				<th colspan="3">For reference &mdash; not included in the profit above</th>
			</tr>
			<?php if ($nonStockSales['sale_value'] > 0) { ?>
				<tr>
					<td class="sign"></td>
					<td>Sales through amount-entry items (no cost recorded)</td>
					<td class="amt"><?php echo $money($nonStockSales['sale_value']); ?></td>
				</tr>
			<?php } ?>
			<?php if ((float) $writeOffs['totals']['cost_value'] != 0) { ?>
				<tr>
					<td class="sign"></td>
					<td>Stock written off on zero-value bills, at cost</td>
					<td class="amt"><?php echo $money($writeOffs['totals']['cost_value']); ?></td>
				</tr>
			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<?php
$html = ob_get_clean();
echo $html;
exit;
