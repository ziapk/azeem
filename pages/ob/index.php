<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$customers = new  DoubleEntry();
$customersData = $customers->getOBs($shop['id']);

$exists = false;
foreach ($customersData as $value) {
    if ($value['sale_date'] == $shop['sale_date']) {
        $exists = true;
    }
}

$stores = new Store();
$userId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$ownerStores = $stores->getOwnerStores($userId);
$shopsData = [];
foreach ($ownerStores as $value) {
    $shopsData[$value['id']] = $value['full_name'];
}

echo mainHeader(['page' => 'customer']);
?>

<div class="container" ng-controller="customerController">

    <?php if ($exists == false) { ?><a href="javascript:void(0)" ng-click="addCustomer()" class="btn btn-primary btn-xs pull-right">Add New</a><?php } ?>

    <h4 class="section-title">Opening Balances</h4>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Shop</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($customersData as $value) { ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td><?php echo $value['amount']; ?></td>
                    <td><?php echo $value['sale_date']; ?></td>
                    <td><?php echo $shopsData[$value['shop_id']]; ?></td>
                    <td><?php if (($userData['role'] === 'owner' || $userData['role'] === 'manager') && $value['sale_date'] == $shop['sale_date']) { ?><a class="btn btn-default btn-xs" href="<?php echo SITE_URL . 'pages/ob/update.php?id=' . $value['id']; ?>"><span class="fa fa-edit"><span></a><?php } ?>
                        <?php if (($userData['role'] === 'owner')) { ?><a class="btn btn-default btn-xs" ng-click="deleteBalance(<?php echo $value['id']; ?>)"><span class="fa fa-remove"><span></a><?php } ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    function createCustomer() {
        window.open("<?php echo SITE_URL; ?>pages/ob/create.php", "", "width=300,height=400");
    }


    app.controller('customerController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window, $log) {
        $scope.currentPage = 1;
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';

        $scope.deleteBalance = (id) => {
            if ($window.confirm("Are you sure?")) {
                $window.location.assign('update.php?action=DELETE&id=' + id);
            }
        }
        $scope.addCustomer = function(size, parentSelector) {
            $scope.form = null
            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'addCustomer.html',
                controller: 'ModalInstanceCtrl',
                size: size
            }).closed.then(function() {
                $window.location.reload();
            });
        };
    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike) {
        $scope.form = {
            sale_date: moment('<?php echo $shop['sale_date']; ?>'),
            amount: 0,
        }
        $scope.shopId = '<?php echo $userData['shopId']; ?>';

        $scope.datePicker = moment('<?php echo $shop['sale_date']; ?>')

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post('create.php', $httpParamSerializerJQLike({
                amount: $scope.form.amount,
                shopId: $scope.shopId,
                sale_date: moment($scope.form.sale_date).format('YYYY-MM-DD')
            }), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                if (res.data.success) {
                    $scope.alert = {
                        type: 'success',
                        message: res.data.message
                    }
                } else {
                    $scope.alert = {
                        type: 'danger',
                        message: res.data.message
                    }
                }
                // $uibModalInstance.close($scope.form);
            });
        };



        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    });
</script>

<script type="text/ng-template" id="addCustomer.html">
    <form ng-submit="ok()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Opening Balance</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <?php if ($userData['role'] == 'owner') { ?>
            <div class="form-group">
                <label>Shop</label>
                <select class="form-control c-select" ng-model="shopId">
                    <?php foreach ($ownerStores as $value) { ?>
                        <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php } ?>
            <div class="form-group">
                <label>Sale Date</label>
                <input date-range-picker class="form-control date-picker" type="text" ng-model="form.sale_date" options="{ autoApply: true, singleDatePicker: true, }" ng-change="changeDP(datePicker.date)" />
            </div>
            <div class="form-group">
                <label>Opening Balance</label>
                <input type="text" ng-model="form.amount" class="form-control" placeholder="Customer's Opening Balance">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>