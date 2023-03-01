<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $supplierObj = new Suppliers();


    $error = "";
    $message = "";

    $supplier = $supplierObj->getSupplier($_GET['id']);

    if(!empty($_POST) && isset($_POST['update'] )) {

        $error = "";

        
        if(empty($_POST['name'])) {
            $error = "Please fill all fields";
        }
        else {

            $data = [                
                'id' => $_GET['id'],
                'name' => $_POST['name'],
                'contact' => $_POST['contact'],
                'company' => $_POST['company'],
                'title' => $_POST['title'],
                'address' => $_POST['address']
            ];

            $update = $supplierObj->updateSupplier($data);
            if(!empty($supplier['account_id'])) {
                $de = new DoubleEntry();
                $de->setOpeningBalance($supplier['account_id'], $_POST['opening_balance']);
            }

            if($update) {
                $message = "Successfully saved!";
            } else {
                $message = "Nothing change";
            }
        }
    }

    echo mainHeader(['page'=> 'supplier']);  
    if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
        header('location: '.SITE_URL.'');
    }

    $supplier = $supplierObj->getSupplier($_GET['id']);

    if(empty($supplier)) {
        header('location: '.SITE_URL.'');
        echo 'Not Found'; exit;
    }
?>
<div class="container" ng-controller="supplierController">
    <h4>Update Supplier</h4>
    
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div  class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-4 form-group">
                <label>Supplier Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $supplier['name'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Supplier Contact</label>
                <input name="contact" type="text" class="form-control" placeholder="Contact" value="<?php echo $supplier['contact'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Supplier title</label>
                <input name="title" type="text" class="form-control" placeholder="title" value="<?php echo $supplier['title'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Supplier Company</label>
                <input name="company" type="text" class="form-control" placeholder="company" value="<?php echo $supplier['company'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Supplier Address</label>
                <input name="address" type="text" class="form-control" placeholder="Address" value="<?php echo $supplier['address'];?>">
            </div>
            <div class="clearfix"></div>
            <?php if(!empty($supplier['account'])) { ?>
                <div class="col-sm-4 form-group">
                    <label for="opening_balance">Opening Balance</label>
                    <input id="opening_balance" name="opening_balance" type="text" class="form-control" placeholder="Supplier's Opening Balance" value="<?php echo $supplier['account']['opening_balance'];?>">
                </div>
            <?php } ?>
            <div class="col-sm-4 form-group">
                <?php if(!empty($supplier['account'])) { ?>
                    <label>Linked Account</label>
                    <strong class="form-control"><?php echo $supplier['account']['title'].' ('.$supplier['account']['code'].')';?></strong>
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
    app.controller('supplierController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window, $log) {
        $scope.supplier = <?php echo json_encode($supplier);?>;
        $scope.linkAccount = () => {;
            $http.post('./linkAccount.php', $httpParamSerializerJQLike($scope.supplier), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function(res) {
                alert(res.data.message);
                window.location.reload();
            });
        }
    });
</script>
<?php
echo mainFooter();  