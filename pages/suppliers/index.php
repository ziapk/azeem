<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getOwnerCategories($userData['created_by']);
echo mainHeader(['page' => 'supplier']);
?>

<div class="container" ng-controller="productController">

    <a href="<?php echo SITE_URL . 'pages/supply'; ?>" class="btn btn-primary btn-xs pull-right">Add Supply</a>
    <a href="javascript:void(0)" style="margin-right: 10px" ng-click="addSuppliers()" class="btn btn-primary btn-xs pull-right">Add Supplier</a>
    <h4>All Suppliers</h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchSupplier()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th>Contact</th>
                <th>Company / Title / Address</th>
                <th>Balance</th>
                <th width="300"></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="li in list">
                <td width="50"><img ng-if="li.image" width="40" class="image" src={{"<?php echo SITE_URL; ?>uploads/products/"+li.image}} /></td>
                <td><strong>{{li.name}}</strong> <br /> {{li.contact}}</td>
                <td><strong>{{li.company}}</strong> - {{li.title}} <br />{{li.address}}</td>
                <td>{{li.closing_balance}}</td>
                <!-- <td>{{li.wallet}}</td> -->
                <td>
                    <a class="btn btn-primary btn-xs" href="<?php echo SITE_URL . "pages/suppliers/update.php?id=" ?>{{li.id}}">Edit</a>
                    <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="../chart-of-accounts/summery.php?t=s&id={{li.account_id}}">Ledger</a><?php } ?>
                    <a class="btn btn-danger btn-xs" href="<?php echo SITE_URL . "pages/suppliers/invoices.php?id=" ?>{{li.id}}">Bills</a>
                    <a class="btn btn-danger btn-xs" href="<?php echo SITE_URL . "pages/suppliers/adjustment.php?id=" ?>{{li.id}}">Payment</a>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="display: flex; align-items: center; justify-content: space-between">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span>
    </div>
</div>

<script type="text/ng-template" id="addSupplier.html">
    <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Supplier</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="form-group">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.name" class="form-control" placeholder="Supplier's Name">
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="scontact">Contact</label>
                        <input id="scontact" type="text" ng-model="form.contact" class="form-control" placeholder="Supplier's Contact">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="stitle">Title</label>
                        <input id="stitle" type="text" ng-model="form.title" class="form-control" placeholder="Supplier's Title">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="scompany">Company</label>
                        <input id="scompany" type="text" ng-model="form.company" class="form-control" placeholder="Supplier's company">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="sopening_balance">Opening Balance</label>
                        <input id="sopening_balance" type="text" ng-model="form.opening_balance" class="form-control" placeholder="Supplier's Opening Balance">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="saddress">Address</label>
                <input id="saddress" type="text" ng-model="form.address" class="form-control" placeholder="Supplier's Address">
            </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>


<script>
    app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        console.log($scope.data)
        $scope.currentPage = 1;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';
        $scope.getSuppliers = (page) => {
            $scope.loading = true;
            $http.get($scope.siteUrl + "api/getSuppliers.php", {
                    params: {
                        page: page || 1,
                        perPage: $scope.data.perPage,
                        search: $scope.search
                    }
                })
                .then(function(response) {
                    $scope.loading = false;
                    if (response.status === 200) {
                        $scope.data = response.data;
                        $scope.list = response.data.records;
                    }
                })
        }

        $scope.perPage = () => {
            $scope.getSuppliers($scope.currentPage);
        }

        $scope.searchSupplier = () => {
            $scope.getSuppliers(1);
        }

        $scope.getSuppliers($scope.currentPage);
        $scope.pageChanged = (page) => {
            $scope.currentPage = page;
            $scope.getSuppliers(page)
        }
        $scope.addToCard = function(id) {
            if ($window.sessionStorage.getItem('shopping')) {
                const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                let found = false;
                shopCart.map(row => {
                    if (row.id == id) {
                        found = true
                        row.qty++;
                    }
                });

                if (!found) {
                    $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart, ...[{
                        id,
                        qty: 1
                    }]]))
                } else {
                    $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart]))
                }
            } else {
                $window.sessionStorage.setItem('shopping', JSON.stringify([{
                    qty: 1,
                    id
                }]))
            }
        }

        $scope.addSuppliers = function(size, parentSelector) {

            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'addSupplier.html',
                controller: 'ModalInstanceCtrl',
                size: size,
                resolve: {
                    siteUrl: function() {
                        return $scope.siteUrl
                    }
                }
            }).closed.then(function(selectedItem) {
                $scope.getSuppliers(1);
            });
        };

    });



    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike, siteUrl) {
        $scope.form = {
            name: "",
            contact: "",
            address: "",
            wallet: 0
        }
        $scope.alert = null;
        $scope.siteUrl = siteUrl;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post($scope.siteUrl + 'api/createSupplier.php', $httpParamSerializerJQLike($scope.form), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                console.log(res)
                if (res.data.success) {
                    $scope.alert = {
                        type: 'success',
                        message: res.data.message
                    }
                } else {
                    $scope.alert = {
                        type: 'danger',
                        message: res.data.message
                    }
                }
                // $uibModalInstance.close($scope.form);
            });
        };


        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    });
</script>

<?php
echo mainFooter();
