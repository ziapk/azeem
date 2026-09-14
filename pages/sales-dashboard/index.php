<?php
/**
 * Sales Dashboard -- owner only.
 *
 * When a shop sells (hour of day, and day x hour) and which items sell most,
 * for one of the owner's shops over a chosen period. A separate page: the
 * existing dashboards are left exactly as they are.
 *
 * Times are Pakistan time, taken from each sale's accounting transaction --
 * see SalesDashboard::getDashboard() for why orders.created_at is not used.
 */
include_once dirname(__FILE__) . '/../../include/settings.php';

if (empty($userData['role']) || $userData['role'] !== 'owner') {
    header('location: ' . SITE_URL);
    exit;
}

$storeObj    = new Store();
$ownerStores = $storeObj->getOwnerStores($userData['id']);
$storeNames  = [];
foreach ($ownerStores as $st) {
    $storeNames[(int) $st['id']] = $st['full_name'];
}

// Only ever one of this owner's own shops, whatever the URL asks for.
$shopId = !empty($_GET['shopId']) ? (int) $_GET['shopId'] : (int) $shop['id'];
if (!isset($storeNames[$shopId])) {
    reset($storeNames);
    $shopId = isset($storeNames[(int) $shop['id']]) ? (int) $shop['id'] : (int) key($storeNames);
}

$today   = date('Y-m-d');
$presets = [
    'today'     => ['Today',        $today, $today],
    '7d'        => ['Last 7 days',  date('Y-m-d', strtotime('-6 days')),  $today],
    '30d'       => ['Last 30 days', date('Y-m-d', strtotime('-29 days')), $today],
    '90d'       => ['Last 90 days', date('Y-m-d', strtotime('-89 days')), $today],
    'month'     => ['This month',   date('Y-m-01'), $today],
    'year'      => ['This year',    date('Y-01-01'), $today],
    'last-year' => ['Last year',    date('Y-01-01', strtotime('-1 year')), date('Y-12-31', strtotime('-1 year'))],
];

$isDate = function ($d) {
    $dt = DateTime::createFromFormat('Y-m-d', (string) $d);
    return $dt && $dt->format('Y-m-d') === $d;
};

$range = !empty($_GET['range']) ? (string) $_GET['range'] : '30d';
$getFrom = isset($_GET['from']) ? (string) $_GET['from'] : '';
$getTo   = isset($_GET['to']) ? (string) $_GET['to'] : '';
if ($range === 'custom' && $isDate($getFrom) && $isDate($getTo)) {
    $from = $getFrom;
    $to   = $getTo;
    if ($from > $to) {
        list($from, $to) = [$to, $from];
    }
} else {
    if (!isset($presets[$range])) {
        $range = '30d';
    }
    $from = $presets[$range][1];
    $to   = $presets[$range][2];
}

$views = ['all' => 'All sales', 'paid' => 'Paid sales', 'credit' => 'Credit sales'];
$view  = isset($_GET['view']) && is_string($_GET['view']) && isset($views[$_GET['view']]) ? $_GET['view'] : 'all';

$data = null;
if ($shopId && isset($storeNames[$shopId])) {
    $dashboard = new SalesDashboard();
    $data = $dashboard->getDashboard($shopId, $from, $to, 15, $view);
}

$fmt = function ($n) {
    return number_format(round((float) $n));
};
$compact = function ($n) {
    $n = (float) $n;
    $abs = abs($n);
    // Trim a trailing ".0" only -- trimming zeros off a whole number turned 20M into 2M.
    $short = function ($v, $decimals) {
        $txt = number_format($v, $decimals);
        return $decimals > 0 ? preg_replace('/\.0$/', '', $txt) : $txt;
    };
    if ($abs >= 1000000) {
        return $short($n / 1000000, $abs >= 10000000 ? 0 : 1) . 'M';
    }
    if ($abs >= 1000) {
        return $short($n / 1000, $abs >= 10000 ? 0 : 1) . 'K';
    }
    return number_format(round($n));
};
$hourLabel = function ($h) {
    $h = (int) $h % 24;
    $suffix = $h < 12 ? 'AM' : 'PM';
    $d = $h % 12 === 0 ? 12 : $h % 12;
    return $d . ' ' . $suffix;
};
$dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

$periodText = date('j M Y', strtotime($from)) . ($from === $to ? '' : ' – ' . date('j M Y', strtotime($to)));

echo mainHeader(['page' => 'sales-dashboard']);
?>
<style>
    .sd {
        --sd-surface: #fcfcfb;
        --sd-ink: #0b0b0b;
        --sd-ink2: #52514e;
        --sd-muted: #898781;
        --sd-grid: #e1e0d9;
        --sd-axis: #c3c2b7;
        --sd-none: #f0efec;
        --sd-series: #2a78d6;
        --sd-series-hover: #5598e7;
        --sd-border: rgba(11, 11, 11, 0.10);
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: var(--sd-ink);
        padding-bottom: 30px;
    }

    .sd h4 {
        margin: 12px 0 2px;
        font-weight: 600;
    }

    .sd h4 small {
        color: var(--sd-ink2);
        font-weight: 400;
    }

    .sd-sub {
        color: var(--sd-ink2);
        margin: 0 0 12px;
        font-size: 13px;
    }

    .sd-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px 14px;
        margin-bottom: 14px;
    }

    .sd-filters label {
        display: flex;
        flex-direction: column;
        font-size: 12px;
        color: var(--sd-ink2);
        font-weight: 500;
        margin: 0;
    }

    .sd-filters select,
    .sd-filters input {
        height: 32px;
        padding: 4px 8px;
        border: 1px solid var(--sd-axis);
        border-radius: 4px;
        background: #fff;
        color: var(--sd-ink);
        font-size: 13px;
        margin-top: 3px;
    }

    .sd-custom {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .sd-filters select,
    .sd-filters input,
    .sd-filters label {
        max-width: 100%;
        min-width: 0;
    }

    .sd-tile .v {
        overflow-wrap: anywhere;
    }

    .sd-spacer {
        flex: 1 1 auto;
    }

    .sd-seg {
        display: inline-flex;
        flex-wrap: wrap;
        max-width: 100%;
        border: 1px solid var(--sd-axis);
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
    }

    .sd-seg button {
        border: 0;
        background: #fff;
        padding: 6px 10px;
        font-size: 12px;
        color: var(--sd-ink2);
        line-height: 18px;
    }

    .sd-seg button+button {
        border-left: 1px solid var(--sd-axis);
    }

    .sd-seg button.active {
        background: var(--sd-ink);
        color: #fff;
    }

    .sd-seg-label {
        font-size: 12px;
        color: var(--sd-ink2);
        font-weight: 500;
        margin-right: 6px;
    }

    .sd-tiles {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .sd-tile,
    .sd-card {
        background: var(--sd-surface);
        border: 1px solid var(--sd-border);
        border-radius: 8px;
    }

    .sd-tile {
        padding: 12px 14px;
    }

    .sd-tile .l {
        font-size: 12px;
        color: var(--sd-ink2);
    }

    .sd-tile .v {
        font-size: 24px;
        font-weight: 600;
        line-height: 1.25;
        margin-top: 4px;
    }

    .sd-tile .s {
        font-size: 12px;
        color: var(--sd-muted);
        margin-top: 2px;
    }

    .sd-card {
        padding: 12px 14px 10px;
        margin-bottom: 14px;
    }

    .sd-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    .sd-card-head h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
    }

    .sd-note {
        font-size: 12px;
        color: var(--sd-ink2);
    }

    .sd-chart {
        position: relative;
        overflow-x: auto;
    }

    .sd-chart svg {
        display: block;
    }

    .sd-chart svg text {
        font-family: inherit;
    }

    .sd-hit:focus {
        outline: none;
    }

    .sd-empty {
        padding: 30px 0;
        text-align: center;
        color: var(--sd-muted);
        font-size: 13px;
    }

    .sd-table summary {
        cursor: pointer;
        font-size: 12px;
        color: var(--sd-ink2);
        margin-top: 6px;
        display: list-item;
    }

    .sd-scroll {
        overflow-x: auto;
    }

    .sd table.sd-t {
        width: 100%;
        font-size: 12px;
        font-variant-numeric: tabular-nums;
        margin-top: 6px;
        border-collapse: collapse;
    }

    .sd table.sd-t th,
    .sd table.sd-t td {
        padding: 4px 8px;
        border-bottom: 1px solid var(--sd-grid);
        text-align: right;
        white-space: nowrap;
    }

    .sd table.sd-t th {
        color: var(--sd-ink2);
        font-weight: 600;
    }

    .sd table.sd-t th.l,
    .sd table.sd-t td.l {
        text-align: left;
        white-space: normal;
    }

    .sd table.sd-t td.neg {
        color: #d03b3b;
    }

    .sd-scale {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: var(--sd-muted);
        margin-top: 8px;
    }

    .sd-scale i {
        display: inline-block;
        width: 18px;
        height: 12px;
        border-radius: 2px;
    }

    .sd-scale .gap {
        width: 10px;
    }

    .sd-hot-row {
        display: grid;
        grid-template-columns: 24px minmax(120px, 38%) 1fr;
        align-items: center;
        gap: 8px;
        padding: 3px 0;
        border-radius: 4px;
    }

    .sd-hot-row:focus {
        outline: 2px solid var(--sd-series);
        outline-offset: 1px;
    }

    .sd-hot-rank {
        font-size: 12px;
        color: var(--sd-muted);
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .sd-hot-name {
        font-size: 13px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sd-hot-track {
        display: flex;
        align-items: center;
        min-width: 0;
        height: 24px;
    }

    .sd-hot-bar {
        height: 14px;
        background: var(--sd-series);
        border-radius: 0 4px 4px 0;
        min-width: 2px;
    }

    .sd-hot-row:hover .sd-hot-bar,
    .sd-hot-row:focus .sd-hot-bar {
        background: var(--sd-series-hover);
    }

    .sd-hot-val {
        margin-left: 6px;
        font-size: 12px;
        color: var(--sd-ink2);
        white-space: nowrap;
    }

    .sd-foot {
        font-size: 12px;
        color: var(--sd-muted);
        margin: 4px 0 0;
        padding-left: 18px;
    }

    .sd-tip {
        position: fixed;
        z-index: 2000;
        pointer-events: none;
        background: #fff;
        border: 1px solid rgba(11, 11, 11, 0.12);
        border-radius: 6px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        padding: 8px 10px;
        font-size: 12px;
        color: var(--sd-ink2);
        max-width: 280px;
        line-height: 1.45;
    }

    .sd-tip .tt {
        color: var(--sd-ink2);
        margin-bottom: 2px;
    }

    .sd-tip strong {
        display: block;
        font-size: 15px;
        color: var(--sd-ink);
        font-weight: 600;
    }
</style>

<div class="container-fluid sd" id="salesDashboard">
    <h4>Sales Dashboard <small><?php echo htmlspecialchars(isset($storeNames[$shopId]) ? $storeNames[$shopId] : ''); ?></small></h4>
    <p class="sd-sub"><strong><?php echo $views[$view]; ?></strong> &middot; <?php echo htmlspecialchars($periodText); ?> &middot; times shown in Pakistan time</p>

    <form class="sd-filters" method="GET" action="">
        <label>
            Period
            <select name="range" id="sdRange">
                <?php foreach ($presets as $key => $p) { ?>
                    <option value="<?php echo $key; ?>" <?php echo $range === $key ? 'selected' : ''; ?>><?php echo $p[0]; ?></option>
                <?php } ?>
                <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?>>Custom range&hellip;</option>
            </select>
        </label>
        <span class="sd-custom" id="sdCustom" <?php echo $range === 'custom' ? '' : 'hidden'; ?>>
            <label>From <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>"></label>
            <label>To <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>"></label>
        </span>
        <?php if (count($storeNames) > 1) { ?>
            <label>
                Shop
                <select name="shopId">
                    <?php foreach ($storeNames as $id => $name) { ?>
                        <option value="<?php echo $id; ?>" <?php echo $id === $shopId ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                    <?php } ?>
                </select>
            </label>
        <?php } ?>
        <button type="submit" class="btn btn-primary btn-sm" style="height: 32px">Apply</button>
        <span>
            <span class="sd-seg-label" style="display: block; margin-bottom: 3px">View</span>
            <input type="hidden" name="view" value="<?php echo $view; ?>">
            <span class="sd-seg" role="group" aria-label="Which sales to show">
                <?php foreach ($views as $key => $label) { ?>
                    <button type="submit" name="view" value="<?php echo $key; ?>" class="<?php echo $view === $key ? 'active' : ''; ?>" aria-pressed="<?php echo $view === $key ? 'true' : 'false'; ?>"><?php echo $label; ?></button>
                <?php } ?>
            </span>
        </span>
        <span class="sd-spacer"></span>
        <span>
            <span class="sd-seg-label">Time charts show</span>
            <span class="sd-seg" role="group" aria-label="Measure shown in the time charts">
                <button type="button" data-measure="sales" class="active" aria-pressed="true">Sales value</button>
                <button type="button" data-measure="bills" aria-pressed="false">Bills</button>
            </span>
        </span>
    </form>

    <?php if (empty($data)) { ?>
        <div class="sd-card">
            <div class="sd-empty">No shop is linked to this owner account.</div>
        </div>
    <?php } else {
        $sm = $data['summary'];
        $peakHour = $data['peak_hour'];
        $peakDay  = $data['peak_day'];
    ?>
        <div class="sd-tiles">
            <div class="sd-tile">
                <div class="l"><?php echo $views[$view]; ?></div>
                <div class="v" title="<?php echo $fmt($sm['sales']); ?>"><?php echo $compact($sm['sales']); ?></div>
                <?php if ($view === 'all' && $sm['sales'] > 0) { ?>
                    <div class="s"><?php echo $compact($sm['paid_sales']); ?> paid &middot; <?php echo $compact($sm['credit_sales']); ?> credit (<?php echo round($sm['credit_sales'] / $sm['sales'] * 100); ?>%)</div>
                <?php } else { ?>
                    <div class="s"><?php echo $fmt($sm['sales']); ?> after discounts</div>
                <?php } ?>
            </div>
            <div class="sd-tile">
                <div class="l"><?php echo $view === 'paid' ? 'Paid bills' : ($view === 'credit' ? 'Credit bills' : 'Bills'); ?></div>
                <div class="v"><?php echo $fmt($sm['bills']); ?></div>
                <div class="s">Average bill <?php echo $sm['bills'] > 0 ? $fmt($sm['sales'] / $sm['bills']) : '0'; ?></div>
            </div>
            <div class="sd-tile">
                <div class="l">Busiest hour, by sales</div>
                <div class="v"><?php echo $peakHour === null ? '&mdash;' : $hourLabel($peakHour['hour']) . ' – ' . $hourLabel($peakHour['hour'] + 1); ?></div>
                <div class="s"><?php echo $peakHour === null ? 'No timed sales' : $compact($peakHour['sales']) . ' sales &middot; ' . $fmt($peakHour['bills']) . ' bills'; ?></div>
            </div>
            <div class="sd-tile">
                <div class="l">Busiest day, by sales</div>
                <div class="v"><?php echo $peakDay === null ? '&mdash;' : $dayNames[$peakDay['weekday']]; ?></div>
                <div class="s"><?php echo $peakDay === null ? 'No timed sales' : $compact($peakDay['sales']) . ' sales &middot; ' . $fmt($peakDay['bills']) . ' bills'; ?></div>
            </div>
            <div class="sd-tile">
                <div class="l">Items sold</div>
                <div class="v"><?php echo $fmt($data['items']['units']); ?></div>
                <div class="s">units, across <?php echo $fmt($data['items']['products']); ?> products</div>
            </div>
        </div>

        <div class="sd-card">
            <div class="sd-card-head">
                <h5 id="sdHourTitle">Sales by hour of day</h5>
                <span class="sd-note" id="sdHourNote"></span>
            </div>
            <div class="sd-chart" id="sdHourChart"></div>
            <details class="sd-table">
                <summary>View as table</summary>
                <div class="sd-scroll" id="sdHourTable"></div>
            </details>
        </div>

        <div class="sd-card">
            <div class="sd-card-head">
                <h5 id="sdHeatTitle">Busiest times &mdash; day and hour</h5>
                <span class="sd-note">Darker is busier</span>
            </div>
            <div class="sd-chart" id="sdHeat"></div>
            <div class="sd-scale" id="sdHeatScale"></div>
            <details class="sd-table">
                <summary>View as table</summary>
                <div class="sd-scroll" id="sdHeatTable"></div>
            </details>
        </div>

        <div class="sd-card">
            <div class="sd-card-head">
                <h5>Hot items &mdash; top <?php echo (int) $data['hot_limit']; ?></h5>
                <span>
                    <span class="sd-seg-label">Rank by</span>
                    <span class="sd-seg" role="group" aria-label="Rank hot items by">
                        <button type="button" data-rank="sales" class="active" aria-pressed="true">Sales value</button>
                        <button type="button" data-rank="units" aria-pressed="false">Units sold</button>
                        <button type="button" data-rank="bills" aria-pressed="false">Bills</button>
                    </span>
                </span>
            </div>
            <div id="sdHot"></div>
            <div class="sd-scroll" id="sdHotTable"></div>
        </div>

        <ul class="sd-foot">
            <li>Times are Pakistan time, taken from when each bill was saved. Sales made after midnight appear in the early hours, after 11 PM on the charts.</li>
            <li><strong>Paid sales</strong> are bills with payment recorded when billed; <strong>credit sales</strong> were saved with nothing paid, onto the customer's account. Customers pay their account rather than individual bills, so a credit bill stays a credit sale after it is settled &mdash; the Receivable Summary in the Profit Summary report shows what is still owed.</li>
            <li>Sales are bill totals after discounts. Parked, cancelled and deleted bills, zero-value write-off bills, and bills for Giveaway customers (samples, donations) are left out.</li>
            <li>Hot items leave out amount-entry rows, whose quantity is a rupee amount. Profit uses each product's current purchase price.</li>
            <?php if ($data['untimed_bills'] > 0) { ?>
                <li><?php echo $fmt($data['untimed_bills']); ?> bill(s) have no recorded time and are left out of the two time charts only.</li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="sd-tip" id="sdTip" role="tooltip" hidden></div>
</div>

<?php if (!empty($data)) { ?>
    <script type="text/javascript">
        (function() {
            'use strict';

            // HEX flags: a product name containing a closing script tag or quotes
            // cannot break out of this element; a stray invalid byte in a name must
            // not blank the page.
            var DATA = <?php echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0)); ?>;
            var DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            var DAYS_SHORT = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            // Sequential blue, light to dark (steps 100-700); "none" is neutral.
            var RAMP = ['#cde2fb', '#9ec5f4', '#6da7ec', '#3987e5', '#256abf', '#184f95', '#0d366b'];
            var C = {
                series: '#2a78d6',
                seriesHover: '#5598e7',
                grid: '#e1e0d9',
                axis: '#c3c2b7',
                muted: '#898781',
                ink: '#0b0b0b',
                ink2: '#52514e',
                none: '#f0efec'
            };
            var SVGNS = 'http://www.w3.org/2000/svg';
            var state = {
                measure: 'sales',
                rank: 'sales'
            };

            /* ---------- formatting ---------- */
            function full(n) {
                return Math.round(n || 0).toLocaleString('en-US');
            }

            function compact(n) {
                n = n || 0;
                var a = Math.abs(n);
                if (a >= 1e6) return trimZero((n / 1e6).toFixed(a >= 1e7 ? 0 : 1)) + 'M';
                if (a >= 1e3) return trimZero((n / 1e3).toFixed(a >= 1e4 ? 0 : 1)) + 'K';
                return full(n);
            }

            function trimZero(s) {
                return s.indexOf('.') >= 0 ? s.replace(/\.?0+$/, '') : s;
            }

            function hourLabel(h) {
                h = ((h % 24) + 24) % 24;
                var d = h % 12 === 0 ? 12 : h % 12;
                return d + ' ' + (h < 12 ? 'AM' : 'PM');
            }

            function hourShort(h) {
                h = ((h % 24) + 24) % 24;
                var d = h % 12 === 0 ? 12 : h % 12;
                return d + (h < 12 ? 'a' : 'p');
            }

            function hourRange(h) {
                return hourLabel(h) + ' – ' + hourLabel(h + 1);
            }

            function measureOf(cell) {
                return state.measure === 'bills' ? cell.bills : cell.sales;
            }

            function measureText(v) {
                return state.measure === 'bills' ? full(v) + ' bills' : full(v) + ' sales';
            }

            function pct(part, whole) {
                return whole > 0 ? (part / whole * 100).toFixed(1) + '%' : '0%';
            }

            /* ---------- DOM helpers (labels via textContent only) ---------- */
            function el(tag, cls, text) {
                var e = document.createElement(tag);
                if (cls) e.className = cls;
                if (text !== undefined && text !== null) e.textContent = text;
                return e;
            }

            function svg(tag, attrs) {
                var e = document.createElementNS(SVGNS, tag);
                for (var k in attrs) {
                    if (Object.prototype.hasOwnProperty.call(attrs, k)) e.setAttribute(k, attrs[k]);
                }
                return e;
            }

            function svgText(x, y, text, attrs) {
                var t = svg('text', attrs || {});
                t.setAttribute('x', x);
                t.setAttribute('y', y);
                t.textContent = text;
                return t;
            }

            function empty(box, msg) {
                box.textContent = '';
                box.appendChild(el('div', 'sd-empty', msg));
            }

            /* ---------- tooltip ---------- */
            var tip = document.getElementById('sdTip');

            function showTip(evt, title, value, lines, anchorEl) {
                tip.textContent = '';
                tip.appendChild(el('div', 'tt', title));
                tip.appendChild(el('strong', null, value));
                (lines || []).forEach(function(l) {
                    tip.appendChild(el('div', null, l));
                });
                tip.hidden = false;
                var x, y;
                if (evt && evt.clientX !== undefined && evt.type.indexOf('pointer') === 0) {
                    x = evt.clientX;
                    y = evt.clientY;
                } else {
                    var r = anchorEl.getBoundingClientRect();
                    x = r.left + r.width / 2;
                    y = r.top;
                }
                var tw = tip.offsetWidth,
                    th = tip.offsetHeight;
                var left = x + 14,
                    top = y - th - 12;
                if (left + tw > window.innerWidth - 8) left = x - tw - 14;
                if (top < 8) top = y + 16;
                tip.style.left = Math.max(8, left) + 'px';
                tip.style.top = top + 'px';
            }

            function hideTip() {
                tip.hidden = true;
            }

            function bindHover(target, onShow, onHide) {
                target.addEventListener('pointerenter', onShow);
                target.addEventListener('pointermove', onShow);
                target.addEventListener('focus', onShow);
                target.addEventListener('pointerleave', function() {
                    hideTip();
                    if (onHide) onHide();
                });
                target.addEventListener('blur', function() {
                    hideTip();
                    if (onHide) onHide();
                });
            }

            /* ---------- hour order: the shop's business day ---------- */
            // Start the axis just after the longest run of hours with no sales, so
            // after-midnight trade follows 11 PM instead of opening the chart.
            function businessHours() {
                var hours = DATA.hours;
                var bestStart = -1,
                    bestLen = 0;
                for (var s = 0; s < 24; s++) {
                    if (hours[s].bills > 0 || hours[(s + 23) % 24].bills === 0) continue;
                    var len = 0;
                    while (len < 24 && hours[(s + len) % 24].bills === 0) len++;
                    if (len > bestLen) {
                        bestLen = len;
                        bestStart = s;
                    }
                }
                var order = [];
                if (bestStart < 0) {
                    var any = hours.some(function(h) {
                        return h.bills > 0;
                    });
                    if (!any) return [];
                    for (var i = 0; i < 24; i++) order.push(i);
                    return order;
                }
                var first = (bestStart + bestLen) % 24;
                for (var j = 0; j < 24 - bestLen; j++) order.push((first + j) % 24);
                return order;
            }

            function niceTicks(max) {
                if (max <= 0) return [0, 1];
                var raw = max / 4;
                var mag = Math.pow(10, Math.floor(Math.log10(raw)));
                var steps = [1, 2, 2.5, 5, 10];
                var step = steps[steps.length - 1] * mag;
                for (var i = 0; i < steps.length; i++) {
                    if (steps[i] * mag * 4 >= max) {
                        step = steps[i] * mag;
                        break;
                    }
                }
                var ticks = [];
                for (var t = 0; t <= 4; t++) ticks.push(step * t);
                return ticks;
            }

            // Column with a 4px rounded data end, square at the baseline.
            function columnPath(x, y, w, h) {
                var r = Math.min(4, w / 2, h);
                return 'M' + x + ',' + (y + h) + 'V' + (y + r) +
                    'Q' + x + ',' + y + ' ' + (x + r) + ',' + y +
                    'H' + (x + w - r) +
                    'Q' + (x + w) + ',' + y + ' ' + (x + w) + ',' + (y + r) +
                    'V' + (y + h) + 'Z';
            }

            /* ---------- chart 1: by hour ---------- */
            function drawHours(order) {
                var box = document.getElementById('sdHourChart');
                var title = document.getElementById('sdHourTitle');
                title.textContent = state.measure === 'bills' ? 'Bills by hour of day' : 'Sales by hour of day';
                if (!order.length) {
                    empty(box, 'No sales with a recorded time in this period.');
                    document.getElementById('sdHourNote').textContent = '';
                    return;
                }
                box.textContent = '';
                var rows = order.map(function(h) {
                    return DATA.hours[h];
                });
                var total = rows.reduce(function(a, r) {
                    return a + measureOf(r);
                }, 0);
                var peak = rows.reduce(function(best, r) {
                    return measureOf(r) > measureOf(best) ? r : best;
                }, rows[0]);
                document.getElementById('sdHourNote').textContent =
                    'Busiest: ' + hourRange(peak.hour) + ' · ' + measureText(measureOf(peak));

                var W = Math.max(box.clientWidth, rows.length * 16 + 60);
                var M = {
                    top: 22,
                    right: 8,
                    bottom: 26,
                    left: 46
                };
                var plotH = 210;
                var H = M.top + plotH + M.bottom;
                var plotW = W - M.left - M.right;
                var band = plotW / rows.length;
                var barW = Math.max(4, Math.min(24, band * 0.64));
                var ticks = niceTicks(measureOf(peak));
                var yMax = ticks[ticks.length - 1];
                var y = function(v) {
                    return M.top + plotH - (v / yMax) * plotH;
                };

                var root = svg('svg', {
                    width: W,
                    height: H,
                    role: 'img',
                    'aria-label': title.textContent + '. Busiest ' + hourRange(peak.hour) + ', ' + measureText(measureOf(peak)) + '.'
                });

                ticks.forEach(function(t, i) {
                    var yy = Math.round(y(t)) + 0.5;
                    root.appendChild(svg('line', {
                        x1: M.left,
                        x2: W - M.right,
                        y1: yy,
                        y2: yy,
                        stroke: i === 0 ? C.axis : C.grid,
                        'stroke-width': 1
                    }));
                    root.appendChild(svgText(M.left - 6, yy + 4, state.measure === 'bills' ? full(t) : compact(t), {
                        'text-anchor': 'end',
                        'font-size': 11,
                        fill: C.muted,
                        style: 'font-variant-numeric: tabular-nums'
                    }));
                });

                var labelEvery = Math.max(1, Math.ceil(34 / band));
                rows.forEach(function(r, i) {
                    var v = measureOf(r);
                    var cx = M.left + i * band + band / 2;
                    var bx = cx - barW / 2;
                    var bar = null;
                    if (v > 0) {
                        var top = y(v);
                        var h = M.top + plotH - top;
                        bar = svg('path', {
                            d: columnPath(bx, top, barW, Math.max(h, 1)),
                            fill: C.series
                        });
                        root.appendChild(bar);
                    }
                    if (i % labelEvery === 0) {
                        root.appendChild(svgText(cx, M.top + plotH + 16, hourShort(r.hour), {
                            'text-anchor': 'middle',
                            'font-size': 11,
                            fill: C.muted
                        }));
                    }
                    if (r === peak && v > 0) {
                        root.appendChild(svgText(cx, y(v) - 6, state.measure === 'bills' ? full(v) : compact(v), {
                            'text-anchor': 'middle',
                            'font-size': 11,
                            'font-weight': 600,
                            fill: C.ink2
                        }));
                    }

                    var hit = svg('rect', {
                        x: M.left + i * band,
                        y: M.top,
                        width: band,
                        height: plotH,
                        fill: 'transparent',
                        tabindex: 0,
                        class: 'sd-hit',
                        'aria-label': hourRange(r.hour) + ': ' + full(r.sales) + ' sales, ' + full(r.bills) + ' bills'
                    });
                    bindHover(hit, function(evt) {
                        if (bar) bar.setAttribute('fill', C.seriesHover);
                        var lines = [
                            state.measure === 'bills' ?
                            full(r.sales) + ' sales' : full(r.bills) + ' bills',
                            'Average bill ' + (r.bills > 0 ? full(r.sales / r.bills) : '0'),
                            'Per day ' + (state.measure === 'bills' ?
                                (r.bills / DATA.days).toFixed(1) + ' bills' :
                                full(r.sales / DATA.days) + ' sales'),
                            pct(v, total) + ' of the period'
                        ];
                        showTip(evt, hourRange(r.hour), measureText(v), lines, hit);
                    }, function() {
                        if (bar) bar.setAttribute('fill', C.series);
                    });
                    root.appendChild(hit);
                });

                box.appendChild(root);
                drawHourTable(rows);
            }

            function drawHourTable(rows) {
                var box = document.getElementById('sdHourTable');
                box.textContent = '';
                var total = rows.reduce(function(a, r) {
                    return a + r.sales;
                }, 0);
                var t = el('table', 'sd-t');
                var hr = el('tr');
                ['Hour', 'Sales', 'Bills', 'Average bill', 'Share of sales'].forEach(function(h, i) {
                    hr.appendChild(el('th', i === 0 ? 'l' : null, h));
                });
                var thead = el('thead');
                thead.appendChild(hr);
                t.appendChild(thead);
                var tb = el('tbody');
                rows.forEach(function(r) {
                    var tr = el('tr');
                    tr.appendChild(el('td', 'l', hourRange(r.hour)));
                    tr.appendChild(el('td', null, full(r.sales)));
                    tr.appendChild(el('td', null, full(r.bills)));
                    tr.appendChild(el('td', null, r.bills > 0 ? full(r.sales / r.bills) : '0'));
                    tr.appendChild(el('td', null, pct(r.sales, total)));
                    tb.appendChild(tr);
                });
                t.appendChild(tb);
                box.appendChild(t);
            }

            /* ---------- chart 2: day x hour heatmap ---------- */
            function drawHeat(order) {
                var box = document.getElementById('sdHeat');
                var scale = document.getElementById('sdHeatScale');
                document.getElementById('sdHeatTitle').textContent =
                    state.measure === 'bills' ? 'Busiest times by bills — day and hour' : 'Busiest times by sales — day and hour';
                scale.textContent = '';
                if (!order.length) {
                    empty(box, 'No sales with a recorded time in this period.');
                    return;
                }
                box.textContent = '';

                var max = 0,
                    peakCell = null;
                for (var d = 0; d < 7; d++) {
                    order.forEach(function(h) {
                        var v = measureOf(DATA.grid[d][h]);
                        if (v > max) {
                            max = v;
                            peakCell = {
                                d: d,
                                h: h
                            };
                        }
                    });
                }

                var labelW = 40;
                var cellW = Math.max(18, Math.floor((box.clientWidth - labelW - 4) / order.length));
                var cellH = 26;
                var W = labelW + cellW * order.length + 4;
                var H = cellH * 7 + 22;
                var root = svg('svg', {
                    width: W,
                    height: H,
                    role: 'img',
                    'aria-label': 'Heatmap of ' + (state.measure === 'bills' ? 'bills' : 'sales') +
                        ' by weekday and hour.' + (peakCell ? ' Busiest: ' + DAYS[peakCell.d] + ' ' + hourRange(peakCell.h) + '.' : '')
                });

                var colorFor = function(v) {
                    if (v <= 0 || max <= 0) return C.none;
                    var i = Math.ceil((v / max) * RAMP.length) - 1;
                    return RAMP[Math.max(0, Math.min(RAMP.length - 1, i))];
                };

                for (var dd = 0; dd < 7; dd++) {
                    root.appendChild(svgText(0, dd * cellH + cellH / 2 + 4, DAYS_SHORT[dd], {
                        'font-size': 11,
                        fill: C.ink2
                    }));
                }
                var labelEvery = Math.max(1, Math.ceil(30 / cellW));
                order.forEach(function(h, ci) {
                    if (ci % labelEvery === 0) {
                        root.appendChild(svgText(labelW + ci * cellW + cellW / 2, 7 * cellH + 14, hourShort(h), {
                            'text-anchor': 'middle',
                            'font-size': 11,
                            fill: C.muted
                        }));
                    }
                });

                var dayTotals = [];
                for (var z = 0; z < 7; z++) {
                    dayTotals[z] = order.reduce(function(a, h) {
                        return a + measureOf(DATA.grid[z][h]);
                    }, 0);
                }

                for (var row = 0; row < 7; row++) {
                    order.forEach(function(h, ci) {
                        var c = DATA.grid[row][h];
                        var v = measureOf(c);
                        var dayIdx = row;
                        var rect = svg('rect', {
                            x: labelW + ci * cellW + 1,
                            y: row * cellH + 1,
                            width: cellW - 2,
                            height: cellH - 2,
                            rx: 2,
                            fill: colorFor(v),
                            tabindex: 0,
                            class: 'sd-hit',
                            'aria-label': DAYS[row] + ' ' + hourRange(h) + ': ' + full(c.sales) + ' sales, ' + full(c.bills) + ' bills'
                        });
                        bindHover(rect, function(evt) {
                            rect.setAttribute('stroke', C.ink);
                            rect.setAttribute('stroke-width', 2);
                            showTip(evt, DAYS[dayIdx] + ', ' + hourRange(h), measureText(v), [
                                state.measure === 'bills' ? full(c.sales) + ' sales' : full(c.bills) + ' bills',
                                'Average bill ' + (c.bills > 0 ? full(c.sales / c.bills) : '0'),
                                pct(v, dayTotals[dayIdx]) + ' of that weekday'
                            ], rect);
                        }, function() {
                            rect.removeAttribute('stroke');
                            rect.removeAttribute('stroke-width');
                        });
                        root.appendChild(rect);
                    });
                }
                box.appendChild(root);

                // Scale legend
                scale.appendChild(el('span', null, 'None'));
                var none = el('i');
                none.style.background = C.none;
                scale.appendChild(none);
                scale.appendChild(el('span', 'gap'));
                scale.appendChild(el('span', null, 'Less'));
                RAMP.forEach(function(c) {
                    var sw = el('i');
                    sw.style.background = c;
                    scale.appendChild(sw);
                });
                scale.appendChild(el('span', null, 'More (up to ' + (state.measure === 'bills' ? full(max) + ' bills' : compact(max) + ' sales') + ' in one hour)'));

                drawHeatTable(order);
            }

            function drawHeatTable(order) {
                var box = document.getElementById('sdHeatTable');
                box.textContent = '';
                var t = el('table', 'sd-t');
                var thead = el('thead');
                var hr = el('tr');
                hr.appendChild(el('th', 'l', state.measure === 'bills' ? 'Bills' : 'Sales'));
                order.forEach(function(h) {
                    hr.appendChild(el('th', null, hourShort(h)));
                });
                hr.appendChild(el('th', null, 'Total'));
                thead.appendChild(hr);
                t.appendChild(thead);
                var tb = el('tbody');
                for (var d = 0; d < 7; d++) {
                    var tr = el('tr');
                    tr.appendChild(el('td', 'l', DAYS[d]));
                    var sum = 0;
                    order.forEach(function(h) {
                        var v = measureOf(DATA.grid[d][h]);
                        sum += v;
                        tr.appendChild(el('td', null, v > 0 ? (state.measure === 'bills' ? full(v) : compact(v)) : '–'));
                    });
                    tr.appendChild(el('td', null, state.measure === 'bills' ? full(sum) : compact(sum)));
                    tb.appendChild(tr);
                }
                t.appendChild(tb);
                box.appendChild(t);
            }

            /* ---------- chart 3: hot items ---------- */
            function drawHot() {
                var box = document.getElementById('sdHot');
                var tableBox = document.getElementById('sdHotTable');
                box.textContent = '';
                tableBox.textContent = '';
                var rows = DATA.hot[state.rank] || [];
                if (!rows.length) {
                    empty(box, 'No items sold in this period.');
                    return;
                }
                var key = state.rank;
                var max = rows.reduce(function(m, r) {
                    return Math.max(m, r[key]);
                }, 0);
                var valueText = function(r) {
                    if (key === 'units') return full(r.units) + ' units';
                    if (key === 'bills') return full(r.bills) + ' bills';
                    return compact(r.sales);
                };

                rows.forEach(function(r, i) {
                    var row = el('div', 'sd-hot-row');
                    row.tabIndex = 0;
                    row.setAttribute('aria-label', (i + 1) + '. ' + r.full_name + ': ' + full(r.sales) + ' sales, ' +
                        full(r.units) + ' units, ' + full(r.bills) + ' bills');
                    row.appendChild(el('div', 'sd-hot-rank', String(i + 1)));
                    var name = el('div', 'sd-hot-name', r.full_name);
                    row.appendChild(name);
                    var track = el('div', 'sd-hot-track');
                    var bar = el('div', 'sd-hot-bar');
                    var share = max > 0 ? r[key] / max : 0;
                    bar.style.width = 'calc((100% - 80px) * ' + share.toFixed(4) + ')';
                    track.appendChild(bar);
                    track.appendChild(el('span', 'sd-hot-val', valueText(r)));
                    row.appendChild(track);
                    bindHover(row, function(evt) {
                        var margin = r.sales > 0 ? (r.profit / r.sales * 100).toFixed(1) + '%' : '–';
                        showTip(evt, r.full_name, valueText(r) + (key === 'sales' ? ' sales' : ''), [
                            full(r.sales) + ' sales · ' + full(r.units) + ' units',
                            full(r.bills) + ' bills · average ' + (r.bills > 0 ? (r.units / r.bills).toFixed(1) : '0') + ' per bill',
                            'Profit ' + full(r.profit) + ' (' + margin + ')'
                        ], row);
                    });
                    box.appendChild(row);
                });

                var t = el('table', 'sd-t');
                var thead = el('thead');
                var hr = el('tr');
                ['#', 'Product', 'Units', 'Bills', 'Sales', 'Cost', 'Profit', 'Margin'].forEach(function(h, i) {
                    hr.appendChild(el('th', i === 1 ? 'l' : null, h));
                });
                thead.appendChild(hr);
                t.appendChild(thead);
                var tb = el('tbody');
                rows.forEach(function(r, i) {
                    var tr = el('tr');
                    tr.appendChild(el('td', null, String(i + 1)));
                    tr.appendChild(el('td', 'l', r.full_name + (r.code ? ' (' + r.code + ')' : '')));
                    tr.appendChild(el('td', null, full(r.units)));
                    tr.appendChild(el('td', null, full(r.bills)));
                    tr.appendChild(el('td', null, full(r.sales)));
                    tr.appendChild(el('td', null, full(r.cost)));
                    tr.appendChild(el('td', r.profit < 0 ? 'neg' : null, full(r.profit)));
                    tr.appendChild(el('td', r.profit < 0 ? 'neg' : null, r.sales > 0 ? (r.profit / r.sales * 100).toFixed(1) + '%' : '–'));
                    tb.appendChild(tr);
                });
                t.appendChild(tb);
                tableBox.appendChild(t);
            }

            /* ---------- wiring ---------- */
            var ORDER = businessHours();

            function renderTime() {
                hideTip();
                drawHours(ORDER);
                drawHeat(ORDER);
            }

            function setActive(groupAttr, value) {
                var btns = document.querySelectorAll('[data-' + groupAttr + ']');
                Array.prototype.forEach.call(btns, function(b) {
                    var on = b.getAttribute('data-' + groupAttr) === value;
                    b.classList.toggle('active', on);
                    b.setAttribute('aria-pressed', on ? 'true' : 'false');
                });
            }

            Array.prototype.forEach.call(document.querySelectorAll('[data-measure]'), function(b) {
                b.addEventListener('click', function() {
                    state.measure = b.getAttribute('data-measure');
                    setActive('measure', state.measure);
                    renderTime();
                });
            });
            Array.prototype.forEach.call(document.querySelectorAll('[data-rank]'), function(b) {
                b.addEventListener('click', function() {
                    state.rank = b.getAttribute('data-rank');
                    setActive('rank', state.rank);
                    hideTip();
                    drawHot();
                });
            });

            var rangeSel = document.getElementById('sdRange');
            if (rangeSel) {
                rangeSel.addEventListener('change', function() {
                    document.getElementById('sdCustom').hidden = rangeSel.value !== 'custom';
                });
            }

            var resizeTimer = null;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(renderTime, 150);
            });
            window.addEventListener('scroll', hideTip, true);

            renderTime();
            drawHot();
        })();
    </script>
<?php } ?>

<?php echo mainFooter(); ?>
