<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$expenseObj = new Expenses();


    $ordersObj = new Orders();
    $dateLabel = "Sales for ";
    $start = $end = date('Y-m-d');

    if(isset($_GET['report'])) {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $expenseData = $expenseObj->getShopExpenses($userData['shopId'], $from, $to);
        $dateLabel .= $from.' to '.$to;
        $start = date('Y-m-d', strtotime($from));
        $end = date('Y-m-d', strtotime($to));
    }
    else {        
        $expenseData = $expenseObj->getShopExpenses($userData['shopId'], date('Y-m-d'));
        $dateLabel .= date('Y-m-d');
        $start = date('Y-m-d');
        $end = date('Y-m-d');

    }

echo mainHeader(['page' => 'expense']);
?>

<div class="container">
    <a href="<?php echo SITE_URL;?>pages/expenses/bulk.php" class="btn btn-danger btn-sm pull-right" style="margin-left: 10px">Add In Bulk</a>    
    <a href="javascript:void(0)" onclick="createCategory()" class="btn btn-success btn-sm pull-right">Add New</a>    
    <h3 style="margin-top: 0">Expenses</h3>
    <form method="GET" action="">
        <h4><?php echo $dateLabel;?></h4>
        <div class="input-group">
        <input class="form-control datepicker" type="text" value="" readonly />
        <div class="input-group-btn">
            <input type="submit" value="Submit" name="report" class="btn btn-primary" />
        </div>
        </div>

        <input type="hidden" id="start" value="<?php echo $start;?>">
        <input type="hidden" id="end" value="<?php echo $end;?>">
        
        <input type="hidden" id="from" name="from" value="<?php echo $start;?>">
        <input type="hidden" id="to" name="to"  value="<?php echo $end;?>">

    </form>
    <table class="table">
        <thead>
            <tr>
                <th width="100px">Sr.#</th>
                <th>Name</th>
                <th width="150px">Price</th>
                <th width="150px">Date</th>
                <th width="100px"></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $total = 0;
         foreach ($expenseData as $key => $cust) {  $total += $cust['price']; ?>
            <tr>
                <td><?php echo $key + 1;?></td>
                <td><?php echo $cust['title'];?></td>
                <td><?php echo $cust['price'];?></td>
                <td><?php echo date('d M Y', strtotime($cust['exp_date']));?></td>
                <td><a onclick="deleteExpense(<?php echo $cust['id'];?>)" class="btn btn-primary btn-xs" href="javascript:void(0)">Delete</a></td>
            </tr>
        <?php }?>
        </tbody>
        <tfoot>
        <tr>
            <th>Total</th>
            <th colspan="6">
                <table style="text-align: right" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <th style="text-align: right">Number of Expenses</th>
                        <th style="text-align: right"><?php echo sizeof($expenseData);?></th>
                    </tr>
                    <tr>
                        <th style="text-align: right">Total Payment</th>
                        <th style="text-align: right"><?php echo $total;?></th>
                    </tr>
                </table>
            </th>
        </tr>
    </tfoot>
    </table>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
function createCategory () {
    window.open("<?php echo SITE_URL;?>pages/expenses/create.php", "", "width=300,height=400"); 
}

function deleteExpense(id) {
    if(confirm('Are you sure ?')) {
        window.open("<?php echo SITE_URL;?>pages/expenses/delete.php?id="+id, "", "width=300,height=400"); 
        window.location.reload();
    }
}

$('.datepicker').daterangepicker({
    maxDate: moment(),
    startDate: new Date($('#start').val()),
    endDate: new Date($('#end').val()),
 },  function(start, end, label) {
     $('#from').val(moment(start).format('YYYY-MM-DD'));
     $('#to').val(moment(end).format('YYYY-MM-DD'));

 });


</script>