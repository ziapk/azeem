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
        $ff = [];
        foreach ($final as $row) {
            $ff[$row['rack']]['rack'] = $row['rack'];
            $ff[$row['rack']]['codes'][] = $row['code'];
        }
        ksort($ff);
        $owner_id = $shop['owner_id'];
        $shop_id = $shop['id'];
        $products = new Products();
        foreach ($ff as $row) {
            $data = [
                'title' => $row['rack'],
                'shop_id' => $shop_id,
                'owner_id' => $owner_id,
                'status' => !empty($row['rack']) && $row['rack'] <= 200 ? 1 : 0,
            ];
            $racks[] = $id = $products->createRack($data);
            foreach ($row['codes'] as $productId) {
                $d = [
                    'product_id' => $productId,
                    'rack_id' => $id,
                    'status' => 1
                ];
                $childId[] = $products->createRackProducts($d);
            }
        }
        echo json_encode(['status' => 200, 'message' => 'Successfully Import all data!', 'data' => $racks]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Only .xls or .xlsx file allowed']);
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Please Select File']);
}
