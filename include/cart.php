<?php
  global $shopData;
  global $userData;
  $productCls = new Products();
  $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
  $list = $productCls->getOwnerProducts($ownerId);
?>
<li uib-dropdown auto-close="outsideClick" on-toggle="refreshList()">
<a href="javascript:void(0)" uib-dropdown-toggle tooltip-placement="bottom" uib-tooltip="Shopping Cart"><img width="22" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/010-shopping-bag-white.svg" alt="" /></a>
    <div class="dropdown-menu cart-menu" uib-dropdown-menu>
    <div class="cart-list">
        <table width="100%">
            <tr ng-repeat="li in finalList" class="cart-list-item">
                <td width="60">
                    <img class="cart-list-image" src={{'<?php echo SITE_URL;?>/uploads/products/'+li.image}} alt=""/>
                </td>
                <td colspan="2">
                    <span class="cart-item-title">{{li.full_name}}<span>
                    <div class="cart-item-controls">
                        <a class="decrease-value" ng-click="decreaseValue(li); $event.stopPropagation()">
                            <span class="glyphicon glyphicon-menu-left"></span>
                        </a>
                        <span class="qty">{{li.qty}}</span>
                        <a href="javascript:void(0);" class="increase-value" ng-click="increaseValue(li); $event.stopPropagation()">
                            <span class="glyphicon glyphicon-menu-right"></span>
                        </a>
                    </div>
                </td>
                <td class="cart-right">
                    {{li.qty * li.price}}
                </td>
            </tr>
            <tr class="cart-total-row" ng-if="finalList.length">
                <td colspan="2" style="text-align: left">
                    <a href="<?php echo SITE_URL; ?>pages/cart" class="btn btn-success btn-xs">Checkout</a>
                    <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="clear()">Clear</a>
                </td>
                <td>
                    Total
                </td>
                <td class="cart-right">
                    {{totalPrice}}
                </td>
            </tr>
            <tr ng-if="!finalList.length">
              <th class="text-center"><h3 style="margin-bottom: 20px">NOTHING IN CART </h3> <a class="btn btn-primary" href="<?php echo SITE_URL;?>pages/product">Add Now</a></th>
            </tr>
        </table>
    </div>
    </div>
</li>
<div id="cccc" style="display: none"><?php echo json_encode($list);?></div>

<script>
app.controller('headerController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
  $scope.list = JSON.parse($('#cccc').html());
  localStorage.setItem('list', JSON.stringify($scope.list));
  $scope.refreshList = function() {
    $scope.cart = JSON.parse($window.localStorage.getItem('shopping'));
    $scope.totalPrice = 0;
    $scope.finalList = [];
    $scope.cart.map(row => {
      console.log(row);
      const obj = $scope.list.find(r => r.id === row.id);
      $scope.finalList.push({...obj, qty: row.qty})
      $scope.totalPrice += row.qty * obj.price
    })
  }

  $scope.clear = () => {
    $scope.cart = $window.localStorage.setItem('shopping', JSON.stringify([]));
    $scope.totalPrice = 0;
    $scope.finalList = [];
  }


  $scope.increaseValue = row => {
    const cart = JSON.parse($window.localStorage.getItem('shopping'))
    cart.map(r => {
      if(row.id == r.id) {
        r.qty++
      }
    })
    $window.localStorage.setItem('shopping', JSON.stringify(cart));
    $scope.refreshList();
  }
  
  $scope.decreaseValue = (row) => {
    const cart = JSON.parse($window.localStorage.getItem('shopping'))
    
    cart.map(r => {
      if(row.id == r.id) {
        r.qty > 1 ? r.qty-- : r.qty
      }
    })
    $window.localStorage.setItem('shopping', JSON.stringify(cart));
    $scope.refreshList()
  }
});
</script>