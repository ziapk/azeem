<?php
// partyLifecycleReport.php — lifecycle of every product tied to a customer,
// supplier and/or publisher, product-wise.
// Expects $party from print.php :: case '28' (Products::getPartyLifecycle()).

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

function plMoney($v, $dp = 0)
{
    $v = (float) $v;
    return ($v < 0 ? '-' : '') . number_format(abs($v), $dp);
}

function plQty($v)
{
    $v = (float) $v;
    return number_format($v, floor($v) == $v ? 0 : 2);
}

function plDate($d)
{
    return $d ? date('d M Y', strtotime($d)) : '—';
}

function plPl($v)
{
    return $v > 0 ? 'pos' : ($v < 0 ? 'neg' : 'zero');
}

// Blank cell for zero, so sparse columns stay readable.
function plCell($v, $fmt = 'qty')
{
    if (!(float) $v) return '';
    return $fmt === 'money' ? plMoney($v) : plQty($v);
}

$typeInfo = [
    'purchase'     => ['label' => 'Purchase',           'cls' => 'b-purchase', 'group' => 'purchase'],
    'sale'         => ['label' => 'Sale',               'cls' => 'b-sale',     'group' => 'sale'],
    'return_in'    => ['label' => 'Customer return',    'cls' => 'b-return',   'group' => 'return'],
    'return_out'   => ['label' => 'Return to supplier', 'cls' => 'b-return',   'group' => 'return'],
    'exchange_in'  => ['label' => 'Exchange in',        'cls' => 'b-exchange', 'group' => 'exchange'],
    'exchange_out' => ['label' => 'Exchange out',       'cls' => 'b-exchange', 'group' => 'exchange'],
];

$acc  = $party['party']['account'];
$pub  = $party['party']['publisher'];
$rows = $party['products'];
$ev   = $party['events'];

// ── Totals: the party's share, and the products' whole lives ───────────────
$t = [
    'purchase_count' => 0, 'bought_qty' => 0, 'bought_value' => 0, 'return_out_qty' => 0, 'return_out_value' => 0,
    'sale_count' => 0, 'sold_qty' => 0, 'revenue' => 0, 'cost' => 0, 'sale_profit' => 0, 'free_qty' => 0,
    'return_in_qty' => 0, 'return_in_value' => 0, 'profit' => 0, 'customer_profit' => 0, 'supplier_result' => 0,
];
$o = ['sold_qty' => 0, 'revenue' => 0, 'net_profit' => 0, 'stock' => 0, 'stock_cost' => 0, 'stock_list' => 0, 'purchase_value' => 0];
$first = null;
$last  = null;
$statusCounts = [];
foreach ($rows as $r) {
    foreach ($t as $k => $_) {
        $t[$k] += $r['party'][$k];
    }
    $p = $r['product'];
    $s = $r['summary'];
    $stock = max(0, (float) $p['ledger_qty']);
    $list  = (float) $p['store_price'] > 0 ? (float) $p['store_price'] : (float) $p['price'];
    $o['sold_qty']       += $s['sold_qty'];
    $o['revenue']        += $s['revenue'];
    $o['net_profit']     += $s['net_profit'];
    $o['purchase_value'] += $s['purchase_value'];
    $o['stock']          += $stock;
    $o['stock_cost']     += $stock * $s['avg_cost'];
    $o['stock_list']     += $stock * $list;

    if ($r['party']['first'] && (!$first || $r['party']['first'] < $first)) $first = $r['party']['first'];
    if ($r['party']['last']  && (!$last  || $r['party']['last']  > $last))  $last  = $r['party']['last'];

    $st = $r['status'];
    if (!isset($statusCounts[$st[0]])) $statusCounts[$st[0]] = ['cls' => $st[1], 'n' => 0];
    $statusCounts[$st[0]]['n']++;
}

// What the party is to us decides which columns mean anything.
// Legend: statuses present in this report, in the order the rules are checked.
$statusDefs = [];
foreach ($productObj->lifecycleStatusDefinitions() as $label => $def) {
    if (isset($statusCounts[$label])) {
        $statusDefs[$label] = ['cls' => $def[0], 'text' => $def[1], 'n' => $statusCounts[$label]['n']];
    }
}

$byAccount = (bool) $acc;
$hasBuy    = $t['bought_qty'] > 0 || $t['return_out_qty'] > 0;
$hasSell   = $t['sold_qty'] > 0 || $t['return_in_qty'] > 0 || $t['free_qty'] > 0;
if (!$byAccount) {
    $hasBuy = $hasSell = true;
}
$margin = $t['revenue'] > 0 ? $t['sale_profit'] / $t['revenue'] * 100 : null;

if ($acc) {
    $role = ($hasBuy && $hasSell) ? 'Customer &amp; supplier' : ($hasBuy ? 'Supplier' : 'Customer');
    $title = $acc['title'];
}
$singleProduct = count($rows) === 1 ? reset($rows) : null;
if (!$acc) {
    $role  = $pub ? 'Publisher' : 'Product';
    $title = $pub ? $pub['full_name'] : ($singleProduct ? $singleProduct['product']['full_name'] : 'No match');
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
<title>Party Lifecycle — <?php echo htmlspecialchars($title); ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #0f172a; background: #f8fafc; padding: 16px; }
    .wrap { max-width: 1400px; margin: 0 auto; }
    h3 { font-size: 13px; text-transform: uppercase; letter-spacing: .5px; color: #475569; margin: 22px 0 8px; }
    h3 .muted { text-transform: none; letter-spacing: 0; font-weight: normal; }
    .muted { color: #64748b; }
    .r { text-align: right; font-variant-numeric: tabular-nums; }
    .pos { color: #15803d; }
    .neg { color: #b91c1c; }
    .zero { color: #94a3b8; }

    .head { background: #1a3a7a; color: #fff; padding: 14px 16px; border-radius: 6px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .head .role { font-size: 10px; text-transform: uppercase; letter-spacing: .6px; opacity: .75; }
    .head .name { font-size: 18px; font-weight: bold; margin-top: 2px; }
    .head .meta { font-size: 11px; opacity: .8; margin-top: 4px; line-height: 1.6; }
    .head .gen { font-size: 11px; opacity: .75; text-align: right; }

    .hero { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 10px; margin-top: 12px; }
    .hero .box { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
    .hero .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
    .hero .big { font-size: 22px; font-weight: bold; margin: 4px 0 2px; font-variant-numeric: tabular-nums; }
    .hero .box.main.pos { border: 2px solid #86efac; background: #f0fdf4; }
    .hero .box.main.neg { border: 2px solid #fca5a5; background: #fef2f2; }

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
    table.grid tfoot td.pos, table.grid tfoot td.neg { color: #f1f5f9; }
    .sep { border-left: 2px solid #e2e8f0; }

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
        .filters { display: none; }
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
// Opens the single-product Product Lifecycle report (27) in a new tab.
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
        <div class="role"><?php echo $role; ?></div>
        <div class="name"><?php echo htmlspecialchars($title); ?></div>
        <div class="meta">
            <?php if ($acc && $pub) { ?>Publisher: <?php echo htmlspecialchars($pub['full_name']); ?> &nbsp;|&nbsp; <?php } ?>
            <?php if ($singleProduct && $byAccount) { ?>Product: <?php echo htmlspecialchars($singleProduct['product']['full_name']); ?> &nbsp;|&nbsp; <?php } ?>
            <?php echo count($rows); ?> product<?php echo count($rows) == 1 ? '' : 's'; ?>
            <?php if ($first) { ?>&nbsp;|&nbsp; Dealing since <?php echo plDate($first); ?>, last <?php echo plDate($last); ?><?php } ?>
        </div>
    </div>
    <div class="gen">
        <strong>Party Lifecycle Report</strong><br>
        <?php echo htmlspecialchars($shopName); ?><br>
        Full history &bull; Generated <?php echo date('d M Y, H:i'); ?>
    </div>
</div>

<?php if (empty($rows)) { ?>
    <div class="empty">No products found for this selection in this shop.</div>
<?php } else { ?>

<!-- ═══ Headline numbers ═══ -->
<div class="hero">
    <?php if (!$byAccount) { ?>
        <div class="box main <?php echo plPl($t['profit']); ?>">
            <div class="lbl"><?php echo $t['profit'] >= 0 ? 'Lifetime profit' : 'Lifetime loss'; ?></div>
            <div class="big <?php echo plPl($t['profit']); ?>"><?php echo plMoney($t['profit']); ?></div>
            <div class="muted">Across all products<?php echo $margin !== null ? ' &bull; ' . number_format($margin, 1) . '% gross margin' : ''; ?></div>
        </div>
        <div class="box">
            <div class="lbl">Bought</div>
            <div class="big"><?php echo plMoney($t['bought_value']); ?></div>
            <div class="muted"><?php echo plQty($t['bought_qty']); ?> units in <?php echo number_format($t['purchase_count']); ?> purchase lines</div>
        </div>
        <div class="box">
            <div class="lbl">Sold</div>
            <div class="big"><?php echo plMoney($t['revenue']); ?></div>
            <div class="muted"><?php echo plQty($t['sold_qty']); ?> units in <?php echo number_format($t['sale_count']); ?> bill lines</div>
        </div>
    <?php } ?>

    <?php if ($byAccount && $hasSell) { ?>
        <div class="box main <?php echo plPl($t['customer_profit']); ?>">
            <div class="lbl"><?php echo $t['customer_profit'] >= 0 ? 'Profit from this customer' : 'Loss on this customer'; ?></div>
            <div class="big <?php echo plPl($t['customer_profit']); ?>"><?php echo plMoney($t['customer_profit']); ?></div>
            <div class="muted">
                Net sales <?php echo plMoney($t['revenue'] - $t['return_in_value']); ?>
                <?php echo $margin !== null ? '&bull; ' . number_format($margin, 1) . '% margin' : ''; ?>
            </div>
        </div>
        <div class="box">
            <div class="lbl">Sold to them</div>
            <div class="big"><?php echo plQty($t['sold_qty']); ?> <span class="muted" style="font-size:12px">units</span></div>
            <div class="muted">
                <?php echo plMoney($t['revenue']); ?> in <?php echo number_format($t['sale_count']); ?> bill lines
                <?php if ($t['return_in_qty']) { ?>&bull; <?php echo plQty($t['return_in_qty']); ?> returned (<?php echo plMoney($t['return_in_value']); ?>)<?php } ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($byAccount && $hasBuy) { ?>
        <div class="box">
            <div class="lbl">Bought from them</div>
            <div class="big"><?php echo plMoney($t['bought_value']); ?></div>
            <div class="muted">
                <?php echo plQty($t['bought_qty']); ?> units in <?php echo number_format($t['purchase_count']); ?> purchase lines
                <?php if ($t['return_out_qty']) { ?>&bull; <?php echo plQty($t['return_out_qty']); ?> sent back (<?php echo plMoney($t['return_out_value']); ?>)<?php } ?>
            </div>
        </div>
        <div class="box main <?php echo plPl($o['net_profit']); ?>">
            <div class="lbl">How their products did</div>
            <div class="big <?php echo plPl($o['net_profit']); ?>"><?php echo plMoney($o['net_profit']); ?></div>
            <div class="muted">Lifetime P/L of these products, all customers &bull; <?php echo plQty($o['sold_qty']); ?> units sold</div>
        </div>
    <?php } ?>

    <?php if (!$byAccount || $hasBuy) { ?>
        <div class="box">
            <div class="lbl">Stock on hand</div>
            <div class="big"><?php echo plQty($o['stock']); ?> <span class="muted" style="font-size:12px">units</span></div>
            <div class="muted">Worth <?php echo plMoney($o['stock_cost']); ?> at cost &bull; <?php echo plMoney($o['stock_list']); ?> at list price</div>
        </div>
    <?php } ?>
</div>

<!-- ═══ Product-wise ═══ -->
<h3>Product-wise <span class="muted">(click a heading to sort, a product name for its full lifecycle)</span></h3>
<div class="filters">
    <button class="on" onclick="filterRows('products', 'data-status', 'all', this)">All (<?php echo count($rows); ?>)</button>
    <?php foreach ($statusDefs as $label => $sd) { ?>
        <button title="<?php echo htmlspecialchars($sd['text']); ?>" onclick="filterRows('products', 'data-status', '<?php echo htmlspecialchars($label); ?>', this)"><?php echo htmlspecialchars($label); ?> (<?php echo $sd['n']; ?>)</button>
    <?php } ?>
</div>
<details class="legend" open>
    <summary>What the status tags mean</summary>
    <div class="legend-grid">
        <?php foreach ($statusDefs as $label => $sd) { ?>
            <div class="legend-row">
                <span class="pill <?php echo $sd['cls']; ?>"><?php echo htmlspecialchars($label); ?></span>
                <span><?php echo htmlspecialchars($sd['text']); ?> <span class="muted">(<?php echo $sd['n']; ?> product<?php echo $sd['n'] == 1 ? '' : 's'; ?>)</span></span>
            </div>
        <?php } ?>
    </div>
    <div class="muted" style="margin-top:6px">Each product gets the first tag that fits, checked top to bottom. Hover a tag in the table for that product's own reason.</div>
</details>
<div class="tbl-wrap">
    <table class="grid" id="products">
        <thead>
            <tr>
                <th colspan="3"></th>
                <?php if ($hasBuy) { ?><th class="grp" colspan="4"><?php echo $byAccount ? 'Bought from them' : 'Purchases'; ?></th><?php } ?>
                <?php if ($hasSell) { ?><th class="grp" colspan="5"><?php echo $byAccount ? 'Sold to them' : 'Sales'; ?></th><?php } ?>
                <th class="grp" colspan="<?php echo $byAccount ? 5 : 3; ?>">Product today<?php echo $byAccount ? ' (all parties)' : ''; ?></th>
            </tr>
            <tr>
                <th class="sortable" onclick="sortTable(this)">#</th>
                <th class="sortable" onclick="sortTable(this)">Product</th>
                <th class="sortable" onclick="sortTable(this)" title="See 'What the status tags mean' above the table">Status</th>
                <?php if ($hasBuy) { ?>
                    <th class="r sortable sep" onclick="sortTable(this)">Units</th>
                    <th class="r sortable" onclick="sortTable(this)">Spent</th>
                    <th class="r sortable" onclick="sortTable(this)">Avg cost</th>
                    <th class="r sortable" onclick="sortTable(this)">Returned</th>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <th class="r sortable sep" onclick="sortTable(this)">Units</th>
                    <th class="r sortable" onclick="sortTable(this)">Net sales</th>
                    <th class="r sortable" onclick="sortTable(this)">Profit / loss</th>
                    <th class="r sortable" onclick="sortTable(this)">Margin</th>
                    <th class="r sortable" onclick="sortTable(this)">Returned</th>
                <?php } ?>
                <?php if ($byAccount) { ?>
                    <th class="r sortable sep" onclick="sortTable(this)">Total sold</th>
                    <th class="r sortable" onclick="sortTable(this)">Lifetime P/L</th>
                <?php } ?>
                <th class="r sortable<?php echo $byAccount ? '' : ' sep'; ?>" onclick="sortTable(this)">Stock</th>
                <th class="r sortable" onclick="sortTable(this)">Stock value</th>
                <th class="sortable" onclick="sortTable(this)"><?php echo $byAccount ? 'Last dealing' : 'Last sale'; ?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $n = 1;
        foreach ($rows as $pid => $r) {
            $p  = $r['product'];
            $s  = $r['summary'];
            $ps = $r['party'];
            $stock   = (float) $p['ledger_qty'];
            $pProfit = $byAccount ? $ps['customer_profit'] : $ps['profit'];
            $pMargin = $ps['revenue'] > 0 ? $ps['sale_profit'] / $ps['revenue'] * 100 : null;
            $lastDay = $byAccount ? $ps['last'] : $s['last_sale'];
        ?>
            <tr data-status="<?php echo htmlspecialchars($r['status'][0]); ?>">
                <td class="muted" data-v="<?php echo $n; ?>"><?php echo $n++; ?></td>
                <td class="wrap-cell">
                    <button class="link" onclick="openProduct(<?php echo (int) $pid; ?>)"><?php echo htmlspecialchars($p['full_name']); ?></button>
                    <?php if ($p['code']) { ?><div class="muted" style="font-size:10px"><?php echo htmlspecialchars($p['code']); ?></div><?php } ?>
                </td>
                <td><span class="pill <?php echo $r['status'][1]; ?>" title="<?php echo htmlspecialchars($r['status'][2]); ?>"><?php echo $r['status'][0]; ?></span></td>
                <?php if ($hasBuy) { ?>
                    <td class="r sep" data-v="<?php echo $ps['bought_qty']; ?>"><?php echo plCell($ps['bought_qty']); ?></td>
                    <td class="r" data-v="<?php echo $ps['bought_value']; ?>"><?php echo plCell($ps['bought_value'], 'money'); ?></td>
                    <td class="r" data-v="<?php echo $ps['bought_qty'] ? $ps['bought_value'] / $ps['bought_qty'] : 0; ?>"><?php echo $ps['bought_qty'] ? plMoney($ps['bought_value'] / $ps['bought_qty'], 2) : ''; ?></td>
                    <td class="r" data-v="<?php echo $ps['return_out_qty']; ?>"><?php echo plCell($ps['return_out_qty']); ?></td>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <td class="r sep" data-v="<?php echo $ps['sold_qty']; ?>"><?php echo plCell($ps['sold_qty']); ?><?php echo $ps['free_qty'] ? ' <span class="muted" title="Giveaway / write-off units">+' . plQty($ps['free_qty']) . ' free</span>' : ''; ?></td>
                    <td class="r" data-v="<?php echo $ps['revenue']; ?>"><?php echo plCell($ps['revenue'], 'money'); ?></td>
                    <td class="r <?php echo plPl($pProfit); ?>" data-v="<?php echo $pProfit; ?>"><?php echo plCell($pProfit, 'money'); ?></td>
                    <td class="r" data-v="<?php echo $pMargin ?? 0; ?>"><?php echo $pMargin !== null ? number_format($pMargin, 1) . '%' : ''; ?></td>
                    <td class="r" data-v="<?php echo $ps['return_in_qty']; ?>"><?php echo plCell($ps['return_in_qty']); ?></td>
                <?php } ?>
                <?php if ($byAccount) { ?>
                    <td class="r sep" data-v="<?php echo $s['sold_qty']; ?>"><?php echo plCell($s['sold_qty']); ?></td>
                    <td class="r <?php echo plPl($s['net_profit']); ?>" data-v="<?php echo $s['net_profit']; ?>"><?php echo plCell($s['net_profit'], 'money'); ?></td>
                <?php } ?>
                <td class="r <?php echo $byAccount ? '' : 'sep'; ?> <?php echo $stock < 0 ? 'neg' : ''; ?>" data-v="<?php echo $stock; ?>"><?php echo plQty($stock); ?></td>
                <td class="r" data-v="<?php echo max(0, $stock) * $s['avg_cost']; ?>"><?php echo plCell(max(0, $stock) * $s['avg_cost'], 'money'); ?></td>
                <td data-v="<?php echo $lastDay; ?>"><?php echo plDate($lastDay); ?></td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totals</td>
                <?php if ($hasBuy) { ?>
                    <td class="r"><?php echo plQty($t['bought_qty']); ?></td>
                    <td class="r"><?php echo plMoney($t['bought_value']); ?></td>
                    <td class="r"><?php echo $t['bought_qty'] ? plMoney($t['bought_value'] / $t['bought_qty'], 2) : ''; ?></td>
                    <td class="r"><?php echo plQty($t['return_out_qty']); ?></td>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <td class="r"><?php echo plQty($t['sold_qty']); ?></td>
                    <td class="r"><?php echo plMoney($t['revenue']); ?></td>
                    <td class="r"><?php echo plMoney($byAccount ? $t['customer_profit'] : $t['profit']); ?></td>
                    <td class="r"><?php echo $margin !== null ? number_format($margin, 1) . '%' : ''; ?></td>
                    <td class="r"><?php echo plQty($t['return_in_qty']); ?></td>
                <?php } ?>
                <?php if ($byAccount) { ?>
                    <td class="r"><?php echo plQty($o['sold_qty']); ?></td>
                    <td class="r"><?php echo plMoney($o['net_profit']); ?></td>
                <?php } ?>
                <td class="r"><?php echo plQty($o['stock']); ?></td>
                <td class="r"><?php echo plMoney($o['stock_cost']); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php if (!empty($party['monthly'])) { ?>
<!-- ═══ Monthly ═══ -->
<h3>Month by month</h3>
<div class="tbl-wrap">
    <table class="grid">
        <thead>
            <tr>
                <th>Month</th>
                <?php if ($hasBuy) { ?><th class="r">Units bought</th><th class="r">Spent</th><?php } ?>
                <?php if ($hasSell) { ?><th class="r">Units sold</th><th class="r">Net sales</th><?php } ?>
                <th class="r">Returned</th>
                <th class="r">Profit / loss</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (array_reverse($party['monthly'], true) as $ym => $m) { ?>
            <tr>
                <td><?php echo date('M Y', strtotime($ym . '-01')); ?></td>
                <?php if ($hasBuy) { ?>
                    <td class="r"><?php echo plCell($m['bought_qty']); ?></td>
                    <td class="r"><?php echo plCell($m['bought_value'], 'money'); ?></td>
                <?php } ?>
                <?php if ($hasSell) { ?>
                    <td class="r"><?php echo plCell($m['sold_qty']); ?></td>
                    <td class="r"><?php echo plCell($m['revenue'], 'money'); ?></td>
                <?php } ?>
                <td class="r"><?php echo plCell($m['returned_qty']); ?></td>
                <td class="r <?php echo plPl($m['profit']); ?>"><?php echo plCell($m['profit'], 'money'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>

<!-- ═══ Timeline ═══ -->
<h3>Timeline
    <?php if ($party['events_total'] > count($ev)) { ?>
        <span class="muted">(latest <?php echo number_format(count($ev)); ?> of <?php echo number_format($party['events_total']); ?> events — pick a product to see all of its history)</span>
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
                <td><?php echo plDate($e['date']); ?></td>
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
                <td class="r pos"><?php echo $in ? '+' . plQty($e['qty']) : ''; ?></td>
                <td class="r neg"><?php echo !$in ? '-' . plQty($e['qty']) : ''; ?></td>
                <td class="r <?php echo $e['balance'] < 0 ? 'neg' : ''; ?>"><?php echo plQty($e['balance']); ?></td>
                <td class="r"><?php echo $unit !== null ? plMoney($unit, 2) : ''; ?></td>
                <td class="r"><?php echo $e['value'] ? plMoney($e['value']) : ''; ?></td>
                <td class="r muted"><?php echo $e['cost'] ? plMoney($e['cost']) . ($e['estimated'] ? '<span class="est">*</span>' : '') : ''; ?></td>
                <td class="r <?php echo plPl($e['profit']); ?>"><?php echo $e['profit'] ? plMoney($e['profit']) : ''; ?></td>
            </tr>
        <?php } ?>
        <?php if (empty($ev)) { ?>
            <tr><td colspan="12" class="muted" style="text-align:center;padding:20px">No transactions for this selection.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<p class="note">
    <strong>How profit is worked out:</strong> every product is costed over its whole history using a moving weighted-average cost,
    so a sale's cost is what that stock actually cost at the time, whoever it was bought from. Sale amounts are after line discounts and a share of any bill discount.
    Customer returns reverse the sale at the refund amount. Giveaways and write-offs count as a loss at cost.
    <?php if ($byAccount) { ?>
        The "Product today" columns cover every supplier and customer, not just this one. For a supplier, "Profit" on their own returns
        is the credit received against the average cost of the units sent back.
    <?php } ?>
    <span class="est">*</span> cost estimated from the product's cost on file, because no purchase had been recorded yet.
</p>

<?php } ?>
</div>
</body>
</html>
