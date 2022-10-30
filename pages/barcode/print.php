<?php
include_once dirname(__FILE__).'/../../include/settings.php';
include_once dirname(__FILE__).'/../../vendor/autoload.php';

// print_r($shop);exit;

$barcode = new \Com\Tecnick\Barcode\Barcode();
$examples = "";
$linear = array(
    'C128A'      => array('0123456789', 'CODE 128 A'),
    'C128B'      => array('0123456789', 'CODE 128 B'),
    'C128C'      => array('0123456789', 'CODE 128 C'),
    'C128'       => array('0123456789', 'CODE 128'),
    'C39E+'      => array('0123456789', 'CODE 39 EXTENDED + CHECKSUM'),
    'C39E'       => array('0123456789', 'CODE 39 EXTENDED'),
    'C39+'       => array('0123456789', 'CODE 39 + CHECKSUM'),
    'C39'        => array('0123456789', 'CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9'),
    'C93'        => array('0123456789', 'CODE 93 - USS-93'),
    'CODABAR'    => array('0123456789', 'CODABAR'),
    'CODE11'     => array('0123456789', 'CODE 11'),
    'EAN13'      => array('0123456789', 'EAN 13'),
    'EAN2'       => array('12',         'EAN 2-Digits UPC-Based Extension'),
    'EAN5'       => array('12345',      'EAN 5-Digits UPC-Based Extension'),
    'EAN8'       => array('1234567',    'EAN 8'),
    'I25+'       => array('0123456789', 'Interleaved 2 of 5 + CHECKSUM'),
    'I25'        => array('0123456789', 'Interleaved 2 of 5'),
    'IMB'        => array('00040123456123456789-12345', 'IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200'),
    // 'IMBPRE'     => array('fatdfatdfatdfatdfatdfatdfatdfatdfatdfatdfatdfatdfatdfatdfatdfatdf', 'IMB pre-processed'),
    'KIX'        => array('0123456789', 'KIX (Klant index - Customer index)'),
    'MSI+'       => array('0123456789', 'MSI + CHECKSUM (modulo 11)'),
    'MSI'        => array('0123456789', 'MSI (Variation of Plessey code)'),
    'PHARMA2T'   => array('0123456789', 'PHARMACODE TWO-TRACKS'),
    'PHARMA'     => array('0123456789', 'PHARMACODE'),
    'PLANET'     => array('0123456789', 'PLANET'),
    'POSTNET'    => array('0123456789', 'POSTNET'),
    'RMS4CC'     => array('0123456789', 'RMS4CC (Royal Mail 4-state Customer Bar Code)'),
    'S25+'       => array('0123456789', 'Standard 2 of 5 + CHECKSUM'),
    'S25'        => array('0123456789', 'Standard 2 of 5'),
    'UPCA'       => array('0123456789', 'UPC-A'),
    'UPCE'       => array('0123456789', 'UPC-E'),
);

foreach ($_POST['full_name'] as $index => $full_name) {
    $code = $_POST['id'][$index].'-AGP';
    $price = $_POST['price'][$index];
    $price = $_POST['price'][$index];
    $qty = $_POST['qty'][$index];
    $bobj = $barcode->getBarcodeObj('C128', $code , -1, -30, 'black', array(0, 0, 0, 0));
    
    for($row = 0; $row < $qty; $row++) {
        $examples .= '<table style="width: 1.2in; table-layout: fixed; border: 0;page-break-before: always; margin-bottom: 20px" cellpadding="0" cellspacing="0"><tr><td><strong style="white-space: nowrap">'.$shop['full_name'].'</strong></td></tr><tr><td><div style="white-space: nowrap">'.$full_name.'</div><td></tr><tr><td colspan="2">'.$bobj->getSvgCode().'</td></tr><tr><td style="white-space: nowrap"><strong style="font-size: 14px;">'.number_format($price).'/-</strong><small style="font-size: 10px">'.$code.'</small></td></tr></table>';
    }
}
?>
<style>
    svg {
        max-width: 100%;
        width: 1.1in;
        height: auto;
    }
</style>
<?php 
echo $examples;
exit;

    if(empty($_POST['full_name']) ) {
        $error = "Please fill all fields";
    }
    else {

        $data = [                
            'id' => $_POST['id'],
            'full_name' => $_POST['full_name'],
            'groupName' => $_POST['groupName'],
            'cat_type' => $_POST['cat_type']
        ];

        $update = $categoryObj->updateCategory($data);

        if($update) {
            $message = "Successfully Assigned!";
        } else {
            $message = "Nothing change";
        }
    }

    if(!empty($error)) {
        echo json_encode(['success' => false, 'error' => $error]);
    }
    if(!empty($message)) {
        echo json_encode(['success' => true, 'message' => $message]);
    }

