<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Products();
$demandObj = new Demands();
$categoryObj = new Categories();
$stores = new Store();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];


$error = "";
$message = "";





echo mainHeader(['page' => 'stock']);;
$categories = $categoryObj->getOwnerCategories($ownerId);

$all = false;
$products = [];
$storeObj = new Store();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['owner_id'];
$ownerStores = $storeObj->getOwnerStores($ownerId);

?>
<div class="container" ng-controller="categoryController">
    <form method="POST" action="" autocomplete="off" ng-submit="submitForm($event)">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <h4>Exchange Stock</h4>
        <div class="row">
        <div class="form-group col-md-6">
            <label for="">Prepared By</label>
            <input type="text" class="form-control" ng-model="form.handover" placeholder="Person Name">
        </div>
        </div>
        <div class="row" ng-repeat="li in formList track by $index">
            <div class="col-md-3 col-sm-4 form-group">
                <label for="">{{$index+1}}. Product<input type="checkbox" ng-model="li.searchBy" style="vertical-align: top"> Search by code</label>
                <input type="text" class="form-control" ng-model="li.fromProduct" placeholder="Search Products" typeahead-on-select="selectProduct($item, li)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, li.searchBy)" typeahead-template-url="product-format.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="col-md-3 col-sm-4 form-group">
                <label for="">Demand Qty</label>
                <input name="demand_qty" ng-disabled="!li.fromProduct.id" type="number" ng-model="li.qty" class="form-control" placeholder="Qty">
            </div>
            <div class="col-md-3 col-sm-4 form-group">
                <label for="">{{$index+1}}. Product<input type="checkbox" ng-model="li.searchBy" style="vertical-align: top"> Search by code</label>
                <input type="text" class="form-control" ng-model="li.toProduct" placeholder="Search Products" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, li.searchBy)" typeahead-template-url="product-format.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <label>&nbsp;</label><br />
            <button type="button" ng-if="formList.length > 1" ng-click="deleteItem($index)" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" ng-if="$index == 0" ng-click="addItem()" class="btn btn-warning btn-sm">Add more</button>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-4 form-group">
                <input type="submit" name="create" value="Create Exchange" class="btn btn-primary">
            </div>
        </div>
    </form>
</div>

<script>
    app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout) {
        $scope.list = [];
        $scope.shopId = '<?php echo $userData['shopId']; ?>';
        $scope.form = {
            handover: '',
        }
        $scope.formList = [{
            qty: 0
        }];

        $scope.form.shop_id = $('#shop_id').val();

        $scope.addItem = () => {
            $scope.formList.push({
                qty: 0
            })
        }

        $('body').on('keydown', 'input', function(e) {
            if (e.key === "Enter") {
                var self = $(this),
                    form = self.parents('form'),
                    focusable, next;
                focusable = form.find('input[type=text], input[type=number], button').filter(':visible');
                next = focusable.eq(focusable.index(this) + 1);
                if (next.length) {
                    next.focus();
                } else {
                    // $('#searchProduct').focus()
                }
                return false;
            }
        });


        $scope.submitForm = ($event) => {

            $event.preventDefault();

            $scope.form.demand_date = $('#demand_date').val();
            $scope.form.items = [];
            $scope.form.create = true;

            $scope.formList.forEach(row => {
                if (row?.fromProduct?.id && row?.toProduct?.id && row.qty) {
                    $scope.form.items.push({
                        handover: $scope.form.handover,
                        fromId: row.fromProduct.id,
                        toId: row.toProduct.id,
                        existFromQty: row.fromProduct.qty,
                        existToQty: row.toProduct.qty,
                        qty: row.qty,
                    })
                }
            });
            $http.post("./exchangeStock.php", $httpParamSerializerJQLike($scope.form), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(function(response) {
                alert(response.data.message);
                if (response.data.status == 200) {
                    $window.location.assign('./index.php');
                }
            });
        }

        $scope.deleteItem = (index) => {
            $scope.formList = $scope.formList.filter((r, i) => i !== index);
        }

        $scope.siteUrl = '<?php echo SITE_URL ?>';

        $scope.books = [];

        $scope.items = $scope.books?.records || [];

        // $scope.searchProduct = function(term, isCodeEnable) {
        //     let searchBy;
        //     if (isCodeEnable) {
        //         searchBy = 'id';
        //     }
        //     if (term) {
        //         return $http.get("<?php echo SITE_URL ?>api/getStores.php", {
        //                 params: {
        //                     term,
        //                     searchBy,
        //                     shopId: $scope.shopId
        //                 }
        //             })
        //             .then(function(response) {

        //                 $scope.list = response.data;
        //                 $scope.priceList = response.data;
        //                 return response.data
        //             });
        //     } else {
        //         return [];
        //     }
        // }

        $scope.selectProduct = function(item, row) {
            row.existQty = parseInt(item.qty);
            row.qty = parseInt(item.qty);
        }

        $scope.searchProduct = function(term, isCodeEnable) {
            if (isCodeEnable === true) {
                params.term = parseFloat(term.split('-')[0]);
                return window.mainList.records.filter(r => r.is_active == 1).filter(r => r.id == params.term || r.code == params.term || r.barcode == params.term || r.searchString?.split('|')?.pop()?.toLowerCase()?.includes(params?.term?.toString()?.toLowerCase()));
            } else {
                const filteredArray = window.mainList.records.filter(r => r.is_active == 1).filter(r => r.id == term || r.code?.toLowerCase() == term?.toLowerCase() || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()))
                const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(r => r.is_active == 1).filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                return secondfilteredArray.slice(0, 30);
            }
        }

        $scope.deleteCategory = function(id) {
            $scope.items = $scope.items.filter(r => r.id !== id);
        }

        $scope.printTags = function(form) {
            $http.post('print.php', $httpParamSerializerJQLike($scope.items), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function() {
                // $scope.getCategories(1);
            });
        };
    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, form) {
        $scope.form = {
            full_name: "",
            cat_type: "",
            ...form
        }
        $scope.ok = function() {
            $uibModalInstance.close($scope.form);
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    });
</script>

<?php
echo mainFooter();
?>
<script type="text/javascript">
    $('.datepicker-hidden').val(moment().format('YYYY-MM-DD'));
    $('.datepicker-single').daterangepicker({
        minDate: moment(),
        singleDatePicker: true,
    }, function(date) {
        $('.datepicker-hidden').val(moment(date).format('YYYY-MM-DD'));
    });
</script>