<?php
// movement_type badge styling
function auditBadge($type) {
    $t = strtolower($type ?? '');
    if (strpos($t, 'sale')     !== false) return ['cls' => 'badge-sale',     'label' => $type];
    if (strpos($t, 'purchase') !== false) return ['cls' => 'badge-purchase', 'label' => $type];
    if (strpos($t, 'supply')   !== false) return ['cls' => 'badge-purchase', 'label' => $type];
    if (strpos($t, 'return')   !== false) return ['cls' => 'badge-return',   'label' => $type];
    if (strpos($t, 'adjust')   !== false) return ['cls' => 'badge-adjust',   'label' => $type];
    if (strpos($t, 'transfer') !== false) return ['cls' => 'badge-transfer', 'label' => $type];
    return ['cls' => 'badge-other', 'label' => $type ?: '—'];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Product Audit Ledger</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #0f172a;
        background: #fff;
        padding: 14px 16px;
    }

    /* ── Report header ── */
    .report-title {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 3px;
    }
    .report-meta {
        font-size: 11px;
        color: #64748b;
        margin-bottom: 14px;
    }

    /* ── Product block ── */
    .product-block {
        margin-bottom: 24px;
        page-break-inside: avoid;
    }
    .product-header {
        background: #1a3a7a;
        color: #fff;
        padding: 6px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .product-header .pname {
        font-size: 13px;
        font-weight: bold;
    }
    .product-header .pmeta {
        font-size: 10px;
        opacity: .75;
        margin-top: 2px;
    }
    .product-header .pstats {
        text-align: right;
        font-size: 11px;
    }
    .product-header .pstats .big {
        font-size: 14px;
        font-weight: bold;
    }

    /* ── Table ── */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        text-align: left;
        white-space: nowrap;
    }
    thead th.r { text-align: right; }
    tbody td {
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
        font-size: 11px;
    }
    tbody td.r { text-align: right; font-variant-numeric: tabular-nums; }
    tbody tr:nth-child(even) td { background: #f8fafc; }
    tbody tr:hover td { background: #eff6ff; }

    /* ── Qty colours ── */
    .qty-in   { color: #15803d; font-weight: 600; }
    .qty-out  { color: #b91c1c; font-weight: 600; }
    .qty-zero { color: #94a3b8; }

    /* ── Running balance ── */
    .bal-pos  { color: #0f172a; font-weight: 600; }
    .bal-neg  { color: #b91c1c; font-weight: 600; }
    .bal-zero { color: #94a3b8; }

    /* ── Badges ── */
    .badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }
    .badge-sale     { background: #dcfce7; color: #14532d; }
    .badge-purchase { background: #dbeafe; color: #1d4ed8; }
    .badge-return   { background: #fef3c7; color: #78350f; }
    .badge-adjust   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .badge-transfer { background: #f3e8ff; color: #6b21a8; }
    .badge-other    { background: #f1f5f9; color: #475569; }

    /* ── Footer row ── */
    .tfoot-row td {
        background: #1e293b;
        color: #f1f5f9;
        font-weight: bold;
        font-size: 11px;
        padding: 5px 8px;
        border-color: #334155;
    }
    .tfoot-row td.r { text-align: right; }

    .no-data {
        padding: 20px;
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        border: 1px dashed #e2e8f0;
        border-top: none;
    }

    @media print {
        body { padding: 0; }
        .product-block { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<div class="report-title">Product Audit — Ledger Report</div>
<div class="report-meta">
    Period: <strong><?php echo htmlspecialchars($from); ?></strong>
    to <strong><?php echo htmlspecialchars($to); ?></strong>
    &nbsp;&bull;&nbsp; Generated: <?php echo date('d M Y, H:i'); ?>
    &nbsp;&bull;&nbsp; Shop: <?php echo (int) $shopId; ?>
    &nbsp;&bull;&nbsp; Products: <?php echo count($orders); ?>
</div>

<?php if (empty($orders)): ?>
    <div class="no-data">No ledger entries found for the selected criteria and date range.</div>
<?php endif; ?>

<?php foreach ($orders as $productId => $group):
    $meta = $group['meta'];
    $rows = $group['rows'];

    // rows are DESC — first row = latest running_balance (closing)
    $closingBalance = (int) ($rows[0]['running_balance'] ?? 0);

    $totalIn  = 0;
    $totalOut = 0;
    foreach ($rows as $r) {
        $q = (int) $r['quantity'];
        if ($q > 0) $totalIn  += $q;
        else        $totalOut += $q;
    }
?>
<div class="product-block">

    <div class="product-header">
        <div>
            <div class="pname"><?php echo htmlspecialchars($meta['product_name'] ?? 'Unknown Product'); ?></div>
            <div class="pmeta">
                Code: <?php echo htmlspecialchars($meta['product_code'] ?? '—'); ?>
                &nbsp;|&nbsp;
                Barcode: <?php echo htmlspecialchars($meta['barcode'] ?? '—'); ?>
                &nbsp;|&nbsp;
                ID: <?php echo (int) $productId; ?>
            </div>
        </div>
        <div class="pstats">
            <div><?php echo count($rows); ?> entries</div>
            <div>In: <strong><?php echo number_format($totalIn); ?></strong>
                 &nbsp;Out: <strong><?php echo number_format(abs($totalOut)); ?></strong></div>
            <div class="big">Balance: <?php echo number_format($closingBalance); ?></div>
        </div>
    </div>

    <?php if (empty($rows)): ?>
        <div class="no-data">No entries for this product in the selected period.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:32px">Sr#</th>
                <th style="width:50px">ID</th>
                <th style="width:95px">Date</th>
                <th style="width:90px">Movement</th>
                <th style="width:70px">Ref Type</th>
                <th style="width:60px">Ref ID</th>
                <th>Note</th>
                <th style="width:55px">By</th>
                <th class="r" style="width:65px">Qty In</th>
                <th class="r" style="width:65px">Qty Out</th>
                <th class="r" style="width:80px">Running Bal</th>
            </tr>
        </thead>
        <tbody>

        <?php
        /* ── Opening balance row ──────────────────────────────────────── */
        $ob = (int) $group['opening_balance'];
        $obClass = $ob > 0 ? 'bal-pos' : ($ob < 0 ? 'bal-neg' : 'bal-zero');
        ?>
            <tr style="background:#f0f9ff;">
                <td colspan="8" style="color:#1e40af;font-weight:600;font-size:11px;">
                    Opening Balance
                    <span style="color:#64748b;font-weight:400;font-size:10px;">
                        (before <?php echo htmlspecialchars($from); ?>)
                    </span>
                </td>
                <td class="r" style="color:#64748b">—</td>
                <td class="r" style="color:#64748b">—</td>
                <td class="r <?php echo $obClass; ?>" style="background:#e0f2fe;">
                    <?php echo number_format($ob); ?>
                </td>
            </tr>

        <?php
        $totalRows = count($rows);
        $sr = $totalRows;
        foreach ($rows as $row):
            $qty     = (int) $row['quantity'];
            $balance = (int) $row['running_balance'];  // already = opening + period_sum
            $badge   = auditBadge($row['movement_type']);

            $qtyInStr  = $qty > 0 ? '+' . number_format($qty) : '';
            $qtyOutStr = $qty < 0 ? number_format(abs($qty))  : '';
            $balClass  = $balance > 0 ? 'bal-pos' : ($balance < 0 ? 'bal-neg' : 'bal-zero');
        ?>
            <tr>
                <td><?php echo $sr--; ?></td>
                <td><?php echo (int) $row['id']; ?></td>
                <td style="white-space:nowrap">
                    <?php echo date('d M y', strtotime($row['created_at'])); ?>
                    <span style="color:#94a3b8;font-size:10px;display:block">
                        <?php echo date('H:i', strtotime($row['created_at'])); ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo $badge['cls']; ?>">
                        <?php echo htmlspecialchars($badge['label']); ?>
                    </span>
                </td>
                <td style="color:#64748b"><?php echo htmlspecialchars($row['ref_type']    ?? '—'); ?></td>
                <td style="color:#64748b"><?php echo htmlspecialchars($row['ref_id']      ?? '—'); ?></td>
                <td style="color:#475569"><?php echo htmlspecialchars($row['note']        ?? ''); ?></td>
                <td style="color:#64748b"><?php echo htmlspecialchars($row['created_by']  ?? '—'); ?></td>
                <td class="r qty-in" ><?php echo $qtyInStr;  ?></td>
                <td class="r qty-out"><?php echo $qtyOutStr; ?></td>
                <td class="r <?php echo $balClass; ?>">
                    <?php echo number_format(abs($balance)); ?>
                    <?php if ($balance < 0): ?>
                        <span style="font-size:9px;color:#b91c1c"> (neg)</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="8">
                    Opening: <?php echo number_format($ob); ?>
                    &nbsp;&nbsp;+In: <?php echo number_format($totalIn); ?>
                    &nbsp;&nbsp;-Out: <?php echo number_format(abs($totalOut)); ?>
                    &nbsp;&nbsp;= Closing
                </td>
                <td class="r">+<?php echo number_format($totalIn); ?></td>
                <td class="r"><?php echo $totalOut < 0 ? '-' . number_format(abs($totalOut)) : '—'; ?></td>
                <td class="r"><?php echo number_format($closingBalance); ?></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>

</div><!-- /.product-block -->
<?php endforeach; ?>

</body>
</html>