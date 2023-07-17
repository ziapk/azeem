<?php
if (empty($disableHeader)) {
    include_once dirname(__FILE__) . '/../../include/settings.php';

    echo mainHeader(['page' => 'product']);
}

$productsObj = new Products();
$products = $productsObj->getRackProductsPagination($shop['owner_id'], [], $shop['id']);
?>

<div class="container" ng-controller="productsController">
    <div class="row">
        <div class="col-sm-4 form-group">
            <label for="">Search Rack No</label>
            <input type="text" class="form-control" ng-model="rackNo" ng-change="refreshRacks(rackNo)">
        </div>
        <div class="col-sm-4 form-group">
            <label for="">Search Rack No By Product CODE</label>
            <input type="text" class="form-control" ng-model="pc" ng-change="refreshByProductId(pc)">
        </div>
    </div>
    <div class="row" style="margin: 0 -6px">
        <div class="col-sm-2" ng-repeat-start="li in products" style="padding: 0 6px">
            <div class="form-group card-item">


                <div class="card-item-title">Rack: {{li.title}}</div>
                <div class="card-body" style="padding: 10px">
                    <span class="badge badge-primary" style="margin: 0 2px 2px" ng-repeat="r in li.products track by $index">{{r}}</span>
                </div>
            </div>
        </div>
        <div ng-repeat-end="li in products track by $index" class="clearfix" ng-if="($index + 1) % 6 == 0"></div>
    </div>
</div>
<script>
    app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {

        $scope.oproducts = <?php echo safe_json_encode(array_values($products)); ?>;
        $scope.products = <?php echo safe_json_encode(array_values($products)); ?>;
        $scope.refreshRacks = rn => {
            $scope.products = rn ? $scope.oproducts.filter(r => r.title == rn) : $scope.oproducts;
        }

        $scope.refreshByProductId = rn => {

            $scope.products = rn ? $scope.oproducts.filter(r => r.products.filter(id => id == rn).length) : $scope.oproducts;
        }


    });
</script>

<?php echo mainFooter(); ?>