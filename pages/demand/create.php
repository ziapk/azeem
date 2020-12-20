<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $productObj = new Products();
    $demandObj = new Demands();
    $categoryObj = new Categories();
    $stores = new Store();

    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];


    $error = "";
    $message = "";


    if(!empty($_POST) && isset($_POST['create'] )) {

        $error = "";

        
        if(empty($_POST['demand_qty']) || empty($_POST['demand_date']) || empty($_POST['product_id']) || empty($_POST['shopId'])) {
            $error = "Please fill all fields";
        }
        else {

            $data = [                
                'demand_qty' => !empty($_POST['demand_qty']) ? $_POST['demand_qty'] : 0,
                'demand_date' => !empty($_POST['demand_date']) ? $_POST['demand_date'] : null,
                'product_id' => !empty($_POST['product_id']) ? $_POST['product_id'] : 0,
                'shopId' => $userData['role'] == 'owner' ? (!empty($_POST['shopId']) ? $_POST['shopId'] : 0) : $userData['shopId'],
                'owner_id' => $ownerId,
            ];

            $create = $demandObj->createDemand($data);

            if($create) {
                $message = "Created Successfully!";
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
    <form method="POST" action="" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <h4>Demand From</h4>
        <div class="row">
            <div class="col-sm-3 form-group">
                <label for="">Select Product</label>
                <select name="product_id" class="form-control">
                    <?php foreach ($products as $type) {?>
                        <option value="<?php echo $type['id'];?>"><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Demand Qty</label>
                <input name="demand_qty" type="number" class="form-control" placeholder="Qty">
            </div>
            <?php if($userData['role'] == 'owner') {?>
            <div class="col-sm-3 form-group">
                <label for="">Select Store</label>
                <select name="shopId" class="form-control">
                    <?php foreach ($ownerStores as $type) {?>
                        <option value="<?php echo $type['id'];?>"><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <?php } else { ?>
            <input type="hidden" name="shopId" value="<?php echo $userData['shopId'];?>">
            <?php }?>
            <div class="col-sm-3 form-group">
                <label for="">Demand Date</label>
                <input name="demand_date_piker" type="text" class="form-control datepicker-single" placeholder="YYYY-MM-DD">
                <input name="demand_date" type="hidden" class="form-control datepicker-hidden">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 form-group">
                <input type="submit" name="create" value="Create" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<?php
echo mainFooter();  

?>
<script type="text/javascript">
 $('.datepicker-hidden').val(moment().format('YYYY-MM-DD'));
 $('.datepicker-single').daterangepicker({
    minDate: moment(),
    singleDatePicker: true,
 }, function(date) {
     $('.datepicker-hidden').val(moment(date).format('YYYY-MM-DD'));
 });
</script>