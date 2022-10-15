<div ng-controller="orderController">
<div class="container">
{{title}}
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th style="vertical-align: middle">Customer Name</th>
                <th style="vertical-align: middle">Price</th>
                <th style="vertical-align: middle">Status</th>
            </tr>
        </thead>
    </table>
    
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
                    <input type="text" class="form-control" ng-model="product" id="searchProduct" placeholder="Search Products" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
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
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="cart in items track by $index" id="item-{{cart.id}}">
                <td>{{$index + 1}}</td>
                <td>{{cart.full_name}}</td>
                <td>{{cart.price}}</td>
                <td><button ng-click="subQty(cart)">-</button>
                <input class="text-center input-qty" type="number" ng-model="qty" ng-value=" cart.qty | number : 2 " ng-change="directlyAdd(qty, cart)">
                <button ng-click="addQty(cart)">+</button></td>
                <td>
                    {{cart.price * cart.qty}}
                    <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
                </td>
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
    <table>
</div>

</div>
<script type="text/javascript">
app.run(['$anchorScroll', function($anchorScroll) {
  $anchorScroll.yOffset = $('.navbar').height(true, true);   // always scroll by 50 extra pixels
}])
app.controller('orderController', function($scope, $http, $httpParamSerializerJQLike, $filter, $location, $anchorScroll) {

    $scope.list = [];
    $scope.priceList = [];
    $scope.focus = true;
    $scope.items = [];
    $scope.customerData = {};
    $scope.subTotal = 0;
    $scope.grandTotal = 0;
    $scope.discount = 0;

    setInterval(() => {
        if($scope.focus === true && !$('#searchProduct').is(':focus')) {

            $('#searchProduct').focus();
        }
    }, 1000);

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
        $scope.calculateSum();

        $location.hash('item-'+p.id);

        // call $anchorScroll()
        $anchorScroll();
        setTimeout(() => {
            $('#item-'+p.id).find('.input-qty').focus();
        }, 100)
        
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
            params.term = parseFloat(term);
        }
        else {
            params.term = term;
        }
        return $http.get("<?php echo SITE_URL?>api/getStores.php", { params })
        .then(function(response) {

            if($scope.focus === true && (term || "").endsWith('-AGP')) {
                response.data && response.data.length && $scope.selectProduct(response.data[0]);
            }
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
        $scope.form = {
            customerId: $scope.customerData && $scope.customerData.id ? $scope.customerData.id : 1,
            subTotal: $scope.subTotal,
            discount: $scope.discount,
            items: $scope.items,
            payment_amount: $scope.payment_amount
        }


        $http.post("<?php echo SITE_URL?>api/placeOrder.php", $httpParamSerializerJQLike($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            window.open("<?php echo SITE_URL;?>print?id="+response.data.order.id, "", "width=300,height=300"); 
            $scope.items = $scope.list = [];
            $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
        });
    }    

    $scope.calculateSum = () => {
        let subtotal = 0;
        $scope.items.map((product) => {
            subtotal += (product.price * product.qty);
        })
        $scope.subTotal = subtotal;
        $scope.grandTotal = $scope.payment_amount = $scope.subTotal - $scope.discount;
    }

})
</script>


<script type="text/ng-template" id="row.html">
  <a>
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
      <span class="pull-right">{{match.model.price}}</span>
  </a>
</script>
