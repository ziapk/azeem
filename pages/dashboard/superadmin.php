<?php

$stores = new Store();
$clientsObj = new Clients();
$clients = $clientsObj->getClients();
$ownerStores = $stores->getStores();
$storeTypesArr = $stores->getStoreTypes();

$productsObj = new Products();
$categoryObj = new Publishers();


$storeTypes = [];
foreach ($storeTypesArr as $key => $value) {
    $storeTypes[$value['id']] = $value;
}

$usersObj = new Users();
$users = $usersObj->getUsers();
$ownerStoreProducts = $productsObj->getStoreProducts($userData['id']);

$currentStore = [];
$storeList = [];

foreach ($ownerStores as $store) {
    $storeList[$store['id']] = $store;
    if ($userData['shopId'] == $store['id']) {
        $currentStore = $store;
    }
}

$products = $productsObj->getOwnerProducts($currentStore['owner_id']);

$publishersArr = $categoryObj->getPublishers($currentStore['owner_id']);
$publishers = [];
foreach ($publishersArr as $key => $value) {
    $publishers[$value['id']] = $value;
}

?>




<div class="container" ng-controller="productController">
    <a href="#" class="btn btn-primary btn sm" ng-click="shopClosing()">Create</a>
    <h4>Clients </h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Client</th>
                <th>Address</th>
                <th>Contract Start</th>
                <th>Expiry</th>
                <th>Register_Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="store in clients track by $index">
                <td>{{ $index + 1 }}</td>
                <td>{{ store.product_title }}</td>
                <td>{{ store.address }}</td>
                <td>{{ store.start_date }}</td>
                <td>{{ store.end_date }}</td>
                <td>
                    {{ store.datetime }}
                </td>
                <td><a class="btn btn-xs btn-primary" href="<?php echo SITE_URL . "pages/configration/?id="; ?>{{store.id}}">Edit Shop</a></td>
            </tr>
        </tbody>
    </table>
    <h4>Locations </h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Title</th>
                <th>Type</th>
                <th>City</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="store in shopData track by $index">
                <td>{{ $index + 1 }}</td>
                <td>{{ store.full_name }}</td>
                <td>{{ store.storeType }}</td>
                <td>{{ store.city }}</td>
                <td>{{ store.location }}</td>
                <td>{{ store.status }}</td>
                <td>
                    {{ store.sale_date }}
                </td>
                <td><a class="btn btn-xs btn-primary" href="<?php echo SITE_URL . "pages/store/update.php?id="; ?>{{store.id}}">Edit Shop</a></td>
            </tr>
        </tbody>
    </table>
    <h4>Users <button type="button" ng-click="addUser()" class="btn btn-xs btn-primary">Add Users</button> </h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Title</th>
                <th>Email</th>
                <th>City</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="store in users track by $index">
                <td>{{ $index + 1 }}</td>
                <td>{{ store.full_name }}</td>
                <td>{{ store.email }}</td>
                <td>{{ store.city }}</td>
                <td>{{ store.shopId }}</td>
                <td>{{ store.status }}</td>
                <td>
                    {{ store.sale_date }}
                </td>
                <td><a class="btn btn-xs btn-primary" href="<?php echo SITE_URL . "pages/profile/edit.php?id="; ?>{{store.id}}">Edit</a></td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    app.controller('productController', function($scope, $timeout, $http, $httpParamSerializerJQLike, $filter, $window, toaster, $uibModal) {
        $scope.currentPage = 1;
        $scope.clients = <?php echo safe_json_encode($clients); ?>;
        $scope.shopData = <?php echo safe_json_encode($storeList); ?>;
        $scope.users = <?php echo safe_json_encode($users); ?>;
        $scope.data = {
            perPage: 12
        }; //$scope.data.records;
        $scope.list = []; //$scope.data.records;
        $scope.searchBy = "";
        $scope.search = ""; //$scope.data.records;
        $scope.courceId = ""; //$scope.data.records;
        $scope.full_name = "";
        $scope.author = "";
        $scope.group = "";
        $scope.board = "";
        $scope.maxSize = 5;
        $scope.checkbox = {}
        $scope.showPicker = {};
        $scope.url = '<?php echo SITE_URL ?>';
        $scope.getProducts = (page) => {
            $scope.loading = true;
            $http.get("<?php echo SITE_URL ?>api/getStoreProducts.php", {
                    params: {
                        page: page || 1,
                        perPage: $scope.data.perPage,
                        search: $scope.search,
                        full_name: $scope.full_name,
                        group: $scope.group,
                        author: $scope.author,
                        board: $scope.board,
                        searchBy: $scope.searchBy,
                        courceId: $scope.courceId
                    }
                })
                .then(function(response) {
                    $scope.loading = false;
                    if (response.status === 200) {
                        $scope.data = response.data;
                        $scope.data.perPage = parseInt(response.data.perPage);
                        $scope.data.totalRecords = parseInt(response.data.totalRecords);
                        $scope.list = response.data.records;
                        $scope.currentPage = response.data.page;
                    }
                })
        }

        $scope.searchProducts = (search, courceId, full_name, group, author, board) => {
            $scope.currentPage = 1;
            $scope.search = search;
            $scope.full_name = full_name;
            $scope.group = group;
            $scope.author = author;
            $scope.board = board;
            $scope.courceId = courceId;
            $scope.getProducts(1);
        }

        $scope.perPage = () => {
            $scope.getProducts($scope.currentPage);
        }

        $scope.getProducts(1);
        $scope.pageChanged = (page) => {
            $scope.getProducts(page)
        }
        $scope.deleteStoreItem = (id) => {
            if ($window.confirm('Are you sure you want to delete this?')) {
                $http.get("<?php echo SITE_URL ?>pages/product/delete_item.php", {
                        params: {
                            id
                        }
                    })
                    .then(function(response) {
                        console.log(response);
                    }).catch(function(err) {
                        console.log(err);
                    })
            }
        }

        $scope.applyClosing = (id, store) => {
            $scope.showPicker = {};
            $scope.showPicker[id] = true;
            $timeout(() => {
                const d = $('.datepicker-single').daterangepicker({
                    autoApply: true,
                    minDate: moment().subtract(1, 'week').format('YYYY-MM-DD'),
                    maxDate: moment().add(1, 'week').format('YYYY-MM-DD'),
                    singleDatePicker: true,
                    locale: {
                        format: 'YYYY-MM-DD'
                    },
                }, function(date) {
                    if ($window.confirm('Are you sure you want to close to sale for Today')) {
                        $http.post('<?php echo SITE_URL; ?>api/closing.php', $httpParamSerializerJQLike({
                            id,
                            sale_date: moment(date).format('YYYY-MM-DD')
                        }), {
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        }).then((response) => {
                            store.sale_date = moment(date).format('YYYY-MM-DD');
                            alert('Date Updated!');
                            $scope.showPicker = {};
                        })
                    }
                    $scope.$apply();
                }).val(store.sale_date);
            }, 100)

        }
        $scope.shopClosing = function(item, closingReport) {
            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'shopClosing.html',
                controller: 'ModalInstanceCtrl',
                resolve: {
                    parentData: {
                        users: $scope.users
                    }
                }
            }).result.then(function(response) {
                $http.post('<?php echo SITE_URL; ?>api/createStore.php', $httpParamSerializerJQLike(response), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function(r) {
                    console.log(r);
                    // $window.location.reload();
                });
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
        $scope.addUser = function(item, closingReport) {
            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'newUser.html',
                controller: 'newUserModalInstanceCtrl',
                resolve: {
                    parentData: {
                        users: $scope.users,
                        shops: $scope.shopData
                    }
                }
            }).result.then(function(response) {
                $http.post('<?php echo SITE_URL; ?>api/createUser.php', $httpParamSerializerJQLike(response), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function(r) {
                    console.log(r);
                    // $window.location.reload();
                });
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };

    });
    app.controller('ModalInstanceCtrl', function($scope, $http, $window, $uibModalInstance, $httpParamSerializerJQLike, parentData) {
        $scope.form = {
            full_name: "",
            store_type: "3",
            status: 1,
            location: "",
            city: "Islamabad",
            company_email: "",
            company_ledger_inbox: "",
            postalCode: "44000",
            phoneNumber1: "0000-0000000",
            phoneNumber2: "0000-0000000",
            phoneNumber3: "0000-0000000",
            image: "",
            owner_id: ""
        };
        $scope.shopUsers = [{
            full_name: '',
            email: '',
            password: '',
            role: '',
        }];
        $scope.newUser = () => {
            $scope.shopUsers = $scope.shopUsers.concat({
                full_name: '',
                email: '',
                password: '',
                role: '',
            });
        }
        $scope.users = parentData.users;
        $scope.ok = function() {
            $uibModalInstance.close({
                ...$scope.form,
                shopUsers: $scope.shopUsers
            });
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    })
    app.controller('newUserModalInstanceCtrl', function($scope, $http, $window, $uibModalInstance, $httpParamSerializerJQLike, parentData) {

        $scope.form = {
            full_name: "",
            city: "",
            cnic: "",
            password: "",
            phoneNumber1: "",
            phoneNumber2: "",
            phoneNumber3: "",
            photo: "avatar1.jpg",
            shopId: "",
            doj: moment().format('YYYY-MM-DD'),
            role: "",
            owner_id: ""
        };
        $scope.users = parentData.users;
        $scope.shops = parentData.shops;
        $scope.ok = function() {
            $uibModalInstance.close({
                ...$scope.form
            });
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    })
</script>
<!-- 
<script>
app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
    $scope.currentPage = 1; 
    $scope.data = {}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.url = "<?php echo SITE_URL; ?>"; //$scope.data.records;
    $scope.deleteStoreItem = (id) => {
        if($window.confirm('Are you sure you want to delete this?')) {
            $http.get("<?php echo SITE_URL ?>pages/product/delete_item.php", {params: { id }})
            .then(function(response) {
                console.log(response);
            }).catch(function(err) {
                console.log(err);
            })
        }
    }
})
</script> -->


<script type="text/ng-template" id="shopClosing.html">
    <form ng-submit="ok()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">New Store</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <input type="text" ng-model="form.full_name" class="form-control" placeholder="Store Name">
                </div>
                <div class="col-sm-6 form-group">
                    <select ng-model="form.store_type" class="form-control">
                        <?php foreach ($storeTypesArr as $type) { ?>
                            <option value="<?php echo $type['id']; ?>"><?php echo $type['full_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.invoice_prefix" type="text" class="form-control" placeholder="Invoice Prefix">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.location" type="text" class="form-control" placeholder="Location">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.city" type="text" class="form-control" placeholder="City">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.company_email" type="text" class="form-control" placeholder="company_email">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.company_ledger_inbox" type="text" class="form-control" placeholder="company_ledger_inbox">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.postalCode" type="text" class="form-control" placeholder="Postal code">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.phoneNumber1" type="text" class="form-control" placeholder="Cell 1">
                </div>
                <div class="col-sm-4 form-group">
                    <input ng-model="form.phoneNumber2" type="text" class="form-control" placeholder="Cell 2">
                </div>
                
                <div class="col-sm-4 form-group">
                    <select ng-model="form.status" class="form-control">
                        <?php foreach ($statusArr as $key => $type) { ?>
                            <option value="<?php echo $key ?>"><?php echo $type; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-12 form-group">
                    <label>Sales Terms and Condition for Short Bill</label>
                    <textarea rows="4" ng-model="form.sale_terms" maxlength="255" type="text" class="form-control" placeholder="Sales Terms and Conditions (Max: 255 characters)"></textarea>
                </div>
                <div class="col-sm-12 form-group">
                    <label>Sales Terms and Condition for Large Bill</label>
                    <textarea rows="4" ng-model="form.sale_terms_lg" maxlength="255" type="text" class="form-control" placeholder="Sales Terms and Conditions (Max: 255 characters)"></textarea>
                </div>
                <div class="col-sm-4 form-group">
                    <label>Select Owner</label>
                    <select class="form-control c-select" ng-model="form.owner_id">
                        <option ng-repeat="acc in users track by $index" ng-value="acc.id">
                            {{acc.full_name}}
                        </option>
                    </select>
                </div>
            </div>

            <div class="row" ng-repeat="li in shopUsers track by $index">

                <div class="col-sm-12">
                    <h4>User.#{{$index+1}}</h4>
                        </div>
                <div class="col-sm-6 form-group">
                <label class="control-label">Full Name</label>
                    <input type="text" ng-model="li.full_name" class="form-control" class="Full name">
                </div>
                <div class="col-sm-6 form-group">
                    <label class="control-label">Role</label>
                    <select ng-model="li.role" class="c-select form-control">
                        <option value="owner">Owner Account</option>
                        <option value="manager">Manager Account</option>
                        <option value="shopkeeper">Counter Account</option>
                    </select>
                </div>
                <div class="col-sm-6 form-group">
                    <label class="control-label">Login ID</label>
                    <input type="text" ng-model="li.email" class="form-control" class="Login ID">
                </div>
                <div class="col-sm-6 form-group">
                    <label class="control-label">Password</label>
                    <input type="text" ng-model="li.password" class="form-control" class="Password">
                </div>
            </div>

            
        </div>
        <div class="modal-footer">
            <button class="btn btn-info" type="button" ng-click="newUser()">Add another member</button>
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>
<script type="text/ng-template" id="newUser.html">
    <form ng-submit="ok()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">New User</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="hidden" name="photo" ng-model="form.photo">
                        <label for="full_name" class="control-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" ng-model="form.full_name" placeholder="Full Name">
                    </div>
                    <div class="form-group">
                        <label for="email" class="control-label">Username</label>
                        <input type="text" class="form-control" ng-model="form.email" />
                    </div>
                    <div class="form-group">
                        <label for="city" class="control-label">City</label>
                        <input type="text" class="form-control" id="city" placeholder="City" ng-model="form.city">
                    </div>
                    <div class="form-group">
                        <label for="cnic" class="control-label">CNIC</label>
                        <input type="text" class="form-control" id="cnic" placeholder="CNIC" ng-model="form.cnic">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber3" class="control-label">EMG. Number</label>
                        <input type="text" class="form-control" id="phoneNumber3" placeholder="Phone Number" ng-model="form.phoneNumber3">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Role</label>
                        <select ng-model="form.role" class="c-select form-control">
                            <option value="owner">Owner Account</option>
                            <option value="manager">Manager Account</option>
                            <option value="shopkeeper">Counter Account</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Shop</label>
                        <select ng-model="form.shopId" class="c-select form-control">
                            <?php foreach ($ownerStores as $store) { ?>
                                <option value="<?php echo $store['id']; ?>"><?php echo $store['full_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber1" class="control-label">Mobile Number</label>
                        <input type="text" class="form-control" id="phoneNumber1" placeholder="Phone Number" ng-model="form.phoneNumber1">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber2" class="control-label">Contact Number</label>
                        <input type="text" class="form-control" id="phoneNumber2" placeholder="Phone Number" ng-model="form.phoneNumber2">
                    </div>
                </div>
                <div class="col-sm-6 form-group">
                    <label>Select Owner</label>
                    <select class="form-control c-select" ng-model="form.owner_id">
                        <option ng-repeat="acc in users track by $index" ng-value="acc.id">
                            {{acc.full_name}}
                        </option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <input ng-model="form.password" type="password" class="form-control" placeholder="Password">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>