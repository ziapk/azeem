<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Employees();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];

$error = "";
$message = "";
$store = $productObj->getEmployee($_GET['id']);

if (!empty($_POST) && isset($_POST['update'])) {

    $error = "";


    if (empty($_POST['full_name'])) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'id' => $_GET['id'],
            "full_name" => !empty($_POST['full_name']) ? $_POST['full_name'] : null,
            "email" => !empty($_POST['email']) ? $_POST['email'] : null,
            "designation" => !empty($_POST['designation']) ? $_POST['designation'] : null,
            "doj" => !empty($_POST['doj']) ? $_POST['doj'] : null,
            "contact_1" => !empty($_POST['contact_1']) ? $_POST['contact_1'] : null,
            "contact_2" => !empty($_POST['contact_2']) ? $_POST['contact_2'] : null,
            "emg_contact_1" => !empty($_POST['emg_contact_1']) ? $_POST['emg_contact_1'] : null,
            "emg_contact_2" => !empty($_POST['emg_contact_2']) ? $_POST['emg_contact_2'] : null,
            "salary" => !empty($_POST['salary']) ? $_POST['salary'] : null,
            "opening_balance" => !empty($_POST['opening_balance']) ? $_POST['opening_balance'] : null,
        ];
        $update = $productObj->update($data);
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
            <div class="col-sm-6 col-md-4 form-group">
                <label for="full_name">Name</label>
                <input name="full_name" type="text" ng-model="customer.full_name" class="form-control" placeholder="Name">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="email">Email</label>
                <input name="email" type="email" ng-model="customer.email" class="form-control" placeholder="Email">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label>Job Title</label>
                <input name="designation" type="text" ng-model="customer.designation" class="form-control" placeholder="designation">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="doj">Date of Joining</label>
                <input name="doj" id="doj" type="text" ng-model="customer.doj" date-range-picker value="<?php echo $store['doj']; ?>" class="form-control" placeholder="Date of Joining" options="{autoApply: true, singleDatePicker: true}">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="contact_1">Contact No 1</label>
                <input name="contact_1" id="contact_1" type="text" ng-model="customer.contact_1" class="form-control" placeholder="Mobile No">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="contact_2">Contact No 2</label>
                <input name="contact_2" id="contact_2" type="text" ng-model="customer.contact_2" class="form-control" placeholder="Mobile No">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="emg_contact_1">Emg. Contact No 1</label>
                <input name="emg_contact_1" id="emg_contact_1" type="text" ng-model="customer.emg_contact_1" class="form-control" placeholder="Mobile No">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="emg_contact_2">Emg. Contact No 2</label>
                <input name="emg_contact_2" id="emg_contact_2" type="text" ng-model="customer.emg_contact_2" class="form-control" placeholder="Mobile No">
            </div>
            <div class="form-group col-sm-6 col-md-4">
                <label for="salary">Salary</label>
                <input name="salary" id="salary" type="text" ng-model="customer.salary" class="form-control" placeholder="Salary">
            </div>
            <?php if (!empty($store['account'])) { ?>
                <div class="col-sm-4 form-group">
                    <label for="opening_balance">Opening Balance</label>
                    <input ng-model="customer.account.opening_balance" id="opening_balance" name="opening_balance" type="text" class="form-control" placeholder="Customer's Opening Balance">
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
        $scope.customer.doj = moment($scope.customer.doj);
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
