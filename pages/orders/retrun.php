<?php
include_once dirname(__FILE__).'/../../include/settings.php';
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
if(empty($id)) {
    echo "Invalid data";
}
$details = !empty($_GET['detail']) && $_GET['detail'] == 'true' ? true : false;
$ordersObj = new Orders();
$order = $ordersObj->getOrder($id);
print_r($order);
?>
    <style>
    body {
        margin: 0;
    }
    .recipt {
        font-family: Tahoma;
        text-align: center;
        font-size: 14px;
        line-height: 1.5;
        padding-top: 0;
        width: 260px;
        margin: 0 auto;
    }
    h4 {
        margin: 0; 
    }
    table {
        border-collapse: collapse;
        font-size: 12px;
        font-family: Tahoma;
}
tr {
    border-bottom: 1px dashed
}
.no-border {
    border: 0;
}
th {
    padding: 0 0 3px;
}
td {
    padding: 3px 0;
    text-align: center;
}
.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

.thead {
    font-size: 10px;
    font-weight: 600
}
.tiny {
    font-size: 10px;
    margin: 0.5em 0
}
.mt-5 {
    margin-top: 5px;
    margin-bottom: 5px;
}
.head h4 {
    font-family: 'Courgette', cursive;
}
.ref, .date,
.head p {
    font-size: 11px;
}
.head {
    margin: 0;
}
.pull-left {
    float: left;
}
.pull-right {
    float: right;
}
</style>
<link href="https://fonts.googleapis.com/css?family=Courgette&display=swap" rel="stylesheet">
<?php

    $foodpanda = [];
    if($order['customer']['type'] == 2) {
        $foodpanda = $order['customer'];
    }

    

    $a = [];
    array_push($a, $shopData['phone_1'], $shopData['phone_2']);
    $result = array_filter( $a, 'strlen' );

    echo mainHeader();



    // order types
        // faulty 
        // return 
        // normal 

?>

<div class="container" ng-controller="productsController">
    <table>
        <tr>
            <th style="padding: 0 12px" width="150">Order Id:</th>
            <th width="200">{{item.order.id}}</th>
            <th width="150" style="padding: 0 12px">Created At:</th>
            <th width="200"><?php echo date('d/m/Y H:i', strtotime($order['order']['created_at']) );?></th>
            <th width="150" style="padding: 0 12px">Status:</th>
            <th width="200" class="text-success">{{statusArr[item.order.status].full_name}}</th>
        </tr>
        <tr>
            <th style="padding: 0 12px" width="150">Customer's Name:</th>
            <th width="200" style="font-weight: bold">{{item.order.customer_name || item.customer.full_name}}</th>
            <th width="150" style="padding: 0 12px">Opening Balance:</th>
            <th width="200" style="font-weight: bold">{{item.customer.account.opening_balance}}</th>
            <th width="150" style="padding: 0 12px">Status:</th>
            <th width="200" class="text-success">{{statusArr[item.order.status].full_name}}</th>
        </tr>
        <tr>
            <th style="padding: 0 12px">Order Price:</th>
            <th style="font-weight: bold">{{item.order.price}}</th>
            <th style="padding: 0 12px">Discount:</th>
            <th style="font-weight: bold">{{item.order.discount}}</th>
            <th style="padding: 0 12px">Paid Amount:</th>
            <th style="font-weight: bold">{{item.order.paid_amount}}</th>
        </tr>
    </table>
    <br>
    <ul ng-if="item.order.status == 2 || item.order.status == 8 || item.order.status == 9" class="list-unstyled">
        <li ng-repeat="itm in item.order_items">
            <strong>{{itm.product_title}}</strong><br />
            {{itm.quantity}} x {{itm.price - itm.discount}} = 
            <strong>{{itm.quantity * itm.price - itm.discount}}</strong><br />
            <span>In Inventory
                <input style="width: 60" type="number" ng-model="itm.inventory" ng-change="addToInventory(itm)">
            </span>
            <span>Faulty <input style="width: 60" type="number" ng-model="itm.faulty" ng-change="addToInventory(itm)"></span><br />
            <p><strong class="text-danger" ng-if="itm.error">invalid quantity</strong></p>
        </li>
    </ul>
    <h3>Return Summery</h3>
    <table>
        <tr>
            <th style="padding: 0 12px" width="250">Total Returnable Amount:</th>
            <th width="200" style="font-weight: bold">{{price}}</th>
        </tr>
        <tr>
            <th width="250" style="padding: 0 12px">Discount:</th>
            <th width="200" style="font-weight: bold">{{item.order.discount}}</th>
        </tr>
        <tr>
            <th width="250" style="padding: 0 12px">Net Returnable Amount:</th>
            <th width="200" style="font-weight: bold" class="text-success">{{price - item.order.discount}}</th>
        </tr>
    </table>
    <p class="text-right" ng-if="item.order.status == 2 || item.order.status == 8 || item.order.status == 9">
        <a href="javascript:void(0)" ng-click="orderReturn()" class="btn btn-default">All to Inventory</a>
        <a href="javascript:void(0)" ng-click="orderFaulty()" class="btn btn-default">All to Faulty</a>
        <a href="javascript:void(0)" ng-click="orderPartial()" class="btn btn-primary">Submit as Filled</a>
    </p>
</div>

<?php echo mainFooter(); ?>
<script>
    app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $window) {
    $scope.item = <?php echo json_encode($order);?>;
    $scope.statusArr = <?php echo json_encode($orderStatusArr);?>;
    $scope.items = {};
    $scope.price = 0;
    $scope.orderReturn = () => {
        $scope.loading = true;
        $http.get("<?php echo SITE_URL?>api/orderReturn.php", {params: {id: $scope.item.order.id, action: 1}})
        .then(res => {
            $scope.loading = false;
        })
    }
    $scope.orderFaulty = (row) => {
        $scope.loading = true;
        $http.get("<?php echo SITE_URL?>api/orderReturn.php", {params: {id: $scope.item.order.id,  action: 2}})
        .then(res => {
            $scope.loading = false;
        })
    }

    $scope.calc = () => {
        $scope.price = 0;
        const items = [];
        $scope.item.order_items.map(r => {
            console.log('r', r);
            if(r.inventory && !r.error) {
                items[r.product_id] = items[r.product_id] || {}
                items[r.product_id].inventory = r.inventory;
                $scope.price += parseFloat(r.price - r.discount) * r.inventory;
            }
            if(r.faulty && !r.error) {
                items[r.product_id] = items[r.product_id] || {}
                items[r.product_id].faulty = r.faulty;
                $scope.price += parseFloat(r.price - r.discount) * r.faulty;
            }
        })
        
    }
    
    $scope.orderPartial = (row) => {
        $scope.loading = true;
        const items = {};
        $scope.item.order_items.map(r => {
            if(r.inventory && !r.error) {
                items[r.id] = items[r.id] || {}
                items[r.id].detail = r;
                items[r.id].inventory = r.inventory;
            }
            if(r.faulty && !r.error) {
                items[r.id] = items[r.id] || {}
                items[r.id].detail = r;
                items[r.id].faulty = r.faulty;
            }
        })
        $http.get("<?php echo SITE_URL?>api/orderReturn.php", {params: {id: $scope.item.order.id, items, action: 3}})
        .then(res => {
            $scope.loading = false;
            // $window.location.reload();
        })
    }

    
    $scope.addToInventory = (row) => {
        console.log(row);
        if(((row.inventory || 0) + (row.faulty || 0)) > row.quantity) {
            row.error = true
        }
        else {
            row.error = false
        }
        if(row.inventory < 0) {
            row.inventory = 0
            
        }
        if(row.faulty < 0) {
            row.faulty = 0
        }
        $scope.calc();
    }
    }) 
</script>