<?php
include_once dirname(__FILE__) . '/../../portal/vendor/autoload.php';
include_once dirname(__FILE__) . '/../include/settings.php';
include_once dirname(__FILE__) . '/../classes/products.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

header("Content-Type: application/json");
if (!empty($_FILES["file"]) && !empty($_FILES["file"]['name'])) {
    $allowed_extension = array('xls', 'xlsx');
    $file_array = explode(".", $_FILES['file']['name']);
    $file_extension = end($file_array);
    $data = [];

    if (in_array($file_extension, $allowed_extension)) {
        $reader->setLoadSheetsOnly($_POST['SheetName']);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($_FILES['file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet()->toArray();

        if (sizeof($worksheet) > 1) {
            $headerRow = array_shift($worksheet);
        }

        $final = [];
        foreach ($worksheet as $key => $value) {
            $final[$key] = [];
            foreach ($headerRow as $index => $heading) {

                $k = $heading;
                $val = $value[$index];

                if (!empty($k)) {

                    $final[$key][$k] = $val;
                }
            }
        }

        $products = new Products();
        $shop_id = $shop['id'];

        $res = [];
        $test = [];

        foreach ($final as $row) {
            $data = [
                'product_id' => $row['product_id'],
                'qty' => $row['qty'],
                'shop_id'=>$shop_id
            ];

            $res[] = $row['product_id'];
            $test[] = $data;
            // $products->maintainProductQty($data);
        }
        
        echo json_encode(['status' => 200, 'message' => 'Successfully Import all data!', 'data' => $res, 'test' => $test]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Only .xls or .xlsx file allowed']);
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Please Select File']);
}
