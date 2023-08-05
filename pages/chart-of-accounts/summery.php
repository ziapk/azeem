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

$journel = $dentry->getLedgerByAccount(['account_id' => $_GET['id'], 'type' => $type]);
$summery = $journel['summery'];

if ($_GET['t'] == 'c') {
    $summery['debit'] += $user['account']['opening_balance'];
} else {
    $summery['credit'] += $user['account']['opening_balance'];
}
$paid = in_array($_GET['t'], ['s', 'emp']) ? $summery['debit'] : $summery['credit'];
$amount = in_array($_GET['t'], ['s', 'emp']) ? $summery['credit'] : $summery['debit'];
$balance = ($amount - $paid);

$url = '?';

foreach ($_GET as $key => $value) {
    $url .= $key . "=" . $value . "&";
}

mainHeader();
?>
<div class="container" ng-controller="coaController">
    <table width="100%">
        <tr>
            <td>
                <h2>Account Summary <a class="btn btn-primary" href="<?php echo 'summeryDownload.php' . $url; ?>" target="_blank">Generate PDF</a></h2>
                <p><?php echo $user['full_name']; ?></p>
                <p><?php echo $user['address']; ?> (<?php echo $user['company']; ?>) </p>
                <p>Contact No: <?php echo $user['phoneNumber']; ?></p>
            </td>
            <td width="300">
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
                </table>

            </td>
        </tr>
    </table>
    <!-- <form method="GET" action=""> -->
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
            <?php foreach ($journel['rows'] as $key => $value) { ?>
                <tr>
                    <td><?php echo $value['transaction_id']; ?></td>
                    <td><?php echo $value['transaction_date']; ?></td>
                    <td>
                        <?php if (!empty($value['order_ref'])) { ?>
                            <a href="javascript:void(0)" onclick="openRecipt(<?php echo $value['order_ref']; ?>)"><?php echo $value['order_ref']; ?></a>
                        <?php } elseif (!empty($value['supply_ref'])) { ?>
                            <a href="javascript:void(0)" onclick="openRecipt2(<?php echo $value['supply_ref']; ?>)"><?php echo $value['supply_ref']; ?></a>
                        <?php } elseif (!empty($value['return_ref'])) { ?>
                            <a href="javascript:void(0)" onclick="openRecipt3(<?php echo $value['return_ref']; ?>)"><?php echo $value['return_ref']; ?></a>
                        <?php } ?>
                    </td>
                    <td><?php echo $value['reference']; ?></td>
                    <td><?php echo $value['v_description']; ?></td>
                    <td><?php echo $value['transsaction_type']; ?></td>
                    <td style="text-align: right"><?php echo number_format($value['debitAmount'], 2); ?></td>
                    <td style="text-align: right"><?php echo number_format($value['creditAmount'], 2); ?></td>
                    <td style="text-align: right; <?php if ($value['balance'] < 0) {
                                                        echo "color: red";
                                                    } ?>"><?php echo number_format($value['balance'], 2); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <!-- </form> -->
</div>

<script>
    app.controller('coaController', function($scope, $http, $httpParamSerializerJQLike) {
        var site_url = '<?php echo SITE_URL ?>';
        $scope.startDate = moment('<?php echo $_GET['from']; ?>' || moment());
        $scope.endDate = moment('<?php echo $_GET['to']; ?>' || moment());
        $scope.form = {
            betweenDate: {
                startDate: moment($scope.startDate),
                toDate: moment($scope.toDate),
            }
        };
        $scope.setRange = (range) => {
            console.log(range.startDate, )
            $scope.startDate = range.startDate?.format('YYYY-MM-DD');
            $scope.endDate = range.endDate?.format('YYYY-MM-DD');
        }
    });
</script>

<?php

mainFooter();
?>

<script>
    function openRecipt(id) {
        window.open("<?php echo SITE_URL; ?>print?id=" + id + "&detail=true&largeView=large", "", "width=800,height=600");
    }

    function openRecipt2(id) {
        window.open("<?php echo SITE_URL; ?>print/supply.php?id=" + id + "&detail=true&largeView=large", "", "width=800,height=600");
    }

    function openRecipt3(id) {
        window.open("<?php echo SITE_URL; ?>print/return.php?id=" + id + "&detail=true&largeView=large", "", "width=800,height=600");
    }
</script>