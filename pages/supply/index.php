<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ordersObj = new Orders();
    $dateLabel = "Sales for ";
    $start = $end = date('Y-m-d');
    $stores = new Store();
    $ownerStores = $stores->getOwnerStores($userData['created_by']);
    
    if(isset($_GET['report'])) {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $orders = $ordersObj->userOrders($userData['shopId'], $from, $to);
        $dateLabel .= $from.' to '.$to;
        $start = date('Y-m-d', strtotime($from));
        $end = date('Y-m-d', strtotime($to));
    }
    else {        
        $orders = $ordersObj->userOrders($userData['shopId'], date('Y-m-d'));
        $dateLabel .= date('Y-m-d');
        $start = date('Y-m-d');
        $end = date('Y-m-d');
    }
    echo mainHeader();
?>

<div class="container" ng-controller="reportController">
    <div class="row">
        <div class="col-sm-3 form-group">
            <label>Supplier's Name</label>
            <input type="hidden" class="form-control" ng-model="supplierId">
            <input type="text" class="form-control" ng-model="supplierName" placeholder="Supplier's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.name for address in searchSupplier($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
        </div>
        <div class="col-sm-3 form-group">
            <label>Supplier's Contact</label>
            <input type="text" class="form-control" ng-model="supplierContact" placeholder="Supplier's Contact" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.contact for address in searchSupplier($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
        </div>
        <div class="col-sm-3 form-group">
            <label>Shop Select</label>
            <select class="form-control c-select" ng-model="shopId">
                    <?php foreach ($ownerStores as $value) { ?>
                        <option value="<?php echo $value['id'];?>"><?php echo $value['full_name'];?></option>
                    <?php } ?>
                </select>
        </div>
        <div class="col-sm-3">
            <label>&nbsp;</label><br />
            <a href="javascript:void(0)" title="Add Fresh Product" ng-click="addFreshProduct()" class="btn btn-danger">Add Fresh Product</a>
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th width="200">Product Id</th>
                <th>Product Name</th>
                <th width="100">P. Price</th>
                <th width="100">S. Price</th>
                <th width="100">Qty</th>
                <th></th>
            </tr>
            <tr>
                <td colspan="7"><input type="text" class="form-control" ng-model="product" placeholder="Search Product to add" typeahead-on-select="selectProduct($item, row)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row2.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0" class="form-control" ng-model="row.product_name" /></td>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="row in items">
                <td><input type="text" class="form-control" ng-model="row.barcode" /></td>
                <td>
                <input type="text" class="form-control" ng-model="row.full_name" placeholder="Product title" /></td>
                <td><input type="number" class="form-control" ng-change="calculateSum()" ng-model="row.pprice" /></td>
                <td width="100"><input type="number" class="form-control" ng-model="row.price" /></td>
                <td width="100"><input type="number" class="form-control" ng-change="calculateSum()" ng-model="row.qty" /></td>
                <td width="100"><input type="number" class="form-control" ng-model="row.publisher_id" /></td>
                <td width="60"><a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a></td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <th class="text-right" colspan="4">Sub Total</th>
                <th>{{subTotal}}</th>
            </tr>
            <tr>
                <th class="text-right" colspan="4">Disc.</th>
                <th width="200"><input type="number" ng-model="discount" class="form-control" ng-change="addDiscount(discount)"></th>
            </tr>
            <tr>
                <th class="text-right" colspan="4">Grand Total</th>
                <th>{{grandTotal}}</th>
            </tr>
            <tr>
                <th class="text-right" colspan="4">Pay Amount</th>
                <th width="200"><input type="number" ng-model="payment_amount" class="form-control"></th>
            </tr>
            <tr>
                <th class="text-right" colspan="4">Balance</th>
                <th width="200">{{grandTotal - payment_amount}}</th>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <th colspan="3">
                    <!-- <a href="#" class="btn btn-info">Place</a> -->
                </th>
                <th class="text-right" colspan="2">
                    
                    <a href="#" class="btn btn-success" ng-click="checkout()">Checkout</a>
                </th>
            </tr>
        </tbody>
    </table>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
app.controller('reportController', function ($scope, $http, $window, $httpParamSerializerJQLike) {
    
    $scope.supplierName = "";
    $scope.supplierContact = "";
    $scope.supplierId = "";
    $scope.product = "";
    $scope.shopId = '4';
    
    $scope.list = [];
    $scope.priceList = [];
    $scope.items = [];
    $scope.customerData = {};
    $scope.subTotal = 0;
    $scope.grandTotal = 0;
    $scope.discount = 0;


    $scope.newData = {
        barcode: "",
        full_name: "",
        pprice: 0,
        price: 0,
        qty: 1,
    }

    $scope.addFreshProduct = function() {
        // $scope.items.push({...$scope.newData});
        $window.open('<?php echo SITE_URL;?>pages/product/create.php?headers=1', '_blank', "menubar=0,resizable=1,width=600,height=600");
    }

    $scope.searchSupplier = function (term) {
        $scope.supplierId = ""
        return $http.get("<?php echo SITE_URL?>api/getSupplier.php", {params: {term}})
        .then(function(response) {
            return response.data
        });
    }

    $scope.selectSupplier = function (p) {
        $scope.supplierId = p.name
        $scope.supplierName = p.name
        $scope.supplierContact = p.contact
    }

    $scope.addDiscount = function (val, obj) {
        if(val > 0) {
            $scope.discount = val;
        } else {
            $scope.discount = 0;
        }
        $scope.calculateSum();
    }
    $scope.directlyAdd = function (val, obj) {
        if(val > 0) {
            obj.qty = val
        }
        $scope.calculateSum();
    }

    $scope.directlyPrice = function (val, obj) {
        console.log($scope.priceList);
        if(val > 0) {
            var v = val / obj.pprice;
            obj.qty = v;
        }
        $scope.calculateSum();
    }

    $scope.remove = function(item) { 
        if(confirm('Are you sure you want delete?')) {
            var index = $scope.items.indexOf(item);
            $scope.items.splice(index, 1);
            $scope.calculateSum();
        }
    }
    $scope.selectProduct = function (p, r) {
        let exists = false;
        $scope.items.map((pro) => {
            if(pro.id == p.id) {
                exists = true;
                pro.qty++;
            }
        })
        $scope.product = "";
        if(!exists) {
            $scope.items.push({...p, price: parseInt(p.price || 0), pprice: parseInt(p.pprice || 0), qty: 1});
        }
        $scope.calculateSum();
        
    }
    
    $scope.selectCustomer = function (p) {
        $scope.customerName = p.full_name;
        $scope.customerData = p;
        
    }

    $scope.addQty = function (row) {
        row.qty++;
        $scope.calculateSum();
    }
    $scope.subQty = function (row) {
        if(row.qty > 1) {
            row.qty--;
            $scope.calculateSum();
        }
    }

    $scope.searchProduct = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getStores.php", {params: {term}})
        .then(function(response) {
            $scope.list = response.data;
            $scope.priceList = response.data;
            return response.data
        });
    }

    $scope.searchCustomer = function () {
        $http.get("<?php echo SITE_URL?>api/getCustomer.php?term="+$scope.customerName)
        .then(function(response) {
            $scope.customersList = response.data;
        });
    }

    $scope.clearSearch = () => {
        $scope.product = "";
        $scope.list = [];
    }
    $scope.clearCustomer = () => {
        $scope.customersList = [];
    }

    $scope.checkout = function () {
        console.log('yes');
        $scope.form = {
            customerId: $scope.customerData && $scope.customerData.id ? $scope.customerData.id : 1,
            subTotal: $scope.subTotal,
            discount: $scope.discount,
            items: $scope.items,
            shopId: $scope.shopId,
            payment_amount: $scope.payment_amount
        }


        $http.post("<?php echo SITE_URL?>api/placeSupply.php", $httpParamSerializerJQLike($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            window.open("<?php echo SITE_URL;?>print?id="+response.data.order.id, "", "width=300,height=300"); 
            $scope.items = $scope.list = [];
            $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
        });
    }    

    $scope.calculateSum = () => {
        let subtotal = 0;
        $scope.items.map((product) => {
            subtotal += (product.pprice * product.qty);
        })
        $scope.subTotal = subtotal;
        $scope.grandTotal = $scope.payment_amount = $scope.subTotal - $scope.discount;
    }



});

</script>

<script type="text/ng-template" id="row.html">
  <a style="display: block">
      <span ng-bind-html="match.model.name | uibTypeaheadHighlight:query"></span> <br />
      <span>{{match.model.contact}}</span>
  </a>
</script>

<script type="text/ng-template" id="row2.html">
  <a style="display: block">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
      <span class="pull-right" style="margin-left: 20px">{{match.model.price}}</span>
  </a>
</script>
