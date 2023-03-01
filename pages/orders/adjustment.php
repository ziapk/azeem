<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
$ordersObj = new Orders();
$customers = new Customers();
$order = $ordersObj->getOrder($id);
echo mainHeader();
?>
    <div class="container" ng-controller="productController">
        <dl class="dl-horizontal">
            <dt>Shop Name:</dt>
            <dd><?php echo $shopData['product_title'];?></dd>
            <dt>Customer Name:</dt>
            <dd><?php echo $order['customer']['full_name'];?></dd>
            <dt>Order Number:</dt>
            <dd><?php echo $order['order']['id'];?></dd>
            <dt>Order Status:</dt>
            <dd><?php echo $orderStatusArr[$order['order']['status']]['full_name'];?></dd>
            <dt>Order Date/Time:</dt>
            <dd><?php echo $order['order']['created_at'];?></dd>
        </dl>
        
        <table class="table">
            <thead>
                <tr>
                    <th width="50">Sr.#</th>
                    <th width="55%">Title</th>
                    <th width="100">Qty</th>
                    <th width="150">Price</th>
                    <th width="150">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['order_items'] as $index => $item) { ?>
                    <tr>
                        <td><?php echo ($index + 1);?></td>
                        <td><?php echo $item['product_title'];?></td>
                        <td><?php echo $item['quantity'];?></td>
                        <td><?php echo $item['price'];?></td>
                        <td><?php echo round($item['quantity'] * $item['price'], 2);?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <thead>
                <tr>
                    <th colspan="3"></th>
                    <th>Sub Total</th>
                    <th><?php echo $order['order']['price'];?></th>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <th>Discount</th>
                    <th><?php echo $order['order']['discount'];?></th>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <th>Paid</th>
                    <th><?php echo $order['order']['paid_amount'];?></th>
                </tr>
                <tr>
                    <th colspan="3"></th>
                    <th>Balance</th>
                    <th><?php echo $order['order']['price'] - $order['order']['discount'] - $order['order']['paid_amount'];?></th>
                </tr>
                <?php 
                if($order['order']['price'] - $order['order']['discount'] - $order['order']['paid_amount'] > 0) { ?>
                    <tr>
                        <th colspan="3"></th>
                        <th>Pay Now</th>
                        <th>
                        
                            <form ng-submit="payToWallet()" class="input-group">
                                <input type="text" ng-model="amount" class="form-control" placeholder="Amount">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">Pay</button>
                                </span>
                            </form>
                        </th>
                    </tr>
                <?php } ?>
            </thead>
        </table>
        
    </div>
<?php
echo mainFooter();
?>
<script>
app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
    
    $scope.id = <?php echo json_encode($_GET['id']);?>; //$scope.data.records;
    

    $scope.payToWallet = function () {
        if($window.confirm('Are you sure you want to proceed this amount?')) {
            $http.post("<?php echo SITE_URL?>api/orderAdjustments.php", $httpParamSerializerJQLike({amount: $scope.amount, id: $scope.id}), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
            .then(function(response) {
                alert('Amount Paid Successfully, transaction id is '+ response.data.transaction.id);
                $window.location.reload();
            });
        }
    }

    
});
</script>