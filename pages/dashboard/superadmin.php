<?php

$stores = new Store();
$productsObj = new Products();
$categoryObj = new Publishers();
$storeTypesArr = $stores->getStoreTypes();


$storeTypes = [];
foreach ($storeTypesArr as $key => $value) {
    $storeTypes[$value['id']] = $value;
}

$usersObj = new Users();
$users = $usersObj->getUsers();
$ownerStores = $stores->getStores();
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
    <a href="#" class="btn btn-primary btn sm">Create</a>
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
    <h4>Users </h4>
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
    app.controller('productController', function($scope, $timeout, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
        $scope.currentPage = 1;
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
        $scope.showClosing = (id, enable, sale_date) => {
            if ($window.confirm('Are you sure you want to close to sale for Today')) {
                $http.post('<?php echo SITE_URL; ?>api/enable.php', $httpParamSerializerJQLike({
                    enable,
                    id
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then((response) => {
                    $window.location.reload();
                })
            }
        }
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