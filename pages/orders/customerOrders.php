<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $id = !empty($_GET['id']) ? $_GET['id'] : 0;
    
    $ordersObj = new Orders();
    $customers = new Customers();
    $customer = $customers->getCustomer($id);
    $orders = $ordersObj->getCustomerOrders($userData['shopId'], $id);
    $ids = [];

    foreach ($orders as $value) {
        $ids[] = $value['id'];
    }

    $transactions = [];
    $trans = [];
    if(!empty($ids)) {
        $trans = $ordersObj->getTransactionsByOIds($ids);
    }

    foreach ($trans as $value) {
        if(empty($transactions[$value['order_id']])) {
            $transactions[$value['order_id']] = 0;
        }
        $transactions[$value['order_id']] += $value['amount'];
    }

    echo mainHeader();
    ?>
    <div class="container">
    <table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Price</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Date/time</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $key => $value) {?>
            <tr>
                <td><?php echo $key+1;?></td>
                <td><?php echo $value['id'];?></td>
                <td><?php echo $customer['full_name'];?></td>
                <td><?php echo $value['price'];?></td>
                <td><?php echo !empty($transactions[$value['id']]) ? $transactions[$value['id']] : null;?></td>
                <td><?php echo !empty($transactions[$value['id']]) ? ($value['price'] - $transactions[$value['id']]) : null;?></td>
                <td><?php echo $value['created_at'];?></td>
                <td>
                    <a href="javascript:void(0)" onclick="openRecipt(<?php echo $value['id'];?>)" class="btn btn-info btn-xs">View Bill</a>
                    <a href="adjustment.php?id=<?php echo $value['id'];?>" class="btn btn-primary btn-xs">Payment History</a>
                </td>
            </tr>
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
     