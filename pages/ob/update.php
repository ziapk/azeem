<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new DoubleEntry();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];

$store = $productObj->getOB($shop['id'], $_GET['id']);

$error = "";
$message = "";
if (!empty($_POST) && isset($_POST['update'])) {

    $error = "";


    if (empty($_POST['amount']) || empty($_POST['sale_date'])) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'id' => $_GET['id'],
            'amount' => $_POST['amount'],
            'sale_date' => $_POST['sale_date']
        ];
        $update = $productObj->updateOB($data);

        if ($update) {
            $message = "Successfully saved!";
        } else {
            $message = "Nothing change";
        }
    }
}


$store = $productObj->getOb($shop['id'], $_GET['id']);


if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: ' . SITE_URL . '');
}



if (empty($store)) {
    header('location: ' . SITE_URL . '');
}



echo mainHeader();

?>
<div class="container" ng-controller="customerController">
    <h4>Update Opening Balance</h4>

    <form method="POST" action="" autocomplete="off">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-3 form-group">
                <input type="text" name="amount" class="form-control" value="<?php echo $store['amount']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input type="hidden" name="id" value="<?php echo $store['id']; ?>" />
                <input name="sale_date" date-range-picker class="form-control date-picker" type="text" ng-model="sale_date" options="{ autoApply: true, singleDatePicker: true, }" />
            </div>
        </div>
        <p class="text-right">
            <input type="submit" name="update" value="Save" class="btn btn-success">
        </p>
    </form>
</div>
<script type="text/javascript">
    app.controller('customerController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window, $log) {
        $scope.customer = <?php echo json_encode($store); ?>;
        $scope.sale_date = moment(<?php $store['sale_date']; ?>);
        $scope.linkAccount = () => {
            ;
            $http.post('./linkAccount.php', $httpParamSerializerJQLike($scope.customer), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                alert(res.data.message);
                window.location.reload();
            });
        }
    });
</script>

<?php
echo mainFooter();
