<?php
$grandTotal = 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Product Audit Ledger</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 16px; }

    h2 { font-size: 16px; margin-bottom: 2px; }
    .report-meta { font-size: 11px; color: #555; margin-bottom: 16px; }

    .product-block { margin-bottom: 28px; page-break-inside: avoid; }
    .product-header {
        background: #1a3a7a;
        color: #fff;
        padding: 6px 10px;
        border-radius: 4px 4px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .product-header .pname { font-size: 13px; font-weight: bold; }
    .product-header .pmeta { font-size: 11px; opacity: .8; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: #f1f5f9;
        color: #334155;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    thead th.num { text-align: right; }
    tbody td { padding: 5px 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
    tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr:hover { background: #eff6ff; }

    /* qty colours */
    .qty-in  { color: #15803d; font-weight: 600; }
    .qty-out { color: #b91c1c; font-weight: 600; }
    .qty-zero{ color: #94a3b8; }

    /* running balance */
    .balance-pos { color: #0f172a; font-weight: 600; }
    .balance-neg { color: #b91c1c; font-weight: 600; }
    .balance-zero{ color: #94a3b8; }

    /* type badge */
    .badge {
        display: inline-block;
        padding: 1px 7px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .badge-sale     { background:#dcfce7; color:#14532d; }
    .badge-purchase { background:#dbeafe; color:#1d4ed8; }
    .badge-return   { background:#fef3c7; color:#78350f; }
    .badge-adjust   { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }
    .badge-other    { background:#f1f5f9; color:#475569; }

    .tfoot-row td {
        background: #1e293b;
        color: #f1f5f9;
        font-weight: bold;
        font-size: 11px;
        padding: 5px 8px;
        border-color: #334155;
    }
    .tfoot-row td.num { text-align: right; }

    .no-data { padding: 16px; text-align: center; color: #94a3b8; font-style: italic; }

    @media print {
        body { padding: 0; }
        .product-block { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<h2>Product Audit — Ledger Report</h2>
<div class="report-meta">
    Period: <strong><?php echo htmlspecialchars($from); ?></strong>
    to <strong><?php echo htmlspecialchars($to); ?></strong>
    &nbsp;|&nbsp; Generated: <?php echo date('d M Y, H:i'); ?>
    &nbsp;|&nbsp; Shop ID: <?php echo (int) $shopId; ?>
    &nbsp;|&nbsp; Products: <?php echo !empty($orders) ? count($orders) : 0; ?>
</div>

<?php if (empty($orders)): ?>
    <div class="no-data">No ledger entries found for the selected criteria.</div>
<?php else: ?>

<?php foreach ($orders as $productId => $group):
    $meta   = $group['meta'];
    $rows   = $group['rows'];

    $totalIn    = 0;
    $totalOut   = 0;
    // Last row (DESC order) has the earliest running_balance;
    // first row has the latest — that is the closing balance
    $closingBalance = !empty($rows) ? $rows[0]['running_balance'] : 0;
    // Opening: closing minus net movement in period
    $netQty = array_sum(array_column($rows, 'quantity'));

    foreach ($rows as $r) {
        if ($r['quantity'] > 0) $totalIn  += $r['quantity'];
        else                    $totalOut += $r['quantity'];
    }
?>

<div class="product-block">
    <div class="product-header">
        <div>
            <div class="pname">
                <?php echo htmlspecialchars($meta['product_name'] ?? '—'); ?>
            </div>
            <div class="pmeta">
                Code: <?php echo htmlspecialchars($meta['product_code'] ?? '—'); ?>
                &nbsp;|&nbsp;
                Barcode: <?php echo htmlspecialchars($meta['barcode'] ?? '—'); ?>
                &nbsp;|&nbsp;
                ID: <?php echo (int) $productId; ?>
            </div>
        </div>
        <div style="text-align:right">
            <div class="pmeta"><?php echo count($rows); ?> entries</div>
            <div style="font-size:13px;font-weight:bold">
                Balance: <?php echo number_format($closingBalance); ?>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:36px">Sr#</th>
                <th style="width:60px">ID</th>
                <th style="width:100px">Date &amp; Time</th>
                <th>Type</th>
                <th>Ref #</th>
                <th>Remarks</th>
                <th class="num" style="width:80px">Qty In</th>
                <th class="num" style="width:80px">Qty Out</th>
                <th class="num" style="width:90px">Running Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sr = count($rows); // count down so ascending sr matches ascending id
        foreach ($rows as $row):
            $qty     = (int) $row['quantity'];
            $balance = (int) $row['running_balance'];
            $type    = strtolower($row['type'] ?? '');

            // Badge class
            $badgeClass = 'badge-other';
            if (strpos($type, 'sale') !== false)     $badgeClass = 'badge-sale';
            elseif (strpos($type, 'purchase') !== false || strpos($type, 'supply') !== false) $badgeClass = 'badge-purchase';
            elseif (strpos($type, 'return') !== false)   $badgeClass = 'badge-return';
            elseif (strpos($type, 'adjust') !== false)   $badgeClass = 'badge-adjust';

            $balanceClass = $balance > 0 ? 'balance-pos' : ($balance < 0 ? 'balance-neg' : 'balance-zero');
        ?>
            <tr>
                <td><?php echo $sr--; ?></td>
                <td><?php echo (int) $row['id']; ?></td>
                <td style="white-space:nowrap">
                    <?php echo date('d M y', strtotime($row['created_at'])); ?><br>
                    <span style="color:#94a3b8;font-size:10px">
                        <?php echo date('H:i', strtotime($row['created_at'])); ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo htmlspecialchars($row['type'] ?? '—'); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($row['reference_id'] ?? '—'); ?></td>
                <td style="color:#475569"><?php echo htmlspecialchars($row['remarks'] ?? ''); ?></td>
                <td class="num qty-in">
                    <?php echo $qty > 0 ? '+' . number_format($qty) : ''; ?>
                </td>
                <td class="num qty-out">
                    <?php echo $qty < 0 ? number_format($qty) : ''; ?>
                </td>
                <td class="num <?php echo $balanceClass; ?>">
                    <?php echo number_format($balance); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="6">Totals &nbsp;(<?php echo count($rows); ?> entries)</td>
                <td class="num"><?php echo $totalIn > 0 ? '+' . number_format($totalIn) : '—'; ?></td>
                <td class="num"><?php echo $totalOut < 0 ? number_format($totalOut) : '—'; ?></td>
                <td class="num"><?php echo number_format($closingBalance); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php endforeach; ?>
<?php endif; ?>

</body>
</html>