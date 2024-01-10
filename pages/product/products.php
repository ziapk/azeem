<?php
if (empty($disableHeader)) {
    include_once dirname(__FILE__) . '/../../include/settings.php';

    echo mainHeader(['page' => 'product']);
}
$programObj = new Programs();
$programs = $programObj->getPrograms();
?>

<div class="container" ng-controller="productsController">
    <div class="row">
        <div class="col-sm-4" ng-if="searchBy != 'multi'">
            <input ng-if="searchBy != 'cource'" class="form-control form-group" ng-change="searchProducts(search, courceId, full_name, group, author, board)" ng-model="search" placeholder="Type here for search..." />
            <select ng-if="searchBy == 'cource'" ng-model="courceId" ng-change="searchProducts(search, courceId, full_name, group, author, board)" class="c-select form-control form-group">
                <option value="">Select a Course</option>
                <?php foreach ($programs as $prog) { ?>
                    <option value="<?php echo $prog['id']; ?>"><?php echo $prog['degree'] . " -> " . $prog['program'] . " -> " . $prog['class']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-2 form-group">
            <select class="form-control" ng-model="searchBy" ng-change="searchProducts(search, courceId, full_name, group, author, board)">
                <option value="">Search By Any</option>
                <option value="group">Search By Group</option>
                <option value="author">Search By Author</option>
                <option value="board">Search By Board</option>
                <option value="category">Search By Category</option>
                <option value="publisher">Search By Publisher</option>
                <option value="subCategory">Search By Sub Category</option>
                <option value="cource">Search By Cource</option>
                <option value="multi">Search By Multiple Colums</option>
            </select>
        </div>
        <div class="col-sm-2 form-group" ng-if="searchBy == 'cource' && courceId && list.length">
            <button ng-click="addToCart(list, 'list')" class="btn btn-primary">All move to <img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/010-shopping-bag-white.svg" alt="" /></button>
        </div>
        <div class="col-sm-4 text-right pull-right form-group">
            <div class="btn-group btn-group-sm">
                <a href="<?php echo SITE_URL; ?>pages/product" class="btn btn-danger"><i class="fa fa-th" aria-hidden="true"></i></a>
                <a href="<?php echo SITE_URL; ?>pages/product/products.php" class="btn btn-danger active"><i class="fa fa-bars" aria-hidden="true"></i></a>
                <?php if ($userData['role'] == 'owner') { ?><a href="<?php echo SITE_URL . "pages/product/create.php" ?>" class="btn btn-info"><span class="fa fa-plus"></span> Product</a><?php } ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2 col-xs-6 form-group" ng-if="searchBy == 'multi'">
            <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Title" ng-model="full_name">
        </div>
        <div class="col-sm-2 col-xs-6 form-group" ng-if="searchBy == 'multi'">
            <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Group" ng-model="group">
        </div>
        <div class="col-sm-2 col-xs-6 form-group" ng-if="searchBy == 'multi'">
            <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Author" ng-model="author">
        </div>
        <div class="col-sm-2 col-xs-6 form-group" ng-if="searchBy == 'multi'">
            <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Board" ng-model="board">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th ng-click="sortBy('title')" ng-class="{active: sortByField === 'title'}">Title / Author - Group <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                    <th>Description/Note</th>
                    <th>SKU/Code</th>
                    <th ng-click="sortBy('price')" ng-class="{active: sortByField === 'price'}">Price <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                    <th ng-click="sortBy('stock')" ng-class="{active: sortByField === 'stock'}">In Stock <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                    <th width="150"></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in list">
                    <td><strong>{{li.full_name}}</strong> <br />{{li.author}} - {{li.group}} - {{li.publisherName}}</td>
                    <td><span uib-tooltip="Racks" class="text-danger text-bold" style="font-size: 1.3em;"><img width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/bookshelf.png" alt="" /> {{li.rackNumbers.split(',').join(' | ')}}</span> <br />{{li.description}} <br /> {{li.note}}
                    </td>
                    <td>
                        <span class="dropdown" style="padding: 0">
                            <a href="#" data-toggle="dropdown">
                                <span><img class="fa" width="14" height="14" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /><code>{{li.code || li.id}}</code></span>
                            </a>
                            <?php if ($userData['role'] === 'owner') { ?>
                                <form ng-submit="submitCode(li)" class="dropdown-menu" style="padding: 12px; width: 300px">
                                    <div>
                                        <input type="text" placeholder="Bar Code" ng-model="li.newBarCode" type="text" class="form-control">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <input type="text" placeholder="Price" ng-model="li.newPrice" ng-value="li.price" type="text" class="form-control">
                                        </span>
                                        <span class="input-group-btn">
                                            <input type="text" placeholder="Whole Sale Price" ng-model="li.wh_price" ng-value="li.wh_price" type="text" class="form-control">
                                        </span>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                                </form>
                            <?php } ?>
                        </span>
                    </td>
                    <td>{{li.price}}</td>
                    <td>{{li.in_hand < 0 ? 0 : li.in_hand}}</td>
                    <td width="230">
                        <a style="padding-left: 0; padding-right: 0" ng-if="li.pin != 1" href="javascript:void(0)" ng-click="addBookmark(li)" class="btn btn-bookmark" uib-tooltip="Pin as running items"><span class="fa fa-heart-o"></span></a>
                        <a style="padding-left: 0; padding-right: 0" ng-if="li.pin == 1" href="javascript:void(0)" ng-click="removeBookmark(li)" class="btn btn-bookmark" uib-tooltip="Remove from Running items list"><span class="fa fa-heart"></span></a>
                        <?php if ($userData['role'] === 'owner') { ?>
                            <a style="padding-left: 0; padding-right: 5px" ng-if="li.dup == 0" href="javascript:void(0)" ng-click="addDuplicate(li)" class="btn btn-bookmark" uib-tooltip="Mark as duplicate"><span class="fa fa-copy text-mute"></span></a>
                            <a style="padding-left: 0; padding-right: 5px" ng-if="li.dup == 1" href="javascript:void(0)" ng-click="removeDuplicate(li)" class="btn btn-bookmark" uib-tooltip="Remove from duplicate"><span class="fa fa-copy text-danger"></span></a>
                            <a ng-if="li.priority == '1'" href="javascript:void(0)" ng-click="setPriority(li)" class="btn btn-priority" uib-tooltip="Mark No Priority"><span class="fa fa-lg fa-check-circle text-success"></span></a>
                            <a ng-if="li.priority != '1'" href="javascript:void(0)" ng-click="setPriority(li)" class="btn btn-priority" uib-tooltip="Mark Priority"><span class="fa fa-lg fa-check-circle-o text-mute"></span></a>
                            <a href="javascript:void(0)" ng-click="setInactive(li)" class="btn btn-dup" uib-tooltip="Mark Inactive"><span class="fa fa-remove text-danger"></span></a>
                        <?php } ?>
                        <a uib-tooltip="Add to Cart" ng-click="addToCart(li)" class="btn btn-xs"><img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/002-add-to-cart.svg" alt="" /></a>
                        <?php if ($userData['role'] === 'owner') { ?>
                            <a class="btn btn-primary btn-xs" href="<?php echo SITE_URL . "pages/product/update.php?id=" ?>{{li.id}}"><span class="fa fa-edit"></span></a> <a class="btn btn-danger btn-xs" href="<?php echo SITE_URL . "pages/product/create.php?id=" ?>{{li.id}}"><span class="fa fa-copy"></span></a>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-custom">
        <ul uib-pagination total-items="data.totalRecords" ng-model="currentPage" max-size="maxSize" class="pagination-sm" boundary-links="true" force-ellipses="true" ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option ng-value="10">10</option>
                <option ng-value="25">25</option>
                <option ng-value="50">50</option>
                <option ng-value="100">100</option>
            </select></span> <span>Total Records <strong>{{data.totalRecords}}</strong></span>
    </div>

</div>
<script>
    app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
        $scope.currentPage = 1;
        $scope.data = {
            perPage: 10
        };
        $scope.list = [];
        $scope.search = "";
        $scope.courceId = "";
        $scope.sortByField = "";
        $scope.sortByOrder = "";
        const search = $window.location.search;
        const url = new URLSearchParams(search);
        $scope.publisher_id = url.get('publisher_id') || '';
        $scope.product_type = url.get('product_type') || '';
        $scope.status = url.get('status') || '';
        $scope.maxSize = 5;

        $scope.sortBy = field => {
            if ($scope.sortByField == field) {
                $scope.sortByField = field;
                $scope.sortByOrder = $scope.sortByOrder == 'desc' ? 'asc' : 'desc';
            } else {
                $scope.sortByField = field;
                $scope.sortByOrder = 'asc';
            }
            $scope.getProducts(1);
        };
        $scope.getProducts = (page) => {
            $scope.loading = true;
            $http.get("<?php echo SITE_URL ?>api/getProducts.php", {
                    params: {
                        page: page || 1,
                        sortByField: $scope.sortByField,
                        sortByOrder: $scope.sortByOrder,
                        perPage: $scope.data.perPage,
                        search: $scope.search,
                        searchBy: $scope.searchBy,
                        courceId: $scope.courceId,
                        publisher_id: $scope.publisher_id,
                        product_type: $scope.product_type,
                        status: $scope.status
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

        $scope.searchProducts = (search, courceId) => {
            $scope.currentPage = 1;
            $scope.search = search;
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
        $scope.addToCart = function(item, type) {
            if ($window.sessionStorage.getItem('shopping')) {
                const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                let found = false;
                shopCart.map(row => {
                    if (type && type == 'list') {
                        item.map(l => {
                            if (row.id == l.id) {
                                found = true
                                row.qty++;
                            }
                        })
                    } else {
                        if (row.id == item.id) {
                            found = true
                            row.qty++;
                        }
                    }
                });

                if (!found) {
                    $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart, ...(type && type == 'list' ? item.map(r => ({
                        ...r,
                        qty: 1
                    })) : [{
                        id: item.id,
                        qty: 1
                    }])]));
                } else {
                    $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart]))
                }
            } else {
                $window.sessionStorage.setItem('shopping', JSON.stringify(type && type == 'list' ? item.map(r => ({
                    ...r,
                    qty: 1
                })) : [{
                    id: item.id,
                    qty: 1
                }]))
            }
            toaster.success({
                body: 'Book Added to Cart successfully!'
            });
        }

        $scope.setPriority = function(item) {
            console.log(parseInt(item.priority));
            $http.get("<?php echo SITE_URL ?>api/setPriority.php", {
                    params: {
                        id: item.id,
                        action: parseInt(item.priority || 0)
                    }
                })
                .then(function(response) {
                    if (response.status === 200) {
                        toaster.success({
                            body: 'Successfully Updated!'
                        });
                        $scope.getProducts();
                    }
                })
        }

        $scope.addBookmark = function(item) {
            $http.get("<?php echo SITE_URL ?>api/setBookmark.php", {
                    params: {
                        id: item.id
                    }
                })
                .then(function(response) {
                    if (response.status === 200) {
                        toaster.success({
                            body: 'Successfully Pinned!'
                        });
                        $scope.getProducts();
                    }
                })
        }
        $scope.addDuplicate = function(item) {
            $http.get("<?php echo SITE_URL ?>api/setDuplicate.php", {
                    params: {
                        id: item.id
                    }
                })
                .then(function(response) {
                    if (response.status === 200) {
                        toaster.success({
                            body: 'Successfully Marked!'
                        });
                        $scope.getProducts();
                    }
                })
        }
        $scope.removeDuplicate = function(item) {
            $http.get("<?php echo SITE_URL ?>api/setDuplicate.php", {
                    params: {
                        id: item.id,
                        action: 2
                    }
                })
                .then(function(response) {
                    if (response.status === 200) {
                        toaster.success({
                            body: 'Successfully Unmarked!'
                        });
                        $scope.getProducts();
                    }
                })
        }
        $scope.removeBookmark = function(item) {
            $http.get("<?php echo SITE_URL ?>api/setBookmark.php", {
                    params: {
                        id: item.id,
                        action: 2
                    }
                })
                .then(function(response) {
                    if (response.status === 200) {
                        toaster.success({
                            body: 'Successfully Unpinned!'
                        });
                        $scope.getProducts();
                    }
                })
        }

        $scope.submitCode = (form) => {
            $http.post("<?php echo SITE_URL ?>pages/product/update.php?id=" + form.id, $httpParamSerializerJQLike({
                    code: form.newBarCode,
                    price: form.newPrice,
                    wh_price: form.wh_price,
                    createCode: true,
                    json_response: true,
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    if (response.data.status === 200) {
                        toaster.success({
                            body: response.data.message
                        });
                        form.newBarCode = '';
                    } else {
                        toaster.success({
                            body: response.data.message
                        });
                        form.newBarCode = '';
                    }
                })
        }

        $scope.setInactive = function(item) {
            if ($window.confirm('Are you sure?')) {
                $http.get("<?php echo SITE_URL ?>api/setInactive.php", {
                        params: {
                            id: item.id,
                            action: item.is_active == 1 ? 0 : 1
                        }
                    })
                    .then(function(response) {
                        if (response.status === 200) {
                            toaster.success({
                                body: 'Marked In-Active Successfully!'
                            });
                            $scope.getProducts();
                        }
                    })
            }
        }

    })
</script>

<?php
echo mainFooter();
