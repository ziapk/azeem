<?php
session_start();
define('SITE_URL', '/tea/');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(empty($_SESSION) && $loginPage != true) {
    header('location: '.SITE_URL.'login.php');
}

$userData = !empty($_SESSION['user_credentials']) ? $_SESSION['user_credentials'] : [];
$shopData = !empty($_SESSION['shopInfo']) ? $_SESSION['shopInfo'] : [];
include_once dirname(__FILE__).'/header.php';
include_once dirname(__FILE__).'/footer.php';




//function __autoload($class_name) {
spl_autoload_register(function ($class_name) {
    //class directories
    $directorys = array('classes/');
    
    //for each directory
    foreach($directorys as $directory)
    {
        //see if the file exsists
        if(file_exists(dirname(__FILE__).'/../'.$directory. strtolower($class_name). '.php'))
        {
            require_once(dirname(__FILE__).'/../'.$directory. strtolower($class_name).'.php');
            //only require the class once, so quit after to save effort (if you got more, then name them something else 
            return;
        }            
    }
});


$statusArr = [
    ['id' => 1, 'full_name'=> 'Active'],
    ['id' => 2, 'full_name'=> 'In Active']
];

$demandStatusArr = [
    0 => ['id' => 0, 'full_name'=> 'Requested'],
    1 => ['id' => 1, 'full_name'=> 'Assiged'],
    2 => ['id' => 2, 'full_name'=> 'Cancel']
];


$orderStatusArr = [
    1 => ['id' => 1, 'full_name'=> 'Park'],
    2 => ['id' => 2, 'full_name'=> 'Paid'],
    3 => ['id' => 3, 'full_name'=> 'Cancelled'],
    4 => ['id' => 4, 'full_name'=> 'Deleted']
];