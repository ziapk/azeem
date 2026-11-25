<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

// Return-order stock repair: lists every return whose inventory ledger postings don't
// match the return itself (e.g. duplicated by re-saving an approved return) and rebuilds
// them one by one or in bulk. Logic lives in Inventory::repairReturnLedgers().

if (empty($userData) || !in_array($userData['role'], ['owner', 'manager'])) {
    header('location: ' . SITE_URL);
    exit;
}

$inventory = new Inventory();
$storeObj = new Store();
$isOwner = $userData['role'] == 'owner';
$ownerStores = $isOwner ? $storeObj->getOwnerStores($userData['id']) : [];

// Managers are locked to their own shop; owners may pick a shop or "all".
$shopId = $isOwner ? (!empty($_REQUEST['shop_id']) ? (int)$_REQUEST['shop_id'] : null) : (int)$userData['shopId'];
$returnId = !empty($_REQUEST['return_id']) ? (int)$_REQUEST['return_id'] : null;

// ── Fix (POST → redirect back with a message) ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fixed = 0;
    $deleted = 0;
    $inserted = 0;
    $errors = [];

    if (!empty($_POST['fix_all'])) {
        $runs = [$inventory->repairReturnLedgers($shopId, $returnId, true, (int)$userData['id'])];
    } else {
        $runs = [];
        foreach ((array)($_POST['return_ids'] ?? []) as $rid) {
            if ((int)$rid > 0) {
                $runs[] = $inventory->repairReturnLedgers($shopId, (int)$rid, true, (int)$userData['id']);
            }
        }
    }
    foreach ($runs as $r) {
        $fixed += count($r['broken']);
        $deleted += $r['deleted_entries'];
        $inserted += $r['inserted_entries'];
        $errors = array_merge($errors, $r['errors'] ?? [], !empty($r['error']) ? [$r['error']] : []);
    }

    $_SESSION['return_repair_msg'] = [
        'ok' => empty($errors),
        'text' => empty($runs)
            ? 'No return orders selected.'
            : "Fixed $fixed return order(s): removed $deleted wrong ledger rows, wrote $inserted correct rows, stock re-synced." .
              (empty($errors) ? '' : ' Errors: ' . implode(' | ', $errors)),
    ];
    header('location: ' . SITE_URL . 'pages/stock/returnRepair.php?' . http_build_query(array_filter([
        'shop_id' => $isOwner ? $shopId : null,
        'return_id' => $returnId,
    ])));
    exit;
}

$flash = $_SESSION['return_repair_msg'] ?? null;
unset($_SESSION['return_repair_msg']);

// ── Audit (dry run) ───────────────────────────────────────────────────────
$audit = $inventory->repairReturnLedgers($shopId, $returnId, false);
$broken = $audit['broken'];

$showIds = array_column($broken, 'return_id');
if ($returnId && !in_array($returnId, $showIds)) {
    $showIds[] = $returnId; // searched return is fine — still show its entries
}
$ledgerRows = $inventory->getReturnLedgerRows($showIds);

$productIds = [];
foreach ($broken as $b) {
    foreach ($b['mismatches'] as $m) {
        $productIds[] = $m['product_id'];
    }
}
$productNames = $inventory->getProductNames($productIds);

$shopNames = [];
foreach ($ownerStores as $s) {
    $shopNames[(int)$s['id']] = $s['full_name'];
}
$shopLabel = function ($id) use ($shopNames) {
    return isset($shopNames[$id]) ? $shopNames[$id] . ' (' . $id . ')' : 'Shop ' . $id;
};
$typeLabel = function ($t) {
    return $t == 2 ? 'Purchase return' : ($t == 1 ? 'Sale return' : '—');
};
$num = function ($n) {
    $n = (float)$n;
    return ($n == (int)$n) ? number_format($n) : number_format($n, 2);
};

echo mainHeader(['page' => 'return-repair']);
?>
<style>
    .rr-summary { display: flex; gap: 12px; flex-wrap: wrap; margin: 10px 0 16px; }
    .rr-stat { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 14px; min-width: 150px; background: #fff; }
    .rr-stat .n { font-size: 20px; font-weight: bold; }
    .rr-stat.bad .n { color: #b91c1c; }
    .rr-stat.good .n { color: #15803d; }
    .rr-table td, .rr-table th { vertical-align: top !important; font-size: 12px; }
    .rr-mm { white-space: nowrap; }
    .rr-diff-neg { color: #b91c1c; font-weight: bold; }
    .rr-diff-pos { color: #1d4ed8; font-weight: bold; }
    .rr-entries { margin-top: 6px; }
    .rr-entries summary { cursor: pointer; color: #1d4ed8; font-size: 11px; }
    .rr-entries table { margin-top: 6px; background: #f8fafc; }
    .rr-entries td, .rr-entries th { padding: 3px 6px !important; font-size: 11px; }
    .rr-in { color: #15803d; font-weight: 600; }
    .rr-out { color: #b91c1c; font-weight: 600; }
</style>

<div class="container-fluid">
    <h4>Return Order Stock Repair</h4>
    <p class="text-muted" style="max-width:900px">
        Compares each approved return order's stock entries in the inventory ledger with the return's current items.
        Returns re-saved after approval used to post their quantity again on every save. <strong>Fix</strong> deletes that
        return's ledger rows, writes one correct row per product (dated at its first posting), and recalculates stock.
        Back up <code>inventory_ledger</code> before fixing in bulk.
    </p>

    <?php if ($flash) { ?>
        <div class="alert alert-<?php echo $flash['ok'] ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($flash['text']); ?></div>
    <?php } ?>
    <?php if (!empty($audit['error'])) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($audit['error']); ?></div>
    <?php } ?>

    <form method="GET" action="<?php echo SITE_URL; ?>pages/stock/returnRepair.php" class="row">
        <?php if ($isOwner) { ?>
            <div class="col-sm-4 col-md-3 form-group">
                <label>Shop</label>
                <select class="form-control" name="shop_id">
                    <option value="">All shops</option>
                    <?php foreach ($ownerStores as $s) { ?>
                        <option value="<?php echo (int)$s['id']; ?>" <?php echo $shopId == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['full_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
        <div class="col-sm-4 col-md-3 form-group">
            <label>Return order ID</label>
            <input type="number" class="form-control" name="return_id" value="<?php echo $returnId ?: ''; ?>" placeholder="All return orders">
        </div>
        <div class="col-sm-4 col-md-3 form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">Check</button>
            <a href="<?php echo SITE_URL; ?>pages/stock/returnRepair.php" class="btn btn-default">Reset</a>
        </div>
    </form>

    <div class="rr-summary">
        <div class="rr-stat"><div class="text-muted">Return orders checked</div><div class="n"><?php echo (int)$audit['checked']; ?></div></div>
        <div class="rr-stat <?php echo $broken ? 'bad' : 'good'; ?>"><div class="text-muted">With wrong entries</div><div class="n"><?php echo count($broken); ?></div></div>
    </div>

    <?php if ($returnId && !$broken) { ?>
        <div class="alert alert-success">
            Return order #<?php echo $returnId; ?>
            <?php echo empty($ledgerRows[$returnId]) ? 'has no stock entries in the ledger.' : 'is correct — its stock entries match the return.'; ?>
        </div>
    <?php } elseif (!$broken) { ?>
        <div class="alert alert-success">All return orders are correct. Nothing to fix.</div>
    <?php } ?>

    <?php if ($broken) { ?>
    <form method="POST" action="<?php echo SITE_URL; ?>pages/stock/returnRepair.php" id="rr-form" onsubmit="return rrConfirm(event)">
        <?php if ($isOwner && $shopId) { ?><input type="hidden" name="shop_id" value="<?php echo $shopId; ?>"><?php } ?>
        <?php if ($returnId) { ?><input type="hidden" name="return_id" value="<?php echo $returnId; ?>"><?php } ?>

        <div style="margin-bottom:10px">
            <button type="submit" name="fix_selected" value="1" class="btn btn-warning">Fix selected (<span id="rr-count">0</span>)</button>
            <button type="submit" name="fix_all" value="1" class="btn btn-danger">Fix all <?php echo count($broken); ?></button>
        </div>

        <table class="table table-bordered table-condensed rr-table">
            <thead>
                <tr>
                    <th style="width:30px"><input type="checkbox" id="rr-all" title="Select all"></th>
                    <th style="width:90px">Return #</th>
                    <th style="width:110px">Type</th>
                    <th>Product / shop</th>
                    <th class="text-right">In ledger</th>
                    <th class="text-right">Should be</th>
                    <th class="text-right">Difference</th>
                    <th style="width:70px"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($broken as $b) {
                $rid = (int)$b['return_id'];
                $mm = $b['mismatches'];
                $span = count($mm);
                foreach ($mm as $i => $m) {
                    $diffCls = $m['difference'] < 0 ? 'rr-diff-neg' : 'rr-diff-pos'; ?>
                <tr>
                    <?php if ($i === 0) { ?>
                        <td rowspan="<?php echo $span; ?>"><input type="checkbox" class="rr-cb" name="return_ids[]" value="<?php echo $rid; ?>"></td>
                        <td rowspan="<?php echo $span; ?>">
                            <a href="javascript:void(0)" onclick="window.open('<?php echo SITE_URL; ?>print/return.php?id=<?php echo $rid; ?>&detail=true&largeView=large','','width=800,height=600')">#<?php echo $rid; ?></a>
                            <?php if ($b['flag'] !== null && $b['flag'] != 2) { ?><br><span class="label label-default">Draft</span><?php } ?>
                            <?php if ($b['flag'] === null) { ?><br><span class="label label-danger">Deleted</span><?php } ?>
                        </td>
                        <td rowspan="<?php echo $span; ?>"><?php echo $typeLabel($b['return_type']); ?></td>
                    <?php } ?>
                    <td>
                        <?php echo htmlspecialchars($productNames[$m['product_id']] ?? ('Product ' . $m['product_id'])); ?>
                        <span class="text-muted">· ID <?php echo (int)$m['product_id']; ?> · <?php echo htmlspecialchars($shopLabel($m['shop_id'])); ?></span>
                    </td>
                    <td class="text-right rr-mm"><?php echo $num($m['ledger_net']); ?></td>
                    <td class="text-right rr-mm"><?php echo $num($m['expected_net']); ?></td>
                    <td class="text-right rr-mm <?php echo $diffCls; ?>"><?php echo ($m['difference'] > 0 ? '+' : '') . $num($m['difference']); ?></td>
                    <?php if ($i === 0) { ?>
                        <td rowspan="<?php echo $span; ?>">
                            <button type="submit" name="return_ids[]" value="<?php echo $rid; ?>" class="btn btn-xs btn-warning" formnovalidate onclick="rrSingle=true">Fix</button>
                        </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                <tr>
                    <td></td>
                    <td colspan="7" style="border-top:none">
                        <?php $rows = $ledgerRows[$rid] ?? []; ?>
                        <details class="rr-entries">
                            <summary>Show <?php echo count($rows); ?> ledger entries for return #<?php echo $rid; ?></summary>
                            <?php echo rrEntriesTable($rows, $shopLabel, $num); ?>
                        </details>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
    <?php } ?>

    <?php if ($returnId && !$broken && !empty($ledgerRows[$returnId])) { ?>
        <h5>Ledger entries for return #<?php echo $returnId; ?></h5>
        <?php echo rrEntriesTable($ledgerRows[$returnId], $shopLabel, $num); ?>
    <?php } ?>
</div>

<?php
function rrEntriesTable($rows, $shopLabel, $num)
{
    ob_start(); ?>
    <table class="table table-bordered table-condensed">
        <thead>
            <tr><th>ID</th><th>Date</th><th>Movement</th><th>Product</th><th>Shop</th><th>Note</th><th class="text-right">In</th><th class="text-right">Out</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r) { $q = (float)$r['quantity']; ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td style="white-space:nowrap"><?php echo date('d M y H:i', strtotime($r['created_at'])); ?></td>
                <td><?php echo htmlspecialchars($r['movement_type']); ?></td>
                <td><?php echo htmlspecialchars($r['product_name'] ?: ('Product ' . $r['product_id'])); ?></td>
                <td><?php echo htmlspecialchars($shopLabel((int)$r['shop_id'])); ?></td>
                <td><?php echo htmlspecialchars($r['note'] ?? ''); ?></td>
                <td class="text-right rr-in"><?php echo $q > 0 ? '+' . $num($q) : ''; ?></td>
                <td class="text-right rr-out"><?php echo $q < 0 ? $num(abs($q)) : ''; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php return ob_get_clean();
}
?>

<script>
    var rrSingle = false;
    (function () {
        var all = document.getElementById('rr-all');
        var count = document.getElementById('rr-count');
        if (!all) return;
        var cbs = document.querySelectorAll('.rr-cb');
        function update() {
            var n = 0;
            cbs.forEach(function (c) { if (c.checked) n++; });
            count.textContent = n;
            all.checked = n === cbs.length;
        }
        all.addEventListener('change', function () {
            cbs.forEach(function (c) { c.checked = all.checked; });
            update();
        });
        cbs.forEach(function (c) { c.addEventListener('change', update); });
    })();

    function rrConfirm(e) {
        var btn = e.submitter || document.activeElement;
        var form = document.getElementById('rr-form');
        if (rrSingle) {
            rrSingle = false;
            // Single-row fix: only send that row's id, not the ticked checkboxes.
            form.querySelectorAll('.rr-cb').forEach(function (c) { c.checked = false; });
            return confirm('Rebuild stock entries for return #' + btn.value + '?');
        }
        if (btn && btn.name === 'fix_all') {
            return confirm('Rebuild stock entries for ALL listed return orders? Wrong ledger rows will be deleted.');
        }
        var n = form.querySelectorAll('.rr-cb:checked').length;
        if (!n) { alert('Tick at least one return order.'); return false; }
        return confirm('Rebuild stock entries for ' + n + ' selected return order(s)?');
    }
</script>

<?php echo mainFooter(); ?>
