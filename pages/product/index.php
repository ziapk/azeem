<?php

include_once dirname(__FILE__) . '/../../include/settings.php';

echo mainHeader(['page' => 'product']);
$programObj = new Programs();
$programs = $programObj->getPrograms();
?>
<style>
    .text-bold {
        font-weight: bold;
    }
</style>
<div class="container" ng-controller="productsController">
    <div class="form-group">
        <div class="row">
            <div class="col-sm-4" ng-if="searchBy != 'multi'">
                <input ng-if="searchBy != 'cource'" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" ng-model="search" placeholder="Type here for search..." />
                <select ng-if="searchBy == 'cource'" ng-model="courceId" ng-change="searchProducts(search, courceId, full_name, group, author, board)" class="c-select form-control">
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
                    <option value="publisher">Search By Publisher</option>
                    <option value="cource">Search By Cource</option>
                    <option value="multi">Search By Multiple Colums</option>
                </select>
            </div>
            <div class="col-sm-2" ng-if="searchBy == 'cource' && courceId && list.length">
                <button ng-click="addToCart(list, 'list')" class="btn btn-primary">All move to <img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/010-shopping-bag-white.svg" alt="" /></button>
            </div>
            <div class="col-sm-4 text-right pull-right">
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo SITE_URL; ?>pages/product" class="btn btn-danger active"><i class="fa fa-th" aria-hidden="true"></i></a>
                    <a href="<?php echo SITE_URL; ?>pages/product/products.php" class="btn btn-danger"><i class="fa fa-bars" aria-hidden="true"></i></a>
                    <?php if ($userData['role'] == 'owner') { ?><a href="<?php echo SITE_URL . "pages/product/create.php" ?>" class="btn btn-info">Create Product</a><?php } ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2" ng-if="searchBy == 'multi'">
                <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Title" ng-model="full_name">
            </div>
            <div class="col-sm-2" ng-if="searchBy == 'multi'">
                <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Group" ng-model="group">
            </div>
            <div class="col-sm-2" ng-if="searchBy == 'multi'">
                <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Author" ng-model="author">
            </div>
            <div class="col-sm-2" ng-if="searchBy == 'multi'">
                <input type="text" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" placeholder="Board" ng-model="board">
            </div>
        </div>
    </div>
    <ul class="feature-products">
        <li ng-repeat="li in list" class="feature-product">
            <div class="product-image">
                <div ng-if="!li.image" class="image"></div>
                <img ng-if="li.image" class="image" src={{'<?php echo SITE_URL; ?>uploads/products/'+li.image}} />
                <?php if ($userData['role'] === 'owner') { ?><a href="<?php echo SITE_URL; ?>pages/product/update.php?id={{li.id}}" class="btn-edit" uib-tooltip="Edit"><img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/edit.svg" alt="" /></a><?php } ?>
                <a href="javascript:void(0)" ng-click="addToCart(li)" class="btn-cart" uib-tooltip="Add to cart"><img width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/002-add-to-cart-red.svg" alt="" /></a>
                <?php if ($userData['role'] === 'owner') { ?>
                    <a ng-if="li.priority == '1'" href="javascript:void(0)" ng-click="setPriority(li)" class="btn btn-priority" uib-tooltip="Mark No Priority"><span class="fa fa-lg fa-check-circle text-success"></span></a>
                    <a ng-if="li.priority != '1'" href="javascript:void(0)" ng-click="setPriority(li)" class="btn btn-priority" uib-tooltip="Mark Priority"><span class="fa fa-lg fa-check-circle-o text-mute"></span></a>
                    <a href="javascript:void(0)" ng-click="setInactive(li)" class="btn btn-dup" uib-tooltip="Mark Inactive"><span class="fa fa-remove text-danger"></span></a>
                <?php } ?>
                <a ng-if="li.pin != '1'" href="javascript:void(0)" ng-click="addBookmark(li)" class="btn-bookmark" uib-tooltip="Pin as running"><span class="fa fa-heart-o"></span></a>
                <a ng-if="li.pin == '1'" href="javascript:void(0)" ng-click="removeBookmark(li)" class="btn-bookmark" uib-tooltip="Added in Running list"><span class="fa fa-heart"></span></a>
                <span class="price">{{li.price}} <em>{{li.currency || 'PKR'}}</em> <?php if ($userData['role'] === 'owner') { ?><span style="color: #888; font-size: 0.75em">| {{li.pprice}} <em>{{li.currency || 'PKR'}}</em></span> <?php } ?></span>

                <span class="qty"><strong>{{li.qty < 0 ? 0 : li.qty}}</strong> Available</span>
            </div>
            <div class="product-content">
                <span class="title">{{li.full_name}}</span>
                <span class="group">{{li.group}} - {{li.publisherName}}</span>
                <span class="author" ng-if="li.author"><img width="12" height="12" src="<?php echo SITE_URL; ?>assets/img/svg/pen.svg" alt="" /> {{li.author}}</span>

                <span class="dropdown" style="padding: 0">
                    <a href="#" data-toggle="dropdown">
                        <span><img class="fa" width="14" height="14" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /><code>{{li.code || li.id}}</code></span>
                    </a>
                    <?php if ($userData['role'] === 'owner') { ?>
                        <form ng-submit="submitCode(li)" class="dropdown-menu" style="padding: 20px; width: 300px">
                            <div class="input-group">
                                <input type="text" placeholder="Bar Code" ng-model="li.newBarCode" type="text" class="form-control">
                                <span class="input-group-btn" style="width: 100px">
                                    <input type="text" placeholder="Price" ng-model="li.newPrice" ng-value="li.price" type="text" class="form-control">
                                </span>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </span>
                            </div>
                        </form>
                    <?php } ?>
                </span>
                <div class="pull-right">
                    <span uib-tooltip="Racks" class="text-danger text-bold" style="font-size: 1.3em;"><img width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/bookshelf.png" alt="" /> {{li.rackNumbers.split(',').join(' | ')}}</span>
                </div>


            </div>
        </li>
    </ul>
    <div style="display: flex; align-items: center; justify-content: space-between">
        <ul uib-pagination total-items="data.totalRecords" ng-model="currentPage" max-size="maxSize" class="pagination-sm" boundary-links="true" force-ellipses="true" ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option ng-value="12">12</option>
                <option ng-value="24">24</option>
                <option ng-value="48">48</option>
                <option ng-value="96">96</option>
            </select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span>
    </div>
</div>
<script type="text/javascript">
    app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
        $scope.currentPage = 1;
        $scope.data = {
            perPage: 12
        }; //$scope.data.records;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.courceId = ""; //$scope.data.records;
        $scope.full_name = "";
        $scope.author = "";
        $scope.group = "";
        $scope.board = "";
        const search = $window.location.search;
        const url = new URLSearchParams(search);
        $scope.publisher_id = url.get('publisher_id') || '';
        $scope.status = url.get('status') || '';
        $scope.maxSize = 5;
        $scope.getProducts = (page) => {
            $scope.loading = true;
            $http.get("<?php echo SITE_URL ?>api/getProducts.php", {
                    params: {
                        page: page || 1,
                        perPage: $scope.data.perPage,
                        search: $scope.search,
                        full_name: $scope.full_name,
                        group: $scope.group,
                        author: $scope.author,
                        board: $scope.board,
                        searchBy: $scope.searchBy,
                        courceId: $scope.courceId,
                        publisher_id: $scope.publisher_id,
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

        $scope.submitCode = (form) => {
            $http.post("<?php echo SITE_URL ?>pages/product/update.php?id=" + form.id, $httpParamSerializerJQLike({
                    code: form.newBarCode,
                    price: form.newPrice,
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
            console.log('item', item);
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
    })
</script>
<?php
echo mainFooter();
