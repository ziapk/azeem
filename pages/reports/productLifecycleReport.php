<?php
// productLifecycleReport.php — whole-life story of one product in one shop.
// Expects $life from print.php :: case '27' (Products::getProductLifecycle()).

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

function lcMoney($v, $dp = 0)
{
    $v = (float) $v;
    return ($v < 0 ? '-' : '') . number_format(abs($v), $dp);
}

function lcQty($v)
{
    $v = (float) $v;
    return number_format($v, floor($v) == $v ? 0 : 2);
}

function lcDate($d)
{
    return $d ? date('d M Y', strtotime($d)) : '—';
}

function lcDays($from, $to = null)
{
    if (!$from) return null;
    $a = new DateTime(substr($from, 0, 10));
    $b = new DateTime($to ? substr($to, 0, 10) : 'today');
    return (int) $a->diff($b)->format('%r%a');
}

function lcSpan($days)
{
    if ($days === null) return '—';
    if ($days < 60) return $days . ' days';
    $y = intdiv($days, 365);
    $m = intdiv($days % 365, 30);
    return trim(($y ? $y . ' yr ' : '') . ($m ? $m . ' mo' : ''));
}

function lcPl($v)
{
    return $v > 0 ? 'pos' : ($v < 0 ? 'neg' : 'zero');
}

$typeInfo = [
    'purchase'     => ['label' => 'Purchase',        'cls' => 'b-purchase', 'group' => 'purchase'],
    'sale'         => ['label' => 'Sale',            'cls' => 'b-sale',     'group' => 'sale'],
    'return_in'    => ['label' => 'Customer return', 'cls' => 'b-return',   'group' => 'return'],
    'return_out'   => ['label' => 'Return to supplier', 'cls' => 'b-return', 'group' => 'return'],
    'exchange_in'  => ['label' => 'Exchange in',     'cls' => 'b-exchange', 'group' => 'exchange'],
    'exchange_out' => ['label' => 'Exchange out',    'cls' => 'b-exchange', 'group' => 'exchange'],
];

$p   = $life['product'];
$s   = $life['summary'];
$ev  = $life['events'];

$listPrice  = (float) $p['store_price'] > 0 ? (float) $p['store_price'] : (float) $p['price'];
$stockNow   = (float) $p['ledger_qty'];
$stockCost  = max(0, $stockNow) * $s['avg_cost'];
$stockList  = max(0, $stockNow) * $listPrice;
$netCash    = $s['cash_in'] - $s['cash_out'];
$position   = $netCash + $stockCost;
$margin     = $s['revenue'] > 0 ? $s['gross_profit'] / $s['revenue'] * 100 : null;
$qtyIn      = $s['purchased_qty'] + $s['exchange_in_qty'];
$sellThru   = $qtyIn > 0 ? min(100, $s['sold_qty'] / $qtyIn * 100) : null;
$ageDays    = lcDays($p['created_at'] ?: $p['store_created_at']);
$sinceSale  = lcDays($s['last_sale']);
$activeDays = $s['first_sale'] ? max(1, lcDays($s['first_sale'], $s['last_sale']) + 1) : null;
$perMonth   = $activeDays ? $s['sold_qty'] / max(1, $activeDays / 30.44) : null;
$cover      = ($perMonth && $stockNow > 0) ? $stockNow / $perMonth : null;
$mismatch   = $p['cached_qty'] !== null && abs((float) $p['cached_qty'] - $stockNow) > 0.001;
$recovered  = $s['cash_out'] > 0 ? $s['cash_in'] / $s['cash_out'] * 100 : null;

// Where the product stands today.
if (!(int) $p['is_active']) {
    $status = ['Inactive', 'st-grey', 'Product is switched off.'];
} elseif (empty($ev)) {
    $status = ['No activity', 'st-grey', 'Never bought or sold in this shop.'];
} elseif ($stockNow < 0) {
    $status = ['Negative stock', 'st-red', 'More units sold than ever received — purchases are missing or quantities are wrong.'];
} elseif ($stockNow == 0 && $s['sold_qty'] > 0) {
    $status = ['Sold out', 'st-amber', 'All stock sold. Last sale ' . lcDate($s['last_sale']) . '.'];
} elseif ($s['sold_qty'] == 0) {
    $status = ['Never sold', 'st-red', lcQty($stockNow) . ' units bought, none sold yet.'];
} elseif ($sinceSale > 180) {
    $status = ['Dead stock', 'st-red', 'No sale for ' . lcSpan($sinceSale) . ' with ' . lcQty($stockNow) . ' units on the shelf.'];
} elseif ($sinceSale > 60) {
    $status = ['Slow moving', 'st-amber', 'Last sale ' . lcSpan($sinceSale) . ' ago.'];
} else {
    $status = ['Active', 'st-green', 'Selling normally.'];
}

$counts = ['all' => count($ev), 'purchase' => 0, 'sale' => 0, 'return' => 0, 'exchange' => 0];
foreach ($ev as $e) {
    $counts[$typeInfo[$e['type']]['group']]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Product Lifecycle — <?php echo htmlspecialchars($p['full_name']); ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #0f172a; background: #f8fafc; padding: 16px; }
    .wrap { max-width: 1280px; margin: 0 auto; }
    h3 { font-size: 13px; text-transform: uppercase; letter-spacing: .5px; color: #475569; margin: 22px 0 8px; }
    .muted { color: #64748b; }
    .r { text-align: right; font-variant-numeric: tabular-nums; }
    .pos { color: #15803d; }
    .neg { color: #b91c1c; }
    .zero { color: #94a3b8; }

    /* ── Header ── */
    .head { background: #1a3a7a; color: #fff; padding: 14px 16px; border-radius: 6px 6px 0 0; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .head .name { font-size: 18px; font-weight: bold; }
    .head .meta { font-size: 11px; opacity: .8; margin-top: 4px; line-height: 1.6; }
    .head .gen { font-size: 11px; opacity: .75; text-align: right; }
    .status-bar { background: #fff; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 6px 6px; padding: 10px 16px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .pill { display: inline-block; padding: 3px 10px; border-radius: 12px; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; }
    .st-green { background: #dcfce7; color: #14532d; }
    .st-amber { background: #fef3c7; color: #78350f; }
    .st-red   { background: #fee2e2; color: #991b1b; }
    .st-grey  { background: #f1f5f9; color: #475569; }

    /* ── Result hero ── */
    .hero { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 10px; margin-top: 12px; }
    .hero .box { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
    .hero .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
    .hero .big { font-size: 22px; font-weight: bold; margin: 4px 0 2px; font-variant-numeric: tabular-nums; }
    .hero .box.main { border-width: 2px; }
    .hero .box.main.pos { border-color: #86efac; background: #f0fdf4; }
    .hero .box.main.neg { border-color: #fca5a5; background: #fef2f2; }

    /* ── Cards ── */
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr)); gap: 10px; }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
    .card .ttl { background: #f1f5f9; padding: 7px 12px; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #334155; }
    .kv { width: 100%; border-collapse: collapse; }
    .kv td { padding: 5px 12px; border-top: 1px solid #f1f5f9; }
    .kv td:last-child { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
    .kv tr.total td { border-top: 2px solid #cbd5e1; font-weight: bold; font-size: 13px; }
    .kv tr.sub td { color: #64748b; font-size: 11px; }
    .kv tr.sub td:last-child { font-weight: normal; }
    .warn { background: #fffbeb; border: 1px solid #fde68a; color: #78350f; padding: 8px 12px; border-radius: 6px; margin-top: 10px; line-height: 1.5; }

    /* ── Milestones ── */
    .ms { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; display: flex; overflow-x: auto; gap: 0; }
    .ms .step { flex: 1 0 120px; position: relative; padding-top: 18px; text-align: center; }
    .ms .step::before { content: ''; position: absolute; top: 6px; left: 0; right: 0; height: 2px; background: #cbd5e1; }
    .ms .step:first-child::before { left: 50%; }
    .ms .step:last-child::before { right: 50%; }
    .ms .dot { position: absolute; top: 0; left: 50%; margin-left: -7px; width: 14px; height: 14px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 1px #cbd5e1; }
    .ms .d { font-weight: bold; font-size: 11px; }
    .ms .t { font-size: 11px; color: #475569; margin-top: 2px; padding: 0 4px; }
    .k-created  { background: #64748b; }
    .k-purchase { background: #2563eb; }
    .k-sale     { background: #16a34a; }
    .k-stockout { background: #dc2626; }
    .k-restock  { background: #7c3aed; }
    .k-today    { background: #0f172a; }

    /* ── Tables ── */
    .tbl-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow-x: auto; }
    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th { background: #f1f5f9; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; white-space: nowrap; position: sticky; top: 0; }
    table.grid th.r { text-align: right; }
    table.grid td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
    table.grid tbody tr:hover td { background: #eff6ff; }
    table.grid tfoot td { background: #1e293b; color: #f1f5f9; font-weight: bold; padding: 6px 8px; }
    table.grid tr.created td { background: #f8fafc; color: #475569; font-style: italic; }
    .two { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(420px, 100%), 1fr)); gap: 10px; }

    .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
    .b-sale     { background: #dcfce7; color: #14532d; }
    .b-purchase { background: #dbeafe; color: #1d4ed8; }
    .b-return   { background: #fef3c7; color: #78350f; }
    .b-exchange { background: #f3e8ff; color: #6b21a8; }
    .tag { display: inline-block; margin-left: 4px; padding: 0 5px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; background: #fee2e2; color: #991b1b; }
    .est { color: #b45309; font-weight: bold; }
    .ref-link { color: #1d4ed8; text-decoration: underline; cursor: pointer; background: none; border: none; font: inherit; }

    .filters { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
    .filters button { border: 1px solid #cbd5e1; background: #fff; padding: 4px 10px; border-radius: 14px; cursor: pointer; font-size: 11px; color: #334155; }
    .filters button.on { background: #1a3a7a; border-color: #1a3a7a; color: #fff; }
    .note { font-size: 11px; color: #64748b; margin-top: 6px; line-height: 1.5; }

    @media (max-width: 600px) {
        body { padding: 8px; }
        .two { grid-template-columns: 1fr; }
    }
    @media print {
        body { background: #fff; padding: 0; }
        .filters { display: none; }
        .card, .tbl-wrap, .hero .box, .ms { break-inside: avoid; }
        table.grid th { position: static; }
    }
</style>
</head>
<body>
<div class="wrap">

<script>
var siteUrl = '<?php echo addslashes($siteUrl); ?>';
function openRef(type, id) {
    var urls = {
        order:        siteUrl + 'print?id=' + id + '&detail=true&largeView=large',
        supply:       siteUrl + 'print/supply.php?id=' + id + '&detail=true&largeView=large',
        return_order: siteUrl + 'print/return.php?id=' + id + '&detail=true&largeView=large'
    };
    if (urls[type]) window.open(urls[type], '', 'width=800,height=600');
}
function filterTimeline(group, btn) {
    document.querySelectorAll('.filters button').forEach(function (b) { b.classList.remove('on'); });
    btn.classList.add('on');
    document.querySelectorAll('#timeline tbody tr').forEach(function (tr) {
        tr.style.display = (group === 'all' || tr.dataset.g === group) ? '' : 'none';
    });
}
</script>

<!-- ═══ Header ═══ -->
<div class="head">
    <div>
        <div class="name"><?php echo htmlspecialchars($p['full_name']); ?></div>
        <div class="meta">
            ID <?php echo (int) $p['id']; ?>
            &nbsp;|&nbsp; Code: <?php echo htmlspecialchars($p['code'] ?: '—'); ?>
            &nbsp;|&nbsp; Barcode: <?php echo htmlspecialchars($p['barcode'] ?: '—'); ?>
            <br>
            Publisher: <?php echo htmlspecialchars($p['publisher_name'] ?: '—'); ?>
            &nbsp;|&nbsp; Category: <?php echo htmlspecialchars($p['category_name'] ?: '—'); ?>
            <?php if ($p['location']) { ?>&nbsp;|&nbsp; Location: <?php echo htmlspecialchars($p['location']); ?><?php } ?>
            &nbsp;|&nbsp; List price: <?php echo lcMoney($listPrice); ?>
            &nbsp;|&nbsp; Cost on file: <?php echo lcMoney($p['pprice']); ?>
        </div>
    </div>
    <div class="gen">
        <strong>Product Lifecycle Report</strong><br>
        <?php echo htmlspecialchars($shopName); ?><br>
        Full history &bull; Generated <?php echo date('d M Y, H:i'); ?>
    </div>
</div>
<div class="status-bar">
    <span class="pill <?php echo $status[1]; ?>"><?php echo $status[0]; ?></span>
    <span><?php echo htmlspecialchars($status[2]); ?></span>
</div>

<!-- ═══ Headline numbers ═══ -->
<div class="hero">
    <div class="box main <?php echo lcPl($s['net_profit']); ?>">
        <div class="lbl"><?php echo $s['net_profit'] >= 0 ? 'Lifetime profit' : 'Lifetime loss'; ?></div>
        <div class="big <?php echo lcPl($s['net_profit']); ?>"><?php echo lcMoney($s['net_profit']); ?></div>
        <div class="muted">
            On units sold<?php echo $margin !== null ? ' &bull; ' . number_format($margin, 1) . '% gross margin' : ''; ?>
        </div>
    </div>
    <div class="box">
        <div class="lbl">Stock now</div>
        <div class="big <?php echo $stockNow < 0 ? 'neg' : ''; ?>"><?php echo lcQty($stockNow); ?> <span style="font-size:12px" class="muted">units</span></div>
        <div class="muted">Worth <?php echo lcMoney($stockCost); ?> at cost &bull; <?php echo lcMoney($stockList); ?> at list price</div>
    </div>
    <div class="box">
        <div class="lbl">Money in vs money out</div>
        <div class="big <?php echo lcPl($netCash); ?>"><?php echo lcMoney($netCash); ?></div>
        <div class="muted">
            <?php echo $recovered !== null ? number_format($recovered, 0) . '% of purchase spend recovered' : 'No purchase spend recorded'; ?>
        </div>
    </div>
    <div class="box">
        <div class="lbl">Life span</div>
        <div class="big"><?php echo lcSpan($ageDays); ?></div>
        <div class="muted">
            Since <?php echo lcDate($p['created_at'] ?: $p['store_created_at']); ?>
            <?php if ($sinceSale !== null) { ?>&bull; last sale <?php echo $sinceSale; ?> days ago<?php } ?>
        </div>
    </div>
</div>

<?php if ($mismatch || $stockNow < 0 || $s['estimated_cost_qty'] > 0) { ?>
<div class="warn">
    <?php if ($stockNow < 0) { ?>
        <strong>Negative stock:</strong> <?php echo lcQty(abs($stockNow)); ?> more units have left the shop than were ever received.
        Purchases are probably missing, so cost and profit below are less reliable.<br>
    <?php } ?>
    <?php if ($s['estimated_cost_qty'] > 0) { ?>
        <strong>Estimated cost:</strong> <?php echo lcQty($s['estimated_cost_qty']); ?> units left the shop before any purchase was recorded.
        They are costed at the product's cost on file (<?php echo lcMoney($p['pprice']); ?>) and marked <span class="est">*</span> in the timeline.<br>
    <?php } ?>
    <?php if ($mismatch) { ?>
        <strong>Stock mismatch:</strong> the shop shows <?php echo lcQty($p['cached_qty']); ?> but the inventory ledger adds up to <?php echo lcQty($stockNow); ?>.
    <?php } ?>
</div>
<?php } ?>

<!-- ═══ Milestones ═══ -->
<h3>Milestones</h3>
<div class="ms">
    <?php foreach ($life['milestones'] as $m) { ?>
        <div class="step">
            <span class="dot k-<?php echo $m['kind']; ?>"></span>
            <div class="d"><?php echo lcDate($m['date']); ?></div>
            <div class="t"><?php echo htmlspecialchars($m['text']); ?></div>
        </div>
    <?php } ?>
    <div class="step">
        <span class="dot k-today"></span>
        <div class="d">Today</div>
        <div class="t"><?php echo $status[0]; ?> — <?php echo lcQty($stockNow); ?> in stock</div>
    </div>
</div>

<!-- ═══ Detail cards ═══ -->
<h3>Summary</h3>
<div class="cards">
    <div class="card">
        <div class="ttl">Profit &amp; loss</div>
        <table class="kv">
            <tr><td>Gross sales (<?php echo lcQty($s['sold_qty']); ?> units)</td><td><?php echo lcMoney($s['gross_sales']); ?></td></tr>
            <tr class="sub"><td>Less discounts (line + bill)</td><td><?php echo lcMoney(-$s['discounts']); ?></td></tr>
            <tr><td>Net sales</td><td><?php echo lcMoney($s['revenue']); ?></td></tr>
            <tr class="sub"><td>Less cost of units sold</td><td><?php echo lcMoney(-$s['sale_cogs']); ?></td></tr>
            <tr><td>Gross profit</td><td class="<?php echo lcPl($s['gross_profit']); ?>"><?php echo lcMoney($s['gross_profit']); ?></td></tr>
            <?php if ($s['return_in_count'] || $s['return_out_count']) { ?>
                <tr class="sub"><td>Returns effect (refunds &amp; supplier credits vs cost)</td><td><?php echo lcMoney($s['returns_net']); ?></td></tr>
            <?php } ?>
            <?php if ($s['giveaway_qty']) { ?>
                <tr class="sub"><td>Giveaways (<?php echo lcQty($s['giveaway_qty']); ?> units)</td><td><?php echo lcMoney($s['giveaway_result']); ?></td></tr>
            <?php } ?>
            <?php if ($s['writeoff_qty']) { ?>
                <tr class="sub"><td>Write-offs (<?php echo lcQty($s['writeoff_qty']); ?> units)</td><td><?php echo lcMoney($s['writeoff_result']); ?></td></tr>
            <?php } ?>
            <tr class="total"><td><?php echo $s['net_profit'] >= 0 ? 'Net profit' : 'Net loss'; ?></td><td class="<?php echo lcPl($s['net_profit']); ?>"><?php echo lcMoney($s['net_profit']); ?></td></tr>
        </table>
    </div>

    <div class="card">
        <div class="ttl">Money in vs money out</div>
        <table class="kv">
            <tr><td>Spent on purchases</td><td><?php echo lcMoney(-$s['cash_out']); ?></td></tr>
            <tr><td>Received from sales</td><td><?php echo lcMoney($s['revenue']); ?></td></tr>
            <?php if ($s['return_in_value']) { ?><tr class="sub"><td>Refunded to customers</td><td><?php echo lcMoney(-$s['return_in_value']); ?></td></tr><?php } ?>
            <?php if ($s['return_out_value']) { ?><tr class="sub"><td>Credited by suppliers</td><td><?php echo lcMoney($s['return_out_value']); ?></td></tr><?php } ?>
            <tr><td>Net cash</td><td class="<?php echo lcPl($netCash); ?>"><?php echo lcMoney($netCash); ?></td></tr>
            <tr class="sub"><td>Plus stock still on hand, at cost</td><td><?php echo lcMoney($stockCost); ?></td></tr>
            <tr class="total"><td>Overall position</td><td class="<?php echo lcPl($position); ?>"><?php echo lcMoney($position); ?></td></tr>
        </table>
    </div>

    <div class="card">
        <div class="ttl">Purchases</div>
        <table class="kv">
            <tr><td>Purchase bills</td><td><?php echo number_format($s['purchase_count']); ?></td></tr>
            <tr><td>Units bought</td><td><?php echo lcQty($s['purchased_qty']); ?></td></tr>
            <tr><td>Total spent</td><td><?php echo lcMoney($s['purchase_value']); ?></td></tr>
            <tr><td>Average unit cost</td><td><?php echo $s['purchased_qty'] ? lcMoney($s['purchase_value'] / $s['purchased_qty'], 2) : '—'; ?></td></tr>
            <tr class="sub"><td>Cheapest / dearest unit cost</td><td><?php echo $s['min_unit_cost'] !== null ? lcMoney($s['min_unit_cost'], 2) . ' / ' . lcMoney($s['max_unit_cost'], 2) : '—'; ?></td></tr>
            <tr class="sub"><td>First / last purchase</td><td><?php echo lcDate($s['first_purchase']) . ' / ' . lcDate($s['last_purchase']); ?></td></tr>
            <?php if ($s['return_out_qty']) { ?><tr class="sub"><td>Returned to suppliers</td><td><?php echo lcQty($s['return_out_qty']); ?> units</td></tr><?php } ?>
            <?php if ($s['exchange_in_qty'] || $s['exchange_out_qty']) { ?><tr class="sub"><td>Exchanged in / out</td><td><?php echo lcQty($s['exchange_in_qty']) . ' / ' . lcQty($s['exchange_out_qty']); ?></td></tr><?php } ?>
        </table>
    </div>

    <div class="card">
        <div class="ttl">Sales &amp; movement</div>
        <table class="kv">
            <tr><td>Sale bills</td><td><?php echo number_format($s['sale_count']); ?></td></tr>
            <tr><td>Units sold</td><td><?php echo lcQty($s['sold_qty']); ?></td></tr>
            <tr><td>Average selling price</td><td><?php echo $s['sold_qty'] ? lcMoney($s['revenue'] / $s['sold_qty'], 2) : '—'; ?></td></tr>
            <tr class="sub"><td>Lowest / highest unit price</td><td><?php echo $s['min_unit_price'] !== null ? lcMoney($s['min_unit_price'], 2) . ' / ' . lcMoney($s['max_unit_price'], 2) : '—'; ?></td></tr>
            <tr class="sub"><td>First / last sale</td><td><?php echo lcDate($s['first_sale']) . ' / ' . lcDate($s['last_sale']); ?></td></tr>
            <tr><td>Sell-through</td><td><?php echo $sellThru !== null ? number_format($sellThru, 0) . '%' : '—'; ?></td></tr>
            <tr><td>Selling rate</td><td><?php echo $perMonth !== null ? lcQty(round($perMonth, 1)) . ' / month' : '—'; ?></td></tr>
            <tr class="sub"><td>Current stock lasts</td><td><?php echo $cover !== null ? number_format($cover, 1) . ' months' : '—'; ?></td></tr>
            <tr class="sub"><td>Customer returns</td><td><?php echo lcQty($s['return_in_qty']); ?> units (<?php echo $s['return_in_count']; ?> returns)</td></tr>
            <tr class="sub"><td>Times stock ran out</td><td><?php echo $s['stockouts']; ?></td></tr>
        </table>
    </div>
</div>

<?php if (!empty($life['monthly'])) { ?>
<!-- ═══ Monthly ═══ -->
<h3>Month by month</h3>
<div class="tbl-wrap">
    <table class="grid">
        <thead>
            <tr>
                <th>Month</th>
                <th class="r">Units in</th>
                <th class="r">Purchase spend</th>
                <th class="r">Units sold</th>
                <th class="r">Returned</th>
                <th class="r">Net sales</th>
                <th class="r">Profit / loss</th>
                <th class="r">Stock at month end</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (array_reverse($life['monthly'], true) as $ym => $m) { ?>
            <tr>
                <td><?php echo date('M Y', strtotime($ym . '-01')); ?></td>
                <td class="r"><?php echo $m['in_qty'] ? lcQty($m['in_qty']) : ''; ?></td>
                <td class="r"><?php echo $m['purchase_value'] ? lcMoney($m['purchase_value']) : ''; ?></td>
                <td class="r"><?php echo $m['sold_qty'] ? lcQty($m['sold_qty']) : ''; ?></td>
                <td class="r"><?php echo $m['returned_qty'] ? lcQty($m['returned_qty']) : ''; ?></td>
                <td class="r"><?php echo $m['revenue'] ? lcMoney($m['revenue']) : ''; ?></td>
                <td class="r <?php echo lcPl($m['profit']); ?>"><?php echo $m['profit'] ? lcMoney($m['profit']) : ''; ?></td>
                <td class="r <?php echo $m['end_stock'] < 0 ? 'neg' : ''; ?>"><?php echo lcQty($m['end_stock']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>

<?php if (!empty($life['suppliers']) || !empty($life['customers'])) { ?>
<div class="two">
    <div>
        <h3>Suppliers</h3>
        <div class="tbl-wrap">
            <table class="grid">
                <thead><tr><th>Supplier</th><th class="r">Bills</th><th class="r">Units</th><th class="r">Spent</th><th class="r">Avg cost</th><th>Last</th></tr></thead>
                <tbody>
                <?php foreach ($life['suppliers'] as $r) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td class="r"><?php echo $r['count']; ?></td>
                        <td class="r"><?php echo lcQty($r['qty']); ?></td>
                        <td class="r"><?php echo lcMoney($r['value']); ?></td>
                        <td class="r"><?php echo $r['qty'] ? lcMoney($r['value'] / $r['qty'], 2) : '—'; ?></td>
                        <td><?php echo lcDate($r['last']); ?></td>
                    </tr>
                <?php } ?>
                <?php if (empty($life['suppliers'])) { ?><tr><td colspan="6" class="muted">No purchases recorded.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <h3>Top customers <span class="muted" style="text-transform:none;letter-spacing:0">(by units, <?php echo count($life['customers']); ?> in total)</span></h3>
        <div class="tbl-wrap">
            <table class="grid">
                <thead><tr><th>Customer</th><th class="r">Bills</th><th class="r">Units</th><th class="r">Net sales</th><th class="r">Profit</th><th>Last</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($life['customers'], 0, 15) as $r) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td class="r"><?php echo $r['count']; ?></td>
                        <td class="r"><?php echo lcQty($r['qty']); ?></td>
                        <td class="r"><?php echo lcMoney($r['value']); ?></td>
                        <td class="r <?php echo lcPl($r['profit']); ?>"><?php echo lcMoney($r['profit']); ?></td>
                        <td><?php echo lcDate($r['last']); ?></td>
                    </tr>
                <?php } ?>
                <?php if (empty($life['customers'])) { ?><tr><td colspan="6" class="muted">No sales recorded.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } ?>

<!-- ═══ Full timeline ═══ -->
<h3>Full timeline</h3>
<div class="filters">
    <button class="on" onclick="filterTimeline('all', this)">All (<?php echo $counts['all']; ?>)</button>
    <button onclick="filterTimeline('purchase', this)">Purchases (<?php echo $counts['purchase']; ?>)</button>
    <button onclick="filterTimeline('sale', this)">Sales (<?php echo $counts['sale']; ?>)</button>
    <?php if ($counts['return']) { ?><button onclick="filterTimeline('return', this)">Returns (<?php echo $counts['return']; ?>)</button><?php } ?>
    <?php if ($counts['exchange']) { ?><button onclick="filterTimeline('exchange', this)">Exchanges (<?php echo $counts['exchange']; ?>)</button><?php } ?>
</div>
<div class="tbl-wrap">
    <table class="grid" id="timeline">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Event</th>
                <th>Reference</th>
                <th>Supplier / customer</th>
                <th class="r">In</th>
                <th class="r">Out</th>
                <th class="r">Stock</th>
                <th class="r">Unit price</th>
                <th class="r">Amount</th>
                <th class="r">Cost</th>
                <th class="r">Profit / loss</th>
                <th class="r">Running P/L</th>
                <th class="r">Avg cost</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($p['created_at'] || $p['store_created_at']) { ?>
            <tr class="created" data-g="all">
                <td></td>
                <td><?php echo lcDate($p['created_at'] ?: $p['store_created_at']); ?></td>
                <td colspan="12">Product created — list price <?php echo lcMoney($p['price']); ?>, cost on file <?php echo lcMoney($p['pprice']); ?></td>
            </tr>
            <?php } ?>
            <?php
            $n = 1;
            foreach ($ev as $e) {
                $ti  = $typeInfo[$e['type']];
                $in  = in_array($e['type'], ['purchase', 'return_in', 'exchange_in']);
                $unit = $e['qty'] > 0 && $e['value'] ? $e['value'] / $e['qty'] : null;
            ?>
            <tr data-g="<?php echo $ti['group']; ?>">
                <td class="muted"><?php echo $n++; ?></td>
                <td><?php echo lcDate($e['date']); ?></td>
                <td>
                    <span class="badge <?php echo $ti['cls']; ?>"><?php echo $ti['label']; ?></span>
                    <?php foreach ($e['tags'] as $tag) { ?><span class="tag"><?php echo $tag; ?></span><?php } ?>
                </td>
                <td>
                    <?php if (in_array($e['ref_type'], ['order', 'supply', 'return_order'])) { ?>
                        <button class="ref-link" onclick="openRef('<?php echo $e['ref_type']; ?>', <?php echo (int) $e['ref_id']; ?>)"><?php echo htmlspecialchars($e['ref_label']); ?></button>
                    <?php } else { ?>
                        <?php echo htmlspecialchars($e['ref_label']); ?>
                    <?php } ?>
                </td>
                <td><?php echo htmlspecialchars($e['party']); ?></td>
                <td class="r pos"><?php echo $in ? '+' . lcQty($e['qty']) : ''; ?></td>
                <td class="r neg"><?php echo !$in ? '-' . lcQty($e['qty']) : ''; ?></td>
                <td class="r <?php echo $e['balance'] < 0 ? 'neg' : ''; ?>" style="font-weight:600"><?php echo lcQty($e['balance']); ?></td>
                <td class="r"><?php echo $unit !== null ? lcMoney($unit, 2) : ''; ?></td>
                <td class="r"><?php echo $e['value'] ? lcMoney($e['value']) : ''; ?></td>
                <td class="r muted"><?php echo $e['cost'] ? lcMoney($e['cost']) . ($e['estimated'] ? '<span class="est">*</span>' : '') : ''; ?></td>
                <td class="r <?php echo lcPl($e['profit']); ?>"><?php echo $e['profit'] ? lcMoney($e['profit']) : ''; ?></td>
                <td class="r <?php echo lcPl($e['cum_profit']); ?>"><?php echo lcMoney($e['cum_profit']); ?></td>
                <td class="r muted"><?php echo $e['avg_cost'] ? lcMoney($e['avg_cost'], 2) : ''; ?></td>
            </tr>
            <?php } ?>
            <?php if (empty($ev)) { ?>
                <tr><td colspan="14" class="muted" style="text-align:center;padding:20px">No purchases, sales, returns or exchanges recorded for this product in this shop.</td></tr>
            <?php } ?>
        </tbody>
        <?php if (!empty($ev)) { ?>
        <tfoot>
            <tr>
                <td colspan="5">Lifetime totals</td>
                <td class="r">+<?php echo lcQty($s['purchased_qty'] + $s['return_in_qty'] + $s['exchange_in_qty']); ?></td>
                <td class="r">-<?php echo lcQty($s['sold_qty'] + $s['giveaway_qty'] + $s['writeoff_qty'] + $s['return_out_qty'] + $s['exchange_out_qty']); ?></td>
                <td class="r"><?php echo lcQty($s['timeline_stock']); ?></td>
                <td colspan="3"></td>
                <td class="r"><?php echo lcMoney($s['net_profit']); ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        <?php } ?>
    </table>
</div>

<p class="note">
    <strong>How profit is worked out:</strong> each unit that leaves is costed at the average cost of the stock on hand at that moment
    (moving weighted average; every purchase updates it). Sale amounts are after line discounts and this item's share of any bill discount.
    Customer returns reverse the sale: the refund is taken back and the units return to stock at the current average cost.
    Giveaways and write-off bills move stock without income, so they show as a loss at cost. Exchanges move stock only.
    Parked, cancelled and deleted bills are left out, exactly as in the stock ledger.
</p>

</div>
</body>
</html>
