<?php
include_once dirname(__FILE__).'/../../include/settings.php';
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerProducts($ownerId);
?>
<style>
    table {
        font-family: Arial, sans-serif
    }
</style>
<div ng-controller="cartController">
<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th style="vertical-align: middle">Customer Name</th>
                <th>
                <div class="dropdown-wrapper">
                    <input type="text" class="form-control" ng-model="customerName" ng-change="searchCustomer()">
                    <div class="list-group recipt-search-dropdown">
                        <a ng-click="selectCustomer(l)" class="list-group-item clearfix" ng-repeat="l in customersList">
                            <h4 class="list-group-item-heading">{{l.full_name}} <span>{{l.barcode}}</span></h4>
                            <span>{{l.group}}</span>  <span class="pull-right">{{l.code}}</span>
                            
                        </a>
                        <a ng-if="customersList.length" ng-click="clearCustomer()" class="list-group-item">Close</a>
                    </div>
                </div>
                </th>
                <th style="vertical-align: middle"><label><span style="vertical-align: middle">Search Product</span> <span style="vertical-align: middle; margin-left: 4px"><input type="checkbox" name="focus" ng-model="focus"><span></label></th>
                <th width="100">
                <div class="dropdown-wrapper">
                    <input type="text" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
                    <!-- <div class="list-group recipt-search-dropdown">
                        <a ng-click="selectProduct(l)" class="list-group-item" ng-repeat="l in list">
                            <h4 class="list-group-item-heading">{{l.full_name}} <span>{{l.price}}</span></h4>
                        </a>
                        <a ng-if="list.length" ng-click="clearSearch()" class="list-group-item">Close</a>
                    </div> -->
                </div>
                </th>
            </tr>
        </thead>
    </table>
    
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Description</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="cart in items track by $index" id="item-{{cart.id}}">
                <td>{{$index + 1}}</td>
                <td>{{cart.full_name}}</td>
                <td>{{cart.price}}</td>
                <td><button ng-click="subQty(cart)">-</button>
                <input class="text-center input-qty" type="number" ng-model="qty" ng-value=" cart.qty | number " ng-change="directlyAdd(qty, cart)">
                <button ng-click="addQty(cart)">+</button></td>
                <td>
                    <input class="text-center" type="number" ng-model="addprice" ng-change="directlyPrice(addprice, cart)">
                </td>
                <td>
                    {{cart.price * cart.qty}}
                    <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td class="text-right" colspan="5">Sub Total</td>
                <td>{{subTotal}}</td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Disc.</td>
                <td width="200"><input type="number" ng-model="discount" class="form-control" ng-change="addDiscount(discount)"></td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Grand Total</td>
                <td>{{grandTotal}}</td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Pay Amount</td>
                <td width="200"><input type="number" ng-model="payment_amount" class="form-control"></td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Balance</td>
                <td width="200">{{grandTotal - payment_amount}}</td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <th colspan="4">
                    <!-- <a href="#" class="btn btn-info">Place</a> -->
                </th>
                <th class="text-right" colspan="2">
                    
                    <a href="#" class="btn btn-primary" ng-click="checkout()"><img width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/001-checkout.svg" alt="" /> Checkout</a>
                </th>
            </tr>
        </tbody>
    <table>
</div>

</div>
<script type="text/javascript">
app.run(['$anchorScroll', function($anchorScroll) {
  $anchorScroll.yOffset = $('.navbar').height(true, true);   // always scroll by 50 extra pixels
}])
app.controller('cartController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $timeout, $location, $anchorScroll) {
    $scope.mainList = <?php echo json_encode($list);?>;

    $scope.list = [];
    $scope.priceList = [];
    $scope.focus = true;

    $scope.customerData = {};
    $scope.gst = 0;
    $scope.service_charges = 0;
    $scope.subTotal = 0;
    $scope.grandTotal = 0;
    $scope.discount = 0;
    const items = [];
    setInterval(() => {
        if($scope.focus === true && !$('#searchProduct').is(':focus')) {
            $scope.product = null
            $('#searchProduct').focus();
        }
    }, 3000);
    if($window.localStorage.getItem('shopping')) {
        const shopCart = JSON.parse($window.localStorage.getItem('shopping'));
        
        shopCart.map(function(row){
            const obj = $scope.mainList.find(function (e) { return e.id == row.id});
            items.push({...obj, qty: row.qty})
        });
        $scope.items = items;
        $timeout(() => {
            $scope.calculateSum()
        });
    }
    else {
        $scope.items = []
    }

    $scope.addTax = () => {
        $scope.calculateSum();
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
            var v = val / obj.price;
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
    $scope.selectProduct = function (p) {
        $scope.product = '';
        $scope.product = null
        let exists = false;
        $scope.items.map((pro) => {
            if(pro.id == p.id) {
                exists = true;
                pro.qty++;
            }
        })
        if(!exists) {
            $scope.items.push({...p, qty: 1});
        }
        $scope.calculateSum();

        // $location.hash('item-'+p.id);

        // // call $anchorScroll()
        // $anchorScroll();
        // $('#item-'+p.id).find('.input-qty').focus();
        // $scope.product = null;
        $timeout(() => {
            $scope.product = '';
        }, 400);
        
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
        const params = {};
        if($scope.focus === true && (term || "").endsWith('-AGP')) {
            params.searchBy = 'id';
            $scope.product = '';
            params.term = parseFloat(term.split('-')[0]);
            const list = localStorage.getItem('list') && JSON.parse(localStorage.getItem('list'));
            const item = list.find(r  => r.id == params.term);
            $scope.product = '';
            $scope.selectProduct(item);
            return [];
        }
        else {
            params.term = term;
            return $http.get("<?php echo SITE_URL?>api/getStores.php", { params })
            .then(function(response) {
  
                $scope.list = response.data;
                $scope.priceList = response.data;
                return response.data
            
            });
        }
    }
    $scope.searchCustomer = function () {
        $http.get("<?php echo SITE_URL?>api/getCustomer.php?term="+$scope.customerName)
        .then(function(response) {
            $scope.customersList = response.data;
        });
    }

    $scope.clearSearch = () => {
        $scope.product = null
        $scope.list = [];
    }
    $scope.clearCustomer = () => {
        $scope.customersList = [];
    }

    $scope.checkout = function () {
        $scope.form = {
            customerId: $scope.customerData && $scope.customerData.id ? $scope.customerData.id : 1,
            subTotal: $scope.subTotal,
            discount: $scope.discount,
            items: $scope.items,
            payment_amount: $scope.payment_amount,
            gst: $scope.gst,
            service_charges: $scope.service_charges
        }


        $http.post("<?php echo SITE_URL?>api/placeOrder.php", $httpParamSerializerJQLike($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            window.open("<?php echo SITE_URL;?>print?id="+response.data.order.id, "", "width=300,height=300"); 
            $scope.items = $scope.list = [];
            $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
            $window.localStorage.setItem('shopping', JSON.stringify($scope.items))
            $window.location.assign('<?php echo SITE_URL?>')
        });
    }    

    $scope.calculateSum = () => {
        let subtotal = 0;
        $scope.items.map((product) => {
            subtotal += (product.price * product.qty);
        })
        $scope.subTotal = subtotal;
        $scope.payment_amount = $scope.subTotal - $scope.discount;
        $scope.grandTotal = $scope.payment_amount = $scope.payment_amount + Math.round($scope.payment_amount * ($scope.gst / 100)) + Math.round($scope.payment_amount * ($scope.service_charges / 100));
        $window.localStorage.setItem('shopping', JSON.stringify($scope.items));

    }

})
</script>


<script type="text/ng-template" id="row.html">
  <a>
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
      <span class="pull-right">{{match.model.price}}</span>
  </a>
</script>
