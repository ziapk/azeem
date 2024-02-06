<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$demandsObj = new Demands();
$stores = new Store();
$productsObj = new Products();


if ($userData['role'] == 'owner') {
    $demands = $demandsObj->getOwnerDemands($userData['id']);
} elseif ($userData['role'] == 'manager') {
    $demands = $demandsObj->getStoreDemands($shop['id']);
} else {
    $demands = $demandsObj->getUserDemands($shop['id'], $userData['id']);
}


echo mainHeader(['page' => 'demand']);


$ownerStores = $stores->getOwnerStores($shop['owner_id']);
$currentStore = [];
$storeList = [];
foreach ($ownerStores as $store) {
    $storeList[$store['id']] = $store;
    if ($userData['shopId'] == $store['id']) {
        $currentStore = $store;
    }
}


?>
<div class="container" ng-controller="categoryController">
    <a href="<?php echo SITE_URL . "pages/demand/create.php" ?>" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Demand</a>
    <h4>Demand Stocks</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sr.#</th>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Shop</th>
                    <th>Demand Date</th>
                    <th>Assign Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                <?php $count = 1;
                foreach ($demands as $demand) { ?>
                    <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo $demand['id']; ?></td>
                        <td><?php echo $demand['title']; ?></td>
                        <td><?php echo $storeList[$demand['shop_id']]['full_name']; ?></td>
                        <td><?php echo $demand['demand_date']; ?></td>
                        <td><?php echo !empty($demand['assign_date']) ? $demand['assign_date'] : 'NULL'; ?></td>
                        <td><?php echo $demandStatusArr[$demand['flag']]['full_name']; ?></td>
                        <td>
                            <?php if ($userData['role'] == 'owner') { ?>
                                <a class="btn btn-success btn-xs" href="<?php echo SITE_URL . "pages/demand/modify.php?id=" . $demand['id']; ?>">Modify</a>
                                <a class="btn btn-success btn-xs" href="<?php echo SITE_URL . "pages/demand/assign.php?id=" . $demand['id']; ?>">Assign</a>
                                <?php
                                if ($demand['flag'] == 0) { ?>
                                    <a class="btn btn-info btn-xs" href="#" ng-click="rejectDemand(<?php echo $demand['id']; ?>)">Reject</a>
                                    <a class="btn btn-danger btn-xs" href="#" ng-click="deleteDemand(<?php echo $demand['id']; ?>)">Delete</a>
                                <?php } ?>
                                <?php } else {
                                if ($demand['flag'] == 0) { ?>
                                    <a class="btn btn-info btn-xs" href="#" ng-click="withdrawalDemand(<?php echo $demand['id']; ?>)">Withdrawal</a>
                                    <a class="btn btn-danger btn-xs" href="#" ng-click="deleteDemand(<?php echo $demand['id']; ?>)">Delete</a>
                                    <a class="btn btn-success btn-xs" href="<?php echo SITE_URL . "pages/demand/modify.php?id=" . $demand['id']; ?>">Modify</a>
                                <?php } ?>
                            <?php } ?>
                            <a class="btn btn-info btn-xs" href="<?php echo SITE_URL . "pages/barcode/index.php?id=" . $demand['id']; ?>">Code</a>
                            <a class="btn btn-success btn-xs" href="<?php echo SITE_URL . "pages/demand/print.php?id=" . $demand['id']; ?>">Print</a>
                        </td>
                    </tr>
                <?php $count++;
                } ?>
            </tbody>
        </table>
    </div>
</div>


<script>
    app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout) {
        $scope.shopId = '<?php echo $userData['shopId']; ?>';
        $scope.deleteDemand = (id) => {
            console.log('id', id)

            if ($window.confirm('Are you sure you want to delete Demand ?')) {

                $http.delete("./deleteDemand.php?id=" + id)
                    .then(function(response) {
                        alert(response.data.message);
                    });
            }
        }

        $scope.withdrawalDemand = (id) => {

            if ($window.confirm('Are you sure you want to withdrawal Demand ?')) {

                $http.delete("./withdrawalDemand.php?id=" + id)
                    .then(function(response) {
                        alert(response.data.message);
                    });
            }
        }

        $scope.rejectDemand = (id) => {

            if ($window.confirm('Are you sure you want to reject Demand ?')) {

                $http.delete("./rejectDemand.php?id=" + id)
                    .then(function(response) {
                        alert(response.data.message);
                    });
            }
        }

        $scope.deleteItem = (index) => {
            $scope.formList = $scope.formList.filter((r, i) => i !== index);
        }

        $scope.siteUrl = '<?php echo SITE_URL ?>';

        $scope.books = <?php echo json_encode($products); ?>;

        $scope.items = $scope.books?.records || [];


        $scope.searchProduct = function(term) {
            return $http.get("<?php echo SITE_URL ?>api/getStores.php", {
                    params: {
                        term,
                        shopId: $scope.shopId
                    }
                })
                .then(function(response) {

                    $scope.list = response.data;
                    $scope.priceList = response.data;
                    return response.data
                });
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

<script type="text/ng-template" id="row.html">
    <a style="min-width: 250px">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
  </a>
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