<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ordersObj = new Orders();
    $dateLabel = "Sales for ";
    $start = $end = date('Y-m-d');
    
    if(isset($_GET['report'])) {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $orders = $ordersObj->userOrders($userData['shopId'], $from, $to);
        $dateLabel .= $from.' to '.$to;
        $start = date('Y-m-d', strtotime($from));
        $end = date('Y-m-d', strtotime($to));
    }
    else {        
        $orders = $ordersObj->userOrders($userData['shopId'], date('Y-m-d'));
        $dateLabel .= date('Y-m-d');
        $start = date('Y-m-d');
        $end = date('Y-m-d');
    }
    echo mainHeader();
?>

<div class="container" ng-controller="reportController">
<form method="GET" ng-submit="getReport()">
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
                <a class="btn btn-xs btn-danger" ng-click="deleteRecipt(row.id)" href="javascript:void(0)">Delete</a>
                <a class="btn btn-xs btn-info" ng-click="openRecipt(row.id)" href="javascript:void(0)">Print</a>
                <a class="btn btn-xs btn-primary" ng-click="openRecipt(row.id, 'details')" href="javascript:void(0)">View</a>
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
app.controller('reportController', function ($scope, $http, $window) {
    $scope.datePicker = { date: {startDate: null, endDate: null} };

    $scope.statusArr = <?php echo json_encode($orderStatusArr);?> 

    $scope.getReport = form => {
        $http.get(<?php echo SITE_URL?>+"api/getSaleReport.php", {params: {from: moment($scope.datePicker.date.startDate).format('YYYY-MM-DD'), to: moment($scope.datePicker.date.endDate).format('YYYY-MM-DD')}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
            }
        })
    }
    $scope.openRecipt = (id, detail) => {
        if(detail) {
            detail = true
        }
        else {
            detail = false
        }
        $window.open("http://localhost/tea/print?id="+id+"&detail="+detail, "", "width=300,height=300"); 
    }
    $scope.deleteRecipt = id => {
        $window.open("http://localhost/tea/pages/orders/delete.php?id="+id, "", "width=300,height=300"); 
    }

});

</script>