<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Products();
$programsObj = new Programs();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];


$error = "";
$message = "";
if (!empty($_POST) && isset($_POST['update'])) {

    $error = "";


    if (empty($_POST['degree']) || empty($_POST['program'])) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'id' => $_GET['id'],
            'degree' => $_POST['degree'],
            'program' => $_POST['program'],
            'class' => !empty($_POST['class']) ? $_POST['class'] : null,
            'pin' => !empty($_POST['pin']) ? 1 : 0,
        ];

        $update = $programsObj->updateProgram($data);

        if ($update) {
            $message = "Successfully Updated!";
        } else {
            $message = "Nothing change";
        }
    }
}



echo mainHeader(['page' => 'program']);
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: ' . SITE_URL . '');
}


$store = $programsObj->getProgram($_GET['id']);
if (empty($store)) {
    header('location: ' . SITE_URL . '');
}
?>
<div class="container">
    <h4>Update Program</h4>

    <form method="POST" action="" autocomplete="off">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-4 form-group">
                <label>Degree</label>
                <input type="text" name="degree" class="form-control" value="<?php echo $store['degree']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Program</label>
                <input type="text" name="program" class="form-control" value="<?php echo $store['program']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Class/Part</label>
                <input type="text" name="class" class="form-control" value="<?php echo $store['class']; ?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Pin</label>
                <input type="checkbox" name="pin" <?php if (!empty($store['pin'])) {
                                                        echo 'checked';
                                                    } ?>>
            </div>
            <div class="col-sm-4 form-group">
                <input type="submit" name="update" value="Save" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<?php
echo mainFooter();
