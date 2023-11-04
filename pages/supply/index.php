<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$supplyObj = new Supply();
$stores = new Store();
$userId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$ownerStores = $stores->getOwnerStores($userId);

$order = [];
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
if (!empty($id)) {
    $order = $supplyObj->getOrder($id);
}

echo mainHeader();
?>
<style>
    .uib-typeahead-match.active span.text-danger {
        background-color: #fff !important
    }
</style>
<div class="container" ng-controller="reportController">
    <div class="row">
        <div class="col-sm-3 form-group">
            <label>Supplier's Name</label>
            <input type="hidden" class="form-control" ng-model="supplierId">
            <input ng-if="!toggleForm.searchMode" type="text" class="form-control" ng-model="supplierName" placeholder="Supplier's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.name for address in searchSupplier($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0" ng-model-options="{debounce: 500}">
            <input ng-if="toggleForm.searchMode" type="text" class="form-control" ng-model="supplierName" placeholder="Customer's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.name for address in searchCustomer($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0" ng-model-options="{debounce: 500}">
            <label><input type="checkbox" ng-model="toggleForm.searchMode"> Exchange Supply with Customer</label>
            <span>Balance: {{supplier.opening_balance | number}} </span>
        </div>
        <div class="col-sm-3 form-group">
            <label>Ref. No</label>
            <input type="text" class="form-control" ng-model="ref_no" placeholder="Ref. No">
        </div>
        <div class="col-sm-3 form-group">
            <label>Shop Select</label>
            <select class="form-control c-select" ng-model="shopId">
                <?php foreach ($ownerStores as $value) { ?>
                    <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-3">
            <label>&nbsp;</label><br />
            <a href="javascript:void(0)" title="Add Product" ng-click="addFreshProduct()" class="btn btn-danger">Add Product</a>
        </div>
        <div class="col-sm-12 form-group">
            <label>Description</label>
            <textarea type="text" rows="3" class="form-control" ng-model="description" placeholder="Summery"></textarea>
        </div>
    </div>
    <?php
    include_once dirname(__FILE__) . '/table.php' ?>

</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    app.controller('reportController', function($scope, $http, $window, $httpParamSerializerJQLike, $anchorScroll, $timeout, $location) {
        $scope.shopId = '<?php echo $userData['shopId']; ?>';
        $scope.supplierName = "";
        $scope.ref_no = "";
        $scope.description = "";
        $scope.supplierId = "";
        $scope.product = "";
        $scope.shopId = '4';
        $scope.createDemand = false;
        $scope.qf = false;

        $scope.selectSupplier = function(p) {
            $http.get("<?php echo SITE_URL ?>api/getOpeningBalance.php", {
                params: {
                    account_id: p.account_id,
                    type: $scope.toggleForm.searchMode ? 'c' : 's'
                }
            }).then(res => {

                $scope.supplierId = p.id
                $scope.supplierName = p.full_name || p.name
                $scope.supplier = {
                    ...p,
                    opening_balance: parseFloat(res.data.balance || 0)
                };
                $scope.shopId = p.shopId;
                // $scope.items = [];
                $scope.calculateSum();
            })
        }

        $scope.orderData = <?php echo json_encode($order); ?>;
        $scope.list = [];
        $scope.priceList = [];
        $scope.show_bundle = parseInt($scope.orderData?.order?.show_bundle) ? true : false;
        $scope.items = $scope.orderData?.order_items?.map(item => ({
            ...item,
            full_name: item.product_title || item.full_name,
            price: parseFloat(item.price),
            unpack_qty: parseFloat(item.unpack_qty || 0),
            pack_qty: parseFloat(item.pack_qty || 0),
            pack_size: parseFloat(item.pack_size),
            quantity: parseFloat(item.quantity || 0) - parseFloat(item.unpack_qty || 0),
            qty: parseFloat(item.quantity || 0) - parseFloat(item.unpack_qty || 0),
            discount: parseFloat(item.discount)
        })) || [];
        $scope.customerData = {};

        $scope.subTotal = $scope.orderData?.order?.price + $scope.orderData?.order?.discount || 0;
        $scope.grandTotal = $scope.orderData?.order?.price || 0;
        $scope.discount = parseFloat($scope.orderData?.order?.discount || 0);
        $scope.payment_amount = parseFloat($scope.orderData?.order?.payment_amount || 0);
        $scope.payment_with_credit = parseFloat($scope.orderData?.order?.payment_with_credit || 0);
        $scope.payment_mode = '1';
        $scope.toggleForm = {
            searchMode: $scope.orderData?.customer ? true : false
        }
        $scope.ref_no = $scope.orderData?.order?.ref_no;

        if ($scope.orderData?.customer || $scope.orderData?.supplier) {
            $scope.selectSupplier($scope.orderData.customer || $scope.orderData.supplier);
        }

        $scope.newData = {
            barcode: "",
            full_name: "",
            pprice: 0,
            price: 0,
            qty: 1,
        }

        $scope.addFreshProduct = function() {
            // $scope.items.push({...$scope.newData});
            $window.open('<?php echo SITE_URL; ?>pages/product/create.php?headers=1', '_blank', "menubar=0,resizable=1,width=800,height=400");
        }

        $scope.initCheckKeypress = (evt) => {
            var e = evt; // for trans-browser compatibility
            var charCode = e.which || e.keyCode;
            if (charCode === 9) {
                $('#searchProduct').focus();
                e.preventDefault();
            }
        }

        $scope.searchSupplier = function(term, init) {
            $scope.supplierId = ""
            return $http.get("<?php echo SITE_URL ?>api/getSupplier.php", {
                    params: {
                        term
                    }
                })
                .then(function(response) {
                    if (init) {
                        $scope.selectSupplier(response.data[0])
                    }
                    return response.data
                });
        }


        $scope.addDiscount = function(val, obj) {
            if (val > 0) {
                $scope.discount = val;
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

        $scope.remove = function(index) {
            if (confirm('Are you sure you want delete?')) {
                const items = JSON.parse(JSON.stringify($scope.items));
                $scope.items = items.filter((r, i) => i !== index);
                $scope.calculateSum();
            }
        }
        $scope.selectProduct = function(p, r) {
            let currentIndex = 1
            let exists = false;
            $scope.items.map((pro, index) => {
                if (pro.id == p.id && p.product_type != 5) {
                    exists = true;
                    currentIndex = index + 1;
                    pro.qty++;
                }
            })
            $scope.product = "";
            if (!exists) {
                $scope.items.unshift({
                    ...p,
                    pack_size: parseFloat(p.pack_size),
                    price: parseInt(p.price || 0),
                    pprice: parseInt(p.pprice || 0),
                    qty: 1
                });
            }

            $timeout(() => {
                if ($scope.qf && p.product_type != 5) {
                    $anchorScroll.yOffset = 160;
                    $location.hash('product-' + currentIndex);
                    $anchorScroll();
                    $('#product-' + currentIndex).find('.discount-field').focus();
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
            const filteredArray = window.mainList.records.filter(r => r.id == term || r.code == term || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()) || r.searchString.includes(term + '|') || r.searchString.includes('|' + term) || r.searchString.includes('|' + term + '|'));
            const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
            return secondfilteredArray.slice(0, 30);
        }

        $scope.searchCustomer = function(term) {
            return $http.get("<?php echo SITE_URL ?>api/getCustomer.php", {
                    params: {
                        term
                    }
                })
                .then(function(response) {
                    return response.data.map(r => ({
                        ...r,
                        contact: r.phoneNumber,
                        name: r.full_name
                    }));

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
        if (!$scope.orderData) {
            $scope.searchSupplier('', true);
        }

        $scope.clearSearch = () => {
            $scope.product = "";
            $scope.list = [];
        }
        $scope.clearCustomer = () => {
            $scope.customersList = [];
        }

        $scope.park = () => {
            $scope.checkout(1)
        }

        $scope.checkout = function(status) {
            $scope.form = {
                supplierId: $scope.supplierId,
                ref_no: $scope.ref_no,
                description: $scope.description,
                subTotal: $scope.subTotal,
                payable: $scope.grandTotal,
                discount: $scope.discount,
                items: $scope.items.filter(product => product.pprice),
                shopId: $scope.shopId,
                id: '<?php echo $_GET['id']; ?>' || 0,
                status: status || 2,
                account_id: $scope.supplier.account_id,
                payment_amount: $scope.payment_amount,
                supplier_type: $scope.toggleForm.searchMode ? 2 : 1,
                payment_with_credit: $scope.payment_with_credit,
                createDemand: $scope.createDemand,
            }


            $http.post("<?php echo SITE_URL ?>api/placeSupply.php", $httpParamSerializerJQLike($scope.form), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    $scope.items = $scope.list = [];
                    $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = $scope.payment_with_credit = 0;
                    alert(response.data.message);
                    $window.location.reload()
                });
        }

        $scope.calculateSum = (price) => {
            let subtotal = 0;
            let counter = 1;
            let forCounter = 1;
            for (const product of $scope.items) {
                product.price = product.price || 0;
                if (product.product_type != 5) {
                    product.srno = counter;
                    counter++;
                } else {
                    product.srno = forCounter;
                    forCounter++;
                    product.price = product.pprice;
                }

                if (price) {
                    product.pprice = parseFloat((product.price * ((100 - (parseFloat(product.discount || 0))) / 100)).toFixed(2));
                }
                if (product.pack_size && $scope.show_bundle) {
                    product.qty = (product.pack_size || 1) * product.pack_qty;
                }

                const qty = $scope.show_bundle ? (product.qty + (product.unpack_qty || 0)) : product.qty;

                if (!$scope.show_bundle) {
                    product.unpack_qty = 0;
                    product.pack_qty = 0;
                    product.pack_size = 0;
                }

                subtotal += parseFloat((product.pprice * qty).toFixed(2));
                product.total = parseFloat((product.pprice * qty).toFixed(2))
            }
            $scope.subTotal = subtotal;
            $scope.grandTotal = $scope.payment_amount = $scope.subTotal - $scope.discount;
        }

        $scope.calculateSum(true);

        $scope.calculatePercent = product => {
            product.discount = parseFloat(((1 - (product.pprice / product.price)) * 100).toFixed(2))
            $scope.calculateSum()
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
                }
                return false;
            }
        });



    });
</script>

<script type="text/ng-template" id="row.html">
    <a style="display: block">
      <span ng-bind-html="match.model.name | uibTypeaheadHighlight:query"></span> <br />
      <span>{{match.model.contact}}</span>
  </a>
</script>

<script type="text/ng-template" id="row2.html">
    <a style="display: flex; align-items: center">
        <span style="margin-right: auto" class="{{match.model.code ? 'text-danger' : ''}}" ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
        <span ng-if="match.model.pack_size" class="label label-primary" style="font-size: 14px">{{match.model.pack_size}}B</span><span ng-if="match.model.pack_size">|</span><span class="label label-success" style="font-size: 14px">{{match.model.qty}}</span> | <span class="label label-danger" style="font-size: 14px">{{match.model.price}}</span>
    </a>
</script>