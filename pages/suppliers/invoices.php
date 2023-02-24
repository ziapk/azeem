<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

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
    <table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Invoice ID</th>
            <th>Supplier</th>
            <th>Date/time</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customer['orders'] as $key => $value) {?>
            <tr>
                <td class="fw-bold"><?php echo $key+1;?></td>
                <td class="fw-bold"><?php echo $value['id'];?></td>
                <td class="fw-bold"><?php echo $customer['supplier']['name'];?></td>
                <td class="fw-bold"><?php echo $value['supply_date'];?></td>
                <td class="fw-bold"><?php echo $value['price'];?></td>
            </tr>
            <tr class="text-danger">
                <th class="text-danger fw-bold">Supply Items</th>
                <th class="text-danger fw-bold">Sr.#</th>
                <th class="text-danger fw-bold">ID</th>
                <th class="text-danger fw-bold">Product</th>
                <th class="text-danger fw-bold">Price</th>
            </tr>
            <?php 
            foreach ($value['order_items'] as $k => $v) {?>
                <tr>
                    <td>-</td>
                    <td><?php echo $k+1;?></td>
                    <td><?php echo $v['id'];?></td>
                    <td><?php echo $v['product_title'];?></td>
                    <td><?php echo $v['price'];?></td>
                </tr>
            <?php } ?>
            
        <?php } ?>
    </tbody>
    </table>
    </div>
    <script>
    function openRecipt (id) {
        window.open("<?php echo SITE_URL;?>print?id="+id+"&detail=true", "", "width=300,height=600"); 
    }
    </script>
    <?php echo mainFooter();
     