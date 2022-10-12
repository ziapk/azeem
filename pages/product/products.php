
<?php 
    if(empty($disableHeader)) {
        include_once dirname(__FILE__).'/../../include/settings.php';
        
        echo mainHeader(['page'=> 'product']);
    }
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
            <div class="col-sm-2 form-group">
                <select class="form-control" ng-model="searchBy" ng-change="searchProducts(search, courceId, full_name, group, author, board)">
                    <option value="">Search By Any</option>
                    <option value="group">Search By Group</option>
                    <option value="author">Search By Author</option>
                    <option value="board">Search By Board</option>
                    <option value="category">Search By Category</option>
                    <option value="subCategory">Search By Sub Category</option>
                    <option value="cource">Search By Cource</option>
                    <option value="multi">Search By Multiple Colums</option>
                </select>
            </div>
            <div class="col-sm-2" ng-if="searchBy == 'cource' && courceId && list.length">
                <button ng-click="addToCart(list, 'list')"  class="btn btn-primary">All move to <img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/010-shopping-bag-white.svg" alt="" /></button>
            </div>
            <div class="col-sm-4 text-right pull-right">
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo SITE_URL;?>pages/product" class="btn btn-danger"><i class="fa fa-th" aria-hidden="true"></i></a>
                    <a href="<?php echo SITE_URL;?>pages/product/products.php" class="btn btn-danger active"><i class="fa fa-bars" aria-hidden="true"></i></a>
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

    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th ng-click="sortBy('title')" ng-class="{active: sortByField === 'title'}">Title <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                <th ng-click="sortBy('group')" ng-class="{active: sortByField === 'group'}">Group  <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                <th ng-click="sortBy('author')" ng-class="{active: sortByField === 'author'}">Author <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                <th ng-click="sortBy('price')" ng-class="{active: sortByField === 'price'}">Price <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                <th ng-click="sortBy('stock')" ng-class="{active: sortByField === 'stock'}">In Stock <em class="fa sort-icon fa-sort-amount-asc" ng-if="sortByOrder === 'asc'"></em> <em class="fa sort-icon fa-sort-amount-desc" ng-if="sortByOrder === 'desc'"></em></th>
                <th width="150"></th>
            </tr>
        </thead>
        <tbody>
                <tr ng-repeat="li in list">
                    <td width="50"><img ng-if="li.image" width="40" class="image" src={{"<?php echo SITE_URL;?>uploads/products/"+li.image}} /></td>
                    <td>{{li.full_name}}</td>
                    <td>{{li.group}}</td>
                    <td>{{li.author}}</td>
                    <td>{{li.price}}</td>
                    <td>{{li.in_hand < 0 ? 0 : li.in_hand}}</td>
                    <td>
                    <a ng-if="li.pin == 0" href="javascript:void(0)" ng-click="addBookmark(li)" class="btn btn-bookmark" uib-tooltip="Pin as running items"><span class="fa fa-heart-o"></span></a>
                    <a ng-if="li.pin != 0" href="javascript:void(0)" ng-click="removeBookmark(li)" class="btn btn-bookmark" uib-tooltip="Remove from Running items list"><span class="fa fa-heart"></span></a>
                    <a uib-tooltip="Add to Cart" ng-click="addToCart(li)" class="btn btn-xs"><img width="18" height="18" src="<?php echo SITE_URL; ?>assets/img/svg/002-add-to-cart.svg" alt="" /></a>
                    <a class="btn btn-primary btn-xs" href="<?php echo SITE_URL."pages/product/update.php?id="?>{{li.id}}"><span class="fa fa-edit"></span></a> <a class="btn btn-danger btn-xs" href="<?php echo SITE_URL."pages/product/create.php?id="?>{{li.id}}"><span class="fa fa-copy"></span></a></td>
                </tr>
        </tbody>
    </table>

    <div style="display: flex; align-items: center; justify-content: space-between"><ul uib-pagination total-items="data.totalRecords" ng-model="currentPage" max-size="maxSize" class="pagination-sm" boundary-links="true" force-ellipses="true" ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage"  ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage"><option ng-value="10">10</option><option ng-value="25">25</option><option ng-value="50">50</option><option ng-value="100">100</option></select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span></div>
    
</div>
<script>
app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
    $scope.currentPage = 1; 
    $scope.data = {perPage: 10}; 
    $scope.list = []; 
    $scope.search = ""; 
    $scope.courceId = ""; 
    $scope.sortByField = ""; 
    $scope.sortByOrder = ""; 
    $scope.maxSize = 5;
    
    $scope.sortBy = field => {
        if($scope.sortByField == field) {
            $scope.sortByField = field;
            $scope.sortByOrder = $scope.sortByOrder == 'desc' ? 'asc' : 'desc';
        }
        else {
            $scope.sortByField = field;
            $scope.sortByOrder = 'asc';
        }
        $scope.getProducts(1);
    }; 
    $scope.getProducts = (page) => {
        $scope.loading = true;
        $http.get("<?php echo SITE_URL?>api/getProducts.php", {params: {page: page || 1, sortByField: $scope.sortByField, sortByOrder: $scope.sortByOrder, perPage: $scope.data.perPage, search: $scope.search, searchBy: $scope.searchBy, courceId: $scope.courceId}})
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
    $scope.addToCart = function (item, type) {
        if($window.localStorage.getItem('shopping')) {
            const shopCart = JSON.parse($window.localStorage.getItem('shopping'));
            let found = false;
            shopCart.map(row => {
                if(type && type == 'list') {
                    item.map(l => {
                        if(row.id == l.id) {
                            found = true
                            row.qty++;
                        }
                    })
                }
                else {
                    if(row.id == item.id) {
                        found = true
                        row.qty++;
                    }
                }
            });
            
            if(!found) {
                $window.localStorage.setItem('shopping', JSON.stringify([...shopCart, ...(type && type=='list' ? item.map(r => ({...r, qty: 1})) : [{id: item.id, qty: 1}] )] ));
            } else {
                $window.localStorage.setItem('shopping', JSON.stringify([...shopCart]))
            }
        }
        else {
            $window.localStorage.setItem('shopping', JSON.stringify(type && type=='list' ? item.map(r => ({...r, qty: 1})) : [{id: item.id, qty: 1}]))
        }
        toaster.success({body: 'Book Added to Cart successfully!'});
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