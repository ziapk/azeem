<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $supplierObj = new Suppliers();


    $error = "";
    $message = "";
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
                'address' => $_POST['address']
            ];

            $update = $supplierObj->updateSupplier($data);

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
    }

?>
<div class="container">
    <h4>Update Store</h4>
    
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
                <label>Supplier Address</label>
                <input name="address" type="text" class="form-control" placeholder="Address" value="<?php echo $supplier['address'];?>">
            </div>
        </div>
        <p class="text-right">
            <input type="submit" name="update" value="Save" class="btn btn-success">
        </p>
    </form>
</div>

<?php
echo mainFooter();  