<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$ordersObj = new Orders();
$dateLabel = "Sales Returns for ";
$start = $end = $shop['sale_date'];

if (isset($_GET['report'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $dateLabel .= '<strong>' . $from . '</strong> to <strong>' . $to . '</strong>';
    $start = date('Y-m-d', strtotime($from));
    $end = date('Y-m-d', strtotime($to));
} else {
    $dateLabel .= '<strong>' . $shop['sale_date'] . '</strong>';
    $start = $shop['sale_date'];
    $end = $shop['sale_date'];
}
echo mainHeader(['page' => 'sale_returns']);
?>

<div class="container" ng-controller="reportController">
    <form method="GET" ng-submit="getReport()" class="form-group">
        <h4><?php echo $dateLabel; ?></h4>
        <div class="input-group">
            <div class="input-group-btn">
                <input type="text" value="" name="orderId" ng-model="orderId" class="form-control" style="width: 150px" placeholder="Order Number #" />
            </div>
            <input date-range-picker class="form-control date-picker" type="text" ng-model="datePicker.date" options="{ locale: {format: 'DD/MM/YYYY'}}" />
            <div class="input-group-btn">
                <input type="submit" value="Submit" name="report" class="btn btn-primary" />
            </div>
            <div class="input-group-btn">
                <a href="<?php echo SITE_URL; ?>pages/orders/adjustment.php" class="btn btn-danger">New Retrun</a>
                <?php if ($userData['role'] === 'manager') { ?>
                    <a href="<?php echo SITE_URL; ?>pages/orders/adjustment.php?LinkForMainShop=1" class="btn btn-danger">Retrun to Main Shop</a>
                <?php } ?>
            </div>
        </div>
    </form>
    <uib-tabset active="activePill">
        <uib-tab select="getReport($event)" index="0" data-tab="mine" heading="My Returns"></uib-tab>
        <uib-tab select="getReport($event)" index="1" data-tab="linked" heading="Link Returns"></uib-tab>
    </uib-tabset>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Order. #</th>
                <th>Customer</th>
                <th>Price</th>
                <th ng-repeat="mode in modes">{{mode.title}}</th>
                <th>Status</th>
                <th>Date/time</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="row in data.records">
                <td>{{$index + 1}}</td>
                <td>{{row.id}}</td>
                <td>{{row.customer_name || row.full_name}}</td>
                <td>{{row.price - row.discount}}</td>
                <td ng-repeat="mode in modes">{{row.prices[mode.id]}}</td>
                <td>{{statusArr[row.status].full_name}}</td>
                <td>{{row.order_date}}</td>
                <td align="right">
                    <?php if ($userData['role'] === 'owner') { ?><a class="btn btn-xs btn-default" href="<?php echo SITE_URL; ?>pages/orders/adjustment.php?return={{row.id}}">Edit</a><?php } ?>
                    <?php if ($userData['role'] === 'manager') { ?><a class="btn btn-xs btn-default" ng-if="row.flag == 1" href="<?php echo SITE_URL; ?>pages/orders/adjustment.php?return={{row.id}}">Edit</a><?php } ?>
                    <?php if ($userData['role'] === 'owner') { ?><a class="btn btn-xs btn-danger" ng-click="deleteRecipt(row.id)" href="javascript:void(0)">Delete</a><?php } ?>
                    <a class="btn btn-xs btn-default" ng-click="openRecipt(row.id, 'details', 'large')" href="javascript:void(0)">Large View</a>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="{{8 + modes.length}}">
                    <table style="text-align: right" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <th style="text-align: right">Number of Orders</th>
                            <th style="text-align: right">{{data.total}}</th>
                        </tr>

                        <tr ng-repeat="(k, d) in data.via">
                            <th style="text-align: right">Pay via {{modeNames[k]}}</th>
                            <th style="text-align: right">{{d | number: 2}}</th>
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

        $scope.getReport = (form) => {
            const orderType = form?.currentTarget?.parentNode?.getAttribute('data-tab') || $scope.activeValue || 'mine';
            $scope.activeValue = orderType || 'mine';
            console.log(orderType)
            $http.get("<?php echo SITE_URL ?>api/getSaleReturnReport.php", {
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
            $window.open("<?php echo SITE_URL; ?>print/return.php?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", (largeView ? "width=600,height=900" : "width=300,height=300"));
        }
        $scope.editRecipt = id => {
            $window.location.assign("<?php echo SITE_URL; ?>pages/recipt/edit.php?id=" + id, '_b');
        }

        $scope.deleteRecipt = function(id) {
            const reason = $window.prompt('Please Write a reason for delete this order')
            if (reason) {
                $http.get('returnDelete.php?id=' + id + '&reason=' + reason).then(function(response) {
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