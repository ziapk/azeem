<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ordersObj = new Orders();
    $dateLabel = "Sales for ";
    $start = $end = date('Y-m-d');
    
    if(isset($_GET['report'])) {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $orders = $ordersObj->userOrders($userData['shopId'], $from, $to);
        $dateLabel .= '<strong>'.$from.'</strong> to <strong>'.$to.'</strong>';
        $start = date('Y-m-d', strtotime($from));
        $end = date('Y-m-d', strtotime($to));
    }
    else {        
        $orders = $ordersObj->userOrders($userData['shopId'], date('Y-m-d'));
        $dateLabel .= '<strong>'.date('Y-m-d').'</strong>';
        $start = date('Y-m-d');
        $end = date('Y-m-d');
    }
    echo mainHeader(['page' =>'order']);
?>

<div class="container" ng-controller="reportController">
<form method="GET" ng-submit="getReport()" class="form-group">
    <h4><?php echo $dateLabel;?></h4>
    <div class="input-group">
        <input date-range-picker class="form-control date-picker" type="text" ng-model="datePicker.date" options="{ locale: {format: 'DD/MM/YYYY'}}" />
        <div class="input-group-btn">
            <input type="submit" value="Submit" name="report" class="btn btn-primary" />
        </div>
    </div>
</form>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Order Number</th>
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
            <td>{{row.full_name}}</td>
            <td>{{(row.price - row.discount).toFixed(0)}}</td>
            <td>{{statusArr[row.status].full_name}}</td>
            <td>{{row.order_date}}</td>
            <td align="right">
                <a class="btn btn-xs btn-danger" ng-click="returnOrder(row.id)" href="javascript:void(0)">Return</a>
                <a ng-if="row.status != 2" class="btn btn-xs btn-primary" href="adjustment.php?id={{row.id}}">Pay</a>
                <a class="btn btn-xs btn-danger" ng-click="deleteRecipt(row.id)" href="javascript:void(0)">Delete</a>
                <a class="btn btn-xs btn-info" ng-click="openRecipt(row.id)" href="javascript:void(0)">Print</a>
                <a class="btn btn-xs btn-default" ng-click="openRecipt(row.id, 'details')" href="javascript:void(0)">View</a>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th colspan="6">
                <table style="text-align: right" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <th style="text-align: right">Number of Orders</th>
                        <th style="text-align: right">{{data.total}}</th>
                    </tr>
                    <tr>
                        <th style="text-align: right">Total Payment</th>
                        <th style="text-align: right">{{data.income}}</th>
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
    $scope.datePicker = { date: {startDate: '<?php echo date('Y-m-d');?>', endDate: '<?php echo date('Y-m-d');?>'} };

    $scope.statusArr = <?php echo json_encode($orderStatusArr);?> 

    $scope.getReport = form => {
        $http.get("<?php echo SITE_URL?>api/getSaleReport.php", {params: {from: moment($scope.datePicker.date.startDate).format('YYYY-MM-DD'), to: moment($scope.datePicker.date.endDate).format('YYYY-MM-DD')}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
            }
        })
    }

    $scope.getReport();


    $scope.openRecipt = (id, detail) => {
        if(detail) {
            detail = true
        }
        else {
            detail = false
        }
        $window.open("<?php echo SITE_URL;?>print?id="+id+"&detail="+detail, "", "width=300,height=300"); 
    }
    /* $scope.deleteRecipt = id => {
        $window.open("<?php echo SITE_URL;?>pages/orders/delete.php?id="+id, "", "width=300,height=300"); 
    } */

    $scope.deleteRecipt = function (id) {
        const reason = $window.prompt('Please Write a reason for delete this order')
        if(reason) {
            $http.get('delete.php?id='+id+'&reason='+reason).then(function(response) {
                if(response && response.data && response.data.success) {
                    $scope.getReport();
                }
                else {
                    
                }
                //$scope.getPublishers(1);
            });
        }
    }
    
    $scope.returnOrder = function (id) {
        $window.location.assign('<?php echo SITE_URL;?>pages/orders/retrun.php?id='+id)
    }

});

</script>
<script type="text/javascript">
 $('.datepicker').daterangepicker({
    minDate: moment().subtract(1, 'year'),
    maxDate: moment(),
    parentEl: '.datepicker-parent',
 },  function(start, end, label) {
     $('#from').val(moment(start).format('YYYY-MM-DD'));
     $('#to').val(moment(end).format('YYYY-MM-DD'));

 });
</script>