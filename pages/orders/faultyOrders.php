<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ordersObj = new Orders();

    $orders = $ordersObj->getFaultyProducts();
    $returns = $ordersObj->getReturnRecords();


echo mainHeader(['page'=>'return']);
?>
<div class="container" ng-controller="orderController22">
    <h4 class="clearfix">All Faulty Products (<small><strong>Selected Items:</strong></small> {{selectedProducts}})
        <a href="javascript:void(0)" ng-click="makeRetrun()" class="btn btn-primary btn-xs pull-right">Make Return Products</a>
    </h4>
    <table class="table">
        <thead>
            <tr>
                <th><input ng-change="handleCheckAll(checkAll)" type="checkbox" ng-model="checkAll"/></th>
                <th>Product Name</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $key => $value) {?>
                <tr>
                    <td><input type="checkbox" ng-change="selectSingle()" ng-model="checkbox[<?php echo $value['product_id'];?>]['select']"/></td>
                    <td><?php echo $value['product_name'];?></td>
                    <td><?php echo $value['quantity'];?></td>
                </tr>
                
            <?php } ?>
        </tbody>
    </table>

    <h4 class="clearfix">All Retrun Orders</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Description</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($returns as $key => $value) {?>
                <tr>
                    <td><?php echo $key + 1;?></td>
                    <td><?php echo $value['full_name'];?></td>
                    <td><?php echo $value['datetime'];?></td>
                    <td><a href="faultyDetail.php?id=<?php echo $value['id'];?>" class="btn btn-xs btn-primary">View Details</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    
</div>

<?php
echo mainFooter();
?>
<script type="text/javascript">
app.controller('orderController22', function($scope, $http, $httpParamSerializerJQLike) {
    $scope.checkAll = false;
    $scope.orders = <?php echo json_encode($orders);?>;
    $scope.checkbox = {};
    $scope.selectedProducts = 0;
    $scope.orders.map(row => $scope.checkbox[row.product_id] = { select: false, qty: row.quantity})
    $scope.handleCheckAll = function (check) {
        $scope.orders.map(row => $scope.checkbox[row.product_id].select = check)
        $scope.selectSingle();
    }

    $scope.selectSingle = function() {
        $scope.selectedProducts = 0;
        Object.values($scope.checkbox).map(row => {
            if(row.select) ($scope.selectedProducts += parseInt(row.qty)) 
        });
    }

    $scope.makeRetrun = function () {
        if(confirm('Are you sure you want to make this action?')) {
            const title = prompt('Enter a Title or description');
            const ids = [];
            Object.keys($scope.checkbox).map(row => {
                if($scope.checkbox[row].select) ids.push(row) 
            });
            const form = {
                title,
                ids
            }
            $http.post("<?php echo SITE_URL?>api/makeReturn.php", $httpParamSerializerJQLike(form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
            .then(function(response) {
                console.log(response)
            });
        }
    }
})
</script>

