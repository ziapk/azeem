<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];

    $productObj = new Products();
    $categoryObj = new Categories();
    $stores = new Store();



    $error = "";
    $message = "";
    if(!empty($_POST) && isset($_POST['update'] )) {

        $error = "";

        
        if(empty($_POST['product_id']) || empty($_POST['sale_price']) || empty($_POST['shopId'])) {
            $error = "Please fill all fields";
        }
        else {

            $data = [                
                'id' => $_GET['id'],
                'qty' => !empty($_POST['qty']) ? $_POST['qty'] : 0,
                'stock_out' => !empty($_POST['stock_out']) ? $_POST['stock_out'] : 0,
                'min_qty' => !empty($_POST['min_qty']) ? $_POST['min_qty'] : 0,
                'sale_price' => !empty($_POST['sale_price']) ? $_POST['sale_price'] : 0,
                'packet' => !empty($_POST['packet']) ? $_POST['packet'] : 0,
                'packet_price' => !empty($_POST['packet_price']) ? $_POST['packet_price'] : 0,
                'location' => !empty($_POST['location']) ? $_POST['location'] : "",
                'product_id' => !empty($_POST['product_id']) ? $_POST['product_id'] : 0,
                'shopId' => !empty($_POST['shopId']) ? $_POST['shopId'] : 0,
                'owner_id' => $ownerId,
            ];

            $assign = $productObj->updateStoreProduct($data);

            if($assign) {
                $message = "Successfully Updated!";
            } else {
                $message = "Nothing change";
            }
        }
    }

    if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
        header('location: '.SITE_URL.'');
    }

    $product = $productObj->getStoreProduct($_GET['id'], $ownerId);

    if(empty($product)) {
        header('location: '.SITE_URL.'');
    }

    $categories = $categoryObj->getOwnerCategories($ownerId);

    echo mainHeader();


    echo mainHeader();  
    $categories = $categoryObj->getOwnerCategories($ownerId);
    $products = $productObj->getOwnerProducts($ownerId);
    $ownerStores = $stores->getOwnerStores($userData['id']);

?>
<div class="container">
    <h4>Update Product</h4>
    
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-3 form-group">
                <select name="product_id" class="form-control">
                    <?php foreach ($products as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($product['product_id'] == $type['id']) {echo 'selected';}; ?>><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-3 form-group">
                <input name="qty" type="number" class="form-control" placeholder="Stock In" value="<?php echo $product['qty']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input name="stock_out" type="number" class="form-control" placeholder="Stock Out" value="<?php echo $product['stock_out']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input name="min_qty" type="number" class="form-control" placeholder="Minimum Stock" value="<?php echo $product['min_qty']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input name="sale_price" type="number" class="form-control" placeholder="Sale Price" value="<?php echo $product['sale_price']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input name="packet" type="number" class="form-control" placeholder="Packet Size" value="<?php echo $product['packet']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input name="packet_price" type="number" class="form-control" placeholder="Packet Price" value="<?php echo $product['packet_price']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <input name="location" type="text" class="form-control" placeholder="Placed In Store" value="<?php echo $product['location']; ?>">
            </div>
            <?php if( $userData['role'] == 'owner') {?>
                <div class="col-sm-3 form-group">
                    <select name="shopId" class="form-control">
                        <?php foreach ($ownerStores as $type) {?>
                            <option <?php if($product['shopId'] == $type['id']) {echo 'selected';}; ?> value="<?php echo $type['id'];?>"><?php echo $type['full_name'];?></option>
                        <?php }?>
                    </select>
                </div>
            <?php } else { ?>
                <input name="shopId" type="hidden" value="<?php echo $userData['shopId'];?>">
            <?php }?>
            <div class="col-sm-3 form-group">
                <input type="submit" name="update" value="Save" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<?php
echo mainFooter();  