<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $productObj = new Products();
    $categoryObj = new Categories();

    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];


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
                'owner_id' => $ownerId,
            ];

            $update = $categoryObj->updateCategory($data);

            if($update) {
                $message = "Successfully Assigned!";
            } else {
                $message = "Nothing change";
            }
        }
    }



    echo mainHeader();  
    if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
        header('location: '.SITE_URL.'');
    }

    $categoryArr = $categoryObj->getOwnerCategories($ownerId);
    if(empty($categoryArr)) {
        header('location: '.SITE_URL.'');
    }

    $store = $productObj->getProduct($_GET['id']);
    if(empty($store)) {
        header('location: '.SITE_URL.'');
    }
?>
<div class="container">
    <h4>Update Category</h4>
    
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div  class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" name="full_name" class="form-control" value="<?php echo $store['full_name'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input type="submit" name="update" value="Save" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<?php
echo mainFooter();  