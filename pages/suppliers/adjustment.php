<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getCategories($userData['created_by']);
echo mainHeader(['page'=> 'supplier']);

$supplierObj = new Suppliers();

if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
    header('location: '.SITE_URL.'');
}

$supplier = $supplierObj->getSupplier($_GET['id']);
if(empty($supplier)) {
    header('location: '.SITE_URL.'');
}

?>
<div class="container" ng-controller="productController">
    <h4>Adjust Balance with Supplier</h4>
    <h5>Current Status: <br><br>BALANCE = <?php echo $supplier['wallet'];?> <br /><br /> <strong class="text-info">Going to Pay = {{wallet}}</strong> <br /><br /> REMAINING BALANCE = {{<?php echo $supplier['wallet'];?> + wallet}}</h5>

    <div class="row">
        <div class="col-sm-6">
            <input type="number" ng-model="wallet" value={{wallet}} ng-change="changeValue()" class="form-control" />
        </div>
        <div class="col-sm-6">
            <input type="button" ng-click="payToWallet()" value="Pay to Supplier" class="btn btn-primary" />
        </div>
    </div>
</div>
<script>
app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
    $scope.data = <?php echo json_encode($supplier);?>; //$scope.data.records;
    $scope.id = <?php echo json_encode($_GET['id']);?>; //$scope.data.records;
    $scope.wallet = -1 * $scope.data.wallet;


    $scope.payToWallet = function () {
        
        $http.post("<?php echo SITE_URL?>api/payToSupplier.php", $httpParamSerializerJQLike({amount: $scope.wallet, id: $scope.id}), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            alert('Amount Paid Successfully, transaction id is '+ response.data.supply.id);
            $window.location.assign('<?php echo SITE_URL.'pages/suppliers'?>');
        });
    }

    
});
</script>
<?php
echo mainFooter();