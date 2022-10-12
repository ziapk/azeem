<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $productObj = new Customers();
    
    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];


    $error = "";
    $message = "";
    if(!empty($_POST) && isset($_POST['update'] )) {

        $error = "";

        
        if(empty($_POST['full_name'])) {
            $error = "Please fill all fields";
        }
        else {

            $data = [                
                'id' => $_GET['id'],
                'full_name' => $_POST['full_name'],
                'code' => $_POST['code'],
                'phoneNumber' => $_POST['phoneNumber'],
                'address' => $_POST['address']
            ];

            $update = $productObj->updateCustomer($data);

            if($update) {
                $message = "Successfully saved!";
            } else {
                $message = "Nothing change";
            }
        }
    }

      
    if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
        header('location: '.SITE_URL.'');
    }

    $store = $productObj->getCustomer($_GET['id']);
    
    if(empty($store)) {
        header('location: '.SITE_URL.'');
    }

    echo mainHeader();

?>
<div class="container">
    <h4>Update Customer</h4>
    
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div  class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" name="full_name" class="form-control" value="<?php echo $store['full_name'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="code" type="text" class="form-control" placeholder="code" value="<?php echo $store['code'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="address" type="text" class="form-control" placeholder="address" value="<?php echo $store['address'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber" type="text" class="form-control" placeholder="phoneNumber" value="<?php echo $store['phoneNumber'];?>">
            </div>
        </div>
        <p class="text-right">
            <input type="submit" name="update" value="Save" class="btn btn-success">
        </p>
    </form>
</div>

<?php
echo mainFooter();  