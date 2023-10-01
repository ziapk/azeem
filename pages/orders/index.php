<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$ordersObj = new Orders();
$dateLabel = "Sales for ";
$start = $end = $shop['sale_date'];

if (isset($_GET['report'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    // $orders = $ordersObj->userOrders($shop['id'], $_GET);
    $dateLabel .= '<strong>' . $from . '</strong> to <strong>' . $to . '</strong>';
    $start = date('Y-m-d', strtotime($from));
    $end = date('Y-m-d', strtotime($to));
} else {
    $data = ['from' => $shop['sale_date']];
    // $orders = $ordersObj->userOrders($shop['id'], $data);
    $dateLabel .= '<strong>' . $shop['sale_date'] . '</strong>';
    $start = $shop['sale_date'];
    $end = $shop['sale_date'];
}
echo mainHeader(['page' => 'order']);
?>

<div class="container" ng-controller="reportController">
    <form method="GET" ng-submit="getReport()" class="form-group">
        <h4><?php echo $dateLabel; ?></h4>
        <div class="input-group">
            <div class="input-group-btn" style="width: 20%">
                <input class="form-control" type="text" ng-model="orderId" placeholder="Order.#" />
            </div>
            <input date-range-picker class="form-control date-picker" type="text" ng-model="datePicker.date" options="{ locale: {format: 'DD/MM/YYYY'}}" />
            <div class="input-group-btn">
                <input type="submit" value="Submit" name="report" class="btn btn-primary" />
            </div>
        </div>
    </form>
    <uib-tabset active="activePill">
        <uib-tab select="getReport($event)" index="0" data-tab="all" heading="All Orders"></uib-tab>
        <uib-tab select="getReport($event)" index="1" data-tab="cash" heading="Paid"></uib-tab>
        <uib-tab select="getReport($event)" index="2" data-tab="credit" heading="Un-Paid"></uib-tab>
        <uib-tab select="getReport($event)" index="3" data-tab="park" heading="Parked"></uib-tab>
        <uib-tab select="getReport($event)" index="4" data-tab="sample" heading="Samples"></uib-tab>
    </uib-tabset>
    <table class="table">
        <thead style="font-size: .7em;">
            <tr>
                <th>Sr.#</th>
                <th>Order. #</th>
                <th>Customer</th>
                <th>Summery</th>
                <th>Price</th>
                <th ng-repeat="mode in modes">{{mode.title}}</th>
                <th>Running</th>
                <th>Status</th>
                <th>Date/time</th>
                <th width="140"></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="row in data.records">
                <td>{{$index + 1}}</td>
                <td style="white-space: nowrap;">
                    <?php if ($userData['role'] == 'owner' || $userData['role'] == 'manager') { ?>
                        <a ng-if="row.recon == 0" class="btn btn-xs btn-danger" ng-click="reconcileRecipt(row.id, 1)" href="javascript:void(0)"><span class="fa fa-check"></span></a>
                        <a ng-if="row.recon == 1" class="btn btn-xs btn-success" ng-click="reconcileRecipt(row.id, 0)" href="javascript:void(0)"><span class="fa fa-check"></span></a>
                    <?php } else { ?>
                        <a ng-if="row.recon == 0" class="btn btn-xs btn-danger" href="javascript:void(0)"><span class="fa fa-check"></span></a>
                        <a ng-if="row.recon == 1" class="btn btn-xs btn-success" href="javascript:void(0)"><span class="fa fa-check"></span></a>
                    <?php } ?>
                    <span>{{row.order_custom_id}}</span>
                </td>
                <td>{{row.customer_name || row.full_name}}</td>
                <td width="200" style="font-size: .7em;">{{row.summery}}</td>
                <td>{{row.price - row.discount | number: 0}}</td>
                <td ng-repeat="mode in modes">{{row.prices[mode.id] | number: 0}}</td>
                <td>{{row.runningTotal | number: 0}}</td>
                <td><span class="label" ng-class="{'label-success': row.status == 2, 'label-primary': row.status == 1, 'label-danger': row.status == 9}">{{statusArr[row.status].full_name | uppercase}}</span></td>
                <td>{{row.order_date | date: 'dd MMM'}}</td>
                <td align="right" class="dropdown">
                    <?php if ($userData['role'] == 'owner') { ?>
                        <a uib-tooltip="EDIT" class="btn btn-default btn-xs" ng-if="row.status != 5" href="{{'<?php echo SITE_URL; ?>pages/recipt/edit.php?id=' + row.id }}" target="_blank"><span class="fa fa-edit"></span></a>
                    <?php } else { ?>
                        <a uib-tooltip="EDIT" class="btn btn-default btn-xs" ng-if="row.status == 1" href="{{'<?php echo SITE_URL; ?>pages/recipt/edit.php?id=' + row.id }}" target="_blank"><span class="fa fa-edit"></span></a>
                    <?php } ?>
                    <a uib-tooltip="Print" class="btn btn-default btn-xs" ng-click="openRecipt(row.id)" href="javascript:void(0)"><span class="fa fa-print"></span></a>
                    <a uib-tooltip="Large View" class="btn btn-default btn-xs" ng-click="openRecipt(row.id, 'details', 'large')" href="javascript:void(0)"><span class="fa fa-file"></span></a>
                    <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?>
                        <a uib-tooltip="Ledger View" class="btn btn-default btn-xs" href="../chart-of-accounts/summery.php?t=c&id={{row.account_id}}" target="_blank"><span class="fa fa-eye"></span></a>
                    <?php } ?>
                    <a uib-tooltip="More" class="btn btn-default btn-xs" href="javascript:void(0)" data-toggle="dropdown"><span class="fa fa-chevron-down"></span></a>
                    <ul class="dropdown-menu pull-right">
                        <li>
                            <?php if ($userData['role'] == 'owner') { ?>
                                <a ng-if="row.status != 5" href="{{'<?php echo SITE_URL; ?>pages/recipt/edit.php?id=' + row.id }}" target="_blank"><span class="fa fa-edit"></span> Edit</a>
                            <?php } else { ?>
                                <a ng-if="row.status == 1" href="{{'<?php echo SITE_URL; ?>pages/recipt/edit.php?id=' + row.id }}" target="_blank"><span class="fa fa-edit"></span> Edit</a>
                            <?php } ?>
                        </li>
                        <li>
                            <a ng-if="row.status == 2" href="{{'<?php echo SITE_URL; ?>pages/orders/adjustment.php?id=' + row.id }}">Return</a>
                        </li>
                        <li>
                            <a ng-click="deleteRecipt(row.id)" href="javascript:void(0)"><span class="fa fa-remove"></span> Delete</a>
                        </li>
                        <li>
                            <a href="{{'<?php echo SITE_URL; ?>pages/recipt/edit.php?dup=' + row.id }}"><span class="fa fa-copy"></span> Duplicate</a>
                        </li>
                        <li>
                            <a ng-click="openRecipt(row.id)" href="javascript:void(0)"><span class="fa fa-print"></span> Print</a>
                        </li>
                        <li>
                            <a ng-click="openRecipt(row.id, 'details')" href="javascript:void(0)"><span class="fa fa-eye"></span> View</a>
                        </li>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?>
                            <li>
                                <a href="../chart-of-accounts/summery.php?t=c&id={{row.account_id}}" target="_blank"><span class="fa fa-eye"></span> Ledger View</a>
                            </li>
                        <?php } ?>
                        <li>
                            <a ng-click="openRecipt(row.id, 'details', 'large')" href="javascript:void(0)"><span class="fa fa-file"></span> Large View</a>
                        </li>
                    </ul>

                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="{{9 + modes.length}}">
                    <table class="table table-hover" style="box-shadow: none; text-align: right" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right">Number of Orders</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.5em">{{data.total || 0}}</td>
                        </tr>

                        <tr ng-repeat="(k, d) in data.via">
                            <td style="text-align: right">Pay via {{modeNames[k]}}</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.5em">{{(d || 0) | number: 2}}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right">Total Credit</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.5em">{{(data.credit || 0) | number: 2}}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right">Total Parked</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.5em">{{(data.park || 0) | number: 2}}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right">G. Total</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.5em">{{(data.totalIncome || 0) | number: 2}}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right">Total Return</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.5em">{{(data.return || 0) | number: 2}}</td>
                        </tr>
                    </table>
                </th>
            </tr>
        </tfoot>
    </table>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    app.controller('reportController', function($scope, $http, $httpParamSerializerJQLike, $window, $document, $uibModal) {
        $scope.datePicker = {
            date: {
                startDate: '<?php echo $shop['sale_date']; ?>',
                endDate: '<?php echo $shop['sale_date']; ?>'
            }
        };
        $scope.orderId = '';

        $scope.statusArr = <?php echo json_encode($orderStatusArr); ?>;
        $scope.activePill = 0;
        $scope.activeValue = 'all';
        $scope.getReport = (form, activeValue) => {
            const orderType = form?.currentTarget?.parentNode?.getAttribute('data-tab') || 'all';
            $scope.activeValue = orderType || activeValue || 'all';
            $http.get("<?php echo SITE_URL ?>api/getSaleReport.php", {
                    params: {
                        from: moment($scope.datePicker.date.startDate).format('YYYY-MM-DD'),
                        to: moment($scope.datePicker.date.endDate).format('YYYY-MM-DD'),
                        orderId: $scope.orderId,
                        orderType
                    }
                })
                .then(function(response) {
                    $scope.loading = false;
                    if (response.status === 200) {
                        $scope.data = response.data;
                    }
                })
        }

        $scope.getReport();


        $scope.openRecipt = (id, detail, largeView) => {
            if (detail) {
                detail = true
            } else {
                detail = false
            }
            $window.open("<?php echo SITE_URL; ?>print?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", (largeView ? "width=600,height=900" : "width=300,height=300"));
        }
        $scope.editRecipt = id => {
            $window.location.assign("<?php echo SITE_URL; ?>pages/recipt/edit.php?id=" + id, '_b');
        }

        $scope.deleteRecipt = function(id) {
            const reason = $window.prompt('Please Write a reason for delete this order')
            if (reason) {
                $http.get('delete.php?id=' + id + '&reason=' + reason).then(function(response) {
                    if (response && response.data && response.data.success) {
                        $scope.getReport();
                    } else {

                    }
                    //$scope.getPublishers(1);
                });
            }
        }
        $scope.reconcileRecipt = function(id, flag) {
            $http.get('reconcile.php?id=' + id + '&flag=' + flag).then(function(response) {
                if (response && response.data && response.data.success) {
                    $scope.getReport(null, $scope.activeValue);
                }
            })
        }

        $scope.returnOrder = function(id) {
            $window.location.assign('<?php echo SITE_URL; ?>pages/orders/retrun.php?id=' + id)
        }

        $scope.modeNames = {};
        $scope.modes = [];
        $scope.searchMode = function() {
            return $http.get("<?php echo SITE_URL ?>api/getPaymentModes.php")
                .then(function(response) {
                    $scope.modes = response.data.records;
                    $scope.modes.forEach(p => {
                        $scope.modeNames[p.id] = p.title;
                    })
                    return response.data
                });
        }

        $scope.searchMode();

    });
</script>
<script type="text/javascript">
    $('.datepicker').daterangepicker({
        minDate: moment().subtract(1, 'year'),
        maxDate: moment(),
        parentEl: '.datepicker-parent',
    }, function(start, end, label) {
        $('#from').val(moment(start).format('YYYY-MM-DD'));
        $('#to').val(moment(end).format('YYYY-MM-DD'));

    });
</script>