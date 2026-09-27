<?php
// seasonReport.php — product-wise season stats for a date range, compared with
// the same dates last year. Optional customer / supplier / publisher / product.
// Expects $season from print.php :: case '29' (Products::getSeasonReport()).

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

function snMoney($v, $dp = 0)
{
    $v = (float) $v;
    return ($v < 0 ? '-' : '') . number_format(abs($v), $dp);
}

function snQty($v)
{
    $v = (float) $v;
    return number_format($v, floor($v) == $v ? 0 : 2);
}

function snDate($d)
{
    return $d ? date('d M Y', strtotime($d)) : '—';
}

function snPl($v)
{
    return $v > 0 ? 'pos' : ($v < 0 ? 'neg' : 'zero');
}

function snCell($v, $fmt = 'qty')
{
    if (!(float) $v) return '';
    return $fmt === 'money' ? snMoney($v) : snQty($v);
}

// Change against last season, e.g. "+12%" / "-30%" / "new".
function snDelta($now, $ly, $invert = false)
{
    $now = (float) $now;
    $ly  = (float) $ly;
    if (!$ly) {
        return $now ? '<span class="delta pos">new</span>' : '';
    }
    $pct  = ($now - $ly) / abs($ly) * 100;
    $good = $invert ? $pct < 0 : $pct > 0;
    // Past +1000% a percentage stops meaning much (1 unit -> 1,721 units); show the multiple.
    $text = ($pct >= 1000 && $ly > 0)
        ? '×' . number_format($now / $ly, 0)
        : ($pct > 0 ? '+' : '') . number_format($pct, 0) . '%';
    return '<span class="delta ' . ($pct == 0 ? 'zero' : ($good ? 'pos' : 'neg')) . '">' . $text . '</span>';
}

function snAvailable(array $p)
{
    return max(0, $p['opening_qty']) + $p['bought_qty'] + $p['return_in_qty'] + $p['exchange_in_qty'];
}

$typeInfo = [
    'purchase'     => ['label' => 'Purchase',           'cls' => 'b-purchase', 'group' => 'purchase'],
    'sale'         => ['label' => 'Sale',               'cls' => 'b-sale',     'group' => 'sale'],
    'return_in'    => ['label' => 'Customer return',    'cls' => 'b-return',   'group' => 'return'],
    'return_out'   => ['label' => 'Return to supplier', 'cls' => 'b-return',   'group' => 'return'],
    'exchange_in'  => ['label' => 'Exchange in',        'cls' => 'b-exchange', 'group' => 'exchange'],
    'exchange_out' => ['label' => 'Exchange out',       'cls' => 'b-exchange', 'group' => 'exchange'],
];

$rg   = $season['range'];
$acc  = $season['party']['account'];
$pub  = $season['party']['publisher'];
$rows = $season['products'];
$ev   = $season['events'];

// ── Totals: party's share (t), whole product (a), last season (l) ──────────
$zero = [
    'opening_qty' => 0, 'opening_value' => 0, 'closing_qty' => 0, 'closing_value' => 0,
    'purchase_count' => 0, 'bought_qty' => 0, 'bought_value' => 0, 'return_out_qty' => 0, 'return_out_value' => 0,
    'exchange_in_qty' => 0, 'exchange_out_qty' => 0, 'sale_count' => 0, 'sold_qty' => 0, 'gross' => 0, 'discounts' => 0,
    'revenue' => 0, 'cost' => 0, 'sale_profit' => 0, 'free_qty' => 0, 'free_result' => 0,
    'return_in_qty' => 0, 'return_in_value' => 0, 'profit' => 0,
];
$t = $a = $l = $zero;
$negative = 0;
$statusCounts = [];
foreach ($rows as $r) {
    foreach ($zero as $k => $_) {
        $t[$k] += $r['party'][$k];
        $a[$k] += $r['all'][$k];
        $l[$k] += $r['ly'][$k];
    }
    // Stock totals count only what is physically there.
    $a['opening_qty'] += max(0, $r['all']['opening_qty']) - $r['all']['opening_qty'];
    $a['closing_qty'] += max(0, $r['all']['closing_qty']) - $r['all']['closing_qty'];
    if ($r['all']['closing_qty'] < 0) $negative++;

    $st = $r['status'];
    if (!isset($statusCounts[$st[0]])) $statusCounts[$st[0]] = 0;
    $statusCounts[$st[0]]++;
}
$statusDefs = [];
foreach ($productObj->lifecycleStatusDefinitions() as $label => $def) {
    if (isset($statusCounts[$label])) {
        $statusDefs[$label] = ['cls' => $def[0], 'text' => $def[1], 'n' => $statusCounts[$label]];
    }
}

$byAccount = (bool) $acc;
$hasBuy    = !$byAccount || $t['bought_qty'] > 0 || $t['return_out_qty'] > 0 || $l['bought_qty'] > 0;
$hasSell   = !$byAccount || $t['sold_qty'] > 0 || $t['return_in_qty'] > 0 || $t['free_qty'] > 0 || $l['sold_qty'] > 0;
$margin    = $t['revenue'] > 0 ? $t['sale_profit'] / $t['revenue'] * 100 : null;
$available = snAvailable($a);
$sellThru  = $available > 0 ? min(100, $a['sold_qty'] / $available * 100) : null;
$netSales  = $t['revenue'] - $t['return_in_value'];
$lyNet     = $l['revenue'] - $l['return_in_value'];

if ($acc) {
    $role  = ($hasBuy && $hasSell) ? 'Customer &amp; supplier' : ($hasBuy ? 'Supplier' : 'Customer');
    $title = $acc['title'];
} elseif ($pub) {
    $role  = 'Publisher';
    $title = $pub['full_name'];
} elseif (count($rows) === 1) {
    $role  = 'Product';
    $title = reset($rows)['product']['full_name'];
} else {
    $role  = 'Whole shop';
    $title = 'All products';
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
<title>Season Report — <?php echo htmlspecialchars($title); ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #0f172a; background: #f8fafc; padding: 16px; }
    .wrap { max-width: 1440px; margin: 0 auto; }
    h3 { font-size: 13px; text-transform: uppercase; letter-spacing: .5px; color: #475569; margin: 22px 0 8px; }
    h3 .muted { text-transform: none; letter-spacing: 0; font-weight: normal; }
    .muted { color: #64748b; }
    .r { text-align: right; font-variant-numeric: tabular-nums; }
    .pos { color: #15803d; }
    .neg { color: #b91c1c; }
    .zero { color: #94a3b8; }
    .delta { font-size: 11px; font-weight: bold; margin-left: 4px; }

    .head { background: #1a3a7a; color: #fff; padding: 14px 16px; border-radius: 6px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .head .role { font-size: 10px; text-transform: uppercase; letter-spacing: .6px; opacity: .75; }
    .head .name { font-size: 18px; font-weight: bold; margin-top: 2px; }
    .head .meta { font-size: 11px; opacity: .85; margin-top: 4px; line-height: 1.6; }
    .head .period { font-size: 14px; font-weight: bold; opacity: 1; }
    .head .gen { font-size: 11px; opacity: .75; text-align: right; }

    .hero { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap: 10px; margin-top: 12px; }
    .hero .box { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
    .hero .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
    .hero .big { font-size: 21px; font-weight: bold; margin: 4px 0 2px; font-variant-numeric: tabular-nums; }
    .hero .big .delta { font-size: 12px; }
    .hero .box.main.pos { border: 2px solid #86efac; background: #f0fdf4; }
    .hero .box.main.neg { border: 2px solid #fca5a5; background: #fef2f2; }
    .bar { height: 6px; background: #e2e8f0; border-radius: 3px; margin: 6px 0 4px; overflow: hidden; }
    .bar span { display: block; height: 100%; background: #1a3a7a; }

    .pill { display: inline-block; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 10px; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
    .st-green { background: #dcfce7; color: #14532d; }
    .st-amber { background: #fef3c7; color: #78350f; }
    .st-red   { background: #fee2e2; color: #991b1b; }
    .st-grey  { background: #f1f5f9; color: #475569; }

    .filters { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
    .filters button { border: 1px solid #cbd5e1; background: #fff; padding: 4px 10px; border-radius: 14px; cursor: pointer; font-size: 11px; color: #334155; }
    .filters button.on { background: #1a3a7a; border-color: #1a3a7a; color: #fff; }
    .legend { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 8px; font-size: 11px; }
    .legend summary { cursor: pointer; font-weight: bold; color: #334155; }
    .legend-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(420px, 100%), 1fr)); gap: 6px 16px; margin-top: 8px; }
    .legend-row { display: flex; gap: 8px; align-items: baseline; line-height: 1.4; }
    .legend-row .pill { flex: 0 0 118px; text-align: center; }

    .tbl-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow-x: auto; }
    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th { background: #f1f5f9; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; white-space: nowrap; }
    table.grid th.r { text-align: right; }
    table.grid th.grp { text-align: center; background: #e2e8f0; color: #334155; border-left: 2px solid #fff; }
    table.grid th.sortable { cursor: pointer; }
    table.grid th.sortable:hover { color: #1a3a7a; }
    table.grid td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
    table.grid td.wrap-cell { white-space: normal; min-width: 220px; }
    table.grid tbody tr:hover td { background: #eff6ff; }
    table.grid tfoot td { background: #1e293b; color: #f1f5f9; font-weight: bold; padding: 6px 8px; }
    table.grid tfoot .delta { color: #f1f5f9; }
    .sep { border-left: 2px solid #e2e8f0; }
    .two { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(420px, 100%), 1fr)); gap: 10px; }

    .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
    .b-sale     { background: #dcfce7; color: #14532d; }
    .b-purchase { background: #dbeafe; color: #1d4ed8; }
    .b-return   { background: #fef3c7; color: #78350f; }
    .b-exchange { background: #f3e8ff; color: #6b21a8; }
    .tag { display: inline-block; margin-left: 4px; padding: 0 5px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; background: #fee2e2; color: #991b1b; }
    .est { color: #b45309; font-weight: bold; }
    .link { color: #1d4ed8; text-decoration: underline; cursor: pointer; background: none; border: none; font: inherit; text-align: left; }
    .note { font-size: 11px; color: #64748b; margin-top: 6px; line-height: 1.5; }
    .empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 30px; text-align: center; color: #64748b; margin-top: 12px; }

    @media (max-width: 600px) { body { padding: 8px; } }
    @media print {
        body { background: #fff; padding: 0; }
        .filters, .legend { display: none; }
        .hero .box, .tbl-wrap { break-inside: avoid; }
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
// Opens the Product Lifecycle report (27) for one product in a new tab.
function openProduct(pid) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = 'print.php';
    f.target = '_blank';
    var fields = { reportType: 27, product_id: pid, shopId: <?php echo (int) $shopId; ?> };
    for (var k in fields) {
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = k; i.value = fields[k];
        f.appendChild(i);
    }
    document.body.appendChild(f);
    f.submit();
    f.remove();
}
function filterRows(tableId, attr, value, btn) {
    btn.parentNode.querySelectorAll('button').forEach(function (b) { b.classList.remove('on'); });
    btn.classList.add('on');
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function (tr) {
        tr.style.display = (value === 'all' || tr.getAttribute(attr) === value) ? '' : 'none';
    });
}
function sortTable(th) {
    var table = th.closest('table'), body = table.tBodies[0];
    var idx = Array.prototype.indexOf.call(th.parentNode.children, th);
    var desc = th.dataset.dir !== 'desc';
    th.parentNode.querySelectorAll('th').forEach(function (h) { delete h.dataset.dir; });
    th.dataset.dir = desc ? 'desc' : 'asc';
    var numeric = th.classList.contains('r');
    Array.prototype.slice.call(body.rows).sort(function (a, b) {
        var x = a.cells[idx].dataset.v !== undefined ? a.cells[idx].dataset.v : a.cells[idx].textContent.trim();
        var y = b.cells[idx].dataset.v !== undefined ? b.cells[idx].dataset.v : b.cells[idx].textContent.trim();
        if (numeric) { x = parseFloat(x) || 0; y = parseFloat(y) || 0; return desc ? y - x : x - y; }
        return desc ? y.localeCompare(x) : x.localeCompare(y);
    }).forEach(function (tr) { body.appendChild(tr); });
}
</script>

<!-- ═══ Header ═══ -->
<div class="head">
    <div>
        <div class="role">Season report &bull; <?php echo $role; ?></div>
        <div class="name"><?php echo htmlspecialchars($title); ?></div>
        <div class="meta">
            <span class="period"><?php echo snDate($rg['from']); ?> – <?php echo snDate($rg['to']); ?></span>
            (<?php echo $rg['days']; ?> days)
            &nbsp;|&nbsp; compared with <?php echo snDate($rg['ly_from']); ?> – <?php echo snDate($rg['ly_to']); ?>
            <br>
            <?php if ($acc && $pub) { ?>Publisher: <?php echo htmlspecialchars($pub['full_name']); ?> &nbsp;|&nbsp; <?php } ?>
            <?php echo count($rows); ?> product<?php echo count($rows) == 1 ? '' : 's'; ?>
        </div>
    </div>
    <div class="gen">
        <strong>Season Report</strong><br>
        <?php echo htmlspecialchars($shopName); ?><br>
        Generated <?php echo date('d M Y, H:i'); ?>
    </div>
</div>

<?php if (empty($rows)) { ?>
    <div class="empty">Nothing moved for this selection in either season.</div>
<?php } else { ?>

<!-- ═══ Headline numbers ═══ -->
<div class="hero">
    <?php if ($hasSell) { ?>
        <div class="box main <?php echo snPl($t['profit']); ?>">
            <div class="lbl"><?php echo $byAccount ? ($t['profit'] >= 0 ? 'Profit from this customer' : 'Loss on this customer') : ($t['profit'] >= 0 ? 'Season profit' : 'Season loss'); ?></div>
            <div class="big <?php echo snPl($t['profit']); ?>"><?php echo snMoney($t['profit']); ?><?php echo snDelta($t['profit'], $l['profit']); ?></div>
            <div class="muted">Last season <?php echo snMoney($l['profit']); ?><?php echo $margin !== null ? ' &bull; ' . number_format($margin, 1) . '% margin' : ''; ?></div>
        </div>
        <div class="box">
            <div class="lbl">Net sales<?php echo $byAccount ? ' to them' : ''; ?></div>
            <div class="big"><?php echo snMoney($netSales); ?><?php echo snDelta($netSales, $lyNet); ?></div>
            <div class="muted">Last season <?php echo snMoney($lyNet); ?><?php echo $t['return_in_value'] ? ' &bull; after ' . snMoney($t['return_in_value']) . ' refunds' : ''; ?></div>
        </div>
        <div class="box">
            <div class="lbl">Units sold<?php echo $byAccount ? ' to them' : ''; ?></div>
            <div class="big"><?php echo snQty($t['sold_qty']); ?><?php echo snDelta($t['sold_qty'], $l['sold_qty']); ?></div>
            <div class="muted">Last season <?php echo snQty($l['sold_qty']); ?> &bull; <?php echo number_format($t['sale_count']); ?> bill lines</div>
        </div>
    <?php } ?>

    <?php if ($hasBuy) { ?>
        <div class="box">
            <div class="lbl">Bought<?php echo $byAccount ? ' from them' : ''; ?></div>
            <div class="big"><?php echo snMoney($t['bought_value']); ?><?php echo snDelta($t['bought_value'], $l['bought_value']); ?></div>
            <div class="muted">
                <?php echo snQty($t['bought_qty']); ?> units (last season <?php echo snQty($l['bought_qty']); ?>)
                <?php if ($t['return_out_qty']) { ?>&bull; <?php echo snQty($t['return_out_qty']); ?> sent back<?php } ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($byAccount && $hasBuy && !$hasSell) { ?>
        <div class="box main <?php echo snPl($a['profit']); ?>">
            <div class="lbl">How their products sold</div>
            <div class="big <?php echo snPl($a['profit']); ?>"><?php echo snMoney($a['profit']); ?></div>
            <div class="muted">Season P/L, all customers &bull; <?php echo snQty($a['sold_qty']); ?> units sold</div>
        </div>
    <?php } ?>

    <div class="box">
        <div class="lbl">Sell-through<?php echo $byAccount ? ' (all customers)' : ''; ?></div>
        <div class="big"><?php echo $sellThru !== null ? number_format($sellThru, 0) . '%' : '—'; ?></div>
        <?php if ($sellThru !== null) { ?><div class="bar"><span style="width:<?php echo round($sellThru); ?>%"></span></div><?php } ?>
        <div class="muted"><?php echo snQty($a['sold_qty']); ?> sold of <?php echo snQty($available); ?> available</div>
    </div>

    <div class="box">
        <div class="lbl">Stock at season end</div>
        <div class="big"><?php echo snQty($a['closing_qty']); ?> <span class="muted" style="font-size:12px">units</span></div>
        <div class="muted">Worth <?php echo snMoney($a['closing_value']); ?> at cost &bull; started with <?php echo snQty($a['opening_qty']); ?></div>
    </div>
</div>

<!-- ═══ Product-wise ═══ -->
<h3>Product-wise <span class="muted">(click a heading to sort, a product name for its full lifecycle)</span></h3>
<div class="filters">
    <button class="on" onclick="filterRows('products', 'data-status', 'all', this)">All (<?php echo count($rows); ?>)</button>
    <?php foreach ($statusDefs as $label => $sd) { ?>
        <button title="<?php echo htmlspecialchars($sd['text']); ?>" onclick="filterRows('products', 'data-status', '<?php echo htmlspecialchars($label); ?>', this)"><?php echo htmlspecialchars($label); ?> (<?php echo $sd['n']; ?>)</button>
    <?php } ?>
</div>
<details class="legend">
    <summary>What the status tags mean <span class="muted">(status is as of today, not the season end)</span></summary>
    <div class="legend-grid">
        <?php foreach ($statusDefs as $label => $sd) { ?>
            <div class="legend-row">
                <span class="pill <?php echo $sd['cls']; ?>"><?php echo htmlspecialchars($label); ?></span>
                <span><?php echo htmlspecialchars($sd['text']); ?> <span class="muted">(<?php echo $sd['n']; ?>)</span></span>
            </div>
        <?php } ?>
    </div>
</details>
<div class="tbl-wrap">
    <table class="grid" id="products">
        <thead>
            <tr>
                <th colspan="4"></th>
                <?php if ($hasBuy) { ?><th class="grp" colspan="3"><?php echo $byAccount ? 'Bought from them' : 'Bought'; ?></th><?php } ?>
                <?php if ($hasSell) { ?><th class="grp" colspan="6"><?php echo $byAccount ? 'Sold to them' : 'Sold'; ?></th><?php } ?>
                <th class="grp" colspan="<?php echo $byAccount ? 4 : 3; ?>">Stock<?php echo $byAccount ? ' (all parties)' : ''; ?></th>
            </tr>
            <tr>
                <th class="sortable" onclick="sortTable(this)">#</th>
                <th class="sortable" onclick="sortTable(this)">Product</th>
                <th class="sortable" onclick="sortTable(this)" title="As of today — see 'What the status tags mean'">Status</th>
                <th class="r sortable" onclick="sortTable(this)" title="Stock on the first day of the season">Opening</th>
                <?php if ($hasBuy) { ?>
                    <th class="r sortable sep" onclick="sortTable(this)">Units</th>
                    <th class="r sortable" onclick="sortTable(this)">Spent</th>
                    <th class="r sortable" onclick="sortTable(this)">Returned</th>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <th class="r sortable sep" onclick="sortTable(this)">Units</th>
                    <th class="r sortable" onclick="sortTable(this)" title="Units sold in the same dates last year">Last season</th>
                    <th class="r sortable" onclick="sortTable(this)">Net sales</th>
                    <th class="r sortable" onclick="sortTable(this)">Profit / loss</th>
                    <th class="r sortable" onclick="sortTable(this)">Margin</th>
                    <th class="r sortable" onclick="sortTable(this)">Returned</th>
                <?php } ?>
                <?php if ($byAccount) { ?>
                    <th class="r sortable sep" onclick="sortTable(this)" title="Units sold to every customer this season">Sold (all)</th>
                <?php } ?>
                <th class="r sortable<?php echo $byAccount ? '' : ' sep'; ?>" onclick="sortTable(this)" title="Units sold ÷ units available (opening + bought + customer returns)">Sell-through</th>
                <th class="r sortable" onclick="sortTable(this)" title="Stock on the last day of the season">Closing</th>
                <th class="r sortable" onclick="sortTable(this)">Closing value</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $n = 1;
        foreach ($rows as $pid => $r) {
            $p   = $r['product'];
            $al  = $r['all'];
            $ps  = $r['party'];
            $ly  = $r['ly'];
            $avl = snAvailable($al);
            $st  = $avl > 0 ? min(100, $al['sold_qty'] / $avl * 100) : null;
            $pm  = $ps['revenue'] > 0 ? $ps['sale_profit'] / $ps['revenue'] * 100 : null;
        ?>
            <tr data-status="<?php echo htmlspecialchars($r['status'][0]); ?>">
                <td class="muted" data-v="<?php echo $n; ?>"><?php echo $n++; ?></td>
                <td class="wrap-cell">
                    <button class="link" onclick="openProduct(<?php echo (int) $pid; ?>)"><?php echo htmlspecialchars($p['full_name']); ?></button>
                    <?php if ($p['code']) { ?><div class="muted" style="font-size:10px"><?php echo htmlspecialchars($p['code']); ?></div><?php } ?>
                </td>
                <td><span class="pill <?php echo $r['status'][1]; ?>" title="<?php echo htmlspecialchars($r['status'][2]); ?>"><?php echo $r['status'][0]; ?></span></td>
                <td class="r <?php echo $al['opening_qty'] < 0 ? 'neg' : ''; ?>" data-v="<?php echo $al['opening_qty']; ?>"><?php echo snQty($al['opening_qty']); ?></td>
                <?php if ($hasBuy) { ?>
                    <td class="r sep" data-v="<?php echo $ps['bought_qty']; ?>"><?php echo snCell($ps['bought_qty']); ?></td>
                    <td class="r" data-v="<?php echo $ps['bought_value']; ?>"><?php echo snCell($ps['bought_value'], 'money'); ?></td>
                    <td class="r" data-v="<?php echo $ps['return_out_qty']; ?>"><?php echo snCell($ps['return_out_qty']); ?></td>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <td class="r sep" data-v="<?php echo $ps['sold_qty']; ?>"><?php echo snCell($ps['sold_qty']); ?><?php echo $ps['free_qty'] ? ' <span class="muted" title="Giveaway / write-off units">+' . snQty($ps['free_qty']) . ' free</span>' : ''; ?></td>
                    <td class="r" data-v="<?php echo $ly['sold_qty']; ?>"><?php echo snCell($ly['sold_qty']); ?><?php echo ($ps['sold_qty'] || $ly['sold_qty']) ? snDelta($ps['sold_qty'], $ly['sold_qty']) : ''; ?></td>
                    <td class="r" data-v="<?php echo $ps['revenue']; ?>"><?php echo snCell($ps['revenue'], 'money'); ?></td>
                    <td class="r <?php echo snPl($ps['profit']); ?>" data-v="<?php echo $ps['profit']; ?>"><?php echo snCell($ps['profit'], 'money'); ?></td>
                    <td class="r" data-v="<?php echo $pm ?? 0; ?>"><?php echo $pm !== null ? number_format($pm, 1) . '%' : ''; ?></td>
                    <td class="r" data-v="<?php echo $ps['return_in_qty']; ?>"><?php echo snCell($ps['return_in_qty']); ?></td>
                <?php } ?>
                <?php if ($byAccount) { ?>
                    <td class="r sep" data-v="<?php echo $al['sold_qty']; ?>"><?php echo snCell($al['sold_qty']); ?></td>
                <?php } ?>
                <td class="r <?php echo $byAccount ? '' : 'sep'; ?>" data-v="<?php echo $st ?? -1; ?>"><?php echo $st !== null ? number_format($st, 0) . '%' : ''; ?></td>
                <td class="r <?php echo $al['closing_qty'] < 0 ? 'neg' : ''; ?>" data-v="<?php echo $al['closing_qty']; ?>"><?php echo snQty($al['closing_qty']); ?></td>
                <td class="r" data-v="<?php echo $al['closing_value']; ?>"><?php echo snCell($al['closing_value'], 'money'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totals</td>
                <td class="r"><?php echo snQty($a['opening_qty']); ?></td>
                <?php if ($hasBuy) { ?>
                    <td class="r"><?php echo snQty($t['bought_qty']); ?></td>
                    <td class="r"><?php echo snMoney($t['bought_value']); ?></td>
                    <td class="r"><?php echo snQty($t['return_out_qty']); ?></td>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <td class="r"><?php echo snQty($t['sold_qty']); ?></td>
                    <td class="r"><?php echo snQty($l['sold_qty']); ?><?php echo snDelta($t['sold_qty'], $l['sold_qty']); ?></td>
                    <td class="r"><?php echo snMoney($t['revenue']); ?></td>
                    <td class="r"><?php echo snMoney($t['profit']); ?></td>
                    <td class="r"><?php echo $margin !== null ? number_format($margin, 1) . '%' : ''; ?></td>
                    <td class="r"><?php echo snQty($t['return_in_qty']); ?></td>
                <?php } ?>
                <?php if ($byAccount) { ?>
                    <td class="r"><?php echo snQty($a['sold_qty']); ?></td>
                <?php } ?>
                <td class="r"><?php echo $sellThru !== null ? number_format($sellThru, 0) . '%' : ''; ?></td>
                <td class="r"><?php echo snQty($a['closing_qty']); ?></td>
                <td class="r"><?php echo snMoney($a['closing_value']); ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php if ($negative) { ?>
    <p class="note"><?php echo $negative; ?> product<?php echo $negative == 1 ? ' ends' : 's end'; ?> the season with negative stock (shown in red). Stock totals count those as zero.</p>
<?php } ?>

<?php if (!empty($season['buckets'])) { ?>
<!-- ═══ Through the season ═══ -->
<h3><?php echo $rg['weekly'] ? 'Week by week' : 'Month by month'; ?></h3>
<div class="tbl-wrap">
    <table class="grid">
        <thead>
            <tr>
                <th><?php echo $rg['weekly'] ? 'Week starting' : 'Month'; ?></th>
                <?php if ($hasBuy) { ?><th class="r">Units bought</th><th class="r">Spent</th><?php } ?>
                <?php if ($hasSell) { ?><th class="r">Units sold</th><th class="r">Net sales</th><?php } ?>
                <th class="r">Returned</th>
                <th class="r">Profit / loss</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($season['buckets'] as $k => $b) { ?>
            <tr>
                <td><?php echo $rg['weekly'] ? date('D d M Y', strtotime($k)) : date('M Y', strtotime($k . '-01')); ?></td>
                <?php if ($hasBuy) { ?>
                    <td class="r"><?php echo snCell($b['bought_qty']); ?></td>
                    <td class="r"><?php echo snCell($b['bought_value'], 'money'); ?></td>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <td class="r"><?php echo snCell($b['sold_qty']); ?></td>
                    <td class="r"><?php echo snCell($b['revenue'], 'money'); ?></td>
                <?php } ?>
                <td class="r"><?php echo snCell($b['returned_qty']); ?></td>
                <td class="r <?php echo snPl($b['profit']); ?>"><?php echo snCell($b['profit'], 'money'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>

<?php if (!$byAccount && ($season['customers'] || $season['suppliers'])) { ?>
<div class="two">
    <div>
        <h3>Top customers this season <span class="muted">(<?php echo count($season['customers']); ?> in total)</span></h3>
        <div class="tbl-wrap">
            <table class="grid">
                <thead><tr><th>Customer</th><th class="r">Bills</th><th class="r">Units</th><th class="r">Net sales</th><th class="r">Profit</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($season['customers'], 0, 15) as $c) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td class="r"><?php echo number_format($c['bills']); ?></td>
                        <td class="r"><?php echo snQty($c['qty']); ?></td>
                        <td class="r"><?php echo snMoney($c['value']); ?></td>
                        <td class="r <?php echo snPl($c['profit']); ?>"><?php echo snMoney($c['profit']); ?></td>
                    </tr>
                <?php } ?>
                <?php if (empty($season['customers'])) { ?><tr><td colspan="5" class="muted">No sales this season.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <h3>Suppliers this season <span class="muted">(<?php echo count($season['suppliers']); ?> in total)</span></h3>
        <div class="tbl-wrap">
            <table class="grid">
                <thead><tr><th>Supplier</th><th class="r">Bills</th><th class="r">Units</th><th class="r">Spent</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($season['suppliers'], 0, 15) as $sp) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sp['name']); ?></td>
                        <td class="r"><?php echo number_format($sp['bills']); ?></td>
                        <td class="r"><?php echo snQty($sp['qty']); ?></td>
                        <td class="r"><?php echo snMoney($sp['value']); ?></td>
                    </tr>
                <?php } ?>
                <?php if (empty($season['suppliers'])) { ?><tr><td colspan="4" class="muted">No purchases this season.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } ?>

<!-- ═══ Timeline ═══ -->
<h3>Season timeline
    <?php if ($season['events_total'] > count($ev)) { ?>
        <span class="muted">(latest <?php echo number_format(count($ev)); ?> of <?php echo number_format($season['events_total']); ?> events — narrow the dates or pick a product to see them all)</span>
    <?php } ?>
</h3>
<div class="filters">
    <button class="on" onclick="filterRows('timeline', 'data-g', 'all', this)">All (<?php echo $counts['all']; ?>)</button>
    <?php foreach (['purchase' => 'Purchases', 'sale' => 'Sales', 'return' => 'Returns', 'exchange' => 'Exchanges'] as $g => $label) { ?>
        <?php if ($counts[$g]) { ?><button onclick="filterRows('timeline', 'data-g', '<?php echo $g; ?>', this)"><?php echo $label; ?> (<?php echo $counts[$g]; ?>)</button><?php } ?>
    <?php } ?>
</div>
<div class="tbl-wrap">
    <table class="grid" id="timeline">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Event</th>
                <th>Reference</th>
                <?php if (!$byAccount) { ?><th>Supplier / customer</th><?php } ?>
                <th class="r">In</th>
                <th class="r">Out</th>
                <th class="r">Product stock</th>
                <th class="r">Unit price</th>
                <th class="r">Amount</th>
                <th class="r">Cost</th>
                <th class="r">Profit / loss</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (array_reverse($ev) as $e) {
            $ti   = $typeInfo[$e['type']];
            $in   = in_array($e['type'], ['purchase', 'return_in', 'exchange_in']);
            $unit = $e['qty'] > 0 && $e['value'] ? $e['value'] / $e['qty'] : null;
        ?>
            <tr data-g="<?php echo $ti['group']; ?>">
                <td><?php echo snDate($e['date']); ?></td>
                <td class="wrap-cell"><button class="link" onclick="openProduct(<?php echo (int) $e['product_id']; ?>)"><?php echo htmlspecialchars($e['product_name']); ?></button></td>
                <td>
                    <span class="badge <?php echo $ti['cls']; ?>"><?php echo $ti['label']; ?></span>
                    <?php foreach ($e['tags'] as $tag) { ?><span class="tag"><?php echo $tag; ?></span><?php } ?>
                </td>
                <td>
                    <?php if (in_array($e['ref_type'], ['order', 'supply', 'return_order'])) { ?>
                        <button class="link" onclick="openRef('<?php echo $e['ref_type']; ?>', <?php echo (int) $e['ref_id']; ?>)"><?php echo htmlspecialchars($e['ref_label']); ?></button>
                    <?php } else { ?>
                        <?php echo htmlspecialchars($e['ref_label']); ?>
                    <?php } ?>
                </td>
                <?php if (!$byAccount) { ?><td><?php echo htmlspecialchars($e['party']); ?></td><?php } ?>
                <td class="r pos"><?php echo $in ? '+' . snQty($e['qty']) : ''; ?></td>
                <td class="r neg"><?php echo !$in ? '-' . snQty($e['qty']) : ''; ?></td>
                <td class="r <?php echo $e['balance'] < 0 ? 'neg' : ''; ?>"><?php echo snQty($e['balance']); ?></td>
                <td class="r"><?php echo $unit !== null ? snMoney($unit, 2) : ''; ?></td>
                <td class="r"><?php echo $e['value'] ? snMoney($e['value']) : ''; ?></td>
                <td class="r muted"><?php echo $e['cost'] ? snMoney($e['cost']) . ($e['estimated'] ? '<span class="est">*</span>' : '') : ''; ?></td>
                <td class="r <?php echo snPl($e['profit']); ?>"><?php echo $e['profit'] ? snMoney($e['profit']) : ''; ?></td>
            </tr>
        <?php } ?>
        <?php if (empty($ev)) { ?>
            <tr><td colspan="12" class="muted" style="text-align:center;padding:20px">No transactions in this season.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<p class="note">
    <strong>How this is worked out:</strong> each product's cost is built from its whole history (moving weighted average), so a
    sale this season is costed at what that stock really cost — even stock bought in an earlier season. Only transactions dated inside
    the season are counted. Customer returns in the season reverse the original sale's profit, even if that sale was last season.
    <strong>Sell-through</strong> = units sold ÷ units available (opening stock + bought + returned by customers).
    "Last season" is the same dates one year earlier. Services and charges (non-stock items) carry no cost and no stock.
    <?php if ($byAccount) { ?>Opening, closing and sell-through always cover the whole product, not just this party.<?php } ?>
    <span class="est">*</span> cost estimated from the product's cost on file.
</p>

<?php } ?>
</div>
</body>
</html>
