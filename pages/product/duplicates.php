<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';
    
    echo mainHeader(['page'=> 'dup']);
    $programObj = new Programs();
    $programs = $programObj->getPrograms();
?>
<div class="container" ng-controller="productsController">
    <div class="form-group">
        <div class="row">
            <div class="col-sm-4" ng-if="searchBy != 'multi'">
                <input ng-if="searchBy != 'cource'" class="form-control" ng-change="searchProducts(search, courceId, full_name, group, author, board)" ng-model="search" placeholder="Type here for search..." />
                <select ng-if="searchBy == 'cource'" ng-model="courceId" ng-change="searchProducts(search, courceId, full_name, group, author, board)" class="c-select form-control">
                    <option value="">Select a Course</option>
                    <?php foreach($programs as $prog) {?>
                    <option value="<?php echo $prog['id']; ?>"><?php echo $prog['degree']." -> ". $prog['program'] ." -> ".$prog['class'];?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-6 text-right pull-right">
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo SITE_URL;?>pages/product" class="btn btn-danger active"><i class="fa fa-th" aria-hidden="true"></i></a>
                    <a href="<?php echo SITE_URL;?>pages/product/products.php" class="btn btn-danger"><i class="fa fa-bars" aria-hidden="true"></i></a>
                    <?php if($userData['role'] == 'owner') {?><a href="<?php echo SITE_URL."pages/product/create.php" ?>" class="btn btn-info">Create Product</a><?php } ?>
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
                <img ng-if="li.image" class="image" src={{'<?php echo SITE_URL;?>uploads/products/'+li.image}} />
                <a href="update.php?id={{li.id}}" class="btn-edit" uib-tooltip="Edit"><img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/edit.svg" alt="" /></a>
                <a href="javascript:void(0)" ng-click="addToCard(li)" class="btn-cart" uib-tooltip="Add to cart"><img width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/002-add-to-cart-red.svg" alt="" /></a>
                <a ng-if="li.dup == 0" href="javascript:void(0)" ng-click="addDuplicate(li)" class="btn btn-dup" uib-tooltip="Mark as duplicate"><span class="fa fa-copy text-mute"></span></a>
                <a ng-if="li.dup == 1" href="javascript:void(0)" ng-click="removeDuplicate(li)" class="btn btn-dup" uib-tooltip="Remove from duplicate"><span class="fa fa-copy text-danger"></span></a>
                <a ng-if="li.pin != '1'" href="javascript:void(0)" ng-click="addBookmark(li)" class="btn-bookmark" uib-tooltip="Pin as running"><span class="fa fa-heart-o"></span></a>
                <a ng-if="li.pin == '1'" href="javascript:void(0)" ng-click="removeBookmark(li)" class="btn-bookmark" uib-tooltip="Added in Running list"><span class="fa fa-heart"></span></a>
                <span class="price">{{li.price}}<em>{{li.currency || 'PKR'}}</em></span>
                <span class="qty"><strong>{{li.qty < 0 ? 0 : li.qty}}</strong> Available</span>
            </div>
            <div class="product-content">
                <span class="title">{{li.full_name}}</span>
                <span class="group">{{li.group}}</span>
                <span class="author" ng-if="li.author"><img width="12" height="12" src="<?php echo SITE_URL; ?>assets/img/svg/pen.svg" alt="" /> {{li.author}}</span>
                <span><img class="fa" width="14" height="14" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /><code>{{li.code || li.id}}</code></span>
            </div>
        </li>
    </ul>
    <div style="display: flex; align-items: center; justify-content: space-between"><ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage"><option ng-value="12">12</option><option ng-value="24">24</option><option ng-value="48">48</option><option ng-value="96">96</option></select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span></div>
</div>
<script type="text/javascript">
app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
    $scope.currentPage = 1; 
    $scope.data = {perPage: 12}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.search = ""; //$scope.data.records;
    $scope.courceId = ""; //$scope.data.records;
    $scope.full_name = "";
    $scope.author = "";
    $scope.group = "";
    $scope.board = "";
    $scope.getProducts = (page) => {
        $scope.loading = true;
        $http.get("<?php echo SITE_URL?>api/getProducts.php", {params: {page: page || 1, perPage: $scope.data.perPage, search: $scope.search, full_name: $scope.full_name, group: $scope.group, author: $scope.author, board: $scope.board, searchBy: $scope.searchBy, courceId: $scope.courceId, dup: 1}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
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
    $scope.addToCard = function (item) {
        if($window.localStorage.getItem('shopping')) {
            const shopCart = JSON.parse($window.localStorage.getItem('shopping'));
            let found = false;
            shopCart.map(row => {
                if(row.id == item.id) {
                    found = true
                    row.qty++;
                    toaster.success({body: 'Book Added to Cart successfully!'});
                }
            });

            if(!found) {
                $window.localStorage.setItem('shopping', JSON.stringify([...shopCart, ...[{id: item.id, qty: 1}] ] ))
            } else {
                $window.localStorage.setItem('shopping', JSON.stringify([...shopCart]))
            }
        }
        else {
            $window.localStorage.setItem('shopping', JSON.stringify([{qty: 1, id: item.id}]))
        }
    }

    $scope.addDuplicate = function (item) {
        $http.get("<?php echo SITE_URL?>api/setDuplicate.php", {params: {id: item.id}})
        .then(function(response) {
            if(response.status === 200) {
                toaster.success({body: 'Successfully Marked!'});
                $scope.getProducts();
            }
        })
    }
    $scope.removeDuplicate = function (item) {
        $http.get("<?php echo SITE_URL?>api/setDuplicate.php", {params: {id: item.id, action: 2}})
        .then(function(response) {
            if(response.status === 200) {
                toaster.success({body: 'Successfully Unmarked!'});
                $scope.getProducts();
            }
        })
    }

    $scope.addBookmark = function (item) {
        $http.get("<?php echo SITE_URL?>api/setBookmark.php", {params: {id: item.id}})
        .then(function(response) {
            if(response.status === 200) {
                toaster.success({body: 'Successfully Pinned!'});
                $scope.getProducts();
            }
        })
    }
    $scope.removeBookmark = function (item) {
        $http.get("<?php echo SITE_URL?>api/setBookmark.php", {params: {id: item.id, action: 2}})
        .then(function(response) {
            if(response.status === 200) {
                toaster.success({body: 'Successfully Unpinned!'});
                $scope.getProducts();
            }
        })
    }
})
</script>
<?php
echo mainFooter();  