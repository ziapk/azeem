<?php
session_start();
include_once dirname(__FILE__) . '/../../include/settings.php';
include_once dirname(__FILE__) . '/../../../portal/mpdf/mpdf.php';

$customers = new  Customers();
$page = !empty($_GET['page']) ? $_GET['page'] : 1000;
$perPage = !empty($_GET['perPage']) ? $_GET['perPage'] : 1000;
$search = !empty($_GET['search']) ? $_GET['search'] : "";
$result = $customers->getCustomersPagination(['page' => $page, 'perPage' => $perPage, 'search' => $search, 'shopId' => $shop['id']]);

ob_start();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/bootstrap.min.css">
<style>
    td,
    th {
        padding: 3px 10px
    }
</style>
<table width="100%" border="1">
    <thead>
        <tr>
            <th width="60">ID</th>
            <th>Name</th>
            <th width="120">Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result['records'] as $key => $value) { ?>
            <tr>
                <td><?php echo $value['id']; ?></td>
                <td><?php echo $value['full_name']; ?></td>
                <td <?php if ($value['closing_balance'] < 0) {
                        echo 'style="color: red"';
                    } ?>><?php echo $value['closing_balance']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">Total</th>
            <th><?php echo $result['closing_total']; ?></th>
        </tr>
    </tfoot>
</table>
<?php
$html = ob_get_contents();
ob_clean();

ob_start();
?>
<table width="100%">
    <tr>
        <td style="padding-top: 10px">
            <span style="font-size: 20px; font-weight: bold"><?php echo strtoupper($shop['full_name']); ?></span><br />
            <?php echo $shop['location']; ?>, <?php echo $shop['city']; ?> <br>
            <strong><small><?php echo implode(", ", $result); ?></small></strong>
        </td>
        <td style="text-align: right"><img width="120" height="60" style="vertical-align: middle; margin-right: 5px; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shop['image']; ?>" /></td>
    </tr>
    <tr>
        <th colspan="2" style="font-size: 1.5em;">
            Customer Balances
        </th>
    </tr>
</table>
<?php
$header = ob_get_contents();
ob_clean();
// echo $html;
$footer = 'Today: ' . date('d-m-Y h:i:s');
$mpdf = new mPDF('c', 'A4', null, 10, 10, 10, '40');
$mpdf->setHeader($header);
$mpdf->setFooter($footer);
$mpdf->WriteHTML($html);

$mpdf->Output($shop['full_name'] . '-Balances.pdf', 'D');
exit;
?>