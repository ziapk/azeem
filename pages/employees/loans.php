<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$customers = new  Employees();
$customersData = $customers->getEmployees($shop['id']);
echo mainHeader(['page' => 'loans']);
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
    <a href="javascript:void(0)" ng-click="addCustomer()" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Loan</a>
    <h4 class="section-title">Loans</h4>
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
            $http.get($scope.siteUrl + "api/getLoans.php", {
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

        $scope.employeesList = <?php echo safe_json_encode($customersData); ?>;
        $scope.oemployeesList = <?php echo safe_json_encode($customersData); ?>;

        $scope.refreshEmployees = search => {
            $scope.employeesList = $scope.oemployeesList.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
        }

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };
        $scope.ok = function() {
            $http.post('createLoan.php', $httpParamSerializerJQLike({
                ...$scope.form,
                employee_id: $scope.form.employee?.id ? $scope.form.employee?.id : null
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
            <h3 class="modal-title" id="modal-title">Track Loan Entry</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="row">
                <div class="col-sm-12 form-group">
                <label for="employee">Loan</label>
                <ui-select custom-dropdown ng-model="form.customer" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose an employee">
                    <ui-select-match placeholder="Enter an employee...">{{$select.selected.full_name}}</ui-select-match>
                    <ui-select-choices repeat="address in employeesList track by $index" refresh="refreshEmployees($select.search)" refresh-delay="0">
                        <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                    </ui-select-choices>
                    </ui-select>
                </div>
                <div class="col-sm-12 form-group">
                    <label for="description">Description</label>
                    <textarea rows="6" id="description" type="text" ng-model="form.description" class="form-control" placeholder="Description"></textarea>
                </div>
                <div class="form-group col-sm-4">
                    <label for="loan_applied">Loan Amount</label>
                    <input id="loan_applied" type="text" ng-model="form.loan_applied" class="form-control" placeholder="Loan Amount">
                </div>
                <div class="form-group col-sm-4">
                    <label for="loan_issued">Issued Amount</label>
                    <input id="loan_issued" type="number" ng-model="form.loan_issued" ng-change="form.installment_amount = form.installment_amount > form.loan_issued ? form.loan_issued : form.installment_amount" class="form-control" placeholder="Issued Amount">
                </div>
                <div class="form-group col-sm-4">
                    <label for="installment_amount">Installment Amount</label>
                    <input id="installment_amount" type="number"  ng-model="form.installment_amount" ng-change="form.installment_amount = form.installment_amount > form.loan_issued ? form.loan_issued : form.installment_amount" max="form.loan_issued" class="form-control" placeholder="Installment Amount">
                    <p ng-if="form.installment_amount && form.loan_issued">No of Installments: {{(form.loan_issued || 1) / (form.installment_amount || 1) | roundup  }}</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>