<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$id = $_GET['id'];
$productObj = new Products();
$demandObj = new Demands();
$categoryObj = new Categories();
$stores = new Store();


$demandDetail = $demandObj->getDemandDetail($id, $shop['owner_id']);


$ownerId = $shop['owner_id'];
$userId = $userData['id'];

$ownerStores = $stores->getOwnerStores($ownerId);

$error = "";
$message = "";

echo mainHeader();
$categories = $categoryObj->getOwnerCategories($ownerId);

$all = false;
$products = [];
$storeObj = new Store();
$isOwner = false;
if ($userData['role'] == 'owner') {
    $isOwner = true;
}

?>
<div class="container" ng-controller="categoryController">
    <form method="POST" action="" autocomplete="off" ng-submit="submitForm($event)">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <h4>Demand From <?php echo $demandDetail['demand_date']; ?></h4>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="">Demand Title</label>
                <input ng-model="form.title" class="form-control" value="" />
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Demand Date</label>
                <input name="demand_date_piker" type="text" class="form-control datepicker-single" placeholder="YYYY-MM-DD">
                <input id="demand_date" type="hidden" class="form-control datepicker-hidden" value="<?php echo $demandDetail['demand_date']; ?>">
            </div>
            <?php if ($isOwner) { ?>
                <div class="col-sm-3 form-group">
                    <label>Shop Select</label>
                    <select class="form-control c-select" ng-model="form.shop_id">
                        <?php foreach ($ownerStores as $value) { ?>
                            <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
        </div>
        <div class="row" ng-repeat="li in form.items track by $index">
            <div class="col-sm-3 form-group" ng-if="li.id">
                <label for="">{{$index+1}}. Product</label>
                <span class="form-control">{{li.full_name}}</span>
            </div>
            <div class="col-sm-3 form-group" ng-if="!li.id">
                <label for=""><input type="checkbox" ng-model="li.searchBy" style="vertical-align: top"> Search by code</label>
                <input type="text" class="form-control" ng-model="li.product" placeholder="Search Products" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, li.searchBy)" typeahead-template-url="product-format.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Demand Qty</label>
                <input type="number" ng-model="li.product_qty" ng-disabled="!li.product.id && !li.product_id" class="form-control" placeholder="Qty">
            </div>
            <label>&nbsp;</label><br />
            <button type="button" ng-if="form.items.length > 1" ng-click="deleteItem($index)" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" ng-if="$index == 0" ng-click="addItem()" class="btn btn-success btn-sm">Add</button>
        </div>
        <div class="row">
            <div class="col-sm-3 form-group">
                <input type="submit" name="create" value="Save Demand" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<script>
    app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout) {
        $scope.list = [];
        $scope.shopId = '<?php echo $userData['shopId']; ?>';
        $scope.form = <?php echo json_encode($demandDetail); ?>;
        const txt = $('title').text();
        $('title').html($scope.form.title + " | " + txt);
        $scope.form.items.map(row => {
            row.product_qty = parseFloat(row.product_qty);
        });

        $scope.deleteItem = (index) => {
            $scope.form.items = $scope.form.items.filter((r, i) => i !== index);
        }

        $scope.addItem = () => {
            $scope.form.items.push({
                qty: 0
            })
        }

        $scope.submitForm = ($event) => {
            $event.preventDefault();
            $scope.form.demand_date = $('#demand_date').val();
            const list = [];

            $scope.form.items.map(row => {
                if ((row.product_id || row.product.id) && (row.product_qty || row.product.qty)) {
                    list.push({
                        ...row,
                        id: row.product_id || row.product.id,
                        qty: row.product_qty || row.product.qty
                    });

                }
            });

            $http.post("./modifyDemand.php", $httpParamSerializerJQLike({
                    ...$scope.form,
                    items: list
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    alert(response.data.message);
                });
        }

        $scope.siteUrl = '<?php echo SITE_URL ?>';

        $scope.books = [];

        $scope.items = [];

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

        $scope.searchProduct = function(term, isCodeEnable) {
            if (isCodeEnable === true) {
                params.term = parseFloat(term.split('-')[0]);
                return window.mainList.records.filter(r => r.id == params.term || r.code == params.term || r.barcode == params.term || r.searchString?.split('|')?.pop()?.toLowerCase()?.includes(params?.term?.toString()?.toLowerCase()));
            } else {
                const filteredArray = window.mainList.records.filter(r => r.id == term || r.code?.toLowerCase() == term?.toLowerCase() || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()))
                const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
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