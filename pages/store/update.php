<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$storeObj = new Store();
$doubleentryObj = new DoubleEntry();
$accountList = $doubleentryObj->getAccounts([$_GET['id']]);
$sa = new ShopAccounts();
$accounts = $sa->getSAs($_GET['id']);

$error = "";
$message = "";
if (!empty($_POST) && isset($_POST['update'])) {

    $error = "";


    if (empty($_POST['full_name']) || empty($_POST['store_type'] || empty($_POST['status']))) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'id' => $_GET['id'],
            'full_name' => $_POST['full_name'],
            'store_type' => $_POST['store_type'],
            'status' => $_POST['status'],
            'location' => !empty($_POST['location']) ? $_POST['location'] : "",
            'city' => !empty($_POST['city']) ? $_POST['city'] : "",
            'company_email' => !empty($_POST['company_email']) ? $_POST['company_email'] : "",
            'postalCode' => !empty($_POST['postalCode']) ? $_POST['postalCode'] : "",
            'phoneNumber1' => !empty($_POST['phoneNumber1']) ? $_POST['phoneNumber1'] : "",
            'phoneNumber2' => !empty($_POST['phoneNumber2']) ? $_POST['phoneNumber2'] : "",
            'phoneNumber3' => !empty($_POST['phoneNumber3']) ? $_POST['phoneNumber3'] : "",
            'sale_terms' => !empty($_POST['sale_terms']) ? $_POST['sale_terms'] : "",
            'sale_terms_lg' => !empty($_POST['sale_terms_lg']) ? $_POST['sale_terms_lg'] : "",
            'status' => !empty($_POST['status']) ? $_POST['status'] : 1,
        ];

        $photo = $_FILES['image'];
        $uploaded = false;
        $image = "";
        if (isset($photo) && count($photo)) {
            if ($photo['error'] == 0) {
                $img = explode('.', $photo['name']);
                $photo['dst_path']     = dirname(__FILE__) . '/../../assets/clients/';

                $data['image'] = $shopData['id'] . '.' . $img[1];

                if (!file_exists($photo['dst_path'])) {

                    mkdir($photo['dst_path'], 0777, true);
                }

                $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'] . $data['image']);
                if ($moved) {
                    $uploaded = true;
                }
            }
        }


        $update = $storeObj->updateStore($data);

        if ($update) {
            $message = "Successfully saved!";
        } else {
            $message = "Nothing change";
        }
    }
}



echo mainHeader();
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: ' . SITE_URL . '');
}

$storeData = $storeObj->getStore($_GET['id']);
if (empty($storeObj)) {
    header('location: ' . SITE_URL . '');
}

$storeTypesArr = $storeObj->getStoreTypes();

?>
<div class="container" ng-controller="accountingController">
    <h4>Update Store</h4>

    <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="product-image"><img src="<?php echo SITE_URL . 'assets/clients/' . $storeData['image']; ?>" alt="" /></div>
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" name="full_name" class="form-control" value="<?php echo $storeData['full_name']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <select name="store_type" class="form-control">
                    <?php foreach ($storeTypesArr as $type) { ?>
                        <option value="<?php echo $type['id']; ?>" <?php if ($storeData['store_type'] == $type['id']) {
                                                                        echo 'selected';
                                                                    }; ?>><?php echo $type['full_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <input name="image" type="file">
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-4 form-group">
                <input name="location" type="text" class="form-control" placeholder="Location" value="<?php echo $storeData['location']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="city" type="text" class="form-control" placeholder="City" value="<?php echo $storeData['city']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="company_email" type="text" class="form-control" placeholder="company_email" value="<?php echo $storeData['company_email']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="postalCode" type="text" class="form-control" placeholder="Postal code" value="<?php echo $storeData['postalCode']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber1" type="text" class="form-control" placeholder="Cell 1" value="<?php echo $storeData['phoneNumber1']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber2" type="text" class="form-control" placeholder="Cell 2" value="<?php echo $storeData['phoneNumber2']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber3" type="text" class="form-control" placeholder="Cell 3" value="<?php echo $storeData['phoneNumber3']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <select name="status" class="form-control">
                    <?php foreach ($statusArr as $key => $type) { ?>
                        <option value="<?php echo $key ?>" <?php if ($storeData['status'] == $key) {
                                                                echo 'selected';
                                                            }; ?>><?php echo $type; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-12 form-group">
                <label>Sales Terms and Condition for Short Bill</label>
                <textarea rows="4" name="sale_terms" maxlength="255" type="text" class="form-control" placeholder="Sales Terms and Conditions (Max: 255 characters)"><?php echo $storeData['sale_terms']; ?></textarea>
            </div>
            <div class="col-sm-12 form-group">
                <label>Sales Terms and Condition for Large Bill</label>
                <textarea rows="4" name="sale_terms_lg" maxlength="255" type="text" class="form-control" placeholder="Sales Terms and Conditions (Max: 255 characters)"><?php echo $storeData['sale_terms_lg']; ?></textarea>
            </div>
            <div class="col-sm-12">
                <p class="text-right">
                    <input type="submit" name="update" value="Save Store" class="btn btn-success">
                </p>
                <h3 class="section-title">Accounts to automate process</h3>
                <hr>
            </div>
            <div class="col-sm-4 form-group" ng-repeat="ac in accountList">
                <label>{{ac.label_value}}</label>
                <select class="form-control c-select" ng-model="ac.account_id">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }})
                    </option>
                </select>
            </div>
            <div class="col-sm-12 form-group">
                <button type="button" class="btn btn-danger" ng-click="updateAccounts(account)">Update Accounts</button>
            </div>
        </div>
    </form>
</div>


<script type="text/javascript">
    app.controller('accountingController', function($scope, $http, $httpParamSerializerJQLike, $window) {
        $scope.account = <?php echo json_encode($storeData); ?>;
        $scope.accountList = <?php echo json_encode($accounts); ?>;
        $scope.accountsList = <?php echo json_encode($accountList); ?>;
        $scope.searchGroup = function(term) {
            return $scope.accountsList.filter((row) => row.title.toLowerCase().includes(term.toLowerCase()) || row.code.toLowerCase().includes(term.toLowerCase()))
        }
        $scope.selectAccount = function(item, data) {
            $scope.account_id = data.account_id;
        }

        $scope.updateAccounts = (accounts) => {
            return $http.post("../chart-of-accounts/updateAccountAll.php", $httpParamSerializerJQLike({
                    accounts: $scope.accountList
                }), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    alert('Updated Successfully!');
                });
        }

    });
</script>
<script type="text/ng-template" id="row.html">
    <a href="javascript:void(0)" class="list-item">
      <pre ng-bind-html="match.model.title | uibTypeaheadHighlight:query"></pre><br />
      <pre ng-bind-html="match.model.code | uibTypeaheadHighlight:query"></pre>
      <pre class="catName" ng-bind="(match.model.account_type == '1' ? 'ASSETS' : match.model.account_type == '2' ? 'LIABILITIES' : match.model.account_type == '3' ? 'EQUITY' : match.model.account_type == '4' ? 'INCOME' : 'EXPENSES' )"></pre>
  </a>
</script>


<?php
echo mainFooter();
