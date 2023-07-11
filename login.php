<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$loginPage = true;

include_once dirname(__FILE__) . '/include/settings.php';

if (!empty($_SESSION['user_credentials'])) {

    header('location: index.php');
}




$error = "";
if (!empty($_POST) && isset($_POST['login'])) {
    $error = "";
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $error = "Please fill all fields";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $usersObj = new Users();

        $userData = $usersObj->login($email, $password);
        if ($userData) {
            if (is_array($userData)) {
                $_SESSION['user_credentials'] = $userData['user'];
                $_SESSION['shopInfo'] = $userData['shopInfo'];
                $_SESSION['shop'] = $userData['shop'];
                $_SESSION['user_logged_in'] = true;
                header('Location: index.php');
            } else {
                $error = $userData;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include_once dirname(__FILE__) . '/include/header.php';
include_once dirname(__FILE__) . '/include/footer.php';
echo mainHeader(['bodyClasses' => ['login-screen']]);
?>
<div class="login-screen-inner">
    <div class="row">
        <div class="col-sm-6">
            <div class="login-left">
                <img src="assets/img/logo.png" class="login-logo-left img-responsive" width="80px" alt="" />
                <img src="assets/img/login-bg.svg" class="login-image-left img-responsive " width="90%" alt="" />
                <div class="author-info clearfix">
                    <span class="pull-left">Powered By: <strong>Zia ur Rehman</strong></span>
                    <span class="pull-right">Contact Us: <strong>0324-5120412</strong></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <form class="login-form" action="" method="POST">
                <img src="assets/img/logo.png" class="login-logo" width="120px" class="img-responsive" alt="" />
                <h2>Welcome Back</h2>
                <div class="divider">
                    <span>Sign In with email</span>
                </div>
                <?php if (!empty($error)) { ?>
                    <p class="text-danger"><?php echo $error; ?></p>
                <?php } ?>
                <div class="form-group">
                    <label for="exampleInputEmail1">Username</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="keep-logged-in">
                        <label class="form-check-label" for="keep-logged-in">Keep me logged in</label>
                    </div>
                </div>
                <button type="submit" name="login" class="btn btn-lg btn-block btn-primary">Go</button>
            </form>
        </div>
    </div>
</div>

<script>
    localStorage.clear();
</script>