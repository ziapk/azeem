<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';
    echo mainHeader();
?>
<div class="container" ng-controller="orderController">
    <a href="<?php echo SITE_URL."pages/product/create.php" ?>" class="btn btn-primary btn-xs pull-right">Create Product</a>
    <h4>Products All </h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchProducts()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <ul class="feature-products">
        <li ng-repeat="li in list" class="feature-product">
            <img ng-if="li.image" class="image" src={{"<?php echo SITE_URL;?>uploads/products/"+li.image}} />
            <div ng-if="!li.image" class="image"></div>
            <span class="title">{{li.full_name}}</span>
            <span class="price">{{li.price}}<em>{{li.currency || 'PKR'}}</em></span>
            <div class="btn-wrap"><a ng-click="addToCard(li.id)" class="btn btn-default btn-lg btn-cart">Add to card</a></div>
        </li>
    </ul>
    <ul uib-pagination total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged()"></ul>
</div>
<script type="text/javascript">
app.controller('orderController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
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
<?php
echo mainFooter();  