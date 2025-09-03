<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$stores = new Store();
if ($userData['role'] == 'owner') {
    $ownerStores = $stores->getOwnerStores($userData['id']);
} else {
    $ownerStores[] = $shop;
}

$id = $_GET['id'];
$return = $_GET['return'];
$orders = new Orders();
if (!empty($return)) {
    $order = $orders->getReturnOrder($return);
} else {
    $order = $orders->getOrder($id);
}

echo mainHeader(['page' => 'sale_returns']);
?>

<div class="container" ng-controller="reportController">
    <div class="row">
        <div class="col-sm-3 form-group">
            <label ng-if="is_supplier == 1">Customer's Name</label>
            <label ng-if="is_supplier == 2">Supplier's Name</label>
            <input type="hidden" class="form-control" ng-model="supplierId">
            <input ng-if="is_supplier == 1" type="text" class="form-control" ng-model="supplierName" placeholder="Customer's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.full_name for address in searchCustomer($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            <input ng-if="is_supplier == 2" type="text" class="form-control" ng-model="supplierName" placeholder="Supplier's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.full_name for address in searchSupplier($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            <label><input type="checkbox" ng-model="return_type" ng-true-value="2" ng-false-value="1" ng-change="resetUsers(return_type)"> Purchase Return </label>
            <label><input type="checkbox" ng-model="is_supplier" ng-true-value="2" ng-false-value="1" ng-change="resetUsers(return_type)"> Is Supplier </label>
            <label><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="show_bundle"></span> <span style="vertical-align: middle">Bundles</span></label>
        </div>
        <div class="col-sm-3 form-group">
            <label>Ref. No</label>
            <input type="text" class="form-control" ng-model="ref_no" placeholder="Ref. No">
        </div>
        <div class="col-sm-3 form-group">
            <label>Shop Select</label>
            <select class="form-control c-select" ng-model="shopId" ng-change="resetFields()">
                <?php foreach ($ownerStores as $value) { ?>
                    <option ng-value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-12 form-group">
            <label>Description</label>
            <textarea type="text" rows="3" class="form-control" ng-model="summery" placeholder="Summery"></textarea>
        </div>
    </div>
    <div>
        <button type="button" class="btn btn-danger btn-xs" ng-click="removeSelected(true)">Remove Selected Only</button>
        <button type="button" class="btn btn-primary btn-xs" ng-click="removeSelected(false)">Remove Un-Selected Only</button>
    </div>
    <div>
        <table class="table">
            <thead class="sticky">
                <tr style="background: #fff">
                    <th>Sr.# <input type="checkbox" ng-model="selectAll" ng-change="changeSelectAll(selectAll)" /></th>
                    <th width="100">Product Id</th>
                    <th>Product Name</th>
                    <th width="100">Discount</th>
                    <th></th>
                    <th width="100">Price</th>
                    <th width="100">Qty</th>
                    <th>Total</th>
                    <th></th>
                </tr>
                <tr style="background: #fff">
                    <td colspan="7"><input type="text" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Product to add" typeahead-on-select="selectProduct($item, row)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="product-format.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1" ng-model-options="{debounce: 500}" class="form-control" ng-model="row.product_name" /></td>
                    <td><input type="checkbox" ng-model="sep"> SEP</td>
                    <td><input type="checkbox" ng-model="qf"> Qty</td>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat-start="row in items track by $index" id="product-{{$index + 1}}">
                    <td><input type="checkbox" ng-model="row.select" />  {{$index + 1}}</td>
                    <td><input type="text" class="form-control" ng-model="row.product_id" /></td>
                    <td>
                        <input type="text" class="form-control" ng-model="row.full_name" placeholder="Product title" />
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="number" class="form-control input-add-dist" ng-model="row.discount_value" ng-change="calculateSum()" style="padding-right: 6px">
                            <span class="dropdown input-group-btn">
                                <button class="btn btn-default" style="padding-inline: 8px" ng-click="row.discount_type = (row.discount_type == 1 ? 2 : 1); calculateSum();">{{row.discount_type == 2 ? 'FIX' : '%'}}</button>
                                <!-- data-toggle="dropdown" -->
                                <!-- <button class="dropdown-toggle btn btn-default" data-toggle="dropdown" style="padding-inline: 8px">{{row.discount_type == 2 ? 'FIX' : '%'}}</button>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:void(0)" ng-click="row.discount_type = 1; calculateSum()">%</a></li>
                                <li><a href="javascript:void(0)" ng-click="row.discount_type = 2; calculateSum()">Fix</a></li>
                            </ul> -->
                            </span>
                        </div>
                    </td>
                    <td>
                        <span ng-if="row.discount">
                            {{row.discount_percent ? row.discount_percent : ''}}
                            <del class="text-danger">{{row.price | number: 2}}</del> / </span>
                        <span class="text-success">{{(row.price - row.discount) | number: 2}}</span>
                    </td>

                    <td width="100"><input type="number" class="form-control" ng-model="row.price" ng-change="calculateSum()" /></td>
                    <td width="100">
                        <input type="number" class="form-control" ng-change="isValid(row,  calculateSum)" max="row.maxQty" ng-model="row.qty" ng-keydown="initCheckKeypress($event)" />
                    </td>
                    <td width="60" style="font-weight: bold;" class="text-right">{{((row.price || 0) - (row.discount || 0)) * (row.qty || 0)}}</td>
                    <td width="60"><a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(row)">Delete</a></td>
                </tr>
                <tr ng-repeat-end="row in items track by $index" ng-if="show_bundle">
                    <td colspan="9">
                        <table style="margin-left: auto;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="100px" style="padding: 8px" class="text-right">Bundles</td>
                                <td colspan="{{show_discount ? 2 : 1}}">
                                    <input type="number" class="form-control" ng-model="row.pack_qty" placeholder="No of Bundles" ng-change="calculateSum()" />
                                </td>
                                <td class="text-right" style="padding: 8px">Bundle Size</td>
                                <td>
                                    <input type="number" class="form-control" ng-model="row.pack_size" placeholder="Products In Bundle" ng-change="calculateSum()" />
                                </td>
                                <td class="text-right" style="padding: 8px">Ex. Items</td>
                                <td>
                                    <input type="number" class="form-control" ng-model="row.unpack_qty" placeholder="Extra Products" ng-change="calculateSum()" />
                                </td>
                                <td style="padding: 8px">Total Qty</td>
                                <td style="padding: 8px; font-weight: bold; font-size: 1.5em">{{row.qty + row.unpack_qty}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr>
                    <th rowspan="10"></th>
                    <th class="text-right" colspan="7">Sub Total</th>
                    <th>{{subTotal}}</th>
                </tr>
                <tr>
                    <td class="text-right" colspan="7">Given Discount</td>
                    <td><strong>{{givenDiscount | number: 2}}</strong></td>
                </tr>
                <tr ng-if="order.order.paid_amount">
                    <th class="text-right" colspan="7">Paid Total</th>
                    <th>{{order.order.paid_amount}}</th>
                </tr>
                <tr>
                    <th class="text-right" colspan="7">Discount</td>
                    <td width="150"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
                </tr>
                <tr>
                    <td class="text-right" colspan="7">Additional Discount</td>
                    <td><strong>{{discount | number: 2}}</strong></td>
                </tr>
                <tr ng-if="grandTotal">
                    <th class="text-right" colspan="7">Grand Total</th>
                    <th>{{grandTotal}}</th>
                </tr>
                <tr ng-if="grandTotal">
                    <th class="text-right" colspan="7">Pay Amount</th>
                    <th width="200"><input type="number" ng-model="payment_amount" ng-change="calcBalanc(payment_amount)" class="form-control"></th>
                </tr>
                <tr ng-if="grandTotal">
                    <th class="text-right" colspan="7">Closing Balance</th>
                    <th width="200">{{ payment_amount - grandTotal }}</th>
                </tr>
            </tbody>
            <tbody>
                <tr>
                    <th colspan="2">
                        <?php if (!empty($order['order']['main_shop_rid']) && $order['order']['main_shop_rid'] == $userData['shopId'] && $userData['role'] === 'owner') { ?>
                            <a href="#" class="btn btn-danger" ng-click="checkout(2)"> Approve Return </a>
                        <?php } 
                        if ($userData['role'] === 'owner') { ?>
                        <a href="#" class="btn btn-success pull-left" ng-click="checkout()">Park for Now</a>
                        <?php } ?>
                    </th>
                    <th colspan="7" class="text-right">
                        <div class="btn-group">
                            <label class="btn btn-default" ng-repeat="li in modes">
                                <input type="radio" name="mode" ng-model="payment_mode" ng-value="li.id" ng-change="printValue(li)">
                                {{li.title}}
                            </label>
                        </div>
                        <?php 
                        if (!empty($order['order']['main_shop_rid']) && $order['order']['main_shop_rid'] == $userData['shopId'] && $userData['role'] === 'owner') { ?>
                            <a href="#" class="btn btn-success" ng-click="checkout()"> Save</a>
                        <?php } elseif ($userData['role'] === 'owner') { ?>
                            
                            <a href="#" class="btn btn-primary" ng-click="checkout(2)"> Return Submit</a>
                        <?php } else { ?>
                            <a href="#" class="btn btn-success" ng-click="checkout()"> Save</a>
                        <?php } ?>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
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
    app.controller('reportController', function($scope, $http, $window, $httpParamSerializerJQLike, $timeout, $anchorScroll, $location) {
        $scope.currentShopId = '<?php echo $shop['id']; ?>';
        $scope.ref_no = "";
        $scope.supplierId = "";
        $scope.summery = "";
        $scope.product = "";
        $scope.sep = false;
        $scope.selectAll = false;
        $scope.order = <?php echo json_encode($order) ?>;
        $scope.returnOrder = <?php echo json_encode($return) ?>;
        $scope.is_supplier = parseInt($scope.order?.order?.is_supplier || 1);
        $scope.return_type = parseInt($scope.order?.order?.return_type || 1);
        $scope.show_bundle = parseInt($scope.order?.order?.show_bundle) ? true : false;
        $scope.supplierName = $scope.order?.order?.customer_name || '';
        $scope.supplierId = $scope.order?.order?.customer_id || '';
        $scope.shopId = $scope.order?.order?.shopId ? parseInt($scope.order.order.shopId) : 0;
        $scope.LinkForMainShop = '<?php echo $_GET['LinkForMainShop']; ?>';

         $scope.changeSelectAll = (select) => {
            
            $scope.items.map(row => {
                row.select  = select;
            })

        }

        
         $scope.removeSelected = (selected) => {
            $scope.items = $scope.items.filter(row => {
                return row.select != selected;
            });

            $scope.calculateSum();

        }



        $scope.items = $scope.order?.order_items?.map(r => ({
            ...r,
            qty: parseFloat(r.quantity),
            pprice: parseFloat(r.price.toString()),
            price: parseFloat(r.price.toString()),
            unpack_qty: parseFloat(r.unpack_qty.toString()),
            pack_qty: parseFloat(r.pack_qty.toString()),
            pack_size: parseFloat(r.pack_size.toString()),
            maxQty: r.quantity.toString() || "1",
            discount_value: parseInt(r.discount_value || r.discount),
            discount_type: parseInt(r.discount_type) || 2,
            full_name: r.product_title,
            select: false,
        })) || [];

        $scope.list = [];
        $scope.priceList = [];
        $scope.customerData = {};
        $scope.subTotal = 0;
        $scope.grandTotal = 0;
        $scope.givenDiscount = parseFloat($scope?.order?.order?.discount || 0);
        $scope.payment_amount = parseFloat($scope?.order?.order?.paid || 0);
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
            select: false,
        }

        $scope.resetUsers = (return_type) => {
            $scope.supplierName = "";
            $scope.supplierId = "";
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
            $scope.supplierName = term;
            return $http.get("<?php echo SITE_URL ?>api/getSupplier.php", {
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
                    if ($scope.order.customer) {
                        $scope.selectSupplier($scope.order.customer);
                    }
                    return response.data
                });
        }
        $scope.searchCustomer = function(term, init) {
            $scope.supplierName = term;
            // $scope.supplierId = ""
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
                    if (init && $scope.order.customer) {
                        $scope.selectSupplier($scope.order.customer);
                    }
                    return response.data
                });
        }

        $scope.isValid = (row, cb) => {
            // console.log(row.qty, row.maxQty)
            // if (parseFloat(row.qty) < parseFloat(row.maxQty)) {
            //     cb();
            // } else {
            // row.qty = parseFloat(row.maxQty);
            // }
            cb()
        }

        $scope.selectSupplier = function(p) {
            console.log('p', p);
            $http.get("<?php echo SITE_URL ?>api/getOpeningBalance.php", {
                params: {
                    account_id: p.account_id,
                    type: $scope.is_supplier == 1 ? 'c' : 's'
                }
            }).then(res => {
                $scope.supplierId = p.id
                $scope.supplierName =  p?.full_name || p?.name || $scope.order?.order?.customer_name
                $scope.supplier = {
                    ...p,
                    full_name: p.full_name || p.name,
                    opening_balance: ((parseFloat(res.data.opening_balance || 0) + parseFloat(res.data.debitAmount || 0)) - parseFloat(res.data.creditAmount || 0))
                };
                $scope.shopId = parseFloat($scope.shopId || p.shopId);
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
            if (!p.discount_type) {
                p.discount_type = 1
            }
            let currentIndex = 1
            let exists = false;
            if (!$scope.sep) {
                $scope.items.map((pro, index) => {
                    if (pro.product_id == p.id) {
                        exists = true;
                        pro.product_id = p.id;
                        pro.qty++;
                        currentIndex = index + 1;
                    }
                })
            }
            $scope.product = "";
            if (!exists) {
                $scope.items.push({
                    ...p,
                    product_id: p.id,
                    price: parseInt(p.price || 0),
                    pprice: parseInt(p.pprice || 0),
                    qty: parseFloat(p.maxQty) || 1,
                    select: false
                });
                currentIndex = $scope.items.length;
            }

            $timeout(() => {
                if ($scope.qf) {
                    $anchorScroll.yOffset = 160;
                    $location.hash('product-' + currentIndex);
                    $anchorScroll();
                    $('#product-' + currentIndex).find('.input-add-dist').focus();
                }
            }, 200);

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

        $scope.partialSearch = (name, query) => {
            const lowerQuery = query.toLowerCase();
            const lowerName = name.toLowerCase();
            let queryIndex = 0;
            for (let i = 0; i < lowerName.length; i++) {
                if (lowerName[i] === lowerQuery[queryIndex]) {
                    queryIndex++;
                    if (queryIndex === lowerQuery.length) return true;
                }
            }
            return false;
        }

        $scope.searchProduct = function(term) {
            if ($scope.shopId == $scope.currentShopId) {
                const filteredArray = window.mainList.records.filter(r => r.is_active == 1).filter(r => r.id == term || r.code == term || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()) || r.searchString.includes(term + '|') || r.searchString.includes('|' + term) || r.searchString.includes('|' + term + '|'));
                const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(r => r.is_active == 1).filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                return secondfilteredArray.slice(0, 30);
            } else {
                return $http.get("<?php echo SITE_URL ?>api/getProducts.php", {
                        params: {
                            search: term,
                            perPage: 30,
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
        }

        $scope.searchMode = function() {
            return $http.get("<?php echo SITE_URL ?>api/getPaymentModes.php")
                .then(function(response) {
                    $scope.modes = response.data.records;
                    return response.data
                });
        }

        $scope.searchMode();

        $scope.searchCustomer('', true);

        $scope.clearSearch = () => {
            $scope.product = "";
            $scope.list = [];
        }

        $scope.calcBalanc = (a) => {
            $scope.payment_amount = a;
        }

        $scope.checkout = function(flag) {
            $scope.form = {
                supplierId: $scope.supplierId,
                supplierName: $scope.supplierName,
                summery: $scope.summery,
                ref_no: $scope.ref_no,
                subTotal: $scope.subTotal,
                discount: $scope.discount,
                return_type: $scope.return_type,
                is_supplier: $scope.is_supplier,
                givenDiscount: $scope.givenDiscount,
                order_id: $scope.returnOrder ? $scope.order.order.order_id : $scope.order.order.id,
                items: $scope.items.map(({
                    product_id,
                    qty,
                    unpack_qty,
                    pack_qty,
                    pack_size,
                    price,
                    discount,
                    discount_type,
                    discount_value
                }) => ({
                    product_id,
                    qty,
                    unpack_qty,
                    pack_qty,
                    pack_size,
                    price,
                    discount,
                    discount_type,
                    discount_value
                })),
                show_bundle: $scope.show_bundle ? 1 : 0,
                shopId: $scope.shopId,
                grandTotal: $scope.grandTotal,
                payment_amount: $scope.payment_amount,
                opening_balance: $scope.supplier.opening_balance,
                returnOrder: $scope.returnOrder,
                flag: flag || 1,
                LinkForMainShop: $scope.LinkForMainShop
            }



            $http.post("<?php echo SITE_URL ?>api/placeCustomerReturn.php", $httpParamSerializerJQLike($scope.form), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    window.open("<?php echo SITE_URL; ?>print/return.php?&detail=true&largeView=large&id=" + response.data.order.id, "", "width=600,height=900");
                    $scope.items = $scope.list = [];
                    $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
                    alert(response.data.message);
                    $window.location.reload()
                });
        }

        $scope.calculateSum = () => {
            let subtotal = 0;
            // $scope.discount = 0;
            $scope.items.map((product) => {
                if (product.pack_qty && $scope.show_bundle) {
                    product.qty = (product.pack_size || 1) * product.pack_qty;
                }
                const qty = $scope.show_bundle ? (product.qty + (product.unpack_qty || 0)) : product.qty;
                if (!$scope.show_bundle) {
                    product.unpack_qty = 0;
                    product.pack_qty = 0;
                    product.pack_size = 0;
                }
                if (product.discount_type == 2) {
                    product.discount = product.discount_value
                    subtotal += ((product.price - product.discount) * qty);
                } else {
                    const price = parseFloat(product.price);
                    if (product.discount_value) {
                        product.discount = price * ((product.discount_value || 0) / 100);
                        $scope.discountPercentValue += (product.discount * qty);
                    } else {
                        product.discount_percent = '';
                        product.discount = 0;
                    }
                    subtotal += ((product.price - product.discount) * qty);
                }
                // subtotal += (product.price * product.qty);
                // $scope.discount += (product.discount * product.qty);
            })
            $scope.subTotal = subtotal;
            $scope.grandTotal = $scope.subTotal - $scope.discount - $scope.givenDiscount;
            console.log($scope.grandTotal)
            $scope.payment_amount = $scope.grandTotal;
        }

        $('body').on('keydown', 'input', function(e) {
            if (e.key === "Enter") {
                var self = $(this),
                    form = self.parents('tr'),
                    focusable, next;
                focusable = form.find('input[type=text], input[type=number]').filter(':visible');
                next = focusable.eq(focusable.index(this) + 1);
                if (next.length) {
                    next.focus();
                } else {
                    $('#searchProduct').focus()
                    $anchorScroll.yOffset = 0;
                    $location.hash('');
                    $anchorScroll();
                }
                return false;
            }
        });

    });
</script>

<script type="text/ng-template" id="row.html">
    <a style="display: block">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span> <br />
      <span>{{match.model.contact}}</span>
  </a>
</script>