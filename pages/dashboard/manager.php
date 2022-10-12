<div class="container" ng-controller="productController">
    
    <a href="<?php echo SITE_URL."pages/product/create.php" ?>" class="btn btn-primary btn-xs pull-right">Create Product</a>
    <h4>Products All </h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchProducts()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>Title</th>
                <th>Price</th>
                <th>In Stock</th>
                <th width="200"></th>
            </tr>
        </thead>
        <tbody>
                <tr ng-repeat="li in list">
                    <td width="50"><img ng-if="li.image" width="40" class="image" src={{"<?php echo SITE_URL;?>uploads/products/"+li.image}} /></td>
                    <td>{{li.full_name}}</td>
                    <td>{{li.price}}</td>
                    <td>{{li.qty}}</td>
                    <td><a ng-click="addToCard(li.id)" class="btn btn-default btn-xs">Add to card</a> <a class="btn btn-primary btn-xs" href="<?php echo SITE_URL."pages/product/update.php?id="?>{{li.id}}">Modify</a></td>
                </tr>
        </tbody>
    </table>
    
</div>
<script>
app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
    $scope.currentPage = 1; 
    $scope.data = {}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.search = ""; //$scope.data.records;
    $scope.getProducts = (page) => {
        $scope.loading = true;
        $http.get(<?php echo SITE_URL?>+"api/getProducts.php", {params: {page: page || 1, search: $scope.search}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
                $scope.list = response.data.records;
            }
        })
    }
    
    $scope.searchProducts = () => {
        $scope.getProducts(1);
    }

    $scope.getProducts($scope.currentPage);
    $scope.pageChanged = () => {
        $scope.getProducts($scope.currentPage)
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
})
</script>