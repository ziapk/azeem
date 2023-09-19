<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
echo mainHeader(['page' => 'barcode']);

$all = false;
$products = [];
$productsObj = new Products();
$storeObj = new Store();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$ownerStores = $storeObj->getOwnerStores($ownerId);
$id = $_GET['id'];
if (!empty($id)) {
    $demandObj = new Demands();
    $demandDetail = $demandObj->getDemandDetail($id, $ownerId);
}

if (!empty($_GET['all']) && $_GET['all'] == '1') {
    $shopId = $_GET['shopId'];
    $products = $productsObj->getOwnerProductsPagination($ownerId, ['page' => 1, 'perPage' => 100000, 'status' => 1], $shopId);
}

?>

<div class="container" ng-controller="categoryController">
    <form class="row" action="" method="GET">
        <div class="col-sm-3 form-group">
            <input type="hidden" class="form-control" name="all" value="1">
            <select class="form-control" name="shopId" ng-model="shopId">
                <?php foreach ($ownerStores as $value) { ?>
                    <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-3 form-group">
            <input type="submit" class="btn btn-primary" value="Fetch All Items" />
        </div>
    </form>
    <h4>Products</h4>
    <div class="form-group">
        <input type="text" class="form-control" ng-model="product" placeholder="Search Products" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row.html" class="form-control" ng-model-options="{debounce: 500}" typeahead-show-hint="true" typeahead-min-length="1">
    </div>

    <form action="print.php" method="post" target="_blank">
        <label><input type="checkbox" name="hidePrice" style="margin-right: 10px">Hide Price</label>
        <label><input type="checkbox" name="hideBarcode" style="margin-right: 10px">Hide Code</label>
        <label><input type="checkbox" name="hideCompany" style="margin-right: 10px">Hide Company</label>
        <label><input type="checkbox" name="showRackNo" style="margin-right: 10px">Show Rack No</label>
        <label><input type="checkbox" name="showPageNo" style="margin-right: 10px">Show Counter</label>
        <table class="table">
            <?php if ($userData['role'] == 'owner') { ?>
                <thead>
                    <tr>
                        <th>Select Shop for Demand</th>
                        <th colspan="2">
                            <label for="">Select Store</label>
                            <select id="shop_id" class="form-control">
                                <?php foreach ($ownerStores as $type) { ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </th>
                    </tr>
                </thead>
            <?php } else { ?>
                <input type="hidden" id="shop_id" value="<?php echo $userData['shopId']; ?>">
            <?php } ?>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Print Qty</th>
                    <th width="200"></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in items" id="item-{{li.id}}">
                    <td>{{li.full_name}}
                        <input type="hidden" value="{{li.id}}" name="id[]" />
                        <input type="hidden" value="{{li.price}}" name="price[]" />
                        <input type="hidden" value="{{li.full_name}}" name="full_name[]" />
                    </td>
                    <td><input type="number" ng-model="li.qty" class="input-qty input-control" name="qty[]" /></td>
                    <td>
                        <a class="btn btn-danger btn-xs" href="javascript:void(0)" ng-click="deleteCategory(li.id)">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <input type="submit" class="btn btn-primary" value="Print Tags" />
        <?php if ($userData['role'] == 'owner' || $userData['role'] == 'manager') { ?>
            <input type="button" class="btn btn-danger pull-right" value="Create Demand" ng-click="createDemand()" />
        <?php } ?>

        <script>
            app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout, $rootScope) {
                $scope.list = []; //$scope.data.records;
                console.log('mainList', $rootScope);
                $scope.siteUrl = '<?php echo SITE_URL ?>';
                $scope.demandDetail = <?php echo safe_json_encode($demandDetail); ?>;
                $scope.shopId = '<?php echo !empty($_GET['shopId']) ? $_GET['shopId'] : $userData['shopId']; ?>';
                $scope.currentShopId = '<?php echo $shop['id']; ?>';

                $scope.books = <?php echo safe_json_encode($products); ?>;

                $scope.list = [];
                console.log('$scope.demandDetail', $scope.demandDetail);
                $scope.items = $scope.demandDetail ? $scope.demandDetail.items.map(r => ({
                    ...r,
                    qty: parseInt(r.product_qty)
                })) : $scope.books?.records?.map(r => ({
                    ...r,
                    qty: parseInt(r.qty)
                }))?.filter(r => r.qty) || [];

                $scope.selectProduct = function(p) {
                    let exists = false;
                    $scope.items.map((pro) => {
                        if (pro.id == p.id) {
                            exists = true;
                            pro.qty = pro.qty || 0;
                            pro.qty++;
                        }
                    })
                    $scope.product = ""
                    if (!exists) {
                        $scope.items.push({
                            ...p,
                            qty: 1
                        });
                    }

                    $location.hash('item-' + p.id);

                    // call $anchorScroll()
                    $anchorScroll();
                    $timeout(() => {
                        $('#item-' + p.id).find('.input-qty').focus();
                    }, 100);

                }

                $scope.partialSearch = (name, query) => {
                    const lowerQuery = query.toLowerCase();
                    const lowerName = name.toLowerCase();
                    let queryIndex = 0;
                    for (let i = 0; i < lowerName.length; i++) {
                        if (lowerName[i] === lowerQuery[queryIndex]) {
                            queryIndex++;
                            if (queryIndex === lowerQuery.length) return true;
                        }
                    }
                    return false;
                }

                $scope.searchProduct = function(term) {
                    if ($scope.shopId == $scope.currentShopId) {

                        const filteredArray = window.mainList.records.filter(r => r.id == term || r.code == term || r.barcode == term || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()))
                        const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;

                        return secondfilteredArray.slice(0, 30);
                    } else {
                        return $http.get("<?php echo SITE_URL ?>api/getStores.php", {
                                params: {
                                    term,
                                    shopId: $scope.shopId
                                }
                            })
                            .then(function(response) {
                                // $scope.list = response.data;
                                return response.data
                            });
                    }


                }

                $scope.deleteCategory = function(id) {
                    $scope.items = $scope.items.filter(r => r.id !== id);
                }

                $scope.printTags = function(form) {
                    $http.post('print.php', $httpParamSerializerJQLike($scope.items), {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    }).then(function() {
                        // $scope.getCategories(1);
                    });
                };

                $scope.createDemand = function() {
                    const form = {
                        demand_title: 'DEMAND BY BARCODE',
                        demand_date: moment().format('YYYY-MM-DD'),
                        shop_id: $('#shop_id').val(),
                        items: $scope.items,
                    }
                    $http.post('<?php echo SITE_URL . "pages/demand/createDemand.php" ?>', $httpParamSerializerJQLike(form), {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    }).then(function(res) {
                        alert(res.data.message)
                        // $scope.getCategories(1);
                    });
                };
            });

            app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, form) {
                $scope.form = {
                    full_name: "",
                    cat_type: "",
                    ...form
                }
                $scope.ok = function() {
                    $uibModalInstance.close($scope.form);
                };

                $scope.cancel = function() {
                    $uibModalInstance.dismiss('cancel');
                };
            });
        </script>

        <script type="text/ng-template" id="row.html">
            <a>
                <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
                <span class="pull-right">{{match.model.price}}</span>
            </a>
        </script>

        <?php
        echo mainFooter();
