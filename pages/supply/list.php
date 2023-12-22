<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$ordersObj = new Supply();
$dateLabel = "Purchase Orders";

echo mainHeader(['page' => 'supplies']);
?>

<div class="container" ng-controller="reportController">
    <form method="GET" ng-submit="getReport()" class="form-group">
        <h4><?php echo $dateLabel; ?></h4>
        <div class="input-group">
            <div class="input-group-btn">
                <input type="text" ng-model="orderId" class="form-control" style="width: 150px" placeholder="Order Number #" />
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
    </uib-tabset>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Order Number</th>
                <th>Ref.#</th>
                <th>Customer</th>
                <th>Price</th>
                <th>Status</th>
                <th>Date/time</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="row in data.records">
                <td>{{$index + 1}}</td>
                <td>{{row.id}}</td>
                <td>{{row.ref_no}}</td>
                <td>{{row.customer_name || row.full_name}}</td>
                <td>{{row.price}}</td>
                <td>{{statusArr[row.status].full_name}}</td>
                <td>{{row.order_date}}</td>
                <td align="right">
                    <?php if ($userData['role'] == 'owner') { ?>
                        <a class="btn btn-xs btn-default" ng-if="row.status != 5" href="{{'<?php echo SITE_URL; ?>pages/supply/index.php?id=' + row.id }}" target="_blank">Edit</a>
                    <?php } else { ?>
                        <a class="btn btn-xs btn-default" ng-if="row.status == 1" href="{{'<?php echo SITE_URL; ?>pages/supply/index.php?id=' + row.id }}" target="_blank">Edit</a>
                    <?php } ?>
                    <a class="btn btn-xs btn-danger" ng-click="deleteRecipt(row.id)" href="javascript:void(0)">Delete</a>
                    <a class="btn btn-xs btn-info" ng-click="openRecipt(row.id)" href="javascript:void(0)">Print</a>
                    <a class="btn btn-xs btn-default" ng-click="openRecipt(row.id, 'details')" href="javascript:void(0)">View</a>
                    <a class="btn btn-xs btn-default" ng-click="openRecipt(row.id, 'details', 'large')" href="javascript:void(0)">Large View</a>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8">
                    <table style="text-align: right" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <th style="text-align: right">Number of Orders</th>
                            <th style="text-align: right">{{data.total}}</th>
                        </tr>
                        <tr>
                            <th style="text-align: right">Total Payment</th>
                            <th style="text-align: right">{{data.income | number: 2}}</th>
                        </tr>
                        <tr>
                            <th style="text-align: right">Total Credit</th>
                            <th style="text-align: right">{{data.credit | number: 2}}</th>
                        </tr>
                        <tr>
                            <th style="text-align: right">Total Parked</th>
                            <th style="text-align: right">{{data.park | number: 2}}</th>
                        </tr>
                        <tr>
                            <th style="text-align: right">G. Total</th>
                            <th style="text-align: right">{{data.totalIncome | number: 2}}</th>
                        </tr>
                        <tr>
                            <th style="text-align: right">Total Return</th>
                            <th style="text-align: right">{{data.return | number: 2}}</th>
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

        $scope.statusArr = <?php echo json_encode($orderStatusArr); ?>

        $scope.getReport = (form, activeValue) => {
            const orderType = form?.currentTarget?.parentNode?.getAttribute('data-tab') || 'all';
            $scope.activeValue = orderType || activeValue || 'all';
            $http.get("<?php echo SITE_URL ?>api/getSupplyReport.php", {
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
            $window.open("<?php echo SITE_URL; ?>print/supply.php?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", (largeView ? "width=600,height=900" : "width=300,height=300"));
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

        $scope.returnOrder = function(id) {
            $window.location.assign('<?php echo SITE_URL; ?>pages/orders/retrun.php?id=' + id)
        }

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