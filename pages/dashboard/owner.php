<?php 

$stores = new Store();
$productsObj = new Products();
$categoryObj = new Publishers();
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
    if($userData['shopId'] == $store['id']) {
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
    <h4>My Shops <small>&lt;<?php echo $currentStore['full_name'];?>&gt;</small></h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Title</th>
                <th>Type</th>
                <th>City</th>
                <th>Location</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; foreach ($ownerStores as $store) { ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $store['full_name']; ?></td>
                    <td><?php echo $storeTypes[$store['store_type']]['full_name']; ?></td>
                    <td><?php echo $store['city']; ?></td>
                    <td><?php echo $store['location']; ?></td>
                    <td><?php echo $statusArr[$store['status']]; ?></td>
                    <td><a href="<?php echo SITE_URL."pages/store/update.php?id=".$store['id'];?>">Modify</a></td>
                </tr>
            <?php $count++; } ?>
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
    <a href="<?php echo SITE_URL."pages/product/create.php" ?>" class="btn btn-primary btn-xs pull-right" style="margin-left: 12px">Create Product</a> <a href="<?php echo SITE_URL."pages/product/assign.php" ?>" class="btn btn-primary btn-xs pull-right">Assign Product</a>
    <h4>Products in stores </h4>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Branch</th>
                <th>Title</th>
                <th>Group</th>
                <th>Price</th>
                <th>In</th>
                <th>Out</th>
                <th>In Hand</th>
                <th>Min. Qty</th>
                <th>Placement</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; foreach ($ownerStoreProducts as $product) { ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $storeList[$product['shopId']]['full_name']; ?></td>
                    <td><?php echo $product['full_name']; ?></td>
                    <td><?php echo $product['group']; ?></td>
                    <td><?php echo $product['sale_price']; ?></td>
                    <td><?php echo $product['qty']; ?></td>
                    <td><?php echo $product['stock_out']; ?></td>
                    <td><?php echo $product['qty'] - $product['stock_out']; ?></td>
                    <td><?php echo $product['min_qty'] ? $product['min_qty'] : '-' ; ?></td>
                    <td><?php echo $product['location'] ? $product['location'] : '-' ; ?></td>
                    <td><a href="<?php echo SITE_URL."pages/product/update_item.php?id=".$product['id'];?>">Modify</a></td>
                    <td><a href="javascript:void(0)" ng-click="deleteStoreItem(<?php echo $product['id'];?>)">delete</a></td>
                </tr>
            <?php $count++; } ?>
        </tbody>
    </table>

    
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
            <?php $count = 1; foreach ($products as $product) { ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo $product['full_name']; ?></td>
                    <td><?php echo !empty($product['publisher_id']) ? $publishers[$product['publisher_id']]['full_name'] : null; ?></td>
                    <td><?php echo $product['group']; ?></td>
                    <td><?php echo $product['code']; ?></td>
                    <td><?php echo $product['barcode'] ? $product['barcode'] : 'NULL' ; ?></td>
                    <td><a href="<?php echo SITE_URL."pages/product/update.php?id=".$product['id'];?>">Modify</a></td>
                </tr>
            <?php $count++; } ?>
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


<script>
app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
    $scope.currentPage = 1; 
    $scope.data = {}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.url = "<?php echo SITE_URL;?>"; //$scope.data.records;
    $scope.deleteStoreItem = (id) => {
        if($window.confirm('Are you sure you want to delete this?')) {
            $http.get("<?php echo SITE_URL?>pages/product/delete_item.php", {params: { id }})
            .then(function(response) {
                console.log(response);
            }).catch(function(err) {
                console.log(err);
            })
        }
    }
})
</script>