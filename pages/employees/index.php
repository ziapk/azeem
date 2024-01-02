<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$customers = new  Employees();
$customersData = $customers->getEmployees($shop['id']);
echo mainHeader(['page' => 'employees']);
function getMonthListBetweenDates($from, $to, $format = "F-Y-d", $intervals = '1 month')
{
    $start    = new DateTime($from);
    $start->modify('first day of this month');
    $end      = new DateTime($to);
    $end->modify('first day of next month');
    $interval = DateInterval::createFromDateString($intervals);
    $period   = new DatePeriod($start, $interval, $end);

    $list = [];

    foreach ($period as $dt) {
        $list[] = strtolower($dt->format($format));
    }

    return $list;
}
?>

<div class="container" ng-controller="customerController">
    <a href="javascript:void(0)" ng-click="addCustomer()" class="btn btn-primary btn-xs pull-right">Add New</a>
    <h4 class="section-title">Employees</h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchEmployee()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <div class="form-group">
        <h5>Select months</h5>
        <div class="row">
            <div class="col-sm-4">
                <select class="form-control" ng-model="form.salary_month">
                    <?php
                    $currentMonth = strtolower(date('F-Y'));
                    $firstMonth = date('Y-m-d', strtotime(date('F Y') . '-1 month'));
                    $lastMonth = date('Y-m-d', strtotime(date('F Y') . '+1 month'));
                    $periods = getMonthListBetweenDates($firstMonth, $lastMonth, 'F-Y', '1 month');
                    foreach ($periods as $date) { ?>
                        <option value="<?php echo $date; ?>" <?php if ($date == $currentMonth) {
                                                                    echo 'selected';
                                                                } ?>>
                            <?php echo strtoupper($date); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-4">
                <a href="javascript:void(0)" class="btn btn-primary" ng-click="generateSalaries(form)">Generat Salaries</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Contact</th>
                    <th>Title / Company / Address</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in list">
                    <td>{{li.id}}</td>
                    <td><strong>{{li.full_name}}</strong> <br /> {{li.phoneNumber}}</td>
                    <td><strong>{{li.company}}</strong> - {{li.title}} <br />{{li.address}}</td>
                    <td>{{statusArr[li.status]}}</td>
                    <td>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-info btn-xs" href="javascript:void(0)" ng-click="assignBooks(li)">Disc.</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="../chart-of-accounts/summery.php?t=emp&id={{li.account_id}}">Ledger</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-xs btn-primary" href="adjustment.php?id={{li.account_id}}">Payments</a><?php } ?>
                        <?php if ($userData['role'] === 'manager') { ?><a class="btn btn-danger btn-xs" href="<?php echo SITE_URL; ?>pages/orders/customerOrders.php?id={{li.id}}">Orders</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="<?php echo SITE_URL; ?>pages/employees/update.php?id={{li.id}}"><span class="fa fa-edit"><span></a><?php } ?>
                        <?php if ($userData['role'] === 'manager') { ?><a ng-click="deleteCustomer(li.id)" class="btn btn-danger btn-xs" href="javascript:void(0)"><span class="fa fa-remove"><span></a><?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="pagination-custom">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select></span> <span>Total Records <strong>{{data.totalRecords}}</strong></span>
    </div>

</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    app.controller('customerController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window, $log) {
        $scope.statusArr = <?php echo safe_json_encode($statusArr); ?>;
        $scope.currentPage = 1;
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';

        $scope.getEmployees = (page) => {
            $scope.loading = true;
            $http.get($scope.siteUrl + "api/getEmployees.php", {
                    params: {
                        page: page || 1,
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
        $scope.generateSalaries = (frm) => {
            console.log('frm', frm);
            $http.post($scope.siteUrl + "api/generateSalaries.php", $httpParamSerializerJQLike(frm), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
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
                window.open("<?php echo SITE_URL; ?>pages/employees/delete.php?id=" + id, "", "width=300,height=400");
                window.location.reload();
            }
        }


        $scope.searchEmployee = () => {
            $scope.getEmployees(1);
        }

        $scope.perPage = () => {
            $scope.getEmployees($scope.currentPage);
        }

        $scope.getEmployees($scope.currentPage);

        $scope.pageChanged = (page) => {
            $scope.currentPage = page;
            $scope.getEmployees(page)
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
                $scope.getEmployees(1);
            });
        };

    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike) {
        $scope.form = {
            full_name: "",
            phoneNumber: "",
            address: "",
            type: false
        }

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };
        $scope.ok = function() {
            $http.post('create.php', $httpParamSerializerJQLike({
                ...$scope.form,
                doj: $scope.form.doj ? moment($scope.form.doj).format('YYYY-MM-DD') : null
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
            <h3 class="modal-title" id="modal-title">Add Employee</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="full_name">Name</label>
                    <input id="full_name" type="text" ng-model="form.full_name" class="form-control" placeholder="Name">
                </div>
                <div class="form-group col-sm-6">
                    <label for="email">Email</label>
                    <input id="email" type="email" ng-model="form.email" class="form-control" placeholder="Email">
                </div>
                <div class="form-group col-sm-6">
                    <label for="designation">Job Title</label>
                    <input id="designation" type="text" ng-model="form.designation" class="form-control" placeholder="designation">
                </div>
                <div class="form-group col-sm-6">
                    <label for="doj">Date of Joining</label>
                    <input id="doj" type="text" date-range-picker ng-model="form.doj" class="form-control" placeholder="Date of Joining" options="{autoApply: true, singleDatePicker: true}">
                </div>
                <div class="form-group col-sm-6">
                    <label for="contact_1">Contact No 1</label>
                    <input id="contact_1" type="text" ng-model="form.contact_1" class="form-control" placeholder="Mobile No">
                </div>
                <div class="form-group col-sm-6">
                    <label for="contact_2">Contact No 2</label>
                    <input id="contact_2" type="text" ng-model="form.contact_2" class="form-control" placeholder="Mobile No">
                </div>
                <div class="form-group col-sm-6">
                    <label for="emg_contact_1">Emg. Contact No 1</label>
                    <input id="emg_contact_1" type="text" ng-model="form.emg_contact_1" class="form-control" placeholder="Mobile No">
                </div>
                <div class="form-group col-sm-6">
                    <label for="emg_contact_2">Emg. Contact No 2</label>
                    <input id="emg_contact_2" type="text" ng-model="form.emg_contact_2" class="form-control" placeholder="Mobile No">
                </div>
                <div class="form-group col-sm-6">
                    <label for="salary">Salary</label>
                    <input id="salary" type="text" ng-model="form.salary" class="form-control" placeholder="Salary">
                </div>
                <div class="form-group col-sm-6">
                    <label for="sopening_balance">Opening Balance</label>
                    <input id="sopening_balance" type="text" ng-model="form.opening_balance" class="form-control" placeholder="Opening Balance">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>