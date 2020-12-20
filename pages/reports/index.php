<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $ordersObj = new Orders();


    $dateLabel = "Sales for ";

    
    if(isset($_POST['report'])) {
        $from = $_POST['from'];
        $to = $_POST['to'];
        $orders = $ordersObj->userOrders($userData['shopId'], $from, $to);
        $dateLabel .= $from.' to '.$to;
    }
    else {        
        $orders = $ordersObj->userOrders($userData['shopId'], date('Y-m-d'));
        $dateLabel .= date('Y-m-d');
    }
    echo mainHeader();
?>

<div class="container">
<form method="POST">
    <h4><?php echo $dateLabel;?></h4>
    <div class="input-group">
    <input class="form-control datepicker" type="text" readonly />
    <div class="input-group-btn">
        <input type="submit" value="Submit" name="report" class="btn btn-primary" />
    </div>
    </div>
    
    <input type="hidden" id="from" name="from">
    <input type="hidden" id="to" name="to">

</form>
<table class="table">
    <thead>
        <tr>
            <th>Sr.#</th>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Price</th>
            <th>Status</th>
            <th>Date/time</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $key => $order) { ?>
        <tr>
            <td><?php echo $key + 1;?></td>
            <td><?php echo $order['id'];?></td>
            <td><?php echo $order['full_name'];?></td>
            <td><?php echo $order['price'];?></td>
            <td><?php echo $orderStatusArr[$order['status']]['full_name'];?></td>
            <td><?php echo $order['order_date'];?></td>
        </tr>
    <?php }?>
        
    </tbody>
</table>
</div>
<?php
echo mainFooter();

?>
<script type="text/javascript">
 $('.datepicker').daterangepicker({
    minDate: moment().subtract(1, 'week'),
    maxDate: moment()
 },  function(start, end, label) {
     $('#from').val(moment(start).format('YYYY-MM-DD'));
     $('#to').val(moment(end).format('YYYY-MM-DD'));

 });
</script>