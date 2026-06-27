<?php
// productPurchaseSaleReport.php — per-product purchase & sale transaction ledger.
// Expects $orders (grouped per product) from print.php :: case '25'
//   $orders[pid] = ['meta' => [...], 'rows' => [ ['txn_type','txn_id','txn_date','line_total'], ... ] ]

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

function ppslBillUrl($siteUrl, $type, $id)
{
    $id = (int) $id;
    if ($type === 'purchase') {
        return $siteUrl . 'print/supply.php?id=' . $id . '&detail=true&largeView=large';
    }
    return $siteUrl . 'print?id=' . $id . '&detail=true&largeView=large';
}

$grand = ['purchase' => 0, 'sale' => 0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Product Purchase / Sale Ledger</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #222; margin: 20px; }
        h2 { margin: 0 0 4px; }
        .sub { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 26px; }
        th, td { border: 1px solid #d0d0d0; padding: 6px 8px; text-align: left; }
        thead th { background: #f3f3f3; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .prodhead th { background: #e9eef5; font-size: 14px; }
        .tinfo { color: #888; font-size: 11px; font-weight: normal; }
        tfoot td { font-weight: bold; background: #f9f9f9; }
        a { color: #1a5db8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media print { body { margin: 0; } table { page-break-inside: auto; } }
    </style>
</head>

<body>
    <h2>Product Purchase / Sale Ledger</h2>
    <div class="sub">
        <?php echo $from ? "From <strong>" . htmlspecialchars($from) . "</strong>" : 'All time'; ?>
        <?php echo $to ? " to <strong>" . htmlspecialchars($to) . "</strong>" : ''; ?>
    </div>

    <?php if (empty($orders)) { ?>
        <p>No data found for the selected criteria.</p>
    <?php } else { ?>
        <?php foreach ($orders as $pid => $block) {
            $meta   = $block['meta'];
            $pTotal = 0;
            $sTotal = 0;
        ?>
            <table>
                <thead>
                    <tr class="prodhead">
                        <th colspan="6">
                            <?php echo htmlspecialchars($meta['product_name']); ?>
                            <span class="tinfo">
                                (Code: <?php echo htmlspecialchars($meta['product_code'] ?: '—'); ?>,
                                Barcode: <?php echo htmlspecialchars($meta['barcode'] ?: '—'); ?>)
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <th style="width:40px">Sr</th>
                        <th>Date</th>
                        <th>Purchase ID</th>
                        <th class="num">Purchase Total</th>
                        <th>Sale Order ID</th>
                        <th class="num">Sale Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($block['rows'] as $r) {
                        $isPurchase = ($r['txn_type'] === 'purchase');
                        $lineTotal  = (float) $r['line_total'];
                        if ($isPurchase) {
                            $pTotal += $lineTotal;
                            $grand['purchase'] += $lineTotal;
                        } else {
                            $sTotal += $lineTotal;
                            $grand['sale'] += $lineTotal;
                        }
                        $billUrl = ppslBillUrl($siteUrl, $r['txn_type'], $r['txn_id']);
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($r['txn_date']); ?></td>
                            <td>
                                <?php if ($isPurchase) { ?>
                                    <a href="<?php echo htmlspecialchars($billUrl); ?>" target="_blank">#<?php echo (int) $r['txn_id']; ?></a>
                                <?php } ?>
                            </td>
                            <td class="num"><?php echo $isPurchase ? number_format($lineTotal, 2) : ''; ?></td>
                            <td>
                                <?php if (!$isPurchase) { ?>
                                    <a href="<?php echo htmlspecialchars($billUrl); ?>" target="_blank">#<?php echo (int) $r['txn_id']; ?></a>
                                <?php } ?>
                            </td>
                            <td class="num"><?php echo !$isPurchase ? number_format($lineTotal, 2) : ''; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="num"><?php echo number_format($pTotal, 2); ?></td>
                        <td></td>
                        <td class="num"><?php echo number_format($sTotal, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        <?php } ?>

        <table>
            <tfoot>
                <tr>
                    <td colspan="3">Grand Total (all products)</td>
                    <td class="num"><?php echo number_format($grand['purchase'], 2); ?></td>
                    <td></td>
                    <td class="num"><?php echo number_format($grand['sale'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    <?php } ?>
</body>

</html>
