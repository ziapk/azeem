<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$stores = new Store();
if ($userData['role'] == 'owner') {
    $ownerStores = $stores->getOwnerStores($userData['id']);
} else {
    $ownerStores[] = $shop;
}

$id = $_GET['id'];

$orders = new Orders();
$order = $orders->getOrder($id);

echo mainHeader();
?>

<div class="container" ng-controller="reportController">
    <div class="row">
        <div class="col-sm-3 form-group">
            <label>Customer's Name</label>
            <input type="hidden" class="form-control" ng-model="supplierId">
            <input type="text" class="form-control" ng-model="supplierName" placeholder="Customer's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.full_name for address in searchSupplier($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
        </div>
        <div class="col-sm-3 form-group">
            <label>Ref. No</label>
            <input type="text" class="form-control" ng-model="ref_no" placeholder="Ref. No">
        </div>
        <div class="col-sm-3 form-group">
            <label>Shop Select</label>
            <select class="form-control c-select" ng-model="shopId" ng-change="resetFields()">
                <?php foreach ($ownerStores as $value) { ?>
                    <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-3">
            <label>Opening Balance</label>
            <strong class="form-control text-danger">{{supplier.opening_balance}}</strong>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th width="200">Product Id</th>
                <th>Product Name</th>
                <th width="100">Sold. Price</th>
                <th width="100">Sold Qty</th>
                <th></th>
            </tr>
            <tr>
                <td colspan="7"><input type="text" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Product to add" typeahead-on-select="selectProduct($item, row)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row2.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0" class="form-control" ng-model="row.product_name" /></td>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="row in items">
                <td><input type="text" class="form-control" ng-model="row.id" /></td>
                <td>
                    <input type="text" class="form-control" ng-model="row.full_name" placeholder="Product title" />
                </td>
                <td width="100"><input type="number" class="form-control" ng-model="row.price" /></td>
                <td width="100">
                    <input type="number" class="form-control" ng-change="isValid(row,  calculateSum)" max="row.maxQty" ng-model="row.qty" ng-keydown="initCheckKeypress($event)" />
                </td>
                <td width="60"><a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(row)">Delete</a></td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <th rowspan="10"></th>
                <th class="text-right" colspan="3">Sub Total</th>
                <th>{{subTotal}}</th>
            </tr>
            <tr>
                <td class="text-right" colspan="3">Given Discount</td>
                <td><strong>{{givenDiscount | number: 2}}</strong></td>
            </tr>
            <tr ng-if="order.order.paid_amount">
                <th class="text-right" colspan="3">Paid Total</th>
                <th>{{order.order.paid_amount}}</th>
            </tr>
            <tr>
                <th class="text-right" colspan="3">Add Charges</td>
                <td width="150"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
            </tr>
            <tr>
                <td class="text-right" colspan="3">Additional Discount</td>
                <td><strong>{{discount | number: 2}}</strong></td>
            </tr>
            <tr>
                <th class="text-right" colspan="3">Opening Balance.</th>
                <th width="200">
                    {{supplier.opening_balance}}
                </th>
            </tr>
            <tr>
                <th class="text-right" colspan="3">Remaining Balance.</th>
                <th width="200">
                    {{supplier.opening_balance - subTotal > 0 ? supplier.opening_balance - (subTotal - givenDiscount) - discount : 0}}
                </th>
            </tr>
            <tr ng-if="grandTotal">
                <th class="text-right" colspan="3">Grand Total</th>
                <th>{{grandTotal}}</th>
            </tr>
            <tr ng-if="grandTotal">
                <th class="text-right" colspan="3">Pay Amount</th>
                <th width="200"><input type="number" ng-model="payment_amount" ng-change="calcBalanc(payment_amount)" class="form-control"></th>
            </tr>
            <tr ng-if="grandTotal">
                <th class="text-right" colspan="3">Closing Balance</th>
                <th width="200">{{ payment_amount - grandTotal }}</th>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <th colspan="6" class="text-right">
                    <div class="btn-group">
                        <label class="btn btn-default" ng-repeat="li in modes">
                            <input type="radio" name="mode" ng-model="payment_mode" ng-value="li.id" ng-change="printValue(li)">
                            {{li.title}}
                        </label>
                    </div>
                    <a href="#" class="btn btn-primary" ng-click="checkout()"> Return Submit</a>
                </th>
            </tr>
        </tbody>
    </table>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    app.directive('onEnterPress', function() {
        return function(scope, element, attrs) {
            element.bind("keydown keypress", function(event) {
                if (event.which === 13) {
                    scope.$apply(function() {
                        scope.$eval(attrs.onEnterPress);
                    });
                    event.preventDefault();
                    $(element).val('')
                }
            });
        };
    });
    app.controller('reportController', function($scope, $http, $window, $httpParamSerializerJQLike) {

        $scope.supplierName = "";
        $scope.ref_no = "";
        $scope.supplierId = "";
        $scope.product = "";
        $scope.shopId = '';
        $scope.order = <?php echo json_encode($order) ?>;

        $scope.items = $scope.order?.order_items?.map(r => ({
            ...r,
            qty: parseFloat(r.quantity),
            pprice: parseFloat(r.price.toString()),
            price: parseFloat(r.price.toString()),
            maxQty: r.quantity.toString(),
            full_name: r.product_title,
        })) || [];

        $scope.list = [];
        $scope.priceList = [];
        $scope.customerData = {};
        $scope.subTotal = 0;
        $scope.grandTotal = 0;
        $scope.givenDiscount = parseFloat($scope?.order?.order?.discount || 0);
        $scope.discount = 0;
        $scope.payment_mode = '1';
        $scope.supplier = null;






        $scope.initCheckKeypress = (evt) => {
            var e = evt; // for trans-browser compatibility
            var charCode = e.which || e.keyCode;
            if (charCode === 9) {
                $('#searchProduct').focus();
                e.preventDefault();
            }
        }

        $scope.newData = {
            barcode: "",
            full_name: "",
            pprice: 0,
            price: 0,
            qty: 1,
        }

        $scope.resetFields = () => {
            $scope.supplierName = "";
            $scope.supplierId = "";
            $scope.product = "";

            $scope.list = [];
            $scope.priceList = [];
            $scope.items = [];
            $scope.customerData = {};
            $scope.subTotal = 0;
            $scope.grandTotal = 0;
            $scope.discount = 0;
            $scope.supplier = null;

        }

        $scope.addFreshProduct = function() {
            // $scope.items.push({...$scope.newData});
            $window.open('<?php echo SITE_URL; ?>pages/product/create.php?headers=1', '_blank', "menubar=0,resizable=1,width=800,height=400");
        }

        $scope.searchSupplier = function(term, init) {
            $scope.supplierId = ""
            return $http.get("<?php echo SITE_URL ?>api/getCustomer.php", {
                    params: {
                        term,
                        shopId: $scope.shopId,
                        accountsOnly: true
                    }
                })
                .then(function(response) {
                    if (init && !$scope.order.customer) {
                        $scope.selectSupplier(response.data[0])
                    }
                    $scope.selectSupplier($scope.order.customer);
                    return response.data
                });
        }

        $scope.isValid = (row, cb) => {
            // console.log(row.qty, row.maxQty)
            if (parseFloat(row.qty) < parseFloat(row.maxQty)) {
                cb();
            } else {
                row.qty = parseFloat(row.maxQty);
            }
        }

        $scope.selectSupplier = function(p) {
            $http.get("<?php echo SITE_URL ?>api/getOpeningBalance.php", {
                params: {
                    account_id: p.account_id,
                    type: 'c'
                }
            }).then(res => {

                console.log(res, ((parseFloat(res.data.opening_balance || 0) + parseFloat(res.data.debitAmount || 0)) - parseFloat(res.data.creditAmount || 0)));
                $scope.supplierId = p.id
                $scope.supplierName = p.full_name
                $scope.supplier = {
                    ...p,
                    opening_balance: ((parseFloat(res.data.opening_balance || 0) + parseFloat(res.data.debitAmount || 0)) - parseFloat(res.data.creditAmount || 0))
                };
                $scope.shopId = p.shopId;
                // $scope.items = [];
                $scope.calculateSum();
            })
        }

        $scope.addDiscount = function(val, obj) {
            if (parseFloat(val) > 0) {
                $scope.discount += parseFloat(val);
            } else if ($scope.discount + parseFloat(val) >= 0) {
                $scope.discount += parseFloat(val);
            } else {
                $scope.discount = 0;
            }
            $scope.calculateSum();
        }
        $scope.directlyAdd = function(val, obj) {
            if (val > 0) {
                obj.qty = val
            }
            $scope.calculateSum();
        }

        $scope.directlyPrice = function(val, obj) {
            console.log($scope.priceList);
            if (val > 0) {
                var v = val / obj.pprice;
                obj.qty = v;
            }
            $scope.calculateSum();
        }

        $scope.remove = function(item) {
            if (confirm('Are you sure you want delete?')) {
                console.log($scope.items);
                $scope.items = $scope.items.filter((r) => r.id !== item.id);
                $scope.calculateSum();
            }
        }
        $scope.selectProduct = function(p, r) {
            let exists = false;
            $scope.items.map((pro) => {
                if (pro.id == p.id) {
                    exists = true;
                    pro.qty = p.maxQty;
                }
            })
            $scope.product = "";
            if (!exists) {
                $scope.items.push({
                    ...p,
                    price: parseInt(p.price || 0),
                    pprice: parseInt(p.pprice || 0),
                    qty: parseFloat(p.maxQty)
                });
            }
            $scope.calculateSum();

        }

        $scope.addQty = function(row) {
            row.qty++;
            $scope.calculateSum();
        }
        $scope.subQty = function(row) {
            if (row.qty > 1) {
                row.qty--;
                $scope.calculateSum();
            }
        }

        $scope.searchProduct = function(search) {
            return $http.get("<?php echo SITE_URL ?>api/getProductFromOrders.php", {
                    params: {
                        search,
                        customerId: $scope.supplierId,
                        shopId: $scope.shopId
                    }
                })
                .then(function(response) {
                    $scope.list = response.data.records;
                    $scope.priceList = response.data.records;
                    return response.data.records
                });
        }

        $scope.searchMode = function() {
            return $http.get("<?php echo SITE_URL ?>api/getPaymentModes.php")
                .then(function(response) {
                    $scope.modes = response.data.records;
                    return response.data
                });
        }

        $scope.searchMode();

        $scope.searchSupplier('', true);

        $scope.clearSearch = () => {
            $scope.product = "";
            $scope.list = [];
        }

        $scope.calcBalanc = (a) => {
            $scope.payment_amount = a;
        }

        $scope.checkout = function() {
            $scope.form = {
                supplierId: $scope.supplierId,
                ref_no: $scope.ref_no,
                subTotal: $scope.subTotal,
                discount: $scope.discount,
                givenDiscount: $scope.givenDiscount,
                order_id: $scope.order.order.id,
                items: $scope.items,
                shopId: $scope.shopId,
                grandTotal: $scope.grandTotal,
                payment_amount: $scope.payment_amount,
                opening_balance: $scope.supplier.opening_balance
            }


            $http.post("<?php echo SITE_URL ?>api/placeCustomerReturn.php", $httpParamSerializerJQLike($scope.form), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    // window.open("<?php echo SITE_URL; ?>print?id="+response.data.order.id, "", "width=300,height=300"); 
                    $scope.items = $scope.list = [];
                    $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
                    alert(response.data.message);
                    // $window.location.reload()
                });
        }

        $scope.calculateSum = () => {
            let subtotal = 0;
            // $scope.discount = 0;
            $scope.items.map((product) => {
                subtotal += (product.price * product.qty);
                $scope.discount += (product.discount * product.qty);
            })
            $scope.subTotal = subtotal;
            $scope.grandTotal = $scope.subTotal - $scope.supplier.opening_balance;
            console.log($scope.grandTotal)
            if ($scope.subTotal > $scope.supplier.opening_balance) {
                $scope.payment_amount = $scope.grandTotal;
            } else {
                $scope.payment_amount = $scope.grandTotal = 0;
            }
        }



    });
</script>

<script type="text/ng-template" id="row.html">
    <a style="display: block">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span> <br />
      <span>{{match.model.contact}}</span>
  </a>
</script>

<script type="text/ng-template" id="row2.html">
    <a style="display: block">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
      <span class="pull-right" style="margin-left: 20px">{{match.model.price}}</span>
  </a>
</script>