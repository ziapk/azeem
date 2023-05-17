<?php
global $shopData;
global $userData;
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerProducts($ownerId);
?>
<div ng-controller="headerController">
  <nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <div class="logo pull-left">
          <a href="<?php echo SITE_URL; ?>" title=""><?php if (!empty($shopData['image'])) { ?>
              <span class="fa">&#xf260;</span> Smart Commerce
            <?php } else { ?>
              <img width="60" src="<?php echo SITE_URL; ?>assets/img/logo.png" alt="" />
            <?php } ?></a>
        </div>
        <div class="pull-left welcome-header-section"><span>Welcome <strong><?php echo $userData['full_name']; ?>!</strong></span></div>

        <ul class="list-inline navbar-right navbar-nav nav">
          <li>
            <a title="" href="javascript:void(0)" data-toggle="dropdown" tooltip-placement="bottom" uib-tooltip="Settings"><span class="fa fa-cog"></span> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a title="" href="<?php echo SITE_URL; ?>pages/profile">Profile</a></li>
              <li class="divider"></li>
              <li><a title="" href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div><!-- /.container-fluid -->
  </nav>
  <div class="sidebar">
    <ul class="nav">
      <li class="<?php if ($params['page'] == 'dashboard') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Dashboard" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Dashboard</span></a></li>
    </ul>
    <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
  </div>
</div>
<script>
  function createCustomer() {
    window.open("<?php echo SITE_URL; ?>pages/customers/create.php", "", "width=300,height=400");
  }

  app.controller('headerController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
    $scope.list = <?php echo safe_json_encode($list); ?>;
    $scope.refreshList = function() {
      $scope.cart = JSON.parse($window.sessionStorage.getItem('shopping'));
      $scope.totalPrice = 0;
      $scope.finalList = [];
      $scope.cart.map(row => {
        const obj = $scope.list.find(r => r.id === row.id);
        $scope.finalList.push({
          ...obj,
          qty: row.qty
        })
        $scope.totalPrice += row.qty * obj.price
      })
    }
    $scope.increaseValue = row => {
      const cart = JSON.parse($window.sessionStorage.getItem('shopping'))
      cart.map(r => {
        if (row.id == r.id) {
          r.qty++
        }
      })
      $window.sessionStorage.setItem('shopping', JSON.stringify(cart));
      $scope.refreshList();
    }

    $scope.decreaseValue = (row) => {
      const cart = JSON.parse($window.sessionStorage.getItem('shopping'))

      cart.map(r => {
        if (row.id == r.id) {
          r.qty > 1 ? r.qty-- : r.qty
        }
      })
      console.log(cart);
      $window.sessionStorage.setItem('shopping', JSON.stringify(cart));
      $scope.refreshList()
    }
  });
</script>