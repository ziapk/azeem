<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$credit = $_GET["credit"];
echo mainHeader(['page' => !empty($credit) ? 'recipt-credit' : 'recipt', 'hideSidebar' => $userData['role'] == 'shopkeeper' ? false : true]);
$ordersObj = new Orders();
$data = ['from' => $shop['sale_date'], 'to' => null];
$orders = $ordersObj->userOrders($shop['id'], $data, 1, false);
$stores = new Store();
$isOwner = $userData['role'] == 'owner';
$userId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$ownerStores = $stores->getOwnerStores($userId);

$publisherObj = new Publishers();
$publishers = $publisherObj->getPublishers($userId);

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
        <h5 class="text-danger"><strong>Today's Parked Bills</strong></h5>
        <div style="display: flex; gap: 10px; flex-wrap: wrap" class="form-group">
            <?php foreach ($orders as $key => $value) { ?>
                <div class="dropdown">
                    <div class="btn-group btn-group-sm">
                        <a class="btn btn-default" href="./edit.php?id=<?php echo $value['id']; ?>"><?php echo $value['full_name'] . ' - ' . $value['order_custom_id']; ?></a>
                        <button type="button" class="btn btn-default" class="dropdown-toggle" data-toggle="dropdown" ng-click="loadOrder(<?php echo $value['id']; ?>)"><span class="fa fa-arrow-down"></span></button>
                        <div class="dropdown-menu" style="width: 90vw; max-width: 400px; padding: 16px">
                            <form role="search" ng-submit="saveOrder(<?php echo $value['id']; ?>)">
                                <input type="text" autocomplete="off" class="form-control form-group" ng-model="pinProduct" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectPinProduct($item)" ng-model-options="{debounce: 100}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="productCode ? 0 : 1">
                                <span ng-if="loading[<?php echo $value['id']; ?>]">Loading...</span>
                                <div ng-if="!loading[<?php echo $value['id']; ?>]">
                                    <ol class="table-list">
                                        <li ng-repeat="li in pinOrder.order_items">
                                            <?php if ($userData['role'] == 'owner') { ?>
                                                <button type="button" class="btn btn-xs btn-danger pull-right" ng-click="deleteItemPinOrder(li.id)"><span class="fa fa-remove"></span></button>
                                            <?php } ?>
                                            {{li.product_title}} <br />

                                            Qty: <strong>{{li.quantity}} x Rs.{{li.price}}</strong>
                                        </li>
                                    </ol>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
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
                    <?php if ($isOwner) { ?>
                        <label style="margin-left: 10px"><span style="vertical-align: middle"><input type="checkbox" ng-model="wsp"></span> <span style="vertical-align: middle">WSP</span></label>
                    <?php } ?>
                    <label style="margin-left: 10px"><span style="vertical-align: middle"><input type="checkbox" name="qf" ng-model="qf"></span> <span style="vertical-align: middle">QF</span></label>
                </td>
            </tr>
            <tr>
                <td>
                    <div>
                        <div class="input-group">
                            <input type="text" autocomplete="off" class="form-control" id="searchProduct-1" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectProduct($item)" ng-model-options="{debounce: 100}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="productCode ? 0 : 1">
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
        <div class="row">
            <div class="col-xs-12" ng-class="{'col-md-9': productPurchases.length}">
                <table class="table table-striped visible-xs">
                    <thead>
                        <?php include_once dirname(__FILE__) . '/table-sm.php'; ?>
                        <table class="table table-striped recipt-table hidden-xs">
                            <thead class="sticky">
                                <th colspan="8" style="padding: 0">
                                    <table class="table" style="box-shadow: none; margin: 0">
                                        <thead>
                                            <tr>
                                                <th style="vertical-align: middle">Customer Name</th>
                                                <th style="width: 200px">
                                                    <div class="dropdown-wrapper" style="position: relative;">
                                                        <input type="text" class="form-control" ng-model="customerName" placeholder="Search Customer" uib-typeahead="address as address.full_name for address in searchCustomer($viewValue)" typeahead-on-select="selectCustomer($item)" ng-model-options="{debounce: 100}" typeahead-template-url="customer.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
                                                    </div>
                                                </th>
                                                <th style="vertical-align: middle">
                                                    <label class="pull-left"><span style="vertical-align: middle"><input type="checkbox" ng-model="show_discount"></span> <span style="vertical-align: middle">Add Discount</span></label>
                                                    <label class="pull-left"><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="show_bundle"></span> <span style="vertical-align: middle">Bundles</span></label>
                                                    <label style="margin-left: 10px"><span style="vertical-align: middle"><input type="checkbox" ng-model="showDescription"></span> <span style="vertical-align: middle">DESC</span></label>
                                                    <label class="pull-left"><span style="vertical-align: middle; margin-left: 10px"><input type="checkbox" ng-model="sep"></span> <span style="vertical-align: middle">SEP</span></label>
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
                                                            <input type="text" autocomplete="off" class="form-control" id="searchProduct-2" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-on-select="selectProduct($item)" ng-model-options="{debounce: 100}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="productCode ? 0 : 1">
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


                                <?php include_once dirname(__FILE__) . '/table.php'; ?>
            </div>
            
            <div ng-if="productPurchases.length || productSales.length || productReturns.length" class="col-xs-12 col-md-3">
                <button type="button" ng-click="hideList()" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> Hide</button>
                <uib-tabset active="activePill" class="orders-tabs">
                    <uib-tab index="0" data-tab="purchase" heading="PO ({{productPurchases.length}})"></uib-tab>
                    <uib-tab index="1" data-tab="sale" heading="SO ({{productSales.length}})"></uib-tab>
                    <uib-tab index="2" data-tab="return" heading="RO ({{productReturns.length}})"></uib-tab>
                </uib-tabset>
                <div>
                    <input placeholder="ORDER ID" ng-model="searchId" />
                    <input placeholder="CUSTOMER" ng-model="searchName" />
                    <input placeholder="QTY" ng-model="searchQty" />
                    <input placeholder="PRICE" ng-model="searchPrice" />
                </div>
                <div style="height: 450px; overflow: auto">
                    <table ng-if="activePill == 0" class="table table-striped">
                        <tr ng-repeat="item in productPurchases | filter: { customerName : searchName, id : searchId,  pprice : searchPrice, quantity : searchQty }">
                            <td>
                                <a ng-click="openSupply(item.id, 'details')" href="javascript:void(0)"><strong class="text-danger">{{item.id}}</strong></a> | {{item.full_name}}<br />
                                <strong>{{item.customerName || item.supplierName}}</strong><br />
                                {{item.supply_date|date:'dd/MM/yyyy'}}<br />
                            </td>
                            <td style="width: 1%; white-space: nowrap">
                                <span class="text-light">{{item.quantity}}</span> x <strong style="font-size: 20px">{{item.price * ((100 - item.discount)/100) | number:2}} </strong> Rs<br />
                            </td>

                        </tr>
                    </table>
                    <table ng-if="activePill == 1" class="table table-striped">
                        <tr ng-repeat="item in productSales | filter: { customerName : searchName, id : searchId,  price : searchPrice, quantity : searchQty } ">
                            <td>
                                <a ng-click="openRecipt(item.id, 'details')" href="javascript:void(0)"><strong class="text-danger">{{item.order_custom_id}}</strong></a> | {{item.full_name}}<br />
                                <strong>{{item.customerName || item.supplierName}}</strong><br />
                                {{item.order_date|date:'dd/MM/yyyy'}}<br />
                            </td>
                            <td style="width: 1%; white-space: nowrap">
                                <span class="text-light">{{item.quantity}}</span> x <strong style="font-size: 20px">{{item.price * ((100 - item.discount)/100) | number:2}} </strong> Rs<br />
                            </td>

                        </tr>
                    </table>
                    <table ng-if="activePill == 2" class="table table-striped">
                        <tr ng-repeat="item in productReturns | filter: { customerName : searchName, id : searchId,  price : searchPrice, quantity : searchQty } ">
                            <td>
                                <a ng-click="openReturn(item.id, 'details')" href="javascript:void(0)"><strong class="text-danger">{{item.id}}</strong></a> | {{item.full_name}}<br />
                                <strong>{{item.customerName || item.supplierName}}</strong><br />
                                {{item.return_date|date:'dd/MM/yyyy'}}<br />
                            </td>
                            <td style="width: 1%; white-space: nowrap">
                                <span class="text-light">{{item.quantity}}</span> x <strong style="font-size: 20px">{{item.price * ((100 - item.discount)/100) | number:2}} </strong> Rs<br />
                            </td>

                        </tr>
                    </table>
                </div>
            </div>
        </div>
      

    
    </div>

</div>
<?php
echo mainFooter();
?>

<script type="text/javascript">
    // var element = $('#fixme');
    // var fixmeTop = element.offset().top;
    // var dummyHeight = $('#dummyHeight');
    // var offset = $('.navbar').height();
    // $(window).on('load scroll', function() { // assign scroll event listener
    //     var fixedHeight = element.height()


    //     var currentScroll = $(window).scrollTop(); // get current position

    //     if ((currentScroll + offset) >= fixmeTop) { // apply position: fixed if you
    //         element.css({ // scroll to that element or below it
    //             top: offset,
    //         });
    //         element.addClass('navbar-fixed-top')
    //         dummyHeight.height(fixedHeight)
    //     } else { // apply position: static
    //         element.removeClass('navbar-fixed-top');
    //         dummyHeight.height(0)
    //     }

    // });
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
                    event.preventDefault();
                    $(element).val('')
                }
            });
        };
    });
    app.controller('cartController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $timeout, $location, $anchorScroll, toaster) {
        
        $scope.mainList = $window.mainList?.records || [];
        $scope.activePill = 0;
        $scope.pinList = [];
        $scope.list = [];
        $scope.focus = false;
        $scope.showDescription = false;
        $scope.qf = false;
        $scope.sep = false;
        $scope.wsp = false;
        $scope.productCode = "";
        $scope.selectedList = {};
        $scope.indexes = [];
        $scope.show_bundle = false;
        $scope.is_active = false;
        $scope.setList = list => {
            $scope.indexes = [];
            Object.keys(list).forEach((key) => {
                if (list[key]) {
                    $scope.indexes.push(parseInt(key));
                }
            });
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

        $scope.loading = {}
        $scope.pinOrder = {}
        $scope.loadOrder = id => {
            if (!$scope.loading[id]) {
                $scope.pinOrder = {};
                $scope.loading[id] = true;
                $http.get("<?php echo SITE_URL ?>api/getOrder.php", {
                    params: {
                        id,
                        shop_id: $scope.shopId
                    }
                }).then(response => {
                    $scope.pinOrder = response.data;
                    $scope.loading[id] = false;
                }).catch(() => {
                    $scope.loading[id] = false;
                })
            }
        }


        $scope.deleteItemPinOrder = (id) => {
            if ($window.confirm('Are you sure?')) {
                const currentItems = $scope.pinOrder.order_items?.filter(r => {
                    return r.id != id
                })

                console.log(currentItems, id)

                $http.post("<?php echo SITE_URL ?>api/addProductToOrder.php", $httpParamSerializerJQLike({
                    items: currentItems,
                    order_id: $scope.pinOrder.order.id
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(response => {
                    // $scope.pinOrder = null;
                    $scope.loadOrder($scope.pinOrder.order.id);
                    $scope.pinProduct = '';
                    $scope.pinProduct = null;
                    toaster.success({
                        body: 'Product removed from order successfully!'
                    });
                    $scope.loading[id] = false;
                }).catch(() => {
                    $scope.loading[id] = false;
                })
            }
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

        $scope.selectPinProduct = (p) => {
            console.log($scope.pinOrder, 'p', p)
            const items = [];
            let exists = false;
            const currentItems = $scope.pinOrder.order_items.map(r => {
                if (r.product_id === p.id) {
                    r.quantity = (parseFloat(r.quantity) + 1);
                    exists = true;
                }
            })
            if (!exists) {

                $scope.pinOrder.order_items.push({
                    ...p,
                    product_id: p.id,
                    new: true,
                    qty: 1,
                    quantity: 1
                });
            }

            $http.post("<?php echo SITE_URL ?>api/addProductToOrder.php", $httpParamSerializerJQLike({
                items: $scope.pinOrder.order_items,
                order_id: $scope.pinOrder.order.id
            }), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(response => {
                // $scope.pinOrder = null;
                $scope.loadOrder($scope.pinOrder.order.id);
                $scope.pinProduct = '';
                $scope.pinProduct = null;
                toaster.success({
                    body: response.data.message
                });
                $scope.loading[id] = false;
            }).catch(() => {
                $scope.loading[id] = false;
            })
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
        $scope.shopId = '<?php echo $userData['shopId']; ?>';

        $scope.customerData = {};
        $scope.summery = '';
        $scope.ref_no = '';
        $scope.gst = parseFloat('<?php echo $shop['gst']; ?>' || 0);
        $scope.service_charges = parseFloat('<?php echo $shop['service_charges']; ?>' || 0);
        $scope.discountPercentValue = 0;
        $scope.show_discount = false;
        $scope.total_discount_type = 2;
        $scope.subTotal = 0;
        $scope.grandTotal = 0;
        $scope.discount = 0;
        $scope.payment_mode = '1';
        $scope.payment_total = 0;
        $scope.overide = false;



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
        const items = [];
        $scope.modes = [];
        $scope.loading = false;

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
        $scope.getPinProducts();

        $scope.printValue = o => {
            $scope.payment_mode = o.id;
        }
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
        if ($window.sessionStorage.getItem('shopping')) {
            const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));

            shopCart.map(function(row) {
                items.push({
                    ...row,
                    pack_qty: parseFloat(row.pack_qty || 0),
                    pack_size: parseFloat(row.pack_size || 0),
                    unpack_qty: parseFloat(row.unpack_qty || 0),
                    discount: row.discount?.toString(),
                    discount_value: row.discount_value?.toString(),
                    sizes: row.sizes?.length ? row.sizes: [{}],
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
        } else {
            $scope.items = []
        }

        $scope.addTax = () => {
            $scope.calculateSum();
        }

        $scope.addDiscount = function(val, obj) {
            if (parseFloat(val) > 0) {
                $scope.discount = (parseFloat($scope.discount) + parseFloat(val));
            } else if (parseFloat($scope.discount) + parseFloat(val) >= 0) {
                $scope.discount = (parseFloat($scope.discount) + parseFloat(val));
            } else {
                alert('Negative Discount value must be less than equal to -' + $scope.discount);
            }
            $scope.calculateSum();
            $scope.total_discount_value = '';
            $scope.discountAmount = '';
            $scope.total_discount_type = 2;
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

        $(document).on("ProdcutAdded", function(e) {
            const items = [];
            let currentIndex = 1;
            if ($window.sessionStorage.getItem('shopping')) {
                currentIndex = $scope.items.length;
                const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));

                shopCart.map(function(row, index) {
                    currentIndex = index + 1;
                    items.push({
                        ...row,
                        discount: parseFloat(row.discount),
                        discount_value: parseFloat(row.discount_value)
                    })
                });
                $scope.items = items;
                $timeout(() => {
                    $scope.calculateSum()
                    if ($scope.qf) {

                        $anchorScroll.yOffset = 200;

                        $location.hash('product-' + currentIndex);
                        $location.hash('sm-product-' + currentIndex);
                        $anchorScroll();
                        if ($('#product-' + currentIndex).find('.input-add-dist').length) {
                            $('#product-' + currentIndex).find('.input-add-dist').focus();
                        } else {
                            $('#product-' + currentIndex).find('.quantity__input').focus();
                        }
                        if ($('#sm-product-' + currentIndex).find('.input-add-dist').length) {
                            $('#sm-product-' + currentIndex).find('.input-add-dist').focus();
                        } else {
                            $('#sm-product-' + currentIndex).find('.quantity__input').focus();
                        }
                    }
                }, 200);

            } else {
                $scope.items = []
            }
        });

        $scope.productPurchases = [];
        $scope.productSales = [];
        $scope.productReturns = [];


        $scope.selectProduct = function(p, sep, disableCalc, event) {
            event && event.stopPropagation();
            if (!p.discount_type) {
                p.discount_type = 1
            }
            let currentIndex = 1
            if (p.product_type == 2 || p.product_type == 5) {
                sep = true;
            }
            if ($scope.sep) {
                sep = true
            }
            if ($scope.wsp) {
                p.price = p.wh_price || p.price
            }
            if (sep) {
                $scope.items.push({
                    ...p,
                    pack_size: parseFloat(p.pack_size || 0),
                    qty: 1,
                    show: true,
                    sizes: [{}],
                    publisher: p.publisherName ? ({
                        full_name: p.publisherName,
                        id: p.publisher_id,
                    }) : null
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
                        $anchorScroll.yOffset = 200;
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

            $scope.getProductPurchases(p);
            $scope.getProductSales(p);
            $scope.getProductReturns(p);

        }

        $scope.hideList = () => {
            $scope.productPurchases = [];
            $scope.productSales = [];
            $scope.productReturns = [];
        }


        $scope.getProductPurchases = function(product) {
            $http.get("<?php echo SITE_URL ?>api/getProductPurchases.php?product_id=" + product.id)
            .then(function(response) {
                $scope.productPurchases = response.data.map(row => ({...row, supply_date: new Date(row.supply_date).toISOString()}));
            });
        }
        $scope.getProductSales = function(product) {
            $http.get("<?php echo SITE_URL ?>api/getProductSales.php?product_id=" + product.id)
            .then(function(response) {
                $scope.productSales = response.data;
            });
        }
        $scope.getProductReturns = function(product) {
            $http.get("<?php echo SITE_URL ?>api/getProductReturns.php?product_id=" + product.id)
            .then(function(response) {
                $scope.productReturns = response.data;
            });
        }
        $scope.addMoreQty = function(obj, val, e) {
            if (val > 0) {
                obj.qty = parseFloat(obj.qty) + (parseFloat(val));
            }
            $scope.calculateSum();
        }
        $scope.selectCustomer = function(p) {
            $scope.customerName = p.full_name;
            $scope.customerData = p;
            $scope.calculateSum(p);
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
                    return response.data
                });
        }

        $scope.searchMode();

        $scope.searchCustomer('', true)

        $scope.clearSearch = () => {
            $scope.product = null
            $scope.list = [];
        }
        $scope.clearCustomer = () => {
            $scope.customersList = [];
        }

        $scope.park = () => {
            $scope.checkout(1);
        }

        $scope.checkout = function(status) {
            if ($scope.items.length) {

                $scope.calculatePayment($scope.payWith);
                $scope.loading = true;
                $scope.form = {
                    status_id: $scope.status_id,
                    expected_delivery_date: moment($scope.expected_delivery_date).format('YYYY-MM-DD'),
                    customer_name: $scope.customerName,
                    customerId: $scope.customerData && $scope.customerData.id ? $scope.customerData.id : 1,
                    subTotal: $scope.subTotal,
                    discount: $scope.discount,
                    items: $scope.items.filter(row => row.price).map(({
                        id,
                        description,
                        pack_size,
                        pack_qty,
                        unpack_qty,
                        qty,
                        discount,
                        discount_type,
                        price,
                        item_status,
                        employeeSelect,
                        expected_dates,
                        services,
                        raw_items,
                        product_type,
                        sizes
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
                        pack_size,
                        pack_qty,
                        unpack_qty,
                        qty,
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
                    id: $scope.id,
                    payment_mode: $scope.payment_mode,
                    status: status || 2,
                    shopId: $scope.shopId,
                    overide: $scope.overide
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
                        }

                        $scope.items = $scope.list = [];
                        $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = $scope.payment_total = 0;
                        $window.sessionStorage.setItem('shopping', JSON.stringify($scope.items))
                        // $window.location.assign('<?php echo SITE_URL ?>')
                        $scope.searchCustomer('', true)
                        $scope.summery = "";
                        $scope.ref_no = "";

                    }).catch(err => {
                        $scope.loading = false;
                        alert(err.message)
                    });

            }
        }

        $scope.initCheckKeypress = (evt) => {
            var e = evt; // for trans-browser compatibility
            var charCode = e.which || e.keyCode;
            console.log('evt', charCode)

            if (charCode === 9 || charCode === 13) {
                console.log('searchProduct', $('#searchProduct'));
                $('#searchProduct-1:visible').focus();
                $('#searchProduct-2:visible').focus();
                e.preventDefault();
            }
        }

        $scope.calculateSum = (c) => {
            const customerData = c || $scope.customerData;
            let subtotal = 0;
            $scope.discountPercentValue = 0;
            let counter = 1;
            let forCounter = 1;
            for (const product of $scope.items) {
                product.price = product.price || 0;
                if (product.product_type != 5) {
                    product.srno = counter;
                    counter++;
                } else {
                    product.frsrno = forCounter;
                    forCounter++;
                }
                if (product.pack_qty && $scope.show_bundle) {
                    product.qty = (product.pack_size || 1) * product.pack_qty;
                }

                let qty = $scope.show_bundle ? (product.qty + (product.unpack_qty || 0)) : product.qty;
                if(product.sizes?.length) {
                    let sizesQty = 0;
                    product.sizes.map(r => {
                        sizesQty += parseInt(r.qty || 0);
                    });

                    if(sizesQty) {
                        product.qty = parseInt(sizesQty);
                    }
                }
                if (!$scope.show_bundle) {
                    product.unpack_qty = 0;
                    product.pack_qty = 0;
                    // product.pack_size = 0;
                }

                if (product.product_type == 1 || product.product_type != 1 && !product.services?.length && !product.raw_items?.length) {
                    if (product.discount_type == 2) {

                        if (parseFloat(customerData.default_discount)) {
                            product.discount_value = product.price;
                        }

                        product.discount = (product.discount_value || 0)
                        product.discount_value = parseFloat(product.discount_value || 0);
                        product.discount_percent = product.discount_value || 0;
                        subtotal += ((product.price - (product.discount || 0)) * qty);
                    } else if (!product.discount_value && customerData.discount_array?.length && customerData.discount_array?.filter(r => r.publisher_id == product.publisher_id).length) {
                        const row = customerData.discount_array.find(r => r.publisher_id == product.publisher_id);
                        const price = parseFloat(product.price);
                        product.discount = price * (parseFloat(row.discount_value) / 100);
                        product.discount_value = row.discount_value;
                        product.discount_percent = row.discount_value + "%";
                        subtotal += ((product.price - product.discount) * qty);
                    } else {
                        const price = parseFloat(product.price);
                        if (parseFloat(customerData.default_discount)) {
                            product.discount_value = customerData.default_discount;
                        }
                        if (product.discount_value) {
                            product.discount = price * (parseFloat(product.discount_value || 0) / 100);
                            $scope.discountPercentValue += (product.discount * qty);
                            product.discount_percent = product.discount_value + "%";
                            product.discount_value = parseFloat(product.discount_value);
                        } else {
                            product.discount_percent = '';
                            product.discount_value = '';
                            product.discount = 0;
                        }
                        subtotal += ((price - product.discount) * qty);
                    }
                } else {
                    product.price = 0;
                    product.services?.forEach(row => {
                        product.price += (row.price || 0) * (row.qty || 1)
                    })
                    product.raw_items?.forEach(row => {
                        product.price += (row.price || 0) * (row.qty || 1)
                    })
                    subtotal += product.price * qty;
                }
            };
            $scope.subTotal = subtotal;
            $scope.payment_amount_before_tax = $scope.subTotal - $scope.discount;
            $scope.payment_amount = $scope.subTotal - $scope.discount;
            $scope.grandTotal = $scope.payment_amount = $scope.payment_amount + Math.round($scope.payment_amount * ($scope.gst / 100)) + Math.round($scope.payment_amount * ($scope.service_charges / 100));
            $window.sessionStorage.setItem('shopping', JSON.stringify($scope.items));

            $scope.total_discount_value = $scope.discountAmount;

            if($scope.total_discount_type === 1 && $scope.subTotal) {
                if($scope.discountAmount <= 100) {

                    $scope.total_discount_value = $scope.subTotal * (parseFloat($scope.discountAmount) / 100);

                }
            }
            else {
                $scope.total_discount_value = $scope.discountAmount;
            }

            

            <?php
            if (empty($credit)) { ?>
                const pay = Object.values($scope.payWith);
                pay.map(p => {
                    if (p.is_default == 1) {
                        $scope.payWith[p.id].amount = $scope.payment_amount;
                        $scope.payment_total = $scope.payment_amount;
                    } else {
                        $scope.payWith[p.id].amount = 0;
                    }
                });
                $scope.calculatePayment($scope.payWith);
            <?php } ?>

        }
        $scope.opublishers = <?php echo !empty($publishers) ? json_encode($publishers) : json_encode([]); ?>;
        $scope.publishers = <?php echo !empty($publishers) ? json_encode($publishers) : json_encode([]); ?>;

        $scope.refreshPublishers = search => {
            $scope.publishers = $scope.opublishers.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
        }


        $scope.openSupply = (id, detail, largeView) => {
            if (detail) {
                detail = true
            } else {
                detail = false
            }
            $window.open("<?php echo SITE_URL; ?>print/supply.php?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", (largeView ? "width=600,height=900" : "width=300,height=300"));
        }
        $scope.openRecipt = (id, detail, largeView) => {
            if (detail) {
                detail = true
            } else {
                detail = false
            }
            $window.open("<?php echo SITE_URL; ?>print?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", (largeView ? "width=600,height=900" : "width=300,height=300"));
        }
        $scope.openReturn = (id, detail, largeView) => {
            if (detail) {
                detail = true
            } else {
                detail = false
            }
            $window.open("<?php echo SITE_URL; ?>print/return.php?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", (largeView ? "width=600,height=900" : "width=300,height=300"));
        }

        $scope.submitCode = (form) => {
            $http.post("<?php echo SITE_URL ?>pages/product/update.php?id=" + form.id, $httpParamSerializerJQLike({
                    author: form.author,
                    full_name: form.newTitle,
                    code: form.newBarCode,
                    product_id: form.product_id,
                    publisher_id: form?.publisher?.id || '',
                    price: form.newPrice,
                    wh_price: form.wh_price,
                    pprice: form.pprice,
                    rackNo: form.rackNo,
                    createCode: true,
                    json_response: true,
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    toaster.success({
                        body: response.data.message
                    });
                    form.newBarCode = '';
                    form.rackNo = '';
                })
        }

    })
</script>