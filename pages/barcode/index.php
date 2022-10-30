<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
echo mainHeader(['page' => 'barcode']);

$all = false;
$products = [];
$productsObj = new Products();
$storeObj = new Store();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['owner_id'];
$ownerStores = $storeObj->getOwnerStores($ownerId);

if(!empty($_GET['all']) && $_GET['all'] == '1') {
    $shopId = $_GET['shopId'];
    $products = $productsObj->getOwnerProductsPagination($ownerId, ['page' => 1, 'perPage' => 100000], $shopId);
}

?>

<div class="container" ng-controller="categoryController">
<form class="row" action="" method="GET">
    <div class="col-sm-3 form-group">
        <input type="hidden" class="form-control" name="all" value="1">
        <select class="form-control" name="shopId">
            <?php foreach ($ownerStores as $value) { ?>
                <option value="<?php echo $value['id'];?>"><?php echo $value['full_name'];?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-3 form-group">
        <input type="submit" class="btn btn-primary" value="Fetch All Items" />
    </div>
</form>
<h4>Products</h4>
<div class="form-group">
<input type="text" class="form-control" ng-model="product" placeholder="Search Products" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
</div>

<form action="print.php" method="post" target="_blank">
<table class="table">
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
            <td><input type="number" ng-model="li.qty" class="input-qty input-control" value="{{li.qty}}" name="qty[]"/></td>
            <td>
                <a class="btn btn-danger btn-xs" href="javascript:void(0)" ng-click="deleteCategory(li.id)">Delete</a>
            </td>
        </tr>
    </tbody>
</table>
<input type="submit" class="btn btn-primary" value="Print Tags" />

<script>
app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout) {
    $scope.list = []; //$scope.data.records;
    $scope.siteUrl = '<?php echo SITE_URL ?>';
    
    $scope.books = <?php echo json_encode($products);?>;

    $scope.list = [];
    $scope.items = $scope.books?.records || [];

    $scope.selectProduct = function (p) {
        let exists = false;
        $scope.items.map((pro) => {
            if(pro.id == p.id) {
                exists = true;
                pro.qty++;
            }
        })
        $scope.product = ""
        if(!exists) {
            $scope.items.push({...p, qty: 1});
        }

        $location.hash('item-'+p.id);

        // call $anchorScroll()
        $anchorScroll();
        $timeout(() => {
            $('#item-'+p.id).find('.input-qty').focus();
        }, 100);
        
    }


    $scope.searchProduct = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getStores.php", {params: {term}})
        .then(function(response) {
            
            $scope.list = response.data;
            $scope.priceList = response.data;
            return response.data
        });
    }

    $scope.deleteCategory = function (id) {
        $scope.items = $scope.items.filter(r => r.id !== id);
    }
    
    $scope.printTags = function (form) {
        $http.post('print.php', $httpParamSerializerJQLike($scope.items), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
            // $scope.getCategories(1);
        });
    };
});

app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, form) {
    $scope.form = {
        full_name: "",
        cat_type: "",
        ...form
    }
    $scope.ok = function () {
        $uibModalInstance.close($scope.form);
    };

    $scope.cancel = function () {
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