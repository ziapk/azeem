<?php
include_once dirname(__FILE__).'/../../include/settings.php';
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerProducts($ownerId);
echo mainHeader(['page' => 'recipt']);
?>
<div ng-controller="cartController">
<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th style="vertical-align: middle">Customer Name</th>
                <th>
                <div class="dropdown-wrapper" style="position: relative;">
                    <input type="text" class="form-control" ng-model="customerName" placeholder="Search Customer" uib-typeahead="address as address.full_name for address in searchCustomer($viewValue)" typeahead-on-select="selectCustomer($item)" ng-model-options="{debounce: 100}" typeahead-template-url="customer.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
                    <!-- <input type="text" class="form-control" ng-model="customerName" ng-change=""> -->
                    <!-- <div class="list-group recipt-search-dropdown">
                        <a ng-click="selectCustomer(l)" class="list-group-item clearfix" ng-repeat="l in customersList">
                            <h4 class="list-group-item-heading"><strong>{{l.full_name}}</strong> <span class="text-danger"><strong>{{l.phoneNumber}}</strong></span></h4>
                            <span style="font-weight: normal" ng-if="li.title != l.full_name">{{l.title}}</span><span class="pull-right">{{l.code}}</span>
                            
                        </a>
                        <a ng-if="customersList.length" ng-click="clearCustomer()" class="list-group-item">Close</a>
                    </div> -->
                </div>
                </th>
                <th style="vertical-align: middle"><label><span style="vertical-align: middle">Search Product</span> <span style="vertical-align: middle; margin-left: 4px"><input type="checkbox" name="focus" ng-model="focus"><span></label></th>
                <th width="100">
                <div class="dropdown-wrapper">
                    <input type="text" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectProduct($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
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
                <td><span ng-if="cart.aprice"><del>{{cart.aprice | number: 2}}</del> / </span>{{cart.price | number: 2}}</td>
                <td>
                    <div class="quantity">
                        <a href="#" class="quantity__minus" ng-click="subQty(cart)"><span>-</span></a>
                        <input class="quantity__input" type="text" ng-model="qty" ng-value=" cart.qty | number " ng-change="directlyAdd(qty, cart)">
                        <a href="#" class="quantity__plus" ng-click="addQty(cart)"><span>+</span></a>
                    </div>
                </td>
                <td>
                    <input class="text-center" type="number" ng-model="addprice" ng-change="directlyPrice(addprice, cart)">
                </td>
                <td>
                    {{cart.price * cart.qty | number: 2}}
                    <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td class="text-right" colspan="5">Sub Total</td>
                <td>{{subTotal | number: 2}}</td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Add Discount</td>
                <td width="200"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
            </tr>
            <tr>
                <td class="text-right" colspan="5" style="color: red; front-weight: bold;">Total Discount</td>
                <td width="200" style="color: red; front-weight: bold;"><strong>{{discount | number: 2}}</strong></td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Grand Total</td>
                <td>{{grandTotal | number: 2}}</td>
            </tr>
            <tr>
                <td class="text-right" colspan="5" style="color: green; front-weight: bold;">Pay Amount</td>
                <td width="200"><input type="number" ng-model="payment_amount" class="form-control"></td>
            </tr>
            <tr>
                <td class="text-right" colspan="5">Balance</td>
                <td width="200">{{grandTotal - payment_amount | number: 2}}</td>
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
<?php
echo mainFooter();
?>

<script type="text/javascript">
app.run(['$anchorScroll', function($anchorScroll) {
  $anchorScroll.yOffset = $('.navbar').height(true, true);   // always scroll by 50 extra pixels
}])
app.directive('onEnterPress', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.onEnterPress);
                });
                event.preventDefault();
            }
        });
    };
});
app.controller('cartController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $timeout, $location, $anchorScroll) {
    $scope.mainList = <?php echo safe_json_encode($list);?>;

    $scope.list = [];
    $scope.priceList = localStorage.getItem('list') && JSON.parse(localStorage.getItem('list'));;
    $scope.focus = false;

    $scope.customerData = {};
    $scope.gst = 0;
    $scope.service_charges = 0;
    $scope.subTotal = 0;
    $scope.grandTotal = 0;
    $scope.discount = 0;
    const items = [];
    // setInterval(() => {
    //     if($scope.focus === true && !$('#searchProduct').is(':focus')) {
    //         $scope.product = null
    //         $('#searchProduct').focus();
    //     }
    // }, 3000);
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
            $scope.discount = (parseFloat($scope.discount) + parseFloat(val));
        }
        $scope.calculateSum();
        $scope.discountAmount = '';
    }
    $scope.directlyAdd = function (val, obj) {
        if(val > 0) {
            obj.qty = val
        }
        $scope.calculateSum();
    }

    $scope.directlyPrice = function (val, obj) {
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
        if(!exists) { // if already not exits in bucket
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
        }, 200);
        
    }
    $scope.selectCustomer = function (p) {
        $scope.customerName = p.full_name;
        $scope.customerData = p;
        $scope.calculateSum(p);
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
        if($scope.focus === true) {
            params.searchBy = 'id';
            $scope.product = '';
            params.term = parseFloat(term.split('-')[0]);
            const list = localStorage.getItem('list') && JSON.parse(localStorage.getItem('list'));
            const item = list.find(r  => r.id == params.term || r.code == params.term || r.barcode == params.term);
            $scope.product = '';
            $scope.selectProduct(item);
            return [];
        }
        else {
            params.term = term;
            return $http.get("<?php echo SITE_URL?>api/getStores.php", { params })
            .then(function(response) {
  
                // $scope.list = response.data;
                // $scope.priceList = response.data;
                return response.data
            
            });
        }
    }
    $scope.searchCustomer = function (value, onloading) {
        $scope.customerName = value;
        return $http.get("<?php echo SITE_URL?>api/getCustomer.php?term="+value)
        .then(function(response) {
            $scope.customersList = response.data;
            if(onloading) {
                $scope.selectCustomer($scope.customersList[0]);
            }
            return response.data
        });
    }

    $scope.searchCustomer('', true)

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

    $scope.calculateSum = (c) => {
        const customerData = c || $scope.customerData;
        let subtotal = 0;
        $scope.items.map((product) => {
            const prod = $scope.priceList.find(r => r.id == product.id);
            let currentRow = null;
            if(customerData.discount_array?.length && customerData.discount_array?.filter(r => r.publisher_id == product.publisher_id).length) {
                const row = customerData.discount_array.find(r => r.publisher_id == prod.publisher_id);
                const price = parseFloat(prod.price);
                product.aprice = price;
                product.price = (price * (100 - parseFloat(row.discount_value)) / 100);
                subtotal += (product.price * product.qty);
            }
            else {
                const price = parseFloat(prod.price);
                product.price = price;
                subtotal += (price * product.qty);
            }
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
<script type="text/ng-template" id="customer.html">
  <a class="clearfix" style="border-bottom: 1px solid #ccc; display: block">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span><br />
      <small><em>{{match.model.company}}</em></small>
  </a>
</script>
