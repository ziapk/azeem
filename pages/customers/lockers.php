<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
echo mainHeader(['page' => 'lockers']);
?>

<div class="container" ng-controller="customerController">
    <a href="javascript:void(0)" ng-click="addCustomer()" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Locker</a>
    <h2 class="section-title">Lockers</h2>
    <h5 class="section-title">Total Amount: <strong style="font-size: 1.3em;">{{data.closing_total | number}}</strong>
        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?>
            <button class="btn btn-sm btn-primary mt-10" ng-click="bulkSendSummery()"><span class="fa fa-envelope"></span> Send Ledgers</button>
        <?php } ?>
    </h5>

    <div class="form-group">
        <input class="form-control" ng-change="searchCustomer()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="50"><input type="checkbox" ng-model="selectAll" ng-change="selectAllItems(selectAll)" /></th>
                    <th>Id</th>
                    <th>Title</th>
                    <th>Balance</th>
                    <th width="300"></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in list">
                    <td><input type="checkbox" ng-model="li.selected" /></td>
                    <td>{{li.id}}</td>
                    <td>{{li.full_name}}</td>
                    <td style="text-align: right;" ng-class="{'text-danger': li.closing_balance < 0}">{{li.closing_balance | number}}</td>
                    <td>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a ng-if="li.is_default == 0" class="btn btn-default btn-xs" href="../chart-of-accounts/summery.php?t=c&id={{li.account_id}}">Ledger</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="<?php echo SITE_URL; ?>pages/customers/update.php?id={{li.id}}"><span class="fa fa-edit"><span></a><?php } ?>
                        <?php if ($userData['role'] === 'owner') { ?><a ng-click="deleteCustomer(li.id)" class="btn btn-danger btn-xs" href="javascript:void(0)"><span class="fa fa-remove"><span></a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a ng-if="li.is_default == 0" class="btn btn-default btn-xs" ng-click="sendSummery(li.account_id)" href="javascript:void(0)"><span class="fa fa-envelope"></span>{{sending[li.account_id] ? 'Sending' : ''}}</a><?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-custom">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul>
        <span>
            Per Page
            <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </span>
        <span>Total Records <strong>{{data.totalRecords}}</strong></span>
    </div>

</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    
    app.controller('customerController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window, $log, toaster) {
        $scope.currentPage = 1;
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';
        $scope.sending = {};

        $scope.selectAllItems = (value) => {
            $scope.list.map(row => row.selected = value)
        }

        $scope.bulkSendSummery = async () => {
            const getList = $scope.list.filter(row => row.selected).map(row => row.account_id);
            if (getList?.length) {
                for (const account_id of getList) {
                    try {
                        $scope.sending[account_id] = true;
                        const res = await $http.post($scope.siteUrl + 'pages/chart-of-accounts/sendSummery.php', $httpParamSerializerJQLike({
                            account_id: [account_id],
                            from: '',
                            to: '',
                            type: 'c',
                        }), {
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        });

                        if (res.data.success) {
                            toaster.success({
                                body: res.data.message
                            })
                        } else {
                            console.log({
                                type: 'danger',
                                message: res.data.message
                            })
                        }
                    } catch (err) {
                        console.log({
                            type: 'danger',
                            message: err?.message || err
                        })
                    }

                    $scope.sending[account_id] = false;

                }


            }

        }

        $scope.sendSummery = (account_id) => {
            $scope.sending[account_id] = true;
            $http.post($scope.siteUrl + 'pages/chart-of-accounts/sendSummery.php', $httpParamSerializerJQLike({
                account_id: [account_id],
                from: '',
                to: '',
                type: 'c',
                customer_name: "<?php echo $user['full_name']; ?>",
            }), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                $scope.sending[account_id] = false;
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
            }).catch(err => {

                $scope.alert = {
                    type: 'danger',
                    message: err?.message || err
                }

                $scope.sending[account_id] = false;
            });
        }

        $scope.getCustomers = (page) => {
            $scope.loading = true;
            $http.get($scope.siteUrl + "api/getCustomers.php", {
                    params: {
                        page: page || 1,
                        account_type: 2, // 2=locker
                        perPage: $scope.data.perPage,
                        search: $scope.search
                    }
                })
                .then(function(response) {
                    $scope.loading = false;
                    if (response.status === 200) {
                        $scope.data = response.data;
                        $scope.list = response.data.records;
                    }
                })
        }

        $scope.deleteCustomer = function(id) {
            if (window.confirm('Are you sure ?')) {
                window.open("<?php echo SITE_URL; ?>pages/customers/delete.php?id=" + id, "", "width=300,height=400");
                window.location.reload();
            }
        }


        $scope.searchCustomer = () => {
            $scope.getCustomers(1);
        }

        $scope.perPage = () => {
            $scope.getCustomers($scope.currentPage);
        }

        $scope.getCustomers($scope.currentPage);

        $scope.pageChanged = (page) => {
            $scope.currentPage = page;
            $scope.getCustomers(page)
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
                $scope.getCustomers(1);
            });
        };

    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike) {
        $scope.form = {
            full_name: "",
            phoneNumber: "",
            address: "",
            default_discount: 0,
            type: false,
            account_type: 2
        }

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post('create.php', $httpParamSerializerJQLike($scope.form), {
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
            <h3 class="modal-title" id="modal-title">Add Locker</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="row">
            <div class="form-group col-sm-6">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.full_name" class="form-control" placeholder="Locker's Name">
            </div>
            <div class="form-group col-sm-6">
                <label for="sopening_balance">Opening Balance</label>
                <input id="sopening_balance" type="text" ng-model="form.opening_balance" class="form-control" placeholder="Locker's Opening Balance">
            </div>
        </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>