<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];



    $productObj = new Products();
    $categoryObj = new Categories();
    $stores = new Store();



    $error = "";
    $message = "";
    if(!empty($_POST) && isset($_POST['create'] )) {

        $error = "";

        
        if(empty($_POST['product_id']) || empty($_POST['shopId'])) {
            $error = "Please fill all fields";
        }
        else {

            $data = [                
                'qty' => !empty($_POST['qty']) ? $_POST['qty'] : 0,
                'stock_out' => !empty($_POST['stock_out']) ? $_POST['stock_out'] : 0,
                'product_id' => !empty($_POST['product_id']) ? $_POST['product_id'] : 0,
                'shopId' => !empty($_POST['shopId']) ? $_POST['shopId'] : 0,
                'owner_id' => $ownerId,
            ];

            $assign = $productObj->assignProduct($data);

            if($assign) {
                $message = "Successfully Assign!";
            } else {
                $message = "Check form carefully!";
            }
        }
    }

    echo mainHeader();  
    $categories = $categoryObj->getOwnerCategories($ownerId);
    $products = $productObj->getOwnerProducts($ownerId);
    $ownerStores = $stores->getOwnerStores($userData['id']);

?>
<div class="container">
    <h4>Assign Product</h4>
    
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-3 form-group">
                <select name="product_id" class="form-control">
                    <?php foreach ($products as $type) {?>
                        <option value="<?php echo $type['id'];?>"><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-3 form-group">
                <input name="qty" type="number" class="form-control" placeholder="Stock In">
            </div>
            <div class="col-sm-3 form-group">
                <input name="stock_out" type="number" class="form-control" placeholder="Stock Out">
            </div>
            <div class="col-sm-3 form-group">
                <select name="shopId" class="form-control">
                    <?php foreach ($ownerStores as $type) {?>
                        <option value="<?php echo $type['id'];?>"><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-3 form-group">
                <input type="submit" name="create" value="Assign" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<?php
echo mainFooter();  