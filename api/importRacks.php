<?php
include_once dirname(__FILE__) . '/../../fee/vendor/autoload.php';
include_once dirname(__FILE__) . '/../settings/settings.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
header("Content-Type: application/json");
if (!empty($_FILES["import"]) && !empty($_FILES["import"]['name'])) {
    $allowed_extension = array('xls', 'xlsx');
    $file_array = explode(".", $_FILES['import']['name']);
    $file_extension = end($file_array);
    $data = [];

    if (in_array($file_extension, $allowed_extension)) {
        $reader->setLoadSheetsOnly($_POST['SheetName']);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($_FILES['import']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet()->toArray();

        if (sizeof($worksheet) > 1) {
            $headerRow = array_shift($worksheet);
        }

        $final = [];
        foreach ($worksheet as $key => $value) {
            $final[$key] = [];
            foreach ($headerRow as $index => $heading) {

                $k = null;
                $val = $value[$index];

                /* switch ($heading) {
                case 'Amount1':
                    $k = 'total';
                break;
                case 'RegistrationNumber':
                    $k = 'regno';
                break;

                case 'InvoiceNumber':
                    $k = 'reciptNumber';
                break;
            } */
                if ($_POST['AmountColumn'] == $heading) {
                    $k = 'total';
                } elseif ($_POST['StudentIdColumn'] == $heading) {
                    $k = 'studentId';
                } elseif ($_POST['ReciptId'] == $heading) {
                    $k = 'reciptNumber';
                }

                if (!empty($k)) {

                    $final[$key][$k] = $val;
                }
            }
        }


        // $recipts = new Recipt();
        $details = [];
        $failed = [];
        $notfound = [];
        $success = [];
        // foreach ($final as $row) {
        //     $res = $recipts->getReciptDetailForAutopay($row['reciptNumber'], $row['studentId']);
        //     if (!empty($res)) {
        //         if ($res['total'] == $row['total']) {
        //             $recipts->payAllFeePlusFine([
        //                 'date' => Settings::dateForSql($_POST['date']),
        //                 'bank' => $_POST['bank'],
        //                 'flag' => 1,
        //                 'reciptId' => $row['reciptNumber'],
        //                 'recipt' => $res['ids'],
        //                 'fineIds' => !empty($res['fineIds']) ? explode(',', $res['fineIds']) : []
        //             ]);
        //             $success[] = $row['reciptNumber'];
        //         } else {
        //             $failed[] = $row['reciptNumber'];
        //         }
        //     } else {
        //         $notfound[] = $row['reciptNumber'];
        //     }
        //     $details[] =  $row;
        // }

        echo json_encode(['status' => 200, 'message' => 'Successfully Import all data!', 'data' => $final]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Only .xls or .xlsx file allowed']);
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Please Select File']);
}
