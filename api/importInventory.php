<?php 
try {
include_once dirname(__FILE__).'/../../../vendor/autoload.php';
include_once dirname(__FILE__).'/../include/settings.php';
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
header("Content-Type: application/json");
if(!empty($_FILES["file"]) && !empty($_FILES["file"]['name']))
{
    $allowed_extension = array('xls', 'xlsx');
    $file_array = explode(".", $_FILES['file']['name']);
    $file_extension = end($file_array);
    $data = [];
    

    if (in_array($file_extension, $allowed_extension)) {

        $reader->setLoadSheetsOnly($_POST['SheetName']);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($_FILES['file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet()->toArray();

        if(sizeof($worksheet) > 1) {
            $headerRow = array_shift($worksheet);
        }


    $final = [];
    foreach ($worksheet as $key => $value) {
        $final[$key] = [];  
        foreach ($headerRow as $index => $heading) {
            foreach ($_POST['row'] as $base => $baseValue) {
                if($baseValue == $heading) {
                    $k = $base;
                }
            }
            if ($_POST['product_id'] == $heading) {
                $k = 'product_id';
            }
            elseif ($_POST['qty'] == $heading) {
                $k = 'qty';
            }
            // elseif ($_POST['full_name'] == $heading) {
            //     $k = 'full_name';
            // }
            // elseif ($_POST['barcode'] == $heading) {
            //     $k = 'barcode';
            // }
            // elseif ($_POST['code'] == $heading) {
            //     $k = 'code';
            // }
            // elseif ($_POST['group'] == $heading) {
            //     $k = 'group';
            // }
            // elseif ($_POST['description'] == $heading) {
            //     $k = 'description';
            // }
            // elseif ($_POST['note'] == $heading) {
            //     $k = 'note';
            // }
            // elseif ($_POST['wh_price'] == $heading) {
            //     $k = 'wh_price';
            // }
            // elseif ($_POST['price'] == $heading) {
            //     $k = 'price';
            // }
            // elseif ($_POST['pprice'] == $heading) {
            //     $k = 'pprice';
            // }
            // elseif ($_POST['min_qty'] == $heading) {
            //     $k = 'min_qty';
            // }
            // elseif ($_POST['pack_size'] == $heading) {
            //     $k = 'pack_size';
            // }
            // elseif ($_POST['pack_price'] == $heading) {
            //     $k = 'pack_price';
            // }
            // elseif ($_POST['pack_qty'] == $heading) {
            //     $k = 'pack_qty';
            // }
            // elseif ($_POST['board'] == $heading) {
            //     $k = 'board';
            // }
            // elseif ($_POST['author'] == $heading) {
            //     $k = 'author';
            // }
            // elseif ($_POST['publisher_id'] == $heading) {
            //     $k = 'publisher_id';
            // }
            // elseif ($_POST['cat_id'] == $heading) {
            //     $k = 'cat_id';
            // }


            print_r("INDEX: " . $index);
            print_r($value);
           
            $k = (!empty($k) ? $k : $heading);
            $val = $value[$index];

            if(!empty($k)) {
                $final[$key][$k] = $val;
            }
        }
    }

        $products = new Products();
        $shop_id = $shop['id'];

        $res = [];
        $test = [];

        print_r($headerRow);
        print_r($final);
        print_r($worksheet);
        print_r($_POST);
        exit;

        foreach ($final as $row) {
            $row["shop_id"] = $shop_id;
            $data = $row;
            $res[] = $row['product_id'];
            $test[] = $data;
            $products->maintainProductQty($data);
            // $products->updateProductGivenFields($data);
        }
        
        echo json_encode(['status' => 200, 'message' => 'Successfully Import all data!', 'data' => $res]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Only .xls or .xlsx file allowed']);
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Please Select File']);
}
} catch (Exception $e) {
    print_r($e);
}