<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
$dentry = new DoubleEntry();
$user = [];
$type = 's';
if ($_GET['t'] == 'c') {
    $type = 'c';
    $customers = new Customers();
    $user = $customers->getUserByAccount($_GET['id']);
} elseif ($_GET['t'] == 's') {
    $suppliers = new Suppliers();
    $user = $suppliers->getUserByAccount($_GET['id']);
} elseif ($_GET['t'] == 'emp') {
    $employees = new Employees();
    $user = $employees->getUserByAccount($_GET['id']);
} elseif ($_GET['t'] == 'e') {
    $expenses = new Categories();
    $user = $expenses->expenseByAccount($_GET['id']);
}

$journel = $dentry->getLedgerByAccount(['account_id' => $_GET['id'], 'type' => $type, 'user' => $user['account']]);
$summery = $journel['summery'];


$paid = $summery['paid'];
$amount = $summery['due'];
$balance = $summery['balance'];

$url = '?';

foreach ($_GET as $key => $value) {
    $url .= $key . "=" . $value . "&";
}

mainHeader();
?>
<div class="container" ng-controller="coaController">
    <div class="row">
        <div class="col-sm-8">
            <h2>Account Summary <br class="visible-xs" /> <a class="btn btn-primary mt-10" href="<?php echo 'summeryDownload.php' . $url; ?>" target="_blank"><span class="fa fa-file"></span> PDF</a>
                <?php if (empty($user['is_default'])) { ?><a class="btn btn-primary mt-10" href="javascript:void(0)" ng-click="sendSummery()"><span class="fa fa-envelope"></span>&nbsp; {{sending ? 'Sending...' : 'Send Summery'}}</a><?php } ?>
            </h2>
            <p><?php echo $user['full_name']; ?></p>
            <p><?php echo $user['address']; ?> (<?php echo $user['company']; ?>) </p>
            <p>Contact No: <?php echo $user['phoneNumber']; ?></p>
        </div>
        <div class="col-sm-4 form-group">
            <table class="table table-sm table-striped">
                <tr>
                    <td>Openining Balance:</td>
                    <td style="font-weight: bold; font-size: 1.3em" width="140"><?php echo number_format($user['account']['opening_balance'], 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Total Invoices:</td>
                    <td style="font-weight: bold; font-size: 1.3em"><?php echo $summery['total']; ?><br /></td>
                </tr>
                <tr>
                    <td>Total Amount:</td>
                    <td style="font-weight: bold; font-size: 1.3em"><?php echo number_format($amount, 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Total Paid:</td>
                    <td style="font-weight: bold; font-size: 1.3em"><?php echo number_format($paid, 0); ?><br /></td>
                </tr>
                <tr>
                    <td>Closing Balance:</td>
                    <td style="font-weight: bold; font-size: 1.3em"><?php echo number_format($balance, 0); ?></td>
                </tr>
            </table>
        </div>
    </div>
    <!-- <form method="GET" action=""> -->
    <div class="table-responsive">
        <table width="100%" class="table table-striped">
            <thead>
                <tr>
                    <th>T.ID</th>
                    <th>Date</th>
                    <th>Order ID</th>
                    <th>Ref.#</th>
                    <th>Description</th>
                    <th>Entry Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Running Balance</th>
                    <th></th>
                </tr>
                <!-- <tr>
                    <th>
                        <input type="hidden" name="from" value="{{startDate}}">
                        <input type="hidden" name="to" value="{{endDate}}">
                        <?php foreach ($_GET as $key => $value) { ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                        <?php } ?>
                    </th>
                    <th>
                        <input date-range-picker class="form-control date-picker" type="text" ng-model="form.betweenDate" options="{ autoApply: true, locale: {format: 'DD/MM/YYYY'}, changeCallback: setRange(form.betweenDate)}">
                    </th>
                    <th><input class="form-control" name="order_id" />
                    </th>
                    <th><input class="form-control" name="reference" /></th>
                    <th><input class="form-control" name="description" /></th>
                    <th><input class="form-control" name="entry_type" /></th>
                    <th colspan="3"><button type="submit" class="btn btn-default">Go</button></th>
                </tr> -->
            </thead>
            <tbody>
                <tr ng-repeat="row in rows">
                    <td>{{row.transaction_id}}</td>
                    <td>{{row.transaction_date}}</td>
                    <td>
                        <a ng-if="row.order_ref" href="javascript:void(0)" ng-click="openRecipt(row.order_ref)">{{row.order_custom_id}}</a>
                        <a ng-if="row.supply_ref" href="javascript:void(0)" ng-click="openRecipt2(row.supply_ref)">{{row.supply_ref}}</a>
                        <a ng-if="row.return_ref" href="javascript:void(0)" ng-click="openRecipt3(row.return_ref)">{{row.return_ref}}</a>
                    </td>
                    <td>{{row.reference}}</td>
                    <td>{{row.v_description}}</td>
                    <td>{{row.transsaction_type}}</td>
                    <td style="text-align: right">{{row.debitAmount | number: 0}}</td>
                    <td style="text-align: right">{{row.creditAmount | number: 0}}</td>
                    <td style="text-align: right; font-weight: bold; font-size: 1.3em" ng-class="{'text-danger': row.balance < 0}">{{row.balance | number: 0}}</td>
                    <td>
                        <?php if ($userData['role'] === 'owner') { ?>
                            <a ng-if="['DIRECT_RECEIVING', 'DIRECT_PAYMENT', 'ROYALTY PAYMENT', 'ADJUSTMENT', 'EXPENSE'].includes(row.transsaction_type) " href="javascript:void(0)" class="text-danger" ng-click="addCustomer(row)">EDIT</a>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- </form> -->
</div>

<script>
    app.controller('coaController', function($scope, $http, $httpParamSerializerJQLike, $window, $uibModal) {
        var site_url = '<?php echo SITE_URL ?>';
        $scope.rows = <?php echo safe_json_encode($journel['rows']); ?>;
        $scope.startDate = moment('<?php echo $_GET['from']; ?>' || moment());
        $scope.endDate = moment('<?php echo $_GET['to']; ?>' || moment());
        $scope.account_id = <?php echo $_GET['id']; ?>;
        $scope.form = {
            betweenDate: {
                startDate: moment($scope.startDate),
                toDate: moment($scope.toDate),
            }
        };
        $scope.sending = false;
        $scope.setRange = (range) => {
            console.log(range.startDate, )
            $scope.startDate = range.startDate?.format('YYYY-MM-DD');
            $scope.endDate = range.endDate?.format('YYYY-MM-DD');
        }

        $scope.sendSummery = () => {
            $scope.sending = true;
            $http.post('sendSummery.php', $httpParamSerializerJQLike({
                account_id: [$scope.account_id],
                from: '',
                to: '',
                type: 'c',
                customer_name: "<?php echo $user['full_name']; ?>",
            }), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                $scope.sending = false;
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

                $scope.sending = false;
            });
        }

        $scope.addCustomer = function(item) {
            console.log('item', item);
            $scope.form = null
            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'addCustomer.html',
                controller: 'ModalInstanceCtrl',
                resolve: {
                    parentData: function() {
                        return item
                    }
                }
            }).closed.then(function() {
                $window.location.reload();
            });
        };

        $scope.openRecipt = (id) => {
            $window.open("<?php echo SITE_URL; ?>print?id=" + id + "&detail=true&largeView=large", "", "width=800,height=600");
        }

        $scope.openRecipt2 = (id) => {
            $window.open("<?php echo SITE_URL; ?>print/supply.php?id=" + id + "&detail=true&largeView=large", "", "width=800,height=600");
        }

        $scope.openRecipt3 = (id) => {
            $window.open("<?php echo SITE_URL; ?>print/return.php?id=" + id + "&detail=true&largeView=large", "", "width=800,height=600");
        }
    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike, parentData) {
        $scope.form = {
            transaction_id: parentData.transaction_id,
            amount: parseFloat(parentData.creditAmount) || parseFloat(parentData.debitAmount)
        }

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post('updateTransaction.php', $httpParamSerializerJQLike($scope.form), {
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
            <h3 class="modal-title" id="modal-title">Add Customer</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="form-group">
                <label for="samount">Transaction Amount [TID: {{form.transaction_id}}]</label>
                <input id="samount" type="text" ng-model="form.amount" class="form-control" placeholder="Amount">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>

<?php

mainFooter();
?>