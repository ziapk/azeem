<?php include_once dirname(__FILE__).'/../../include/settings.php';


if(isset($_POST['save'])) {
    print_r($_POST);

    $data= $_POST;

    $data['id'] = $shopData['id'];

    $clients = new Clients();
    $clients->updateClient($data);

    $_SESSION['shopInfo'] = $data;
    header('location: '.SITE_URL.'');
}


echo mainHeader();
global $shopData;
?>
<div class="container">
    <h4>Shop Details</h4>
    <form method="POST" action="" autocomplete="off">
        <div class="row">
            <div class="col-sm-6 form-group">
                <input name="product_title" value="<?php echo $shopData['product_title'];?>" class="form-control" placeholder="Shop Name" />
            </div>
            <div class="col-sm-6 form-group">
                <input name="tag_line" value="" class="form-control" placeholder="Tagline" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_1" value="<?php echo $shopData['phone_1'];?>" class="form-control" placeholder="Phone 1" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_2" value="<?php echo $shopData['phone_2'];?>" class="form-control" placeholder="Phone 2" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_3" value="<?php echo $shopData['phone_3'];?>" class="form-control" placeholder="Phone 3" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_4" value="<?php echo $shopData['phone_4'];?>" class="form-control" placeholder="Phone 4" />
            </div>
            <div class="col-sm-12 col-md-12 form-group">
                <textarea name="address" rows="5" class="form-control" placeholder="Address"><?php echo $shopData['address'];?></textarea>
            </div>
            <div class="col-sm-12 col-md-12 form-group">
                <input type="submit" value="Save" name="save" class="btn btn-primary btn-sm" />
            </div>
        </div>
    </form>
</div>
<?php echo mainHeader();?>