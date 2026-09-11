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

$netProfit = $netSalesProfit - $totalExpenses;

$noCostUnits = (float) $margin['units_without_cost'];
$noCostValue = (float) $margin['profit_without_cost'];

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
					Plus: Sales through amount-entry items
					<small>(<?php echo $money($nonStockSales['products']); ?> row(s), no cost recorded)</small>
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
				<td>Payments refunded to Customers</td>
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

<?php if (!empty($lossMakers)) { ?>
	<h3 style="color:#a00">Needs Attention &mdash; Sold Below Cost</h3>
	<p style="font-size:9.5pt; margin:0 0 8px">
		These products show a loss on this basis. Usually the purchase price is
		wrong &mdash; a pack price recorded against a per-piece selling price &mdash;
		rather than the goods genuinely being sold below cost. Check
		<strong>List Price</strong> against <strong>Purchase Price</strong>.
		<br>
		Correct them in
		<a href="<?php echo SITE_URL; ?>pages/product/fix-pprice.php?shopId=<?php echo (int) $shopId; ?>&amp;from=<?php echo urlencode($from); ?>&amp;to=<?php echo urlencode($to); ?>"
			target="_blank"><strong>Fix Purchase Prices</strong></a>, which lists every
		affected product with the cost actually paid and lets you edit it inline.
	</p>
	<table class="brk">
		<thead>
			<tr>
				<th class="l">#</th>
				<th class="l">Product</th>
				<th>List Price</th>
				<th>Purchase Price</th>
				<th>Units Sold</th>
				<th>Sale Value</th>
				<th>Cost</th>
				<th>Loss</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;
			$lossTotal = 0;
			foreach ($lossMakers as $r) {
				$lossTotal += $r['profit']; ?>
				<tr>
					<td class="l"><?php echo $i; ?></td>
					<td class="l">
						<?php echo htmlspecialchars($r['full_name']); ?>
						<small>(#<?php echo $r['product_id']; ?>)</small>
					</td>
					<td><?php echo $money($r['list_price']); ?></td>
					<td><?php echo $money($r['pprice']); ?></td>
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
				<th class="l" colspan="7">Total loss on the products listed above</th>
				<td class="loss"><?php echo $neg($lossTotal); ?></td>
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
		<?php if ($noCostUnits > 0) { ?>
			<li>
				<strong><?php echo $money($noCostUnits); ?> units</strong> were sold on products
				with no purchase price set. Their full sale value of
				<strong><?php echo $money($noCostValue); ?></strong> is counted as profit here,
				so the figures above are overstated by up to that amount.
			</li>
		<?php } ?>
		<li>
			Money Movement is cash in and out during the period. It does not belong in
			the profit calculation &mdash; a receipt may settle a bill from an earlier
			period, and a bill raised now may be paid later.
		</li>
	</ul>
</div>

<?php
$html = ob_get_clean();
echo $html;
exit;
