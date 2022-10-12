<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];

    if($ownerId != $userId) {
        header('Location: '.SITE_URL.'');
    }


    $productObj = new Products();
    $demandObj = new Demands();
    $categoryObj = new Categories();
    $stores = new Store();



    $error = "";
    $message = "";

    

    

    if(empty($_GET['id']) || !is_numeric($_GET['id']) ) {
        header('location: '.SITE_URL.'');
    }


    $demand = $demandObj->getStoreDemand($_GET['id'], $ownerId);


    if(empty($demand)) {
        header('location: '.SITE_URL.'');
    }

    

    if(!empty($_POST) && isset($_POST['create'] )) {

        $error = "";

        
        if(empty($_POST['assign_qty']) || empty($_POST['assign_date']) || empty($_POST['warehouse_id']) || empty($_POST['flag'])) {
            $error = "Please fill all fields";
        }
        else {

            $data = [                
                'id' => $_GET['id'],
                'assign_qty' => !empty($_POST['assign_qty']) ? $_POST['assign_qty'] : 0,
                'assign_date' => !empty($_POST['assign_date']) ? $_POST['assign_date'] : 0,
                'warehouse_id' => !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : 0,
                'shopId' => $demand['shopId'],
                'flag' => !empty($_POST['flag']) ? $_POST['flag'] : 0,
                'owner_id' => $ownerId,
            ];

            $assign = $demandObj->assignDemand($data);
            
            $message = "";
            $error = "";

            if($assign['status'] != 200) {
                $error = $assign['message'];
            } else {
                $message = $assign['message'];
            }
        }
    }

    




    echo mainHeader();  
    $categories = $categoryObj->getOwnerCategories($ownerId);
    $products = $productObj->getOwnerProducts($ownerId);
    $ownerStores = $stores->getOwnerStores($userData['id']);

?>
<div class="container">
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <h4>Demand From</h4>
        <div class="row">
            <div class="col-sm-3 form-group">
                <select name="product_id" class="form-control" readonly>
                    <?php foreach ($products as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($demand['product_id'] == $type['id']) {echo 'selected';}; ?>><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-3 form-group">
                <input name="demand_qty" type="number" class="form-control" placeholder="Stock In" value="<?php echo $demand['demand_qty']; ?>" readonly>
            </div>
            <div class="col-sm-3 form-group">
                <select name="shopId" class="form-control" readonly>
                    <?php foreach ($ownerStores as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($demand['shopId'] == $type['id']) {echo 'selected';}; ?>><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-3 form-group">
            <input name="demand_date" type="text" class="form-control" placeholder="Demand Date (i.e: YYYY-MM-DD)" value="<?php echo $demand['demand_date']; ?>" readonly>
            </div>
        </div>
        <h4>Assign Product</h4>
        <div class="row">
            <div class="col-sm-3 form-group">
                <label for="">Assign Qty</label>
                <input name="assign_qty" type="number" class="form-control" placeholder="Assign Qty" value="<?php echo $demand['assign_qty']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Assign Date</label>
                <input name="assign_date" type="text" class="form-control" placeholder="YYYY-MM-DD" value="<?php echo $demand['assign_date']; ?>">
            </div>
            <div class="col-sm-3 form-group">
                <label for="">assign From</label>
                <select name="warehouse_id" class="form-control">
                    <?php foreach ($ownerStores as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($demand['warehouse_id'] == $type['id']) {echo 'selected';}; ?>><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>

            <div class="col-sm-3 form-group">
                <label for="">assign Status</label>
                <select name="flag" class="form-control">
                    <?php foreach ($demandStatusArr as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($demand['flag'] == $type['id']) {echo 'selected';}; ?>><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            
            
        </div>
        <?php // if($demand['flag'] == 0) {
        ?>
            <div class="row">
                <div class="col-sm-3 form-group">
                    <input type="submit" name="create" value="Assign" class="btn btn-success">
                </div>
            </div>
        <?php // } 
        ?>

    </form>
</div>

<?php
echo mainFooter();  