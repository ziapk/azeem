<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$id = $_GET['id'];
$productObj = new Products();
$demandObj = new Demands();
$categoryObj = new Categories();
$stores = new Store();




$demandDetail = $demandObj->getDemandDetail($id, $shop['owner_id']);


$ownerId = $shop['owner_id'];
$userId = $userData['id'];

?>
<style>
    th,
    td {
        padding: 4px 10px
    }
</style>

<table width="100%" border="1" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="5">
                <table width="100%">
                    <tr>
                        <th width="100" align="left">Demand Title:</th>
                        <th align="left"><?php echo $demandDetail['title']; ?></th>
                        <th width="100" align="left">Demand Date:</th>
                        <th align="left"><?php echo $demandDetail['demand_date']; ?></th>
                    </tr>
                </table>
            </th>
        </tr>
        <tr>
            <th width="30">Sr.#</th>
            <th align="left">Name</th>
            <th width="60">Qty</th>
            <th width="60">Assigned</th>
            <th width="60">Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($demandDetail['items'] as $key => $value) { ?>
            <tr>
                <td align="center"><?php echo $key + 1; ?></td>
                <td><?php echo $value['full_name']; ?></td>
                <td align="center"><?php echo $value['product_qty']; ?></td>
                <td align="center"><?php echo $value['product_assign_qty']; ?></td>
                <td align="center"><?php echo $value['price']; ?></td>
            </tr>
        <?php
        } ?>
    </tbody>
</table>