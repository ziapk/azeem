<?php
global $shopData;
global $userData;
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerProducts($ownerId);
?>
<div ng-controller="headerController">
  <table width="50%" style="margin: auto">
    <tr>
      <td>
        <input type="range" ng-model="fontsize" min="13" max="40" ng-change="updateFont(fontsize)" />
      </td>
      <td>{{fontsize}}</td>
    </tr>
  </table>
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
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              Create
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/product/create.php"><span class="nav-menu-text">+ Product</span></a></li>
            </ul>
          </li>
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
      <li class="<?php if ($params['page'] == 'order') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Order Generator" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt/order.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Create Order</span></a></li>
      <li class="<?php if ($params['page'] == 'order') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Sales" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Sales</span></a></li>
      <li class="<?php if ($params['page'] == 'product-create') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Add New Product" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">New Product</span></a></li>
      <li class="<?php if ($params['page'] == 'status') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Status" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/status/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Statuses</span></a></li>
      <li class="<?php if ($params['page'] == 'product') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
      <li class="<?php if ($params['page'] == 'employees') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Employees" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/employees"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Employees</span></a></li>
      <li class="<?php if ($params['page'] == 'running') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Running Items" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/running.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /> <span class="nav-menu-text">Running Items</span></a></li>
      <li class="<?php if ($params['page'] == 'mode') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/modes"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Payment Modes</span></a></li>
      <li class="<?php if ($params['page'] == 'coa') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Accounts</span></a></li>
      <li class="<?php if ($params['page'] == 'customers') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/customer.svg" alt="" /><span class="nav-menu-text">Customers</span></a></li>
      <li class="<?php if ($params['page'] == 'category') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /><span class="nav-menu-text">Categories</span></a></li>
      <li class="<?php if ($params['page'] == 'services') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Services" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/services/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /><span class="nav-menu-text">Services</span></a></li>
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