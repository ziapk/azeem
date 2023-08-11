<?php
function drawInvoice()
{
    ob_start();
    $_GET['id'] = 2908;
    $_GET['detail'] = 'true';
    include dirname(__FILE__) . '/../print/index.php';
    $html = ob_get_clean();
    return $html;
}
