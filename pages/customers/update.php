<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Customers();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];

$store = $productObj->getCustomer($_GET['id']);

$error = "";
$message = "";
if (!empty($_POST) && isset($_POST['update'])) {

    $error = "";


    if (empty($_POST['full_name'])) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'id' => $_GET['id'],
            'full_name' => $_POST['full_name'],
            'code' => $_POST['code'],
            'title' => $_POST['title'],
            'company' => $_POST['company'],
            'type' => $_POST['type'],
            'phoneNumber' => $_POST['phoneNumber'],
            'type' => $_POST['type'],
            'address' => $_POST['address']
        ];
        $update = $productObj->updateCustomer($data);
        if (!empty($store['account_id'])) {
            $de = new DoubleEntry();
            $de->setOpeningBalance($store['account_id'], $_POST['opening_balance']);
        }




        if ($update) {
            $message = "Successfully saved!";
        } else {
            $message = "Nothing change";
        }
    }
}


$store = $productObj->getCustomer($_GET['id']);

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: ' . SITE_URL . '');
}



if (empty($store)) {
    header('location: ' . SITE_URL . '');
}



echo mainHeader();

?>
<div class="container" ng-controller="customerController">
    <h4>Update Customer</h4>

    <form method="POST" action="" autocomplete="off">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" name="full_name" class="form-control" value="<?php echo $store['full_name']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="code" type="text" class="form-control" placeholder="code" value="<?php echo $store['code']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="title" type="text" class="form-control" placeholder="title" value="<?php echo $store['title']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="company" type="text" class="form-control" placeholder="company" value="<?php echo $store['company']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="address" type="text" class="form-control" placeholder="address" value="<?php echo $store['address']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber" type="text" class="form-control" placeholder="phoneNumber" value="<?php echo $store['phoneNumber']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <label for="type">Show on Closing Report</label>
                <select name="type" id="type" class="form-control">
                    <option <?php echo $store['type'] == 2 ? 'selected' : ''; ?> value="2">No</option>
                    <option <?php echo $store['type'] == 1 ? 'selected' : ''; ?> value="1">Yes</option>
                </select>
            </div>
            <?php if (!empty($store['account'])) { ?>
                <div class="col-sm-4 form-group">
                    <label for="opening_balance">Opening Balance</label>
                    <input id="opening_balance" name="opening_balance" type="text" class="form-control" placeholder="Customer's Opening Balance" value="<?php echo $store['account']['opening_balance']; ?>">
                </div>
            <?php } ?>
            <div class="col-sm-4 form-group">
                <?php if (!empty($store['account'])) { ?>
                    <label for="opening_balance">Linked Account</label>
                    <strong class="form-control"><?php echo $store['account']['title'] . ' (' . $store['account']['code'] . ')'; ?></strong>
                <?php } else { ?>
                    <p>No Linked Account: <a href="javascript:void(0)" ng-click="linkAccount()">Link and Account</a></p>
                <?php } ?>
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
