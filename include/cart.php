<li uib-dropdown auto-close="outsideClick" on-toggle="refreshList()">
    <a href="javascript:void(0)" uib-dropdown-toggle><span class="glyphicon glyphicon-shopping-cart"></span> Cart</a>
    <div class="dropdown-menu cart-menu" uib-dropdown-menu>
    <div class="cart-list">
        <table width="100%">
            <tr ng-repeat="li in finalList" class="cart-list-item">
                <td width="60">
                    <img class="cart-list-image" src={{'<?php echo SITE_URL;?>/uploads/products/'+li.image}} alt=""/>
                </td>
                <td>
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
            <tr class="cart-total-row">
                <td>
                    <a href="<?php echo SITE_URL; ?>pages/cart" class="btn btn-success btn-xs">Checkout</a>
                    <a href="javascript:void(0)" class="btn btn-danger btn-xs">Clear</a>
                </td>
                <td>
                    Total
                </td>
                <td class="cart-right">
                    {{totalPrice}}
                </td>
            </tr>
        </table>
    </div>
    </div>
</li>

<script>
app.controller('headerController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
  $scope.list = <?php echo json_encode($list);?>;
  $scope.refreshList = function() {
    $scope.cart = JSON.parse($window.localStorage.getItem('shopping'));
    $scope.totalPrice = 0;
    $scope.finalList = [];
    $scope.cart.map(row => {
      const obj = $scope.list.find(r => r.id === row.id);
      $scope.finalList.push({...obj, qty: row.qty})
      $scope.totalPrice += row.qty * obj.price
    })
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
    console.log(cart);
    $window.localStorage.setItem('shopping', JSON.stringify(cart));
    $scope.refreshList()
  }
});
</script>