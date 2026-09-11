<?php
/**
 * Fix Purchase Prices.
 *
 * Lists products sold at a loss in a period and lets the purchase price be
 * corrected inline. The usual cause is a supply line entered per pack while the
 * product is still sold per piece -- products.pprice is overwritten by every new
 * supply, so one pack-basis entry mis-costs the whole product.
 */
include_once dirname(__FILE__) . '/../../include/settings.php';

if ($userData['role'] != 'owner' && $userData['role'] != 'manager') {
    echo 'Access denied.';
    exit;
}

$productObj = new Products();
$stores     = new Store();
$ownerStores = $stores->getOwnerStores($userData['id']);

$shopId = !empty($_GET['shopId']) ? (int) $_GET['shopId'] : $shop['id'];
$from   = !empty($_GET['from']) ? $_GET['from'] : date('Y-01-01');
$to     = !empty($_GET['to'])   ? $_GET['to']   : date('Y-m-d');

$rows = $productObj->getPPriceReviewRows($shopId, $from, $to, 300);

echo mainHeader(['page' => 'product']);
?>
<style>
    .grid td,
    .grid th {
        padding: 5px 7px;
        border: 1px solid #ccc;
        font-size: 13px;
        white-space: nowrap;
    }

    .grid th {
        background: #f0dede;
        text-align: right;
    }

    .grid th.l,
    .grid td.l {
        text-align: left;
        white-space: normal;
    }

    .grid td {
        text-align: right;
    }

    .grid {
        width: 100%;
        border-collapse: collapse;
    }

    .grid tbody tr.changed {
        background: #fff8dc;
    }

    .grid tbody tr.saved {
        background: #e8f5e9;
    }

    .grid input.pp {
        width: 90px;
        text-align: right;
        padding: 2px 4px;
    }

    .hint {
        cursor: pointer;
        color: #337ab7;
        text-decoration: underline dotted;
    }

    .loss {
        color: #a00;
    }

    .wrap {
        overflow-x: auto;
    }

    .bar {
        position: sticky;
        top: 0;
        background: #fff;
        padding: 8px 0;
        z-index: 5;
        border-bottom: 1px solid #ddd;
    }
</style>

<div class="container-fluid" ng-controller="fixPPriceController">
    <h4>Fix Purchase Prices <small>products sold below cost</small></h4>

    <form method="GET" class="row" style="margin-bottom: 10px">
        <div class="col-sm-3 form-group">
            <label>From</label>
            <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($from); ?>">
        </div>
        <div class="col-sm-3 form-group">
            <label>To</label>
            <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($to); ?>">
        </div>
        <?php if ($userData['role'] == 'owner') { ?>
            <div class="col-sm-3 form-group">
                <label>Shop</label>
                <select class="form-control" name="shopId">
                    <?php foreach ($ownerStores as $value) { ?>
                        <option value="<?php echo $value['id']; ?>" <?php echo $value['id'] == $shopId ? 'selected' : ''; ?>>
                            <?php echo $value['full_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
        <div class="col-sm-3 form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">Show</button>
        </div>
    </form>

    <div class="alert alert-info" style="padding: 8px 12px">
        <strong>{{rows.length}}</strong> product(s) sold below cost in this period.
        A purchase price above the selling price usually means a supply was entered
        <em>per pack</em> while the product is sold <em>per piece</em>.
        Click a suggested figure to fill it in, then Save.
        <br>
        <small>
            <strong>Note:</strong> the product purchase price is stored as a whole
            number, so costs are rounded to the nearest rupee when saved &mdash;
            a supplier cost of 19.50 is kept as 20.
        </small>
    </div>

    <div class="bar">
        <button class="btn btn-success" ng-click="saveAll()" ng-disabled="saving || changedCount() === 0">
            {{saving ? 'Saving...' : 'Save ' + changedCount() + ' change(s)'}}
        </button>
        <button class="btn btn-default" ng-click="resetAll()" ng-disabled="saving || changedCount() === 0">Reset</button>
        <button class="btn btn-link" ng-click="applySuggestedAll()" ng-disabled="saving">Fill all with suggested</button>
        <span ng-if="message" style="margin-left: 10px" class="text-success">{{message}}</span>
        <span ng-if="error" style="margin-left: 10px" class="text-danger">{{error}}</span>
    </div>

    <div class="wrap">
        <table class="grid">
            <thead>
                <tr>
                    <th class="l">#</th>
                    <th class="l">Product</th>
                    <th>Sale Price</th>
                    <th>Current Cost</th>
                    <th>Last Paid</th>
                    <th>Lowest Paid</th>
                    <th>Suggested</th>
                    <th>Units Sold</th>
                    <th>Sale Value</th>
                    <th>Loss</th>
                    <th>New Cost</th>
                    <th>New Profit</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="r in rows" ng-class="{changed: isChanged(r), saved: r.savedOk}">
                    <td class="l">{{$index + 1}}</td>
                    <td class="l">
                        {{r.full_name}} <small>(#{{r.product_id}}<span ng-if="r.code"> / {{r.code}}</span>)</small>
                    </td>
                    <td>{{r.sale_price | number:0}}</td>
                    <td class="loss">{{r.current_pprice | number:0}}</td>
                    <td>
                        <span ng-if="r.last_paid" class="hint" ng-click="setCost(r, r.last_paid)"
                            title="{{r.supply_date}}">{{r.last_paid | number:2}}</span>
                        <span ng-if="!r.last_paid">&mdash;</span>
                    </td>
                    <td>
                        <span ng-if="r.min_paid" class="hint" ng-click="setCost(r, r.min_paid)">{{r.min_paid | number:2}}</span>
                        <span ng-if="!r.min_paid">&mdash;</span>
                    </td>
                    <td>
                        <span ng-if="suggested(r) !== null" class="hint"
                            ng-click="setCost(r, suggested(r))"><strong>{{suggested(r) | number:0}}</strong></span>
                        <span ng-if="suggested(r) === null">&mdash;</span>
                    </td>
                    <td>{{r.units | number:0}}</td>
                    <td>{{r.sale_value | number:0}}</td>
                    <td class="loss">({{-r.profit | number:0}})</td>
                    <td>
                        <input type="number" step="1" min="0" class="form-control pp" ng-model="r.newPPrice">
                    </td>
                    <td ng-class="{loss: newProfit(r) < 0}">{{newProfit(r) | number:0}}</td>
                </tr>
                <tr ng-if="rows.length === 0">
                    <td class="l" colspan="12"><em>No products were sold below cost in this period.</em></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    app.controller('fixPPriceController', function($scope, $http) {
        var raw = <?php echo json_encode($rows); ?>;

        $scope.rows = raw.map(function(r) {
            r.sale_price     = parseFloat(r.sale_price) || 0;
            r.current_pprice = parseFloat(r.current_pprice) || 0;
            r.last_paid      = r.last_paid === null ? null : parseFloat(r.last_paid);
            r.min_paid       = r.min_paid === null ? null : parseFloat(r.min_paid);
            r.same_basis_cost = r.same_basis_cost === null ? null : parseFloat(r.same_basis_cost);
            r.units          = parseFloat(r.units) || 0;
            r.sale_value     = parseFloat(r.sale_value) || 0;
            r.profit         = parseFloat(r.profit) || 0;
            r.newPPrice      = r.current_pprice;
            r.savedOk        = false;
            return r;
        });

        // Average price actually realised per unit, so the new profit preview
        // reflects the discounts that were really given.
        function netUnitPrice(r) {
            return r.units ? r.sale_value / r.units : r.sale_price;
        }

        // Prefer a cost recorded on the same unit basis as the selling price;
        // fall back to the cheapest ever paid, which is almost always the
        // per-piece figure when the latest entry was per pack.
        $scope.suggested = function(r) {
            if (r.same_basis_cost !== null && r.same_basis_cost > 0 && r.same_basis_cost < netUnitPrice(r)) {
                return r.same_basis_cost;
            }
            if (r.min_paid !== null && r.min_paid > 0 && r.min_paid < netUnitPrice(r)) {
                return r.min_paid;
            }
            return null;
        };

        $scope.setCost = function(r, value) {
            // products.pprice is an integer column, so keep the grid honest.
            r.newPPrice = Math.round(value);
            r.savedOk = false;
        };

        $scope.applySuggestedAll = function() {
            $scope.rows.forEach(function(r) {
                var s = $scope.suggested(r);
                if (s !== null) {
                    $scope.setCost(r, s);
                }
            });
        };

        $scope.isChanged = function(r) {
            return r.newPPrice !== '' && r.newPPrice !== null &&
                parseFloat(r.newPPrice) !== r.current_pprice;
        };

        $scope.changedCount = function() {
            return $scope.rows.filter($scope.isChanged).length;
        };

        $scope.newProfit = function(r) {
            var cost = parseFloat(r.newPPrice);
            if (isNaN(cost)) { return r.profit; }
            return (netUnitPrice(r) - cost) * r.units;
        };

        $scope.resetAll = function() {
            $scope.rows.forEach(function(r) {
                r.newPPrice = r.current_pprice;
                r.savedOk = false;
            });
            $scope.message = '';
            $scope.error = '';
        };

        $scope.saveAll = function() {
            var changed = $scope.rows.filter($scope.isChanged);
            if (!changed.length) { return; }

            $scope.saving = true;
            $scope.message = '';
            $scope.error = '';

            $http.post('<?php echo SITE_URL; ?>api/updatePPrices.php', {
                rows: changed.map(function(r) {
                    return { product_id: r.product_id, pprice: parseFloat(r.newPPrice) };
                })
            }).then(function(res) {
                $scope.saving = false;
                if (res.data && res.data.status === 200) {
                    $scope.message = res.data.message;
                    changed.forEach(function(r) {
                        r.current_pprice = parseFloat(r.newPPrice);
                        r.profit  = $scope.newProfit(r);
                        r.savedOk = true;
                    });
                } else {
                    $scope.error = (res.data && res.data.message) || 'Could not save.';
                }
            }, function() {
                $scope.saving = false;
                $scope.error = 'Could not save. Please try again.';
            });
        };
    });
</script>

<?php echo mainFooter(); ?>
