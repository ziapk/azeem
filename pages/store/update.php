<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $storeObj = new Store();
    $doubleentryObj = new DoubleEntry();
    $accountList = $doubleentryObj->getAccounts($_GET['id']);

    $error = "";
    $message = "";
    if(!empty($_POST) && isset($_POST['update'] )) {

        $error = "";

        
        if(empty($_POST['full_name']) || empty($_POST['store_type'] || empty($_POST['status']))) {
            $error = "Please fill all fields";
        }
        else {

            
            

            $data = [                
                'id' => $_GET['id'],
                'full_name' => $_POST['full_name'],
                'store_type' => $_POST['store_type'],
                'status' => $_POST['status'],
                'location' => !empty($_POST['location']) ? $_POST['location'] : "",
                'city' => !empty($_POST['city']) ? $_POST['city'] : "",
                'postalCode' => !empty($_POST['postalCode']) ? $_POST['postalCode'] : "",
                'phoneNumber1' => !empty($_POST['phoneNumber1']) ? $_POST['phoneNumber1'] : "",
                'phoneNumber2' => !empty($_POST['phoneNumber2']) ? $_POST['phoneNumber2'] : "",
                'phoneNumber3' => !empty($_POST['phoneNumber3']) ? $_POST['phoneNumber3'] : "",
                'status' => !empty($_POST['status']) ? $_POST['status'] : 1,
            ];

            $photo = $_FILES['image'];
            $uploaded = false;
            $image = "";
            if(isset($photo) && count($photo) ) {
                if($photo['error'] == 0) {
                    $img = explode('.', $photo['name']);
                    $photo['dst_path'] 	= dirname(__FILE__).'/../../assets/clients/';
                    
                    $data['image'] = $shopData['id'].'.'.$img[1];

                    if (!file_exists($photo['dst_path'])) {

                        mkdir($photo['dst_path'], 0777, true);

                    }
                    
                    $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'].$data['image']);
                    if($moved) {	
                        $uploaded = true;

                    }
            
                }
            }


            $update = $storeObj->updateStore($data);

            if($update) {
                $message = "Successfully saved!";
            } else {
                $message = "Nothing change";
            }
        }
    }



    echo mainHeader();  
    if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
        header('location: '.SITE_URL.'');
    }

    $storeData = $storeObj->getStore($_GET['id']);
    if(empty($storeObj)) {
        header('location: '.SITE_URL.'');
    }

    $storeTypesArr = $storeObj->getStoreTypes();

?>
<div class="container" ng-controller="accountingController">
    <h4>Update Store</h4>
    
    <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div  class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="product-image"><img src="<?php echo SITE_URL.'assets/clients/'.$storeData['image'];?>" alt="" /></div>
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" name="full_name" class="form-control" value="<?php echo $storeData['full_name'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <select name="store_type" class="form-control">
                    <?php foreach ($storeTypesArr as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($storeData['store_type'] == $type['id']) {echo 'selected';};?> ><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <input name="image" type="file">
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-4 form-group">
                <input name="location" type="text" class="form-control" placeholder="Location" value="<?php echo $storeData['location'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="city" type="text" class="form-control" placeholder="City" value="<?php echo $storeData['city'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="postalCode" type="text" class="form-control" placeholder="Postal code" value="<?php echo $storeData['postalCode'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber1" type="text" class="form-control" placeholder="Cell 1" value="<?php echo $storeData['phoneNumber1'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber2" type="text" class="form-control" placeholder="Cell 2" value="<?php echo $storeData['phoneNumber2'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber3" type="text" class="form-control" placeholder="Cell 3" value="<?php echo $storeData['phoneNumber3'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <select name="status" class="form-control">
                    <?php foreach ($statusArr as $key => $type) {?>
                        <option value="<?php echo $key?>" <?php if($storeData['status'] == $key) {echo 'selected';};?> ><?php echo $type;?></option>
                        <?php }?>
                    </select>
            </div>
            <div class="col-sm-12">
                <p class="text-right">
                    <input type="submit" name="update" value="Save Store" class="btn btn-success">
                </p>
                <h3 class="section-title">Accounts to automate process</h3>
                <hr>
            </div>
            <div class="col-sm-4 form-group">
                <label>Inventory</label>
                <select class="form-control c-select" ng-model="account.assets">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Cash/Sale</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.cash" placeholder="Account Name" typeahead-on-select="selectAccount(account.cash, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.cash">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Expense</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.expense" placeholder="Account Name" typeahead-on-select="selectAccount(account.expense, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.expense">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Receivings Account</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.receiving" placeholder="Account Name" typeahead-on-select="selectAccount(account.receiving, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.receiving">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Receivable - for customer</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.receivable" placeholder="Account Name" typeahead-on-select="selectAccount(account.receivable, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.receivable">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Payable - for supplier</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.payable" placeholder="Account Name" typeahead-on-select="selectAccount(account.payable, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.payable">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Sale - Discount</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.sale_discount" placeholder="Account Name" typeahead-on-select="selectAccount(account.sale_discount, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.sale_discount">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Purchase - Discount</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.purchase_discount" placeholder="Account Name" typeahead-on-select="selectAccount(account.purchase_discount, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.purchase_discount">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Sale - Returns</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.sale_returns" placeholder="Account Name" typeahead-on-select="selectAccount(account.sale_returns, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.sale_returns">
                    <option ng-repeat="acc in accountsList" ng-value="acc.id">
                        {{acc.title}} - ({{acc.code}} - {{ (acc.account_type == '1' ? 'ASSETS' : acc.account_type == '2' ? 'LIABILITIES' : acc.account_type == '3' ? 'EQUITY' : acc.account_type == '4' ? 'INCOME' : 'EXPENSES' ) }}) 
                    </option>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Purchase - Returns</label>
                <!-- <input type="text" class="type-ahead-input form-control" ng-model="account.purchase_returns" placeholder="Account Name" typeahead-on-select="selectAccount(account.purchase_returns, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0"> -->
                <select class="form-control c-select" ng-model="account.purchase_returns">
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

app.controller('accountingController', function($scope, $http, $httpParamSerializerJQLike, $window){
    $scope.account = <?php echo json_encode($storeData);?>;
    $scope.accountsList = <?php echo json_encode($accountList);?>;
    $scope.searchGroup = function (term) {
        return $scope.accountsList.filter((row) => row.title.toLowerCase().includes(term.toLowerCase()) || row.code.toLowerCase().includes(term.toLowerCase()))
    }
    $scope.selectAccount = function (item, data) {
        $scope.account_id = data.account_id;
    }

    $scope.updateAccounts = (accounts) => {
        return $http.post("../chart-of-accounts/updateAccounts.php", $httpParamSerializerJQLike({ cash: accounts.cash.id || accounts.cash, payable: accounts.payable.id || accounts.payable, receiving: accounts.receiving.id || accounts.receiving, receivable: accounts.receivable.id || accounts.receivable, expense: accounts.expense.id || accounts.expense, sale_discount: accounts.sale_discount.id || accounts.sale_discount, purchase_discount: accounts.purchase_discount.id || accounts.purchase_discount, sale_returns: accounts.sale_returns.id || accounts.sale_returns, purchase_returns: accounts.purchase_returns.id || accounts.purchase_returns, assets: accounts.assets.id || accounts.assets, shopId: <?php echo $_GET['id'];?> }), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
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