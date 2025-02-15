<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$clients = new Clients();
$shopData = $clients->getClient($_GET['id']);

if (isset($_POST['save'])) {
    $data = $_POST;
    $data['id'] = $shopData['id'];
    $photo = $_FILES['image'];
    $uploaded = false;
    $image = "";
    if (isset($photo) && count($photo)) {
        if ($photo['error'] == 0) {
            $img = explode('.', $photo['name']);
            $photo['dst_path']     = dirname(__FILE__) . '/../../assets/clients/';

            $data['image'] = $shopData['id'] . '.' . $img[1];

            if (!file_exists($photo['dst_path'])) {

                mkdir($photo['dst_path'], 0777, true);
            }

            $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'] . $data['image']);
            if ($moved) {
                $uploaded = true;
            }
        }
    }


    $clients = new Clients();
    $clients->updateClient($data);
    if (!empty($data['image'])) {
        // $clients->updateClientImage($data);
    } else {
        $data['image'] = $_SESSION['shopInfo']['image'];
    }

    $_SESSION['shopInfo'] = $data;
    header('location: ' . SITE_URL . '');
}


echo mainHeader();
?>
<div class="container">
    <h4>Shop Details</h4>
    <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
        <div class="product-image"><img src="<?php echo SITE_URL . 'assets/clients/' . $shopData['image']; ?>" alt="" /></div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <input name="product_title" value="<?php echo $shopData['product_title']; ?>" class="form-control" placeholder="Shop Name" />
            </div>
            <div class="col-sm-3 form-group">
                <input name="tag_line"  value="<?php echo $shopData['tag_line']; ?>" class="form-control" placeholder="Tagline" />
            </div>
            <div class="col-sm-3 form-group">
                <input name="image" type="file">
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_1" value="<?php echo $shopData['phone_1']; ?>" class="form-control" placeholder="Phone 1" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_2" value="<?php echo $shopData['phone_2']; ?>" class="form-control" placeholder="Phone 2" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_3" value="<?php echo $shopData['phone_3']; ?>" class="form-control" placeholder="Phone 3" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="phone_4" value="<?php echo $shopData['phone_4']; ?>" class="form-control" placeholder="Phone 4" />
            </div>
            <div class="col-sm-6 col-md-3 form-group">
                <input name="end_date" value="<?php echo $shopData['end_date']; ?>" class="form-control" placeholder="Expiry" />
            </div>
            <div class="col-sm-12 col-md-12 form-group">
                <textarea name="address" rows="5" class="form-control" placeholder="Address"><?php echo $shopData['address']; ?></textarea>
            </div>
            <div class="col-sm-12 col-md-12 form-group">
                <input type="submit" value="Save" name="save" class="btn btn-primary btn-sm" />
            </div>
        </div>
    </form>
</div>
<?php echo mainFooter();
