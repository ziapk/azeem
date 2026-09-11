<?php
/**
 * Profit & Loss statement.
 *
 * Expects, from accounting.php case '12':
 *   $pl            breakdown from DoubleEntry::getPLStatementBreakdown()
 *   $expenseRows   rows from DoubleEntry::getPLExpenseRows()
 *   $openingStock  / $closingStock  from Products::getStockValuation()
 *   $reportTitle, $subtitle, $srNo, $params
 */

$money = function ($n) {
	$n = round((float) $n);
	return number_format($n);
};
// Contra / deduction lines print in brackets.
$neg = function ($n) use ($money) {
	return '(' . $money(abs($n)) . ')';
};

$netSales     = $pl['gross_sales'] - $pl['sale_returns'] - $pl['sale_discount'];
$netPurchases = $pl['gross_purchases'] - $pl['purchase_returns'] - $pl['purchase_discount'];

$useStock = ($plBasis === 'stock');

if (!$useStock) {
	// Trading account: gross profit is net sales less net purchases, both drawn
	// only from transactions inside the date range. No stock figure is read, so
	// nothing from before the range can reach this statement.
	$cogs        = $netPurchases;
	$grossProfit = $netSales - $netPurchases;
} else {

// Stock actually held, i.e. ignoring any negative on-hand shortfall.
$openOnHand  = (float) $openingStock['positive']['value'];
$closeOnHand = (float) $closingStock['positive']['value'];
$cogsOnHand  = $openOnHand + $netPurchases - $closeOnHand;

// A negative on-hand quantity is stock that was sold but never recorded as
// bought, so its cost belongs in COGS. Only the *movement* over the period
// matters -- a shortfall carried in unchanged from last year is not this
// year's cost.
$openShortfall     = (float) $openingStock['negative']['value'];
$closeShortfall    = (float) $closingStock['negative']['value'];
$shortfallMovement = ($openingStock['negative_mode'] === 'exclude')
	? 0.0
	: $openShortfall - $closeShortfall;

$openingValue = $openOnHand + ($openingStock['negative_mode'] === 'exclude' ? 0.0 : $openShortfall);
$closingValue = $closeOnHand + ($closingStock['negative_mode'] === 'exclude' ? 0.0 : $closeShortfall);

$cogs        = $cogsOnHand + $shortfallMovement;
$grossProfit = $netSales - $cogs;

}

$totalExpenses = 0.0;
foreach ($expenseRows as $e) {
	$totalExpenses += (float) $e['amount'];
}

$netResult = $grossProfit - $totalExpenses;

$grossMargin = $netSales != 0 ? ($grossProfit / $netSales) * 100 : 0;
$netMargin   = $netSales != 0 ? ($netResult / $netSales) * 100 : 0;

$hasDataIssues = !empty($pl['unclassified']) || ($useStock && (
	$openingStock['negative']['products'] > 0
	|| $closingStock['negative']['products'] > 0
	|| $openingStock['unpriced']['products'] > 0
	|| $closingStock['unpriced']['products'] > 0
	|| !empty($closingStock['non_stock_items'])
));

ob_start();
?>
<style>
	body {
		font: 11pt Arial, sans-serif;
		line-height: 1.3;
	}

	@page {
		margin: 25px 25px 25px 40px;
		<?php if (empty($params['pdf'])) { ?>size: Legal;<?php } ?>
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

	table.pl {
		width: 100%;
		border-collapse: collapse;
		font-size: 11pt;
	}

	table.pl td,
	table.pl th {
		padding: 5px 8px;
		border: 1px solid #000;
	}

	table.pl th {
		text-align: left;
		background: #eee;
	}

	.section th {
		background: #ddd;
		font-size: 11.5pt;
		text-transform: uppercase;
	}

	.amt {
		text-align: right;
		width: 150px;
		white-space: nowrap;
	}

	.sub {
		padding-left: 26px !important;
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

	h3.attn {
		margin: 22px 0 4px;
		font-size: 13pt;
		color: #a00;
	}

	h3.attn+p {
		margin: 0 0 8px;
		font-size: 9.5pt;
	}

	table.attn {
		width: 100%;
		border-collapse: collapse;
		font-size: 9.5pt;
	}

	table.attn td,
	table.attn th {
		padding: 4px 6px;
		border: 1px solid #000;
	}

	table.attn th {
		background: #f0dede;
		text-align: right;
	}

	table.attn th.l,
	table.attn td.l {
		text-align: left;
	}

	table.attn td {
		text-align: right;
		white-space: nowrap;
	}

	table.attn tfoot td,
	table.attn tfoot th {
		font-weight: bold;
		background: #f6f6f6;
	}
</style>

<h1><?php echo $reportTitle; ?></h1>
<h4><?php echo $subtitle; ?></h4>

<table class="pl" id="resultTable">
	<thead>
		<tr>
			<th>Particulars</th>
			<th class="amt">Amount</th>
			<th class="amt">Total</th>
		</tr>
	</thead>
	<tbody>
		<tr class="section">
			<th colspan="3">Revenue</th>
		</tr>
		<tr>
			<td>Gross Sales</td>
			<td class="amt"><?php echo $money($pl['gross_sales']); ?></td>
			<td class="amt"></td>
		</tr>
		<tr>
			<td class="sub">Less: Sale Returns</td>
			<td class="amt"><?php echo $neg($pl['sale_returns']); ?></td>
			<td class="amt"></td>
		</tr>
		<tr>
			<td class="sub">Less: Sale Discount</td>
			<td class="amt"><?php echo $neg($pl['sale_discount']); ?></td>
			<td class="amt"></td>
		</tr>
		<tr class="subtotal">
			<th>Net Sales</th>
			<td class="amt"></td>
			<td class="amt"><?php echo $money($netSales); ?></td>
		</tr>

		<tr class="section">
			<th colspan="3"><?php echo $useStock ? 'Cost of Goods Sold' : 'Purchases'; ?></th>
		</tr>
		<?php if ($useStock) { ?>
			<tr>
				<td>Opening Stock on hand <small>(as at <?php echo $openingStock['as_of']; ?>)</small></td>
				<td class="amt"><?php echo $money($openOnHand); ?></td>
				<td class="amt"></td>
			</tr>
		<?php } ?>
		<tr>
			<td>Gross Purchases</td>
			<td class="amt"><?php echo $money($pl['gross_purchases']); ?></td>
			<td class="amt"></td>
		</tr>
		<tr>
			<td class="sub">Less: Purchase Returns</td>
			<td class="amt"><?php echo $neg($pl['purchase_returns']); ?></td>
			<td class="amt"></td>
		</tr>
		<tr>
			<td class="sub">Less: Purchase Discount</td>
			<td class="amt"><?php echo $neg($pl['purchase_discount']); ?></td>
			<td class="amt"></td>
		</tr>
		<?php if ($useStock) { ?>
			<tr class="subtotal">
				<td>Net Purchases</td>
				<td class="amt"><?php echo $money($netPurchases); ?></td>
				<td class="amt"></td>
			</tr>
			<tr>
				<td>Less: Closing Stock on hand <small>(as at <?php echo $closingStock['as_of']; ?>)</small></td>
				<td class="amt"><?php echo $neg($closeOnHand); ?></td>
				<td class="amt"></td>
			</tr>
			<?php if ($shortfallMovement != 0) { ?>
				<tr>
					<td>
						Stock shortfall movement
						<small>(negative on-hand stock &mdash; sold but never stocked)</small>
					</td>
					<td class="amt">
						<?php echo $shortfallMovement < 0 ? $neg($shortfallMovement) : $money($shortfallMovement); ?>
					</td>
					<td class="amt"></td>
				</tr>
			<?php } ?>
		<?php } ?>
		<tr class="subtotal">
			<th><?php echo $useStock ? 'Cost of Goods Sold' : 'Net Purchases'; ?></th>
			<td class="amt"></td>
			<td class="amt"><?php echo $money($cogs); ?></td>
		</tr>

		<tr class="grand">
			<th>Gross Profit</th>
			<td class="amt"></td>
			<td class="amt <?php echo $grossProfit < 0 ? 'loss' : ''; ?>">
				<?php echo $grossProfit < 0 ? $neg($grossProfit) : $money($grossProfit); ?>
			</td>
		</tr>

		<tr class="section">
			<th colspan="3">Operating Expenses</th>
		</tr>
		<?php if (empty($expenseRows)) { ?>
			<tr>
				<td colspan="3"><em>No expenses recorded in this period.</em></td>
			</tr>
		<?php } else {
			$count = 1;
			foreach ($expenseRows as $e) { ?>
				<tr>
					<td><?php if ($srNo) {
							echo $count . '. ';
						} ?><?php echo htmlspecialchars($e['title']); ?></td>
					<td class="amt"><?php echo $money($e['amount']); ?></td>
					<td class="amt"></td>
				</tr>
			<?php $count++;
			}
		} ?>
		<tr class="subtotal">
			<th>Total Expenses</th>
			<td class="amt"></td>
			<td class="amt"><?php echo $money($totalExpenses); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr class="grand">
			<th><?php echo $netResult < 0 ? 'Net Loss' : 'Net Profit'; ?></th>
			<td class="amt"></td>
			<td class="amt <?php echo $netResult < 0 ? 'loss' : ''; ?>">
				<?php echo $netResult < 0 ? $neg($netResult) : $money($netResult); ?>
			</td>
		</tr>
		<tr>
			<td>Gross Margin</td>
			<td class="amt"></td>
			<td class="amt"><?php echo number_format($grossMargin, 2); ?>%</td>
		</tr>
		<tr>
			<td>Net Margin</td>
			<td class="amt"></td>
			<td class="amt"><?php echo number_format($netMargin, 2); ?>%</td>
		</tr>
	</tfoot>
</table>

<?php if ($hasDataIssues) { ?>
	<div class="note">
		<h5>Stock shortfall &amp; data quality</h5>
		<ul>
			<?php foreach (['Opening' => $openingStock, 'Closing' => $closingStock] as $label => $sv) {
				if ($sv['negative']['products'] > 0) { ?>
					<li>
						<strong><?php echo $label; ?> stock:</strong>
						<?php echo $sv['negative']['products']; ?> product(s) hold a negative
						on-hand quantity (<?php echo $money($sv['negative']['units']); ?> units,
						<?php echo $money($sv['negative']['value']); ?> at cost). These are
						treated as sold, so their cost is carried into Cost of Goods Sold.
						<?php $worst = reset($sv['shortfall_items']);
						if (!empty($worst)) { ?>
							Largest: <?php echo htmlspecialchars($worst['full_name']); ?>
							(#<?php echo $worst['product_id']; ?>,
							<?php echo $money($worst['qty']); ?> units,
							<?php echo $money($worst['value']); ?>).
						<?php } ?>
					</li>
				<?php }
				if ($sv['unpriced']['products'] > 0) { ?>
					<li>
						<strong><?php echo $label; ?> stock:</strong>
						<?php echo $sv['unpriced']['products']; ?> product(s) holding
						<?php echo $money($sv['unpriced']['units']); ?> units have no purchase
						price set, so they are valued at zero.
					</li>
				<?php }
			} ?>
			<?php if (!empty($closingStock['non_stock_items'])) { ?>
				<li>
					<strong>Non-stock items:</strong>
					<?php echo $closingStock['non_stock']['products']; ?> amount-entry /
					service row(s) are excluded from stock valuation &mdash; their quantity
					field holds a rupee amount, not a unit count, so they were never
					inventory. <em>Their sales remain in Revenue above.</em>
					<?php $names = array_slice(array_column($closingStock['non_stock_items'], 'full_name'), 0, 4); ?>
					e.g. <?php echo htmlspecialchars(implode(', ', $names)); ?>.
				</li>
			<?php } ?>
			<?php if ($shortfallMovement != 0) { ?>
				<li>
					<strong>Effect on this statement:</strong> the shortfall changed by
					<?php echo $money($shortfallMovement); ?> over the period, which is the
					amount added to Cost of Goods Sold above. Cost of Goods Sold on held
					stock alone would be <?php echo $money($cogsOnHand); ?>.
				</li>
			<?php } ?>
			<?php if (!empty($pl['unclassified'])) { ?>
				<li>
					<strong>Ledger:</strong> <?php echo count($pl['unclassified']); ?> entr(ies)
					on the trading accounts carry a transaction type this report does not
					recognise and were left out of revenue and purchases.
				</li>
			<?php } ?>
		</ul>
	</div>
<?php } ?>

<?php if (!empty($attention['rows'])) { ?>
	<h3 class="attn">Needs Attention &mdash; Stock Records</h3>
	<?php if ($useStock) { ?>
		<p>
			These products hold a negative on-hand quantity: the sale is real, but the
			stock was never recorded as bought. Their cost is included in Cost of Goods
			Sold above. Showing the <?php echo $attention['shown']; ?> largest of
			<?php echo $attention['total']; ?> affected product(s), by effect on COGS.
		</p>
		<table class="attn">
			<thead>
				<tr>
					<th class="l">#</th>
					<th class="l">Product</th>
					<th>Opening Qty</th>
					<th>Purchase Qty</th>
					<th>Sale Qty</th>
					<th>Return In</th>
					<th>Closing Qty</th>
					<th>Purchase Amt</th>
					<th>Sale Amt</th>
					<th>Effect on COGS</th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1;
				$effectTotal = 0;
				foreach ($attention['rows'] as $r) {
					$effectTotal += $r['cogs_effect']; ?>
					<tr>
						<td class="l"><?php echo $i; ?></td>
						<td class="l">
							<?php echo htmlspecialchars($r['full_name']); ?>
							<small>(#<?php echo $r['product_id']; ?>)</small>
						</td>
						<td><?php echo $money($r['opening_qty']); ?></td>
						<td><?php echo $money($r['purchase_units']); ?></td>
						<td><?php echo $money($r['sale_units']); ?></td>
						<td><?php echo $money($r['return_in_units']); ?></td>
						<td><?php echo $money($r['closing_qty']); ?></td>
						<td><?php echo $money($r['purchase_amount']); ?></td>
						<td><?php echo $money($r['sale_amount']); ?></td>
						<td><?php echo $r['cogs_effect'] < 0 ? $neg($r['cogs_effect']) : $money($r['cogs_effect']); ?></td>
					</tr>
				<?php $i++;
				} ?>
			</tbody>
			<tfoot>
				<tr>
					<th class="l" colspan="9">Total effect of the products listed above</th>
					<td><?php echo $effectTotal < 0 ? $neg($effectTotal) : $money($effectTotal); ?></td>
				</tr>
			</tfoot>
		</table>
	<?php } else { ?>
		<p>
			Sold inside this period without a matching purchase entry in the same
			period. These figures do <strong>not</strong> affect the Trading Account
			above &mdash; it is built from sales and purchases only. This is the list of
			stock that went out with no supply record, valued at purchase price.
			Showing the <?php echo $attention['shown']; ?> largest of
			<?php echo $attention['total']; ?> affected product(s).
		</p>
		<table class="attn">
			<thead>
				<tr>
					<th class="l">#</th>
					<th class="l">Product</th>
					<th>Purchase Qty</th>
					<th>Return In</th>
					<th>Sale Qty</th>
					<th>Unbacked Qty</th>
					<th>Purchase Amt</th>
					<th>Sale Amt</th>
					<th>Unbacked at Cost</th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1;
				$gapTotal = 0;
				foreach ($attention['rows'] as $r) {
					$gapTotal += $r['gap_value']; ?>
					<tr>
						<td class="l"><?php echo $i; ?></td>
						<td class="l">
							<?php echo htmlspecialchars($r['full_name']); ?>
							<small>(#<?php echo $r['product_id']; ?>)</small>
						</td>
						<td><?php echo $money($r['purchase_units']); ?></td>
						<td><?php echo $money($r['return_in_units']); ?></td>
						<td><?php echo $money($r['sale_units']); ?></td>
						<td><?php echo $money($r['gap_units']); ?></td>
						<td><?php echo $money($r['purchase_amount']); ?></td>
						<td><?php echo $money($r['sale_amount']); ?></td>
						<td><?php echo $money($r['gap_value']); ?></td>
					</tr>
				<?php $i++;
				} ?>
			</tbody>
			<tfoot>
				<tr>
					<th class="l" colspan="8">Total unbacked stock at cost (listed above)</th>
					<td><?php echo $money($gapTotal); ?></td>
				</tr>
			</tfoot>
		</table>
	<?php } ?>
<?php } ?>

<?php
$html = ob_get_clean();
echo $html;
exit;
