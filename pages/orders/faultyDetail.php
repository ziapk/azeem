<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $ordersObj = new Orders();
    $id = !empty($_GET['id']) ? $_GET['id'] : 0;
    $returns = $ordersObj->getReturnRecord($id);


    $return = $returns['data']; 
    $total = 0;

    echo mainHeader(['page'=> 'return']);
?>
<style>
th {
    font-weight: bold;
}
</style>
    <div class="container">
        <h4><strong><?php echo $return['full_name'];?></strong></h4>
        <p><?php echo $return['datetime']; ?></p>
        <table class="table">
            <thead>
                <tr>
                    <th width="60">Sr.#</th>
                    <th width="60">ID</th>
                    <th>Name</th>
                    <th width="100">Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($returns['items'] as $key => $row) { $total += $row['quantity'];?>
                    <tr>
                        <td><?php echo $key + 1;?></td>
                        <td><?php echo $row['product_id'];?></td>
                        <td><?php echo $row['product_name'];?></td>
                        <td><?php echo $row['quantity'];?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <th colspan="3">Total</th>
                <th><?php echo $total;?></th>
            </tfoot>
        </table>
    </div>
<?php
echo mainFooter();