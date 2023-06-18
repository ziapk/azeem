<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerProducts($ownerId);
$orders = new Orders();
$id = !empty($_GET['id']) ? $_GET['id'] : (!empty($_GET['dup']) ? $_GET['dup'] : 0);
$order = $orders->getOrder($id);
if (!empty($_GET['dup'])) { // remove id from order
    unset($order['order']['id']);
}
echo mainHeader(['page' => 'recipt', 'title' => (!empty($_GET['dup']) ? "Duplicate => " : "") . $order['customer']['full_name']]);
if (in_array($order['order']['status'], [1, 2, 8, 9]) || !empty($_GET['dup'])) {

    $allowCustomer = true;
    // if (in_array($order['order']['status'], [1]) || !empty($_GET['dup'])) {
    //     $allowCustomer = false;
    // }
?>
    <div ng-controller="cartController">
        <div class="container">
            <h5><strong class="text-danger">Running Products</strong> <small class="text-danger"><strong>Click to Add</strong></small></h5>
            <span class="btn-group btn-group-sm form-group">
                <a class="btn btn-default" ng-repeat="l in pinList" href="javascript:void(0)" ng-click="selectProduct(l, 's')">{{l.full_name}}</a>
            </span>
            <table class="table">
                <thead>
                    <tr>
                        <th style="vertical-align: middle">Customer Name</th>
                        <th style="width: 200px">
                            <div class="dropdown-wrapper" style="position: relative;">
                                <input <?php echo empty($allowCustomer) ? 'disabled' : ''; ?> type="text" class="form-control" ng-model="customerName" placeholder="Search Customer" uib-typeahead="address as address.full_name for address in searchCustomer($viewValue)" typeahead-on-select="selectCustomer($item)" ng-model-options="{debounce: 100}" typeahead-template-url="customer.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
                            </div>
                        </th>
                        <th style="vertical-align: middle">
                            <label class="pull-left"><span style="vertical-align: middle"><input type="checkbox" ng-model="show_discount"></span> <span style="vertical-align: middle">Add Discount</span></label>

                            <div class="pull-right">
                                <label><span style="vertical-align: middle">QF</span> <span style="vertical-align: middle; margin-left: 4px;"><input type="checkbox" name="qf" ng-model="qf"><span></label>
                                <label><span style="vertical-align: middle">Search Product</span> <span style="vertical-align: middle; margin-left: 4px"><input type="checkbox" name="focus" ng-model="focus"><span></label>
                            </div>
                        </th>
                        <th width="100">
                            <div class="dropdown-wrapper align-right">
                                <input type="text" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectProduct($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>

            <?php
            $mode = 'edit';

            echo include_once dirname(__FILE__) . '/table.php'; ?>
        </div>

    </div>
    <?php
    echo mainFooter();
    ?>

    <script type="text/javascript">
        app.run(['$anchorScroll', function($anchorScroll) {
            $anchorScroll.yOffset = $('.navbar').height(true, true); // always scroll by 50 extra pixels
        }])
        app.directive('onEnterPress', function() {
            return function(scope, element, attrs) {
                element.bind("keydown keypress", function(event) {
                    if (event.which === 13) {
                        scope.$apply(function() {
                            scope.$eval(attrs.onEnterPress);
                        });
                        $(element).val('')
                        event.preventDefault();
                    }
                });
            };
        });
        app.controller('cartController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $timeout, $location, $anchorScroll) {
            $scope.mainList = <?php echo safe_json_encode($list); ?>;
            $scope.shopId = '<?php echo $userData['shopId']; ?>';
            $scope.list = [];
            $scope.priceList = sessionStorage.getItem('list') && JSON.parse(sessionStorage.getItem('list'));;
            $scope.focus = false;
            $scope.qf = false;

            $scope.data = <?php echo json_encode($order); ?>;
            $scope.customerData = {};
            $scope.summery = $scope.data.order.summery;
            $scope.ref_no = $scope.data.order.ref_no;
            $scope.show_discount = parseInt($scope.data.order.show_discount) ? true : false
            $scope.gst = $scope.data.order.gst;
            $scope.service_charges = $scope.data.order.service_charges;
            $scope.subTotal = $scope.data.order.price;
            $scope.discountPercentValue = 0;
            $scope.grandTotal = $scope.data.order.price + $scope.data.order.discount;
            $scope.discount = parseFloat($scope.data.order.discount);
            $scope.payment_mode = '1';
            const items = [];
            $scope.modes = [];
            $scope.pinList = [];
            $scope.payment_total = 0;

            $scope.calculatePayment = (payWith) => {
                $scope.payment_total = 0;
                $scope.payWith = payWith;
                Object.values(payWith).map(row => {
                    $scope.payment_total += parseFloat(row.amount || 0)
                })
            }
            $scope.modeNames = [];
            $scope.payWith = {};



            $scope.getPinProducts = () => {
                // $scope.loading = true;
                $http.get("<?php echo SITE_URL ?>api/getProducts.php", {
                        params: {
                            page: 1,
                            perPage: 10,
                            search: '',
                            full_name: '',
                            group: '',
                            author: '',
                            board: '',
                            searchBy: '',
                            courceId: '',
                            bookmark: 1
                        }
                    })
                    .then(function(response) {
                        // $scope.loading = false;
                        if (response.status === 200) {
                            $scope.pinList = response.data.records;
                        }
                    })
            }

            $scope.searchCustomer = function(value, onloading) {
                $scope.customerName = value;
                return $http.get("<?php echo SITE_URL ?>api/getCustomer.php?term=" + value)
                    .then(function(response) {
                        $scope.customersList = response.data;
                        if (onloading) {
                            $scope.selectCustomer($scope.customersList[0]);
                        }
                        return response.data
                    });
            }

            $scope.getPinProducts();

            $scope.calculateSum = (c) => {
                const customerData = c || $scope.customerData;
                let subtotal = 0;
                $scope.discountPercentValue = 0;
                $scope.items.map((product) => {
                    let currentRow = null;
                    if (product.discount_type == 2) {
                        product.discount = parseFloat(product.discount_value)
                        subtotal += ((product.price - product.discount) * product.qty);
                    } else if (customerData.discount_array?.length && customerData.discount_array?.filter(r => r.publisher_id == product.publisher_id).length) {
                        const row = customerData.discount_array.find(r => r.publisher_id == product.publisher_id);
                        const price = parseFloat(product.price);
                        product.discount = price * (parseFloat(row.discount_value) / 100);
                        product.discount_percent = row.discount_value + "%";
                        subtotal += ((product.price - product.discount) * product.qty);
                    } else {
                        const price = parseFloat(product.price);
                        if (product.discount_value) {
                            product.discount = price * ((product.discount_value || 0) / 100);
                            $scope.discountPercentValue += (product.discount * product.qty);
                        } else {
                            product.discount_percent = '';
                            product.discount = 0;
                        }
                        subtotal += ((product.price - product.discount) * product.qty);
                    }
                })
                $scope.subTotal = subtotal;
                $scope.payment_amount = $scope.subTotal - $scope.discount;
                $scope.grandTotal = $scope.payment_amount = $scope.payment_amount + Math.round($scope.payment_amount * ($scope.gst / 100)) + Math.round($scope.payment_amount * ($scope.service_charges / 100));
                // $window.sessionStorage.setItem('shopping', JSON.stringify($scope.items));
                const pay = Object.values($scope.payWith);
                pay.map(p => {
                    if (p.is_default == 1) {
                        $scope.payWith[p.id].amount = $scope.payment_amount;
                    } else {
                        $scope.payWith[p.id].amount = 0;
                    }
                });

            }

            $scope.initCheckKeypress = (evt) => {
                var e = evt; // for trans-browser compatibility
                var charCode = e.which || e.keyCode;
                if (charCode === 9) {
                    $('#searchProduct').focus();
                    e.preventDefault();
                }
            }

            $scope.printValue = o => {
                $scope.payment_mode = o.id;
            }
            const shopCart = $scope.data.order_items;
            console.log(' row.discount_type', $scope.data.order_items);

            shopCart.map(function(row) {
                const obj = $scope.mainList.find(function(e) {
                    return e.id == row.product_id
                });
                $scope.discountPercentValue += parseFloat(row.discount);
                $scope.subTotal = parseFloat($scope.subTotal) + parseFloat($scope.discount);
                items.push({
                    ...obj,
                    qty: row.quantity,
                    show: true,
                    description: row.description,
                    discount: row.discount,
                    discount_type: row.discount_type,
                    discount_value: row.discount_type == 2 ? parseFloat(row.discount) : (parseFloat(row.discount || 0) / row.price) * 100
                })
            });
            if ($window.sessionStorage.getItem('shopping')) {
                const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                shopCart.map(function(row) {
                    const obj = $scope.mainList.find(function(e) {
                        return e.id == row.id
                    });
                    items.push({
                        ...obj,
                        qty: row.qty,
                        show: row.show,
                        description: row.description
                    })
                });
                $scope.items = items;
                $timeout(() => {
                    $scope.calculateSum()
                });
            }
            $scope.items = items;

            $scope.addTax = () => {
                $scope.calculateSum();
            }

            $(document).on("ProdcutAdded", function(e) {
                const shopCart = $scope.data.order_items;
                const items = [];
                shopCart.map(function(row) {
                    const obj = $scope.mainList.find(function(e) {
                        return e.id == row.product_id
                    });
                    $scope.discountPercentValue += parseFloat(row.discount);
                    $scope.subTotal = parseFloat($scope.subTotal) + parseFloat($scope.discount);
                    items.push({
                        ...obj,
                        qty: row.quantity,
                        show: true,
                        description: row.description,
                        discount: row.discount,
                        discount_value: row.discount_type == 2 ? row.discount : (parseFloat(row.discount || 0) / row.price) * 100
                    })
                });

                if ($window.sessionStorage.getItem('shopping')) {
                    const storageCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                    storageCart.map(function(row) {
                        const obj = $scope.mainList.find(function(e) {
                            return e.id == row.id
                        });
                        items.push({
                            ...obj,
                            qty: row.qty,
                            show: row.show,
                            description: row.description
                        })
                    });
                    $scope.items = items;
                    $timeout(() => {
                        $scope.calculateSum()
                    });
                }
            });

            $scope.addDiscount = function(val, obj) {
                if (parseFloat(val) > 0) {
                    $scope.discount = (parseFloat($scope.discount) + parseFloat(val));
                } else if (parseFloat($scope.discount) + parseFloat(val) >= 0) {
                    $scope.discount = (parseFloat($scope.discount) + parseFloat(val));
                } else {
                    alert('Negative Discount value must be less than equal to -' + $scope.discount);
                }
                $scope.calculateSum();
                $scope.discountAmount = '';
            }
            $scope.addMoreQty = function(obj, val, e) {
                if (val > 0) {
                    obj.qty = parseFloat(obj.qty) + (parseFloat(val));
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
                if (val > 0) {
                    var v = val / obj.price;
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
            $scope.selectProduct = function(p, sep) {
                let currentIndex = 0
                if (sep) {
                    $scope.items.push({
                        ...p,
                        qty: 1,
                        show: true
                    });
                    currentIndex = $scope.items.length;
                } else {
                    $scope.product = '';
                    $scope.product = null
                    let exists = false;

                    $scope.items.map((pro, index) => {
                        if (pro.id == p.id && !pro.show) {
                            currentIndex = index + 1;
                            exists = true;
                            pro.qty++;
                        }
                    })
                    if (!exists) { // if already not exits in bucket
                        $scope.items.push({
                            ...p,
                            qty: 1
                        });
                        currentIndex = $scope.items.length;
                    }
                }
                $scope.calculateSum();

                // // call $anchorScroll()
                // $scope.product = null;
                $timeout(() => {

                    if ($scope.qf) {
                        $location.hash('product-' + currentIndex);
                        $anchorScroll();
                        if ($('#product-' + currentIndex).find('.input-add-dist').length) {
                            $('#product-' + currentIndex).find('.input-add-dist').focus();
                        } else {
                            $('#product-' + currentIndex).find('.input-qty').focus();
                        }
                    }

                    $scope.product = '';
                }, 200);

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
                const params = {};
                if ($scope.focus === true) {
                    params.searchBy = 'id';
                    $scope.product = '';
                    params.term = parseFloat(term.split('-')[0]);
                    const list = sessionStorage.getItem('list') && JSON.parse(sessionStorage.getItem('list'));
                    const item = list.find(r => r.id == params.term || r.code == params.term || r.barcode == params.term);
                    $scope.product = '';
                    $scope.selectProduct(item);
                    return [];
                } else {
                    params.term = term;
                    return $http.get("<?php echo SITE_URL ?>api/getStores.php", {
                            params,
                            shopId: $scope.shopId
                        })
                        .then(function(response) {

                            // $scope.list = response.data;
                            // $scope.priceList = response.data;
                            return response.data

                        });
                }
            }

            $scope.searchMode = function() {
                return $http.get("<?php echo SITE_URL ?>api/getPaymentModes.php")
                    .then(function(response) {
                        $scope.modes = response.data.records;
                        $scope.modes.forEach(p => {
                            $scope.modeNames[p.id] = p.title;
                            $scope.payWith[p.id] = {
                                ...p,
                                amount: p.is_default == 1 ? $scope.payment_amount : 0
                            }
                        })
                        $scope.data.transactions?.forEach(row => {
                            console.log('payWith', $scope.payWith);
                            $scope.payWith[row.payment_mode].amount = parseFloat(row.amount);
                        })

                        $scope.calculatePayment($scope.payWith);

                        return response.data
                    });
            }

            $scope.searchMode();

            $scope.clearSearch = () => {
                $scope.product = null
                $scope.list = [];
            }

            $scope.selectCustomer = (p, full_name) => {
                $scope.customerName = full_name || p.full_name;
                $scope.customerData = p;
                console.log('p', p, $scope.calculateSum);
                $scope.calculateSum(p);
            }

            $scope.selectCustomer($scope.data.customer, $scope.data.order.customer_name);

            $scope.park = () => {
                console.log('i am called');
                $scope.checkout(1);
            }

            $scope.checkout = function(status) {
                if ($scope.items.length) {
                    $scope.calculatePayment($scope.payWith);
                    $scope.loading = true;
                    $scope.form = {
                        customerId: $scope.customerData && $scope.customerData.id ? $scope.customerData.id : 1,
                        customer_name: $scope.customerName,
                        subTotal: $scope.subTotal,
                        discount: $scope.discount,
                        items: $scope.items.map(({
                            id,
                            description,
                            qty,
                            discount,
                            discount_type,
                            price
                        }) => ({
                            id,
                            description,
                            qty,
                            discount,
                            discount_type,
                            price
                        })),
                        payment_amount: $scope.payment_total,
                        payment_with: $scope.payWith,
                        gst: $scope.gst,
                        service_charges: $scope.service_charges,
                        summery: $scope.summery,
                        show_discount: $scope.show_discount,
                        ref_no: $scope.ref_no,
                        id: $scope.data.order.id,
                        payment_mode: $scope.payment_mode,
                        status: status || 2
                    }

                    $http.post("<?php echo SITE_URL ?>api/placeOrder.php", $httpParamSerializerJQLike($scope.form), {
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        })
                        .then(function(response) {
                            $scope.loading = false;
                            if (status == 1) {
                                alert(response.data.message);
                            } else {
                                window.open("<?php echo SITE_URL; ?>print?id=" + response.data.order.id, "", "width=300,height=300");
                                $scope.items = $scope.list = [];
                                $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
                                // $window.sessionStorage.setItem('shopping', JSON.stringify($scope.items))
                                $window.location.assign('<?php echo SITE_URL ?>')
                                $scope.customersList.length && $scope.selectCustomer($scope.customersList[0]);
                            }
                        }).catch(err => {
                            $scope.loading = false;
                            alert(err.message)
                        });
                }
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
<?php } else {
    echo '<div class="container-fluid"><div class="alert alert-success">This Ordre Has been Processed from Park State</div></div>';
} ?>