<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$orders = new Orders();
$id = !empty($_GET['id']) ? $_GET['id'] : (!empty($_GET['dup']) ? $_GET['dup'] : 0);
$order = $orders->getOrder($id);
if (!empty($_GET['dup'])) { // remove id from order
    unset($order['order']['id']);
}



$publisherObj = new Publishers();
$isOwner = $userData['role'] == 'owner';
$userId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$publishers = $publisherObj->getPublishers($userId);

echo mainHeader(['page' => 'recipt', 'title' => (!empty($_GET['dup']) ? "Duplicate => " : "") . $order['order']['customer_name'], 'hideSidebar' => $userData['role'] == 'shopkeeper' ? false : true]);
if (in_array($order['order']['status'], [1, 2, 8, 9]) || !empty($_GET['dup'])) {

    $allowCustomer = true;
    // if (in_array($order['order']['status'], [1]) || !empty($_GET['dup'])) {
    //     $allowCustomer = false;
    // }
?>
    <style>
        .uib-typeahead-match.active span.text-danger {
            background-color: #fff !important
        }

        .text-bold {
            font-weight: bold;
        }

        .dropdown-height .dropdown-menu {
            max-height: 300px;
            overflow: auto;
        }
    </style>
    <div ng-controller="cartController">
        <div class="container">
            <!-- <div class="form-group" ng-if="pinPrograms && key.toLowerCase().includes('uniform')" ng-repeat="(key, li) in pinPrograms">
                <a href="javascript:void(0)">{{key}}</a>
                >
                <span ng-repeat="(k, l) in li">
                    <a href="javascript:void(0)" ng-class="{'text-danger text-bold': selectedSize == k }">{{k}}</a>
                    > <span ng-repeat="(dd, i) in l">
                        <a class="btn btn-xs" ng-class="{'btn-danger': dd == 'Girls', 'btn-primary': dd == 'Boys'}" style="border-radius: 4px" href="#" ng-click="setSelectedProgramItems(i.items, dd, k)">{{dd}}</a>
                        <a href="javascript:void(0)" class="btn btn-default  btn-xs" ng-click="addAllBooks(i.items)">All</a>
                    </span>
                </span>
            </div>
            <div class="form-group" ng-if="selectedProgramItems.length">
                <span ng-repeat="row in selectedProgramItems" style="display: inline-block; padding: 2px"><a href="javascript:void(0)" class="btn" style="border-radius: 4px; padding: 2px 6px" ng-class="{'btn-danger': selectedUniform == 'Girls', 'btn-primary': selectedUniform == 'Boys', 'btn-default': selectedUniform && !['Boys', 'Girls'].includes(selectedUniform)}" ng-click="selectProduct(row)"><strong>{{row.full_name}}</strong> <span class="badge badge-warning">{{row.board}}</span></a></span>
            </div> -->
            <h5><strong class="text-danger">Running Products</strong> <small class="text-danger"><strong>Click to Add</strong></small></h5>
            <span class="btn-group btn-group-sm form-group dropdown">
                <a class="dropdown-item btn btn-primary" href="#" data-toggle="dropdown"><span class="nav-menu-icon" style="margin-right: 6px"></span><span class="nav-menu-text">Syllabus</span>
                    <div class="fa fa-caret-down"></div>
                </a>
                <ul class="dropdown-menu" style="min-width: 250px; max-height: 300px;">
                    <li ng-if="pinPrograms" ng-repeat="(key, li) in pinPrograms">
                        <a style="padding: 3px 6px" class="dropdown-item" href="#"><span class="nav-menu-text" style="white-space: normal">{{key}}</span></a>
                        <ul class="dropdown-menu dropdown-submenu" style="min-width: 250px; max-height: 300px;">
                            <li ng-repeat="(k, l) in li">
                                <a href="javascript:void(0)" ng-class="{'text-danger text-bold': selectedSize == k }">{{k}}</a>
                                <ul class="dropdown-menu dropdown-submenu" style="min-width: 250px;">
                                    <li ng-repeat="(dd, i) in l">
                                        <a href="javascript:void(0)" ng-if="!key.toLowerCase().includes('uniform')" ng-click="addAllBooks(i.items)">{{dd}}</a>
                                        <a href="javascript:void(0)" ng-if="key.toLowerCase().includes('uniform')">{{dd}}</a>
                                        <ul class="dropdown-menu dropdown-submenu" style="min-width: 250px;" ng-if="key.toLowerCase().includes('uniform')">
                                            <li ng-repeat="book in i.items">
                                                <a href="javascript:void(0)" ng-click="selectProduct(book, 's', undefined, $event)">{{book.full_name}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
                <a class="btn btn-default" ng-repeat="l in pinList" href="javascript:void(0)" ng-click="selectProduct(l, 's')">{{l.full_name}}</a>
            </span>
            <div>
                <a href="#" class="btn btn-primary" ng-click="checkout()"><img width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/001-checkout.svg" alt="" /> Checkout</a>
            </div>
            <div class="clearfix" id="dummyHeight"></div>
            <table class="table visible-xs">
                <tr>
                    <td>
                        <input type="text" class="form-control" ng-model="customerName" placeholder="Search Customer" uib-typeahead="address as address.full_name for address in searchCustomer($viewValue)" typeahead-on-select="selectCustomer($item)" ng-model-options="{debounce: 100}" typeahead-template-url="customer.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label><span style="vertical-align: middle"><input type="checkbox" ng-model="show_discount"></span> <span style="vertical-align: middle">Disc</span></label>
                        <label style="margin-left: 10px"><span style="vertical-align: middle;"><input type="checkbox" ng-model="sep"></span> <span style="vertical-align: middle">SEP</span></label>
                        <label style="margin-left: 10px"><span style="vertical-align: middle"><input type="checkbox" ng-model="showDescription"></span> <span style="vertical-align: middle">DESC</span></label>
                        <label style="margin-left: 10px"><span style="vertical-align: middle"><input type="checkbox" ng-model="wsp"></span> <span style="vertical-align: middle">WSP</span></label>
                        <label style="margin-left: 10px"><span style="vertical-align: middle"><input type="checkbox" name="qf" ng-model="qf"></span> <span style="vertical-align: middle">QF</span></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div>
                            <div class="input-group">
                                <input type="text" autocomplete="off" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectProduct($item)" ng-model-options="{debounce: 100}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="productCode ? 0 : 1">
                                <span class="input-group-btn" style="width: 40%">
                                    <input type="text" ng-model="productCode" class="form-control" id="exampleInputAmount" placeholder="CODE">
                                </span>
                                <span class="input-group-addon" style="width: 40px">
                                    <label><span style="vertical-align: middle">I-A</span> <span style="vertical-align: middle; margin-left: 4px;"><input type="checkbox" name="is_active" ng-model="is_active"></span></label>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <table class="table table-striped visible-xs">
                <thead>
                    <tr>
                        <?php echo include_once dirname(__FILE__) . '/table-sm.php'; ?>
                    </tr>
                </thead>
            </table>
            <table class="table table-striped recipt-table hidden-xs">
                <thead id="fixme">
                    <th colspan="8">
                        <table class="table" style="box-shadow: none; margin: 0">

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
                                        <label class="pull-left"><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="show_bundle" ng-change="calculateSum()"></span> <span style="vertical-align: middle">Bundles</span></label>
                                        <label class="pull-left"><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="sep" ng-change="calculateSum()"></span> <span style="vertical-align: middle">SEP</span></label>
                                        <label class="pull-left"><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="showDescription"></span> <span style="vertical-align: middle">DESC</span></label>
                                        <?php if ($isOwner) { ?>
                                            <label class="pull-left"><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="wsp"></span> <span style="vertical-align: middle">WSP</span></label>
                                        <?php } ?>
                                        
                                        <div class="pull-right">
                                            <label><span style="vertical-align: middle; margin-right: 4px;"><input type="checkbox" name="qf" ng-model="qf"></span> <span style="vertical-align: middle; display: inline-block">QF</span></label>
                                            <label><span style="vertical-align: middle; margin-right: 4px"><input type="checkbox" name="focus" ng-model="focus"></span> <span style="vertical-align: middle; display: inline-block">SM</span></label>
                                            <label><span style="vertical-align: middle; margin-right: 4px"><input type="checkbox" name="serverSide" ng-model="serverSide" ng-change="loadProduct()"></span> <span style="vertical-align: middle; display: inline-block">Search Product</span></label>
                                        </div>
                                    </th>
                                    <th width="100">
                                        <div class="dropdown-wrapper align-right dropdown-height">
                                            <div class="input-group">
                                                <input type="text" autocomplete="off" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectProduct($item)" ng-model-options="{debounce: 100}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="productCode ? 0 : 1">
                                                <span class="input-group-btn" style="width: 90px">
                                                    <input type="text" ng-model="productCode" class="form-control" id="exampleInputAmount" placeholder="CODE">
                                                </span>
                                                <span class="input-group-addon" style="width: 40px">
                                                    <label><span style="vertical-align: middle">I-A</span> <span style="vertical-align: middle; margin-left: 4px;"><input type="checkbox" name="is_active" ng-model="is_active"></span></label>
                                                </span>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </th>

                    <?php

                    echo include_once dirname(__FILE__) . '/table.php'; ?>
        </div>

    </div>
    <?php
    echo mainFooter();
    ?>

    <script type="text/javascript">
        var element = $('#fixme');
        var fixmeTop = element.offset().top;
        var dummyHeight = $('#dummyHeight');
        var offset = $('.navbar').height();
        $(window).on('load scroll', function() { // assign scroll event listener
            var fixedHeight = element.height()


            var currentScroll = $(window).scrollTop(); // get current position

            if ((currentScroll + offset) >= fixmeTop) { // apply position: fixed if you
                element.css({ // scroll to that element or below it
                    top: offset,
                });
                element.addClass('navbar-fixed-top')
                dummyHeight.height(fixedHeight)
            } else { // apply position: static
                element.removeClass('navbar-fixed-top');
                dummyHeight.height(0)
            }

        });
        app.run(['$anchorScroll', function($anchorScroll) {
            $anchorScroll.yOffset = 200; // always scroll by 50 extra pixels
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
        app.controller('cartController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $timeout, $location, $anchorScroll, toaster) {
            $scope.mainList = $window.mainList?.records || [];
            $scope.shopId = '<?php echo $userData['shopId']; ?>';
            $scope.list = [];
            $scope.focus = false;
            $scope.qf = false;
            $scope.sep = false;
            $scope.wsp = false;
            $scope.overide = false;
            $scope.productCode = "";

            $scope.selectedList = {};
            $scope.indexes = [];
            $scope.setList = list => {
                $scope.indexes = [];
                Object.keys(list).forEach((key) => {
                    if (list[key]) {
                        $scope.indexes.push(parseInt(key));
                    }
                });
            }

            $scope.deleteAll = (indexes, list) => {
                if (confirm('Are you sure you want delete?')) {
                    if (indexes?.length) {
                        $scope.items = list.reduce((acc, value, index) => {
                            if (!indexes.includes(value.srno)) {
                                acc.push(value);
                            }
                            return acc;
                        }, []);
                    } else {
                        $scope.items = [];
                    }

                    $scope.indexes = [];
                    $scope.selectedList = {};
                    $scope.calculateSum();
                }

            }
            $scope.inActiveAll = (indexes, list) => {
                if (confirm('Are you sure you want in-active?')) {
                    if (indexes?.length) {
                        const removableItems = [];
                        $scope.items = list.reduce((acc, value, index) => {
                            if (!indexes.includes(value.srno)) {
                                acc.push(value);
                            } else {
                                $scope.setInactive(value, 0);
                            }
                            return acc;
                        }, []);
                    }
                    $scope.indexes = [];
                    $scope.selectedList = {};
                    $scope.calculateSum();
                }
            }
            $scope.activeAll = (indexes, list) => {
                if (confirm('Are you sure you want active?')) {
                    if (indexes?.length) {
                        const removableItems = [];
                        $scope.items = list.reduce((acc, value, index) => {
                            if (!indexes.includes(value.srno)) {
                                acc.push(value);
                            } else {
                                $scope.setInactive(value, 1);
                                acc.push({
                                    ...value,
                                    is_active: 1
                                });
                            }
                            return acc;
                        }, []);
                    }
                    $scope.indexes = [];
                    $scope.selectedList = {};
                    $scope.calculateSum();
                }
            }

            $scope.setInactive = function(item, action) {
                $http.get("<?php echo SITE_URL ?>api/setInactive.php", {
                    params: {
                        id: item.id,
                        action
                    }
                })
            }



            $scope.data = <?php echo json_encode($order); ?>;
            $scope.customerData = {};
            $scope.summery = $scope.data.order.summery;
            $scope.ref_no = $scope.data.order.ref_no;
            $scope.show_discount = parseInt($scope.data.order.show_discount) ? true : false;
            $scope.show_bundle = parseInt($scope.data.order.show_bundle) ? true : false;
            $scope.total_discount_type = 2;
            $scope.is_active = false;
            $scope.showDescription = false;
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
            $scope.status_id = $scope.data.order.status_id;
            $scope.expected_delivery_date = $scope.data.order.expected_delivery_date;

            $scope.addAllBooks = (books, key) => {
                books?.forEach(book => {
                    $scope.selectProduct(book, false, true);
                });
                $scope.calculateSum();
            }

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

            $scope.selectedProgramItems = [];
            $scope.selectedSize = '';
            $scope.selectedUniform = '';
            $scope.setSelectedProgramItems = (items, type, size) => {
                $scope.selectedUniform = $scope.selectedSize == size ? '' : type;
                $scope.selectedProgramItems = $scope.selectedSize == size ? [] : JSON.parse(JSON.stringify(items));
                $scope.selectedSize = $scope.selectedSize == size ? '' : size;
            }
            $scope.pinPrograms = '';
            $scope.getPinPrograms = () => {
                // $scope.loading = true;
                $http.get("<?php echo SITE_URL ?>api/getPinPrograms.php")
                    .then(function(response) {
                        const list = {};
                        response.data.map(r => {
                            list[r.degree] = list[r.degree] || {}
                            list[r.degree][r.class] = list[r.degree][r.class] || {}
                            list[r.degree][r.class][r.program] = list[r.degree][r.class][r.program] || {}
                            list[r.degree][r.class][r.program].items = r.items;
                        })
                        $scope.pinPrograms = list;
                    })
            }

            $scope.getPinPrograms();

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

                // All cart arithmetic lives in assets/js/bill-calc.js — one implementation
                // shared by every screen that shows a cart. Only UI concerns stay here.
                Object.assign($scope, BillCalc.compute({
                    items: $scope.items,
                    customerData: customerData,
                    show_bundle: $scope.show_bundle,
                    discount: $scope.discount,
                    gst: $scope.gst,
                    service_charges: $scope.service_charges,
                    discountAmount: $scope.discountAmount,
                    total_discount_type: $scope.total_discount_type,
                }));

                // $window.sessionStorage.setItem('shopping', JSON.stringify($scope.items));
                const pay = Object.values($scope.payWith);
                pay.map(p => {
                    if (p.is_default == 1) {
                        $scope.payWith[p.id].amount = $scope.payment_amount;
                    } else {
                        $scope.payWith[p.id].amount = 0;
                    }
                });
                // keep payment_total in step with the amounts just written, as the POS
                // does — checkout recomputes it anyway, this is for the live display
                $scope.calculatePayment($scope.payWith);

            }

            $scope.loadProduct = function() {
                $http.get("<?php echo SITE_URL ?>api/getProducts.php?perPage=10000&status=&racks=1&session=1")
                    .then(function(response) {
                        const records = response.data.records.map(({
                            min_qty,
                            other_codes,
                            discount_amount,
                            discount_type,
                            board,
                            cat_id,
                            ...product
                        }) =>
                        product)
                        $scope.mainList = response.data;
                        toaster.success({
                        body: 'Items Updated!'
                        });
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
            console.log('shopCart', shopCart);
            $scope.searchEmployee = function(value) {
                return $http.get("<?php echo SITE_URL ?>api/getEmployees.php?search=" + value)
                    .then(function(response) {
                        return response.data.records
                    });
            }
            $scope.searchServices = function(value, onloading) {
                return $http.get("<?php echo SITE_URL ?>api/getServices.php?search=" + value)
                    .then(function(response) {
                        return response.data.records
                    });
            }
            $scope.selectService = (item, row) => {
                row.services = row.services || [];
                row.services.push({
                    service: item
                });
                row.service = '';
                $scope.calculateSum();
            }
            $scope.selectRaw = (item, row) => {
                row.raw_items = row.raw_items || [];
                row.raw_items.push({
                    product: item,
                    price: item.price,
                    qty: 1
                });
                row.raw = '';
                $scope.calculateSum();
            }

            $scope.convertToSizes = (str) => {
                const inputString = str;

                // Remove the 'SIZES: ' part and trim the string
                const sizesString = inputString.replace('SIZES: ', '').trim();

                // Regex pattern to match size and quantity pairs
                const regex = /(\d+)"?\/(\d+)/g;

                const arr = [];
                
                // Use matchAll to find all matches
                for (const match of sizesString.matchAll(regex)) {
                    arr.push({
                        size: parseInt(match[1].trim()),
                        qty: parseInt(match[2].trim()),
                    });
                }
                return arr;
            }

            $scope.replaceSizesText = (text) => {
                // Regex pattern to match "SIZES:" and everything after it
                const regex = /SIZES:.*$/;

                // Replace the matched text with an empty string
                return text.replace(regex, '');
            }

            shopCart.map(function(row) {
                const obj = $scope.mainList.find(function(e) {
                    return e.id == row.product_id
                });
                $scope.discountPercentValue += parseFloat(row.discount);
                $scope.subTotal = parseFloat($scope.subTotal) + parseFloat($scope.discount);
                items.push({
                    ...obj,
                    ...row,
                    sizes: row.description?.includes('SIZES:') ? $scope.convertToSizes(row.description): [{}],
                    description: row.description?.includes('SIZES:') ? $scope.replaceSizesText(row.description): row.description,
                    id: obj?.id || row.product_id,
                    full_name: obj?.full_name || row.product_title,
                    discount: row.discount?.toString(),
                    qty: parseFloat(row.quantity) - parseFloat(row.unpack_qty || 0),
                    unpack_qty: parseFloat(row.unpack_qty || 0),
                    pack_qty: parseFloat(row.pack_qty || 0),
                    pack_size: parseFloat(row.pack_size || obj.pack_size || 0),
                    discount_value: row.discount_type == 2 ? parseFloat(row.discount) : (parseFloat(row.discount || 0) / row.price) * 100,
                    publisher: row.publisherName ? ({
                        full_name: row.publisherName,
                        id: row.publisher_id,
                    }) : null
                })
            });
            if ($window.sessionStorage.getItem('shopping')) {
                const sessionShopCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                sessionShopCart.map(function(row) {
                    const obj = $scope.mainList.find(function(e) {
                        return e.id == row.id
                    });
                    items.push({
                        ...obj,
                        ...row,
                        id: obj?.id || row.product_id,
                        full_name: obj?.full_name || row.product_title,
                        discount: parseFloat(row.discount),
                        qty: row.quantity,
                        discount_value: row.discount_type == 2 ? parseFloat(row.discount) : (parseFloat(row.discount || 0) / row.price) * 100,
                        publisher: row.publisherName ? ({
                            full_name: row.publisherName,
                            id: row.publisher_id,
                        }) : null
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
                        ...row,
                        id: obj?.id || row.product_id,
                        full_name: obj?.full_name || row.product_title,
                        discount: parseFloat(row.discount),
                        qty: row.quantity || row.qty || 1,
                        discount_value: row.discount_type == 2 ? parseFloat(row.discount) : (parseFloat(row.discount || 0) / row.price) * 100,
                    })
                });

                if ($window.sessionStorage.getItem('shopping')) {
                    const storageCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                    storageCart.map(function(row) {
                        const obj = $scope.mainList.find(function(e) {
                            return e.id == row.id
                        });
                        const final = {
                            ...obj,
                            ...row,
                            id: obj.id,
                            qty: row.quantity || row.qty || 1,
                            discount: parseFloat(row.discount),
                            discount_value: row.discount_type == 2 ? parseFloat(row.discount) : (parseFloat(row.discount || 0) / row.price) * 100,
                        };
                        items.push(final)
                    });
                    $window.sessionStorage.setItem('shopping', JSON.stringify([]));
                    $scope.items = items;
                    $timeout(() => {
                        $scope.calculateSum()
                        if ($scope.qf) {
                            const currentIndex = $scope.items.length;
                            $location.hash('product-' + currentIndex);
                            $anchorScroll();
                            if ($('#product-' + currentIndex).find('.input-add-dist').length) {
                                $('#product-' + currentIndex).find('.input-add-dist').focus();
                            } else {
                                $('#product-' + currentIndex).find('.quantity__input').focus();
                            }
                        }
                    }, 200);
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
                $scope.total_discount_value = '';
                $scope.total_discount_type = 2;
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
            $scope.addSize = function(items) {
                console.log(items);
                items.push({});
                $scope.calculateSum();
            }
            $scope.removeSize = function(items, index) {
                items.splice(index, 1);
                $scope.calculateSum();
            }
            $scope.selectProduct = function(p, sep, disableCalc, event) {
                event && event.stopPropagation();
                let currentIndex = 1
                let tempSep = sep;
                if (!p.discount_type) {
                    p.discount_type = 1
                }
                if (p.product_type == 2 || p.product_type == 5) {
                    tempSep = true;
                }

                if ($scope.wsp) {
                    p.price = p.wh_price || p.price
                }


                if (tempSep) {
                    $scope.items.push({
                        ...p,
                        qty: 1,
                        pack_size: parseFloat(p.pack_size || 0),
                        show: true,
                        sizes: [{}],
                        publisher: p.publisherName ? ({
                            full_name: p.publisherName,
                            id: p.publisher_id,
                        }) : null
                    });
                } else {
                    $scope.product = '';
                    $scope.product = null
                    let exists = false;


                    if (!$scope.sep) {
                        $scope.items.map((pro, index) => {
                            if (pro.id == p.id && !pro.show) {
                                currentIndex = index + 1;
                                exists = true;
                                pro.qty++;
                            }
                        })
                    }
                    if (!exists) { // if already not exits in bucket

                        $scope.items.push({
                            ...p,
                            pack_size: parseFloat(p.pack_size || 0),
                            qty: 1,
                            sizes: [{}],
                            publisher: p.publisherName ? ({
                                full_name: p.publisherName,
                                id: p.publisher_id,
                            }) : null
                        });

                        currentIndex = $scope.items.length;
                    }
                }

                if (!disableCalc) {
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
                                $('#product-' + currentIndex).find('.quantity__input').focus();
                            }
                        }

                        $scope.product = '';
                    }, 200);
                }

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
                const params = {};
                if ($scope.serverSide === true) {
                    if ($scope.productCode) {
                        const filteredArray = $scope.mainList?.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).filter(r => {
                            const txt = r.searchString.split('|').pop()?.toLowerCase();
                            const exits = txt?.split(',')?.filter(tt => tt?.toLowerCase()?.startsWith($scope.productCode?.toLowerCase()));
                            return exits.length;
                        });
                        const secondfilteredArray = term ? filteredArray.filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                        return secondfilteredArray;
                    } else {
                        const filteredArray = $scope.mainList?.records.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).filter(r => r.id == term || r.code?.toLowerCase() == term?.toLowerCase() || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()))
                        const secondfilteredArray = !filteredArray.length ? $scope.mainList?.records.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                        return secondfilteredArray.slice(0, 30);

                    }
                }
                else if ($scope.focus === true) {
                    params.term = parseFloat(term.split('-')[0]);
                    const item = window.mainList.records.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).find(r => r.id == params.term || r.code == params.term || r.barcode == params.term || r.searchString?.split('|')?.pop()?.toLowerCase()?.includes(params?.term?.toString()?.toLowerCase()));
                    $scope.product = '';
                    $scope.selectProduct(item);
                    return [];
                } else {
                    if ($scope.productCode) {
                        const filteredArray = mainList.records.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).filter(r => {
                            const txt = r.searchString.split('|').pop()?.toLowerCase();
                            const exits = txt?.split(',')?.filter(tt => tt?.toLowerCase()?.startsWith($scope.productCode?.toLowerCase()));
                            return exits.length;
                        });
                        const secondfilteredArray = term ? filteredArray.filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                        return secondfilteredArray;
                    } else {
                        const filteredArray = window.mainList.records.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).filter(r => r.id == term || r.code?.toLowerCase() == term?.toLowerCase() || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()))
                        const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(r => $scope.is_active ? r.is_active == 0 : r.is_active == 1).filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                        return secondfilteredArray.slice(0, 30);

                    }
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
                        status_id: $scope.status_id,
                        expected_delivery_date: moment($scope.expected_delivery_date).format('YYYY-MM-DD'),
                        customerId: $scope.customerData && $scope.customerData.id ? $scope.customerData.id : 1,
                        customer_name: $scope.customerName,
                        subTotal: $scope.subTotal,
                        discount: $scope.discount,
                        items: $scope.items.filter(row => row.price).map(({
                            id,
                            description,
                            qty,
                            pack_size,
                            unpack_qty,
                            pack_qty,
                            discount,
                            discount_type,
                            price,
                            item_status,
                            employeeSelect,
                            expected_dates,
                            services,
                            raw_items,
                            product_type,
                            sizes,
                        }) => {
                            let des = "";
                            if(sizes?.filter(r => r.qty && r.size)?.length) {
                                sizes?.map((r, i) => {
                                    if(i === 0) {
                                        des += " SIZES: ";
                                    }
                                    des += ' ' + (isNaN(r.size) ? r.size : r.size +'"') + '/'+ r.qty +", ";
                                })

                            }
                            return ({
                            id,
                            description: (description || "") + des,
                            qty,
                            pack_size,
                            pack_qty,
                            unpack_qty,
                            discount,
                            discount_type,
                            start_date: expected_dates?.startDate ? moment(expected_dates.startDate).format('YYYY-MM-DD') : null,
                            end_date: expected_dates?.endDate ? moment(expected_dates.endDate).format('YYYY-MM-DD') : null,
                            employee_id: employeeSelect?.id,
                            price,
                            item_status,
                            product_type,
                            services,
                            raw_items,
                        })}),
                        shopId: $scope.shopId,
                        payment_amount: $scope.payment_total,
                        payment_with: $scope.payWith,
                        gst: $scope.gst,
                        service_charges: $scope.service_charges,
                        summery: $scope.summery,
                        show_discount: $scope.show_discount,
                        ref_no: $scope.ref_no,
                        id: $scope.data.order.id,
                        payment_mode: $scope.payment_mode,
                        status: status || 2,
                        overide: $scope.overide
                    }

                    $http.post("<?php echo SITE_URL ?>api/placeOrder.php", $scope.form, {
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(function(response) {
                            const ids = $scope.items.map(i => i.product_id || i.id);
                            $http.get(`<?php echo SITE_URL; ?>api/sync-product.php?product_ids=${ids.join(',')}`);
                            console.log('response', response)
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
                                $scope.summery = '';
                                $scope.ref_no = '';
                            }
                        }).catch(err => {
                            $scope.loading = false;
                            alert(err.message)
                        });
                }
            }
            $scope.opublishers = <?php echo !empty($publishers) ? json_encode($publishers) : json_encode([]); ?>;
            $scope.publishers = <?php echo !empty($publishers) ? json_encode($publishers) : json_encode([]); ?>;

            $scope.refreshPublishers = search => {
                $scope.publishers = $scope.opublishers.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
            }
            $scope.submitCode = (form) => {
                $http.post("<?php echo SITE_URL ?>pages/product/update.php?id=" + form.id, $httpParamSerializerJQLike({
                        author: form.author,
                        full_name: form.newTitle,
                        product_id: form.product_id,
                        publisher_id: form?.publisher?.id || '',
                        code: form.newBarCode,
                        wh_price: form.wh_price,
                        pprice: form.pprice,
                        price: form.newPrice,
                        rackNo: form.rackNo,
                        createCode: true,
                        json_response: true,
                    }), {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(function(response) {
                        if (response.data.status === 200) {
                            toaster.success({
                                body: response.data.message
                            });
                            form.newBarCode = '';
                        } else {
                            toaster.success({
                                body: response.data.message
                            });
                            form.newBarCode = '';
                        }
                    })
            }

        })
    </script>


<?php } else {
    echo '<div class="container-fluid"><div class="alert alert-success">This Ordre Has been Processed from Park State</div></div>';
} ?>