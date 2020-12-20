<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getOwnerCategories($userData['created_by']);
echo mainHeader();
?>

<div class="container" ng-controller="productController">
    
    <a href="<?php echo SITE_URL.'pages/supply';?>" class="btn btn-primary btn-xs pull-right">Add Supply</a>
    <a href="javascript:void(0)" style="margin-right: 10px" ng-click="addSuppliers()" class="btn btn-primary btn-xs pull-right">Add Supplier</a>
    <h4>All Suppliers</h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchSupplier()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Contact</th>
                <th>Wallet</th>
                <th width="200"></th>
            </tr>
        </thead>
        <tbody>
                <tr ng-repeat="li in list">
                    <td width="50"><img ng-if="li.image" width="40" class="image" src={{"<?php echo SITE_URL;?>uploads/products/"+li.image}} /></td>
                    <td>{{li.name}}</td>
                    <td>{{li.contact}}</td>
                    <td>{{li.wallet}}</td>
                    <td><a class="btn btn-primary btn-xs" href="<?php echo SITE_URL."pages/suppliers/update.php?id="?>{{li.id}}">Modify</a></td>
                </tr>
        </tbody>
    </table>

    
    
</div>

<script type="text/ng-template" id="addSupplier.html">
    <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Supplier</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.name" class="form-control" placeholder="Supplier's Name">
            </div>
            <div class="form-group">
                <label for="scontact">Contact</label>
                <input id="scontact" type="text" ng-model="form.contact" class="form-control" placeholder="Supplier's Contact">
            </div>
            <div class="form-group">
                <label for="saddress">Address</label>
                <input id="saddress" type="text" ng-model="form.address" class="form-control" placeholder="Supplier's Address">
            </div>
            <div class="form-group">
                <label for="swallet">Balance</label>
                <input id="swallet" type="text" ng-model="form.wallet" class="form-control" placeholder="Supplier's balance">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit" ng-click="ok()">OK</button>
            <button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
        </div>
    </form>
    </script>

<script type="text/ng-template" id="addSupply.html">
    <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Supply Bill</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.name" class="form-control" placeholder="Supplier's Name">
            </div>
            <div class="form-group">
                <label for="scontact">Contact</label>
                <input id="scontact" type="text" ng-model="form.contact" class="form-control" placeholder="Supplier's Contact">
            </div>
            <div class="form-group">
                <label for="saddress">Address</label>
                <input id="saddress" type="text" ng-model="form.address" class="form-control" placeholder="Supplier's Address">
            </div>
            <div class="form-group">
                <label for="swallet">Balance</label>
                <input id="swallet" type="text" ng-model="form.wallet" class="form-control" placeholder="Supplier's balance">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit" ng-click="ok()">OK</button>
            <button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
        </div>
    </form>
</script>

<script>
app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
    $scope.currentPage = 1; 
    $scope.data = {}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.search = ""; //$scope.data.records;
    $scope.siteUrl = '<?php echo SITE_URL ?>';
    $scope.getSuppliers = (page) => {
        $scope.loading = true;
        $http.get($scope.siteUrl+"api/getSuppliers.php", {params: {page: page || 1, search: $scope.search}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
                $scope.list = response.data.records;
            }
        })
    }
    
    $scope.searchSupplier = () => {
        $scope.getSuppliers(1);
    }

    $scope.getSuppliers($scope.currentPage);
    $scope.pageChanged = () => {
        $scope.getSuppliers($scope.currentPage)
    }
    $scope.addToCard = function (id) {
        if($window.localStorage.getItem('shopping')) {
            const shopCart = JSON.parse($window.localStorage.getItem('shopping'));
            let found = false;
            shopCart.map(row => {
                if(row.id == id) {
                    found = true
                    row.qty++;
                }
            });

            if(!found) {
                $window.localStorage.setItem('shopping', JSON.stringify([...shopCart, ...[{id, qty: 1}] ] ))
            } else {
                $window.localStorage.setItem('shopping', JSON.stringify([...shopCart]))
            }
        }
        else {
            $window.localStorage.setItem('shopping', JSON.stringify([{qty: 1, id}]))
        }
    }

    $scope.addSuppliers = function (size, parentSelector) {
    
        $uibModal.open({
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'addSupplier.html',
            controller: 'ModalInstanceCtrl',
            size: size
        }).result.then(function (selectedItem) {
            console.log(1)
            
            /* $http.post($scope.siteUrl+'api/createSupplier.php', $httpParamSerializerJQLike(selectedItem), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
                $scope.getSuppliers(1);
            }); */
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
    
    
    $scope.addSupply = function (size, parentSelector) {
    
        $uibModal.open({
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'addSupply.html',
            controller: 'SupplyModalInstanceCtrl',
            size: size
        }).result.then(function (selectedItem) {
            console.log(2)
            /* $http.post($scope.siteUrl+'api/createSupplier.php', $httpParamSerializerJQLike(selectedItem), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
                $scope.getSuppliers(1);
            }); */
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
});



app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance) {
    $scope.form = {
        name: "",
        contact: "",
        address: "",
        wallet: 0
    }
    $scope.ok = function () {
        $uibModalInstance.close($scope.form);
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});

app.controller('SupplyModalInstanceCtrl', function ($scope, $uibModalInstance) {
    $scope.form = {
        name: "",
        contact: "",
        address: "",
        wallet: 0
    }
    $scope.ok = function () {
        $uibModalInstance.close($scope.form);
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});
</script>
<?php
echo mainFooter();