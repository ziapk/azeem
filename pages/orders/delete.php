<?php 
print_r($_GET);

$id = !empty($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    echo 'invalid id';
}
include_once dirname(__FILE__).'/../../include/settings.php';

if(isset($_POST['submitDelete'])) {
    $reason = !empty($_POST['reason']) ? $_POST['reason'] : null;
    $orders = new Orders();
    $orders->changeOrderFlag(['id' => $id, 'reason' => $reason, 'flag' => 2]);
    echo '<script>window.close()</script>';
}

?>
<form method="POST">
    <input name="id" type="hidden" value="<?php echo $id;?>">
    <textarea name="reason" style="width: 100%" placeholder="Write a reason to delete..."></textarea>
    <input type="submit" value="delete" name="submitDelete">
</form>