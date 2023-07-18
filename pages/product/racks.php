<?php
if (empty($disableHeader)) {
    include_once dirname(__FILE__) . '/../../include/settings.php';

    echo mainHeader(['page' => 'racks']);
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
        <div class="col-sm-4 form-group">
            <label>&nbsp;</label> <br />
            <a href="javascript:void(0)" class="btn btn-primary" ng-click="addCustomer()">Add Product to Rack</a>
        </div>
    </div>
    <div class="row" style="margin: 0 -6px">
        <div class="col-sm-2" ng-repeat-start="li in products" style="padding: 0 6px">
            <div class="form-group card-item">


                <div class="card-item-title">Rack: {{li.title}}</div>
                <div class="card-body" style="padding: 10px">
                    <span style="margin: 0 2px 2px; font-size: 1em; display: inline-block; padding: 0; padding-left: 5px" class="label" ng-class="{'label-default': !pc || r !== pc, 'label-danger': pc && r == pc}" ng-repeat="r in li.products track by $index"><span>{{r}}</span><a href="javascript:void(0)" style="margin-left: 5px; border-radius: 0 3px 3px 0" class="btn btn-default btn-xs" ng-click="deleteRackProduct(li.rack_id, r)">x</a></span>
                </div>
            </div>
        </div>
        <div ng-repeat-end="li in products track by $index" class="clearfix" ng-if="($index + 1) % 6 == 0"></div>
    </div>
</div>
<script>
    app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster, $uibModal) {

        $scope.oproducts = <?php echo safe_json_encode(array_values($products)); ?>;
        $scope.products = <?php echo safe_json_encode(array_values($products)); ?>;
        $scope.refreshRacks = rn => {
            $scope.products = rn ? $scope.oproducts.filter(r => r.title == rn) : $scope.oproducts;
        }

        $scope.refreshByProductId = rn => {

            $scope.products = rn ? $scope.oproducts.filter(r => r.products.filter(id => id == rn).length) : $scope.oproducts;
        }

        $scope.addCustomer = function(size, parentSelector) {
            $scope.form = null
            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'addCustomer.html',
                controller: 'ModalInstanceCtrl',
                size: size
            }).closed.then(function() {
                $window.location.reload();
            });
        };

        $scope.deleteRackProduct = (rack_id, product_id) => {
            $http.post('rackProductDelete.php', $httpParamSerializerJQLike({
                rack_id,
                product_id
            }), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                if (res.data) {
                    $scope.alert = {
                        type: 'success',
                        message: res.data.message
                    }
                }
                $window.location.reload();
            });
        }


    });
    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike) {
        $scope.form = {
            rack: "",
            product_id: ""
        }

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post('rackCreate.php', $httpParamSerializerJQLike($scope.form), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                $scope.alert = {
                    type: 'success',
                    message: res.data.message
                }
            });
        };



        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    });
</script>

<script type="text/ng-template" id="addCustomer.html">
    <form ng-submit="ok()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Customer</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="form-group">
                <label for="rack">Rack No</label>
                <input id="rack" type="text" ng-model="form.rack" class="form-control" placeholder="Rack No">
            </div>
            <div class="form-group">
                <label for="product_id">Product Code</label>
                <input id="product_id" type="text" ng-model="form.product_id" class="form-control" placeholder="Product Code">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>

<?php echo mainFooter(); ?>