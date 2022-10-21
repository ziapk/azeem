<?php
include_once dirname(__FILE__).'/../../include/settings.php';
$productCls = new Categories();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerCategories($ownerId);
$sortedList = [];
foreach ($list as $l) {
    $sortedList['groupNameList'][] = $l['groupName'];
    $sortedList['group'][$l['groupName']][] = $l;
}
$sortedList['groupNameList'] = array_unique($sortedList['groupNameList']);

echo mainHeader(['page'=> 'expense']);
?>
<div ng-controller="cartController">
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th style="vertical-align: middle; text-align: right" width="250">
                        Filter by Group
                    </th>
                    <th style="vertical-align: middle" width="250">
                        <select class="form-control" ng-model="form.group" ng-change="prepareExpense(form.group)">
                            <option value="">All</option>
                            <option ng-repeat="li in mainList.groupNameList" ng-value="li">{{li}}</option>
                        </select>
                    </th>
                    <th style="vertical-align: middle; text-align: right" width="250">
                        Expense Date
                    </th>
                    <th style="vertical-align: middle; text-align: right; position: relative">
                        <input type="text" class="form-control datepicker-single"/>
                    </th>
                </tr>
            </thead>
        </table>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Sr.#</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="cart in form.expenses track by $index" id="item-{{cart.id}}">
                    <td>{{$index + 1}}</td>
                    <td>{{cart.full_name}}</td>
                    <td><input class="form-control" type="text" ng-model="cart.description" /></td>
                    <td><input class="form-control text-center" type="text" ng-model="cart.amount" ng-change="calculateSum(form.expenses)"  /></td>
                </tr>
            </tbody>
            <thead>
                <tr>
                    <th class="text-right" colspan="3">Total</th>
                    <th width="200" class="text-center">{{grandTotal}}</th>
                </tr>
            </thea>
            <tbody>
                <tr>
                    <th class="text-right" colspan="4">
                        
                        <a href="#" class="btn btn-primary" ng-click="checkout()"><img width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/001-checkout.svg" alt="" /> Checkout</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>

</div>
<script type="text/javascript">

app.controller('cartController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $timeout) {
    $scope.mainList = <?php echo json_encode($sortedList);?>;
    $scope.shopId = <?php echo $userData['shopId'];?>;
    $scope.list = [];
    $scope.priceList = [];

    $scope.form = {
        group: 'General',
        expenses: {},
        exp_date: moment().format('YYYY-MM-DD')
    };

    $scope.prepareExpense = (group) => {
        $scope.form.expenses = {};
        $scope.grandTotal = 0;
        if(group) {
            $scope.mainList.group[group].forEach(row => {
                $scope.form.expenses[row.id] = {...row, amount: 0 };
            })
        }
        else {
            Object.keys($scope.mainList.group).map(g => {
                $scope.mainList.group[g].forEach(row => {
                    $scope.form.expenses[row.id] = {...row, amount: 0 };
                })
            })
        }
    };

    $scope.prepareExpense($scope.form.group);

    $scope.checkout = function () {
        const form = [];
        Object.values($scope.form.expenses).map(row => row.amount ? form.push ({
            cat_id: row.id,
            title: row.full_name,
            description: row.description || '',
            price: row.amount,
            details: row.groupName,
            exp_date: $scope.form.exp_date,
            shop_id: $scope.shopId
        }) : null);

        console.log(form);

        $http.post("<?php echo SITE_URL?>api/placeExpenses.php", $httpParamSerializerJQLike({ form }), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            // window.open("<?php echo SITE_URL;?>print?id="+response.data.order.id, "", "width=300,height=300"); 
            // $scope.form = $scope.list = [];
            // $scope.subTotal = $scope.discount = $scope.grandTotal = $scope.payment_amount = 0;
            $scope.form = {
                group: 'General',
                expenses: {},
                exp_date: moment().format('YYYY-MM-DD')
            };


            $scope.prepareExpense($scope.form.group);
        });

    }    

    $scope.calculateSum = (array) => {
        console.log(array);
        let subtotal = 0;
        Object.values(array).map((product) => {
            subtotal += parseFloat(product.amount || 0);
        })
        $scope.grandTotal = subtotal;
    }
        $('.datepicker-single').daterangepicker({
            autoApply: true,
            maxDate: moment().format('YYYY-MM-DD'),
            singleDatePicker: true,
            locale: {
                format: 'YYYY-MM-DD'
            },
        }, function(date) {
            $scope.form.exp_date = moment(date).format('YYYY-MM-DD');
            $scope.$apply();
        });

})
</script>

<?php 
echo mainFooter();?>

