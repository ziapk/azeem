<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getOwnerCategories($shop['owner_id']);
echo mainHeader(['page' => 'customer']);

$customerObj = new Customers();

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: ' . SITE_URL . '');
}

$user = $customerObj->getUserByAccount($_GET['id']);
if (empty($user)) {
    header('location: ' . SITE_URL . '');
}

$dentry = new DoubleEntry();
$journel = $dentry->getLedgerByAccount(['account_id' => $user['account_id'], 'type' => 'c', 'user' => $user['account']]);
$summery = $journel['summery'];

$paid = $summery['paid'];
$amount = $summery['due'];
$balance = $summery['balance'];



$data = [
    'paid' => $paid,
    'amount' => $amount,
    'balance' => $balance
];

?>
<div class="container" ng-controller="productController">
    <table width="100%">
        <tr>
            <td valign="top">
                <h2>Account Summary</h2>
                <p><strong><?php echo $user['full_name']; ?></strong></p>
                <p><?php echo $user['address']; ?> (<?php echo $user['company']; ?>) </p>
                <p>Contact No: <?php echo $user['phoneNumber']; ?></p>
            </td>
            <td width="500">
                <table width="100%">
                    <tr>
                        <td>Opening Balance:</td>
                        <td width="140"><?php echo number_format($user['account']['opening_balance'], 2); ?><br /></td>
                    </tr>
                    <tr>
                        <td>Total Invoices:</td>
                        <td><?php echo $summery['total']; ?><br /></td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td><?php echo number_format($amount, 2); ?><br /></td>
                    </tr>
                    <tr>
                        <td>Total Paid:</td>
                        <td><?php echo number_format($paid, 2); ?><br /></td>
                    </tr>
                    <tr>
                        <td>Closing Balance:</td>
                        <td><?php echo number_format($balance, 2); ?></td>
                    </tr>
                    <tr>
                        <td>
                            <h3 class="text-success h3">Receiving Amount = {{wallet}}</h3>
                            <h4 class="text-danger">REMAINING BALANCE = {{data.balance - wallet}}</h4>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
    <div class="row">
        <div class="col-sm-2 form-group">
            <label>Reference No</label>
            <input ng-model="reference" value={{reference}} class="form-control" />
        </div>
        <div class="col-sm-2 form-group">
            <label>Bill No</label>
            <input ng-model="order_ref" value={{order_ref}} class="form-control" />
        </div>
        <div class="col-sm-5 form-group">
            <label>Description</label>
            <input ng-model="summery" value={{summery}} class="form-control" />
        </div>
        <div class="col-sm-3">
            <label>Receiving Amount</label>
            <input type="number" ng-model="wallet" value={{wallet}} ng-change="changeValue()" class="form-control" />
        </div>
        <div class="col-sm-12 text-right">
            <strong class="text-success h3">Receiving Amount = {{wallet}}</strong>
            <div class="btn-group">
                <label class="btn btn-default" ng-repeat="li in modes">
                    <input type="radio" name="mode" ng-model="payment_mode" ng-value="li.id" ng-change="printValue(li)">
                    {{li.title}}
                </label>
            </div>
            <input type="button" ng-click="payToWallet()" value="Generate Receiving" class="btn btn-danger" />
        </div>
    </div>
</div>
<script>
    app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
        $scope.data = <?php echo json_encode($data); ?>; //$scope.data.records;
        $scope.id = <?php echo json_encode($_GET['id']); ?>; //$scope.data.records;
        $scope.wallet = $scope.data.balance;

        $scope.payment_mode = '1';
        $scope.modes = [];


        $scope.payToWallet = function() {

            $http.post("<?php echo SITE_URL ?>api/receivePayments.php", $httpParamSerializerJQLike({
                    amount: $scope.wallet,
                    id: $scope.id,
                    summery: $scope.summery,
                    ref_no: $scope.reference,
                    order_ref: $scope.order_ref
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    console.log(response)
                    alert('Amount Paid Successfully, transaction id is ' + response.data.transaction.id);
                    $window.location.assign('<?php echo SITE_URL . 'pages/customers' ?>');
                });
        }

        $scope.searchMode = function() {
            return $http.get("<?php echo SITE_URL ?>api/getPaymentModes.php")
                .then(function(response) {
                    $scope.modes = response.data.records;
                    return response.data
                });
        }

        $scope.searchMode();


    });
</script>
<?php
echo mainFooter();
