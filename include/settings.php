<?php
session_start();
define('SITE_URL', '/');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

error_reporting(0);

if(empty($_SESSION) && $loginPage != true) {
    header('location: '.SITE_URL.'login.php');
}

$userData = !empty($_SESSION['user_credentials']) ? $_SESSION['user_credentials'] : [];
$shopData = !empty($_SESSION['shopInfo']) ? $_SESSION['shopInfo'] : [];
$shop = !empty($_SESSION['shop']) ? $_SESSION['shop'] : [];

$pages = [
    "assign" => "Assign Book",
    "recipt" => "Recipt",
    "dashboard" => "Dashboard",
    "product" => "Products",
    "supplier" => "Suppliers",
    "customer" => "Customers",
    "program" => "Programs",
    "running" => "Running Items",
    "category" => "Categories",
    "publisher" => "Publishers",
    "reports" => "Reports",
    "barcode" => "Barcodes",
    "order" => "Sales",
    "return" => "Returns",
    "expense" => "Expenses",
];

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
    1 => 'Active',
    2 => 'In Active'
];

$discountTypesArr = [
    1=> 'Percentage',
    2=> 'Fixed'
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
    4 => ['id' => 4, 'full_name'=> 'Deleted'],
    5 => ['id' => 5, 'full_name'=> 'Return to Inventory'],
    6 => ['id' => 6, 'full_name'=> 'Return to Faulty'],
    7 => ['id' => 7, 'full_name'=> 'Return as Partial'],
    8 => ['id' => 8, 'full_name'=> 'Partial Paid'],
    9 => ['id' => 9, 'full_name'=> 'Not Paid']
];

$reportsArray = [
    0 => ['id' => 0, 'title'=> 'Product List', 'access' => ['owner', 'manager']],
    1 => ['id' => 1, 'title'=> 'Sales Report', 'access' => ['shopkeeper', 'owner', 'manager']],
    2 => ['id' => 2, 'title'=> 'Summery Product Wise', 'access' => ['shopkeeper', 'owner', 'manager']],
    3 => ['id' => 3, 'title'=> 'Summery Date Wise', 'access' => ['owner', 'manager']],
    4 => ['id' => 4, 'title'=> 'Returns Report', 'access' => ['owner', 'manager']],
    5 => ['id' => 5, 'title'=> 'Return to Inventory', 'access' => ['owner', 'manager']],
    6 => ['id' => 6, 'title'=> 'Return to Faulty', 'access' => ['owner', 'manager']],
    7 => ['id' => 7, 'title'=> 'Return as Lahore', 'access' => ['owner', 'manager']],
    8 => ['id' => 8, 'title'=> 'Expense Report', 'access' => ['shopkeeper', 'owner', 'manager']],
    9 => ['id' => 9, 'title'=> 'Expense Summery Report', 'access' => ['shopkeeper', 'owner', 'manager']],
];

$returnArray = [
    1 => ['id' => 1, 'title'=> 'Return to Inventory'],
    2 => ['id' => 2, 'title'=> 'Return to Faulty'],
    3 => ['id' => 3, 'title'=> 'Return as Lahore'],
];

$catTypesArr = [
    1 => 'Expense',
    2 => 'Product',
];


function dateToSimple($date) {
    if($date == '00-00-0000' || $date == '0000-00-00' || empty($date) || !$date || !isset($date)) {
        return '';
    }else {
        $newDate = DateTime::createFromFormat('Y-m-d', $date);
        if($newDate) {
            return $newDate->format('d-m-Y');
        }else {
            return '';
        }
    }
}  