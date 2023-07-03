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

<div class="container" ng-controller="reportController">
    <div class="row">
        <div class="col-sm-3 form-group">
            <label>Supplier's Name</label>
            <input type="hidden" class="form-control" ng-model="supplierId">
            <input ng-if="!toggleForm.searchMode" type="text" class="form-control" ng-model="supplierName" placeholder="Supplier's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.name for address in searchSupplier($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            <input ng-if="toggleForm.searchMode" type="text" class="form-control" ng-model="supplierName" placeholder="Customer's Name" typeahead-on-select="selectSupplier($item)" uib-typeahead="address as address.name for address in searchCustomer($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
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
            <a href="javascript:void(0)" title="Add Fresh Product" ng-click="addFreshProduct()" class="btn btn-danger">Add Fresh Product</a>
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
        $scope.supplierId = "";
        $scope.product = "";
        $scope.shopId = '4';

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
                    opening_balance: $scope.toggleForm.searchMode ? ((parseFloat(res.data.opening_balance || 0) + parseFloat(res.data.debitAmount || 0)) - parseFloat(res.data.creditAmount || 0)) : ((parseFloat(res.data.opening_balance || 0) + parseFloat(res.data.creditAmount || 0) - parseFloat(res.data.debitAmount || 0)))
                };
                $scope.shopId = p.shopId;
                // $scope.items = [];
                $scope.calculateSum();
            })
        }

        $scope.orderData = <?php echo json_encode($order); ?>;
        $scope.list = [];
        $scope.priceList = [];
        $scope.items = $scope.orderData?.order_items?.map(item => ({
            ...item,
            price: parseFloat(item.price),
            quantity: parseFloat(item.quantity),
            qty: parseFloat(item.quantity),
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

        $scope.remove = function(item) {
            if (confirm('Are you sure you want delete?')) {
                var index = $scope.items.indexOf(item);
                $scope.items.splice(index, 1);
                $scope.calculateSum();
            }
        }
        $scope.selectProduct = function(p, r) {
            let currentIndex = 0
            let exists = false;
            $scope.items.map((pro, index) => {
                if (pro.id == p.id) {
                    exists = true;
                    currentIndex = index + 1;
                    pro.qty++;
                }
            })
            $scope.product = "";
            if (!exists) {
                $scope.items.push({
                    ...p,
                    price: parseInt(p.price || 0),
                    pprice: parseInt(p.pprice || 0),
                    qty: 1
                });
                currentIndex = $scope.items.length;
            }

            $timeout(() => {
                $location.hash('product-' + currentIndex);
                $anchorScroll();
                $('#product-' + currentIndex).find('.discount-field').focus();
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

        $scope.searchProduct = function(term) {
            const filteredArray = window.mainList.records.filter(obj => obj.searchString.includes('|' + term + '|') || obj.searchString.includes('|' + term) || obj.searchString.includes(term + '|'));

            const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(obj => obj.searchString.includes(term)) : filteredArray;

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
                subTotal: $scope.subTotal,
                payable: $scope.grandTotal,
                discount: $scope.discount,
                items: $scope.items,
                shopId: $scope.shopId,
                id: '<?php echo $_GET['id']; ?>' || 0,
                status: status || 2,
                account_id: $scope.supplier.account_id,
                payment_amount: $scope.payment_amount,
                supplier_type: $scope.toggleForm.searchMode ? 2 : 1,
                payment_with_credit: $scope.payment_with_credit,
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
                    // $window.location.reload()
                });
        }

        $scope.calculateSum = () => {
            let subtotal = 0;
            $scope.items.map((product) => {
                product.pprice = Math.round(product.price * ((100 - (parseFloat(product.discount || 0))) / 100));
                subtotal += Math.round(product.pprice * product.qty);
                product.total = Math.round(product.pprice * product.qty)

                return Object.assign({}, product);
            })
            $scope.subTotal = subtotal;
            $scope.grandTotal = $scope.payment_amount = $scope.subTotal - $scope.discount;
        }

        $scope.calculatePercent = product => {
            product.discount = Math.round(((1 - (product.pprice / product.price)) * 100).toFixed(1))
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