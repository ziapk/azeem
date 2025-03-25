<?php
if (!isset($_SESSION)) {
    session_start();
}
define('SITE_URL', '/');
date_default_timezone_set('Asia/Karachi');
ini_set('max_input_vars', 20000);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// error_reporting(0);

if (empty($_SESSION) && $loginPage != true) {
    header('location: ' . SITE_URL . 'login.php');
}

$userData = !empty($_SESSION['user_credentials']) ? $_SESSION['user_credentials'] : [];
$shopData = !empty($_SESSION['shopInfo']) ? $_SESSION['shopInfo'] : [];
$shop = !empty($_SESSION['shop']) ? $_SESSION['shop'] : [];
// todo query
// => add column in customers = account_id
// => add column in suppliers = account_id
// => add table = payment_modes
// => add table = account_transactions -> add column -> shopId
// => add column in accounts = shopId, owner_id, opening_balance
// => add table = account_ledger_entries -> add column -> payment_mode = default 1 cash
// => ALTER TABLE `store` ADD `assets` INT NULL DEFAULT NULL AFTER `sale_date`, ADD `cash` INT NULL AFTER `assets`, ADD `expense` INT NULL AFTER `cash`, ADD `receivable` INT NULL AFTER `expense`, ADD `payable` INT NULL AFTER `receivable`, ADD `sale_discount` INT NULL AFTER `payable`, ADD `purchase_discount` INT NULL AFTER `sale_discount`;

// prepared_products

// CREATE TABLE worker (
//     id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each worker
//     shop_id INT NOT NULL,             -- Shop identifier
//     owner_id INT NOT NULL,            -- Owner identifier
//     full_name VARCHAR(255) NOT NULL,  -- Full name of the worker
//     email VARCHAR(255),        -- Email of the worker (optional, unique)
//     designation VARCHAR(100),         -- Designation of the worker
//     doj DATE NOT NULL,                -- Date of joining
//     contact_1 VARCHAR(15) NOT NULL,   -- Primary contact number
//     contact_2 VARCHAR(15),            -- Secondary contact number (optional)
//     emg_contact_1 VARCHAR(15),        -- Emergency contact 1
//     emg_contact_2 VARCHAR(15),        -- Emergency contact 2 (optional)
//     rate_per_practical DECIMAL(10, 2) NOT NULL, -- Rate per practical for the worker
//     account_id INT,                   -- Associated account ID (optional)
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Record creation timestamp
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Last update timestamp
// );


// CREATE TABLE workorder (
//     id INT AUTO_INCREMENT PRIMARY KEY,      -- Unique identifier for each workorder
//     worker_id INT NOT NULL,                 -- Reference to the worker (required)
//     status TINYINT(1) NOT NULL,             -- Status: 1 = processing, 2 = completed
//     flag TINYINT(1) DEFAULT 1,              -- Payment flag: 1 = pending, 2 = paid
//     shop_id INT NOT NULL,                   -- Reference to the shop (required)
//     owner_id INT NOT NULL,                  -- Reference to the owner (required)
//     price DECIMAL(15, 2) DEFAULT 0.00,      -- Total price of the work order
//     payment_amount DECIMAL(15, 2) DEFAULT 0.00, -- Amount paid for the work order
//     discount DECIMAL(15, 2) DEFAULT 0.00,   -- Discount applied to the work order
//     wo_date DATE NOT NULL,                  -- Work order creation date
//     ref_no VARCHAR(255) DEFAULT NULL,       -- Reference number for the work order
//     description TEXT DEFAULT NULL,          -- Description or notes for the work order
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Record creation timestamp
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Last update timestamp
//     FOREIGN KEY (worker_id) REFERENCES worker(id) ON DELETE CASCADE -- Worker relationship
// );

// CREATE TABLE workorder_items (
//     id INT AUTO_INCREMENT PRIMARY KEY,              -- Unique identifier for each item
//     workorder_id INT NOT NULL,                      -- Reference to the workorder (required)
//     product_id INT NOT NULL,                        -- Reference to the product (required)
//     qty INT NOT NULL,                               -- Quantity of products
//     practical_qty INT NOT NULL,                     -- Number of practicals per product
//     cost_per_practical DECIMAL(10, 2) NOT NULL,     -- Cost per practical
//     saleprice_per_practical DECIMAL(10, 2) NOT NULL,-- Sale price per practical
//     discount DECIMAL(10, 2) DEFAULT 0.00,          -- Discount applied (optional, default 0)
//     total_price DECIMAL(15, 2) GENERATED ALWAYS AS (qty * practical_qty * cost_per_practical) STORED, -- Total cost
//     total_cost  DECIMAL(15, 2) GENERATED ALWAYS AS (qty * practical_qty * saleprice_per_practical - discount) STORED, -- Total price after discount
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Record creation timestamp
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Last update timestamp
//     FOREIGN KEY (workorder_id) REFERENCES workorder(id) ON DELETE CASCADE, -- Workorder relationship
//     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE -- Product relationship
// );

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

include_once dirname(__FILE__) . '/header.php';
include_once dirname(__FILE__) . '/footer.php';


function delimitArray($array, $address, $delimiter = "_")
{
    $address = explode($delimiter, $address);
    $num_args = count($address);

    $val = $array;
    for ($i = 0; $i < $num_args; $i++) {
        // every iteration brings us closer to the truth
        $val = $val[$address[$i]];
    }
    return $val;
}

function safe_json_encode($value, $options = 0, $depth = 512, $utfErrorFlag = false)
{
    $encoded = json_encode($value, $options, $depth);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_UTF8:
            $clean = utf8ize($value);
            if ($utfErrorFlag) {
                return 'UTF8 encoding error'; // or trigger_error() or throw new Exception()
            }
            return safe_json_encode($clean, $options, $depth, true);
        default:
            return 'Unknown error'; // or trigger_error() or throw new Exception()

    }
}

function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } else if (is_string($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
}

//function __autoload($class_name) {
spl_autoload_register(function ($class_name) {
    //class directories
    $directorys = array('classes/');

    //for each directory
    foreach ($directorys as $directory) {
        //see if the file exsists
        if (file_exists(dirname(__FILE__) . '/../' . $directory . strtolower($class_name) . '.php')) {
            require_once(dirname(__FILE__) . '/../' . $directory . strtolower($class_name) . '.php');
            //only require the class once, so quit after to save effort (if you got more, then name them something else 
            return;
        }
    }
});
$accStatusArray = array(
    '0' => 'In-Active',
    '1' => 'Active',
    '2' => 'Disable',
);
$productTypes = array(
    '1' => 'Ready Made',
    '2' => 'Service',
    '3' => 'Raw Material',
    '4' => 'Asset',
    '5' => 'F.O.R',
);

$statusArr = [
    1 => 'Active',
    2 => 'In Active'
];

$discountTypesArr = [
    1 => 'Percentage',
    2 => 'Fixed'
];

$demandStatusArr = [
    0 => ['id' => 0, 'full_name' => 'Requested'],
    1 => ['id' => 1, 'full_name' => 'Assiged'],
    2 => ['id' => 2, 'full_name' => 'Rejected'],
    3 => ['id' => 3, 'full_name' => 'Withdrawal'],
    4 => ['id' => 4, 'full_name' => 'Deleted']
];


$orderStatusArr = [
    1 => ['id' => 1, 'full_name' => 'Park'],
    2 => ['id' => 2, 'full_name' => 'Paid'],
    3 => ['id' => 3, 'full_name' => 'Cancelled'],
    4 => ['id' => 4, 'full_name' => 'Deleted'],
    5 => ['id' => 5, 'full_name' => 'Return to Inventory'],
    6 => ['id' => 6, 'full_name' => 'Return to Faulty'],
    7 => ['id' => 7, 'full_name' => 'Return as Partial'],
    8 => ['id' => 8, 'full_name' => 'Partial Paid'],
    9 => ['id' => 9, 'full_name' => 'Un-Paid']
];

$returnOrderStatusArr = [
    1 => ['id' => 1, 'full_name' => 'Need Approval'],
    2 => ['id' => 2, 'full_name' => 'Approved']
];
$orderPriority = [
    1 => 'No Priority',
    2 => 'Low',
    3 => 'Medium',
    4 => 'High',
    5 => 'Urgent'
];

$reportsArray = [
    0 => ['id' => 0, 'title' => 'Product List', 'access' => ['owner', 'manager']],
    1 => ['id' => 1, 'title' => 'Sales Report', 'access' => ['shopkeeper', 'owner', 'manager']],
    2 => ['id' => 2, 'title' => 'Summery Product Wise', 'access' => ['shopkeeper', 'owner', 'manager']],
    3 => ['id' => 3, 'title' => 'Summery Date Wise', 'access' => ['owner', 'manager']],
    4 => ['id' => 4, 'title' => 'Returns Report', 'access' => ['owner', 'manager']],
    5 => ['id' => 5, 'title' => 'Return to Inventory', 'access' => ['owner', 'manager']],
    6 => ['id' => 6, 'title' => 'Return to Faulty', 'access' => ['owner', 'manager']],
    7 => ['id' => 7, 'title' => 'Return as Lahore', 'access' => ['owner', 'manager']],
    8 => ['id' => 8, 'title' => 'Expense Report', 'access' => ['shopkeeper', 'owner', 'manager']],
    9 => ['id' => 9, 'title' => 'Expense Summery Report', 'access' => ['shopkeeper', 'owner', 'manager']],
    10 => ['id' => 10, 'title' => 'Closing Balance Report', 'access' => ['shopkeeper', 'owner', 'manager']],
    11 => ['id' => 11, 'title' => 'Trial Balance', 'access' => ['owner', 'manager'] ],
    12 => ['id' => 12, 'title' => 'Profit and Loss', 'access' => ['owner', 'manager'] ],
    13 => ['id' => 13, 'title' => 'Account Ledger', 'access' => ['owner', 'manager']],
    14 => ['id' => 14, 'title' => 'EasyPaisa &amp; Bank Report', 'access' => ['owner', 'manager']],
    15 => ['id' => 15, 'title' => 'EasyPaisa &amp; Bank Report (Summery)', 'access' => ['owner', 'manager']],
    16 => ['id' => 16, 'title' => 'Pin Products (Min Qty)', 'access' => ['owner', 'manager']],
    17 => ['id' => 17, 'title' => 'Purchase Report', 'access' => ['owner', 'manager']],
    18 => ['id' => 18, 'title' => 'Purchase Report (Product wise)', 'access' => ['owner', 'manager']],
    19 => ['id' => 19, 'title' => 'Sample Report Order Wise', 'access' => ['owner', 'manager']],
    20 => ['id' => 20, 'title' => 'Sample Report Product Wise', 'access' => ['owner', 'manager']],
    21 => ['id' => 21, 'title' => 'Stock Audit (Product Wise)', 'access' => ['owner', 'manager']],
    22 => ['id' => 22, 'title' => 'Payments Records', 'access' => ['owner', 'manager']],
    23 => ['id' => 23, 'title' => 'Adjustment Records', 'access' => ['owner', 'manager']],
];

$categories = [
    'Inventory' => [0, 16],       // Report IDs belonging to Sales
    'Sales' => [1, 2, 3],       // Report IDs belonging to Sales
    'Purchase' => [17,18,],         // Report IDs belonging to Purchase
    'Expense' => [8,9],           // Report IDs belonging to Expense
    'Returns' => [4, 5, 6, 7],       // Report IDs belonging to Sales
    'Sample' => [19,20],            // Report IDs belonging to Audit
    'Audit' => [10, 21],            // Report IDs belonging to Audit
    'Accounting' => [11, 12, 13, 14, 15, 22, 23],            // Report IDs belonging to Audit
];

$groupedReports = [];
foreach ($categories as $category => $reportIds) {
    foreach ($reportIds as $id) {
        if (isset($reportsArray[$id])) {
            $groupedReports[$category][$id] = $reportsArray[$id];
        }
    }
}


$returnArray = [
    1 => ['id' => 1, 'title' => 'Return to Inventory'],
    2 => ['id' => 2, 'title' => 'Return to Faulty'],
    3 => ['id' => 3, 'title' => 'Return as Lahore'],
];

$catTypesArr = [
    1 => 'Expense',
    2 => 'Product',
];

$pinArr = [
    0 => 'No',
    1 => 'Yes',
];

function dbDateToClient($date, $format = 'd/m/Y h:i A', $from = 'US/Eastern', $to = 'Asia/Karachi')
{
    $cdate = new DateTime($date, new DateTimeZone($from));
    $cdate->setTimezone(new DateTimeZone($to));
    return $cdate->format($format);
}
function dateToSimple($date)
{
    if ($date == '00-00-0000' || $date == '0000-00-00' || empty($date) || !$date || !isset($date)) {
        return '';
    } else {
        $newDate = DateTime::createFromFormat('Y-m-d', $date);
        if ($newDate) {
            return $newDate->format('d-m-Y');
        } else {
            return '';
        }
    }
}

function UserInfo()
{
    global $userData;
    global $shopData;
    global $shop;
    return [
        'user' => $userData,
        'client' => $shopData,
        'shop' => $shop
    ];
}

function nestedDrawList($list, $menuClass = null, $subMenuClass = null)
{
    $html = "";
    foreach ($list as $key => $m) {
        $html    .= '<li class="clearfix" data-tree-branch="' . $m['key'] . '" data-tree-click="' . $m['key'] . '"><svg xmlns="http://www.w3.org/2000/svg" width="41.14" height="41.138" viewBox="0 0 41.14 41.138"><path d="M3.741,33.658a.625.625,0,0,0-.624.624v2.492H.624A.622.622,0,0,0,0,37.4v3.117a.624.624,0,0,0,.624.624H40.516a.624.624,0,0,0,.623-.624V37.4a.622.622,0,0,0-.623-.623H38.022V34.282a.624.624,0,0,0-.623-.624H35.763V14.751H37.4a.624.624,0,0,0,.623-.624V11.634h2.494a.624.624,0,0,0,.288-1.177L20.857.072a.612.612,0,0,0-.575,0L.336,10.457a.624.624,0,0,0,.288,1.177H3.117v2.492a.625.625,0,0,0,.624.624H5.377V33.658Zm5.59-18.907h4.854V33.658H9.331Zm8.81,0H23V33.658H18.141Zm8.812,0h4.856V33.658H26.953Z"/></svg><span>[' . $m['code'] . ']</span> ' . $m['title'];
        $html  .= '<span class="btn-group ml-10"><a class="btn-xs btn btn-default" onclick=\'fillModifyForm(' . json_encode($m) . ')\' data-toggle="modal" href="#editCategory"> <span class="fa fa-pencil"></span></a><a class="btn-xs btn btn-default" onclick=\'fillModifyForm2(' . json_encode($m) . ')\' data-toggle="modal" href="#newAccount"> <span class="fa fa-plus"></span></a>' . (!empty($m['parent_id']) ? '<a class="btn-xs btn btn-default" ng-click=\'deleteAccount(' . json_encode($m) . ')\' href="javascript:void(0)"> <span class="fa fa-remove"></span></a></span>' : '');
        $html  .= '</li>';
        if (count($m['children']) > 0) {
            $html .= nestedDrawList($m['children']);
        }
    }
    return $html;
}

function array_tree_expand_without_filter(array $array, $id = 'id', $parent = 'parent_id', $children = 'children')
{
    $r = array();
    $after_filter_array = $array;
    foreach ($after_filter_array as $v) {
        $k = $v[$id];
        $r[$k] = $v;
        $r[$k][$children] = array();
    }
    $adopted = array();
    foreach ($r as $k => $v) {
        if (isset($r[$v[$parent]])) {
            $v['key'] = $v['parent_id'] . '-' . $v['id'];
            $r[$v[$parent]][$children][] = &$r[$k];
            $adopted[] = $k;
        }
    }
    foreach ($adopted as $id) {
        unset($r[$id]);
    }
    return $r;
}

function drawList($list, $menuClass = 'list-unstyled', $subMenuClass = null)
{
    global $page;
    global $commonArray;
    $assocList = [];
    foreach ($list as $key => $value) {
        $value['key'] = !empty($assocList[$value['parent_id']]) ? $assocList[$value['parent_id']]['key'] . '-' . $value['id'] : $value['id'];
        $assocList[$value['id']] = $value;
    }

    $mainMenu = array_tree_expand_without_filter($assocList);

    $html = '<ul class="' . $menuClass . '" id="tree">';
    $html    .= nestedDrawList($mainMenu);
    $html .= '</ul>';
    return $html;
}


function drawJs($array)
{
    $commonArray = SITE_URL;
    $JsArray = '';
    foreach ($array as $value) {
        $JsArray .= "<script src='" . $commonArray . $value . "' type='text/javascript'></script>\n";
    }
    print_r($JsArray);
    exit;
    return $JsArray;
}

function convertNumberToWord($num)
{
    $num = str_replace(array(',', ' '), '', trim($num));
    if (!$num) {
        return false;
    }
    $num = (int) $num;
    $words = array();
    $list1 = array(
        '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array(
        '', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ($tens < 20) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . (($levels && (int) ($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return ucwords(implode(' ', $words)) . ' Rupees only';
}
