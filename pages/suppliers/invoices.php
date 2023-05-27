<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$id = !empty($_GET['id']) ? $_GET['id'] : 0;

// $ordersObj = new Orders();
$customers = new Supply();
$customer = $customers->getOrders($id);
// print_r($customer);exit;
// $orders = $ordersObj->getOrders($userData['shopId'], $id);
// $ids = [];

// foreach ($orders as $value) {
//     $ids[] = $value['id'];
// }

// $transactions = [];
// $trans = [];
// if(!empty($ids)) {
//     $trans = $ordersObj->getTransactionsByOIds($ids);
// }

// foreach ($trans as $value) {
//     if(empty($transactions[$value['order_id']])) {
//         $transactions[$value['order_id']] = 0;
//     }
//     $transactions[$value['order_id']] += $value['amount'];
// }

echo mainHeader();
?>
<div class="container">
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="80">Sr.#</th>
                <th>Invoice ID</th>
                <th>Supplier</th>
                <th>Date/time</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customer['orders'] as $key => $value) { ?>
                <tr>
                    <td class="fw-bold"><?php echo $key + 1; ?></td>
                    <td class="fw-bold"><?php echo $value['id']; ?></td>
                    <td class="fw-bold"><?php echo $customer['supplier']['name']; ?></td>
                    <td class="fw-bold"><?php echo $value['supply_date']; ?></td>
                    <td class="fw-bold"><?php echo number_format($value['price'], 0); ?></td>
                    <td><a href="javascipt:void(0)" ng-click="order = order == <?php echo $value['id']; ?> ? '' : <?php echo $value['id']; ?> " class="fa fa-eye"></a></td>
                </tr>
                <tr class="text-danger" ng-if="order == <?php echo $value['id']; ?>">
                    <th class="text-danger fw-bold" style="text-align: right">Items <span class="fa fa-arrow-down"></span></th>
                    <th class="text-danger fw-bold">Sr.#</th>
                    <th class="text-danger fw-bold">ID</th>
                    <th class="text-danger fw-bold">Product</th>
                    <th class="text-danger fw-bold">Qty</th>
                    <th class="text-danger fw-bold">Price</th>
                </tr>
                <?php
                foreach ($value['order_items'] as $k => $v) { ?>
                    <tr ng-if="order == <?php echo $value['id']; ?>">
                        <td align="right"><span class="fa text-danger fa-arrow-right"></span></td>
                        <td><?php echo $k + 1; ?></td>
                        <td><?php echo $v['product_id']; ?></td>
                        <td><?php echo $v['product_title']; ?></td>
                        <td><?php echo $v['quantity']; ?></td>
                        <td><?php echo number_format($v['price'], 0); ?></td>
                    </tr>
                <?php } ?>

                <tr class="text-danger" ng-if="order == <?php echo $value['id']; ?>">
                    <th class="text-danger fw-bold" style="text-align: right">Items <span class="fa fa-arrow-up"></span></th>
                    <th class="text-danger fw-bold">Sr.#</th>
                    <th class="text-danger fw-bold">ID</th>
                    <th class="text-danger fw-bold">Product</th>
                    <th class="text-danger fw-bold">Qty</th>
                    <th class="text-danger fw-bold">Price</th>
                </tr>

            <?php } ?>
        </tbody>
    </table>
</div>
<script>
    function openRecipt(id) {
        window.open("<?php echo SITE_URL; ?>print?id=" + id + "&detail=true", "", "width=300,height=600");
    }
</script>
<?php echo mainFooter();
