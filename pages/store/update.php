<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $storeObj = new Store();


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

    $store = $storeObj->getStore($_GET['id']);
    if(empty($store)) {
        header('location: '.SITE_URL.'');
    }

    $storeTypesArr = $storeObj->getStoreTypes();

?>
<div class="container">
    <h4>Update Store</h4>
    
    <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div  class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="product-image"><img src="<?php echo SITE_URL.'assets/clients/'.$store['image'];?>" alt="" /></div>
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" name="full_name" class="form-control" value="<?php echo $store['full_name'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <select name="store_type" class="form-control">
                    <?php foreach ($storeTypesArr as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($store['store_type'] == $type['id']) {echo 'selected';};?> ><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <input name="image" type="file">
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-4 form-group">
                <input name="location" type="text" class="form-control" placeholder="Location" value="<?php echo $store['location'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="city" type="text" class="form-control" placeholder="City" value="<?php echo $store['city'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="postalCode" type="text" class="form-control" placeholder="Postal code" value="<?php echo $store['postalCode'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber1" type="text" class="form-control" placeholder="Cell 1" value="<?php echo $store['phoneNumber1'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber2" type="text" class="form-control" placeholder="Cell 2" value="<?php echo $store['phoneNumber2'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <input name="phoneNumber3" type="text" class="form-control" placeholder="Cell 3" value="<?php echo $store['phoneNumber3'];?>">
            </div>
            <div class="col-sm-4 form-group">
                <select name="status" class="form-control">
                    <?php foreach ($statusArr as $key => $type) {?>
                        <option value="<?php echo $key?>" <?php if($store['status'] == $key) {echo 'selected';};?> ><?php echo $type;?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <p class="text-right">
            <input type="submit" name="update" value="Save" class="btn btn-success">
        </p>
    </form>
</div>

<?php
echo mainFooter();  