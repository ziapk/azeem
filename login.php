<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$loginPage = true;

include_once dirname(__FILE__).'/include/settings.php';

if(!empty($_SESSION['user_credentials'])) {
    
    header('location: index.php');
}




$error = "";
if(!empty($_POST) && isset($_POST['login'] )) {
    $error = "";
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $error = "Please fill all fields";
    }
    else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $usersObj = new Users();

        $userData = $usersObj->login($email,$password);
        if($userData) {
            if(is_array($userData)) {
                $_SESSION['user_credentials'] = $userData['user'];
                $_SESSION['shopInfo'] = $userData['shopInfo'];
                $_SESSION['user_logged_in'] = true;
                header('Location: index.php');
            }
            else {
                $error = $userData;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include_once dirname(__FILE__).'/include/header.php';
include_once dirname(__FILE__).'/include/footer.php';
echo mainHeader();
?>
<div class="container">
    <img src="" alt="">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <form action="" method="POST">
                <div class="logo-section text-center">
                    <img width="150" src="<?php echo SITE_URL;?>assets/img/logo.png" alt="" />
                </div>
                <?php if(!empty($error)) { ?>
                    <p class="text-danger"><?php echo $error; ?></p>                    
                <?php }?>
                <div class="form-group">
                    <label for="exampleInputEmail1">Username</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <button type="submit" name="login" class="btn btn-primary">Submit</button>
                <a href="/mobileworld" class="btn btn-info" title="Mobile World">Go To Mobile World</a>
            </form>
        </div>
    </div>
</div>
<?php
echo mainFooter();