<?php

$stores = new Store();
$productsObj = new Products();
$publisherObj = new Publishers();
$storeTypesArr = $stores->getStoreTypes();


$storeTypes = [];
foreach ($storeTypesArr as $key => $value) {
    $storeTypes[$value['id']] = $value;
}

$ownerStores = $stores->getOwnerStores($userData['id']);
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
$publishersArr = $publisherObj->getPublishers($currentStore['owner_id']);
$publishers = [];
foreach ($publishersArr as $key => $value) {
    $publishers[$value['id']] = $value;
}
?>

<div class="container" ng-controller="productController">
    <h4>My Shops <small>&lt;<?php echo $currentStore['full_name']; ?>&gt;</small></h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Title</th>
                <th>Type</th>
                <th>City</th>
                <th>Location</th>
                <th>Status</th>
                <th colspan="3" style="text-align: center">Sale Related Actions</th>
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
                    <span ng-if="!showPicker[store.id]">{{ store.sale_date }}</span>
                    <div style="position: relative; width: 100px" ng-if="showPicker[store.id]"><input type="text" class="form-control datepicker-single" /></div>
                </td>
                <td>
                    <label uib-tooltip="When you enable this option [SHOP'S MANAGER] can close Today's Sale" tooltip-placement="bottom"><input type="checkbox" ng-model="store.sale_date_show" ng-true-value="'1'" ng-false-value="'0'" ng-change="showClosing(store.id, store.sale_date_show)"> Enable</label>
                </td>
                <td>
                    <a class="btn btn-xs btn-danger" href="javascript:void(0)" ng-click="applyClosing(store.id, store)">Sale Close</a>
                </td>
                <td><a class="btn btn-xs btn-primary" href="<?php echo SITE_URL . "pages/store/update.php?id="; ?>{{store.id}}">Edit Shop</a></td>
            </tr>
        </tbody>
    </table>
    <!-- <h4>Hot Products</h4>
    <table class="table">Products in stores

        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Title</th>
                <th>Number of Sales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Physics 2nd year</td>
                <td>20</td>
            </tr>
        </tbody>
    </table> -->
    <a href="<?php echo SITE_URL . "pages/product/create.php" ?>" class="btn btn-primary btn-xs pull-right" style="margin-left: 12px">Create Product</a> <a href="<?php echo SITE_URL . "pages/product/assign.php" ?>" class="btn btn-primary btn-xs pull-right">Assign Product</a>
    <h4>Products in stores </h4>
    <div class="row">
        <div class="form-group col-sm-5">
            <input class="form-control" ng-change="searchProducts(search, searchShop)" ng-model="search" placeholder="Type here for search..." />
        </div>
        <div class="col-sm-3 form-group">
            <ui-select custom-dropdown ng-model="form.publisher" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a publisher" ng-change="selectPublisher()">
                <ui-select-match placeholder="Enter a publisher...">{{$select.selected.full_name}}</ui-select-match>
                <ui-select-choices repeat="address in publishers track by $index" refresh="refreshPublishers($select.search)" refresh-delay="0">
                    <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                </ui-select-choices>
            </ui-select>

            <input type="hidden" name="publisher_id" value={{form.publisher.id}} />
        </div>
        <div class="form-group col-sm-4">
            <select class="form-control" ng-model="searchShop" ng-change="searchProducts(search, searchShop)">
                <option value="">All Shops</option>
                <option ng-repeat="store in shopData track by $index" ng-value="store.id">{{store.full_name}}</option>
            </select>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Product ID - CODE</th>
                <th>Branch</th>
                <th>Title / Author - Group</th>
                <th>Price</th>
                <th>In</th>
                <th>Out</th>
                <th>In Hand</th>
                <th>Min. Qty</th>
                <th>Placement</th>
                <th width="150"></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="li in list track by $index">
                <td>{{li.product_id}} / {{li.code}}</td>
                <td>{{shopData[li.shopId].full_name}}</td>
                <td><strong>{{li.full_name}}</strong> <br />{{li.author}} - {{li.group}}</td>
                <td>{{li.sale_price}}</td>
                <td>{{li.qty}}</td>
                <td>{{li.stock_out}}</td>
                <td>{{li.qty - li.stock_out}}</td>
                <td>{{li.min_qty}}</td>
                <td>{{li.location}}</td>
                <td>
                    <a class="btn btn-xs btn-primary" href="{{url + 'pages/product/update_item.php?id=' + li.id}}">Modify</a> |
                    <a class="btn btn-xs btn-danger" href="javascript:void(0)" ng-click="deleteStoreItem(li.id)">delete</a>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="display: flex; align-items: center; justify-content: space-between">
        <ul uib-pagination total-items="data.totalRecords" ng-model="currentPage" max-size="maxSize" class="pagination-sm" boundary-links="true" force-ellipses="true" ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option ng-value="10">10</option>
                <option ng-value="25">25</option>
                <option ng-value="50">50</option>
                <option ng-value="100">100</option>
            </select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span>
    </div>


    <!-- <h4>Products All </h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Title</th>
                <th>Publisher</th>
                <th>Group</th>
                <th>Code</th>
                <th>BAR Code</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1;
            foreach ($products as $product) { ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $product['full_name']; ?></td>
                    <td><?php echo !empty($product['publisher_id']) ? $publishers[$product['publisher_id']]['full_name'] : null; ?></td>
                    <td><?php echo $product['group']; ?></td>
                    <td><?php echo $product['code']; ?></td>
                    <td><?php echo $product['barcode'] ? $product['barcode'] : 'NULL'; ?></td>
                    <td><a href="<?php echo SITE_URL . "pages/product/update.php?id=" . $product['id']; ?>">Modify</a></td>
                </tr>
            <?php $count++;
            } ?>
        </tbody>
    </table> -->
    <!-- <h4>Pending Orders</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Customer</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>PCIT</td>
                <td width="120">3500</td>
            </tr>
            <tr>
                <td>2</td>
                <td>PCW</td>
                <td width="120">4500</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th>80000</th>
            </tr>
        </tfoot>
    </table>
    <h4>Pending Bills</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Supplier</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>XYZ</td>
                <td width="120">3500</td>
            </tr>
            <tr>
                <td>2</td>
                <td>XYZ</td>
                <td width="120">4500</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th>80000</th>
            </tr>
        </tfoot>
    </table> -->
</div>
<script type="text/javascript">
    app.controller('productController', function($scope, $timeout, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
        $scope.currentPage = 1;
        $scope.shopData = <?php echo safe_json_encode($storeList); ?>;
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
        $scope.form = {

        }
        $scope.url = '<?php echo SITE_URL ?>';
        $scope.opublishers = <?php echo !empty($publishersArr) ? json_encode($publishersArr) : json_encode([]); ?>;
        $scope.publishers = <?php echo !empty($publishersArr) ? json_encode($publishersArr) : json_encode([]); ?>;
        $scope.refreshPublishers = search => {
            $scope.publishers = $scope.opublishers.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
        }
        $scope.selectPublisher = () => {
            $scope.getProducts(1);
        }
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
                        shopId: $scope.shopId,
                        courceId: $scope.courceId,
                        publisher_id: $scope.form.publisher?.id
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

        $scope.searchProducts = (search, shopId) => {
            $scope.currentPage = 1;
            $scope.search = search;
            $scope.shopId = shopId;
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