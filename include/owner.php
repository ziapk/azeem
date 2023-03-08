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
          <a href="<?php echo SITE_URL; ?>" title=""><?php if(!empty($shopData['image'])) { ?>
            <span class="fa">&#xf260;</span> Smart Commerce
          <?php } else { ?>
            <img width="60" src="<?php echo SITE_URL; ?>assets/img/logo.png" alt="" />
          <?php } ?></a>
        </div>
        <div class="pull-left welcome-header-section"><span>Welcome <strong><?php echo $userData['full_name'];?>!</strong></span></div>
        
      <ul class="list-inline navbar-right navbar-nav nav">
        <li class="dropdown" style="padding: 0; margin-right: -1px">
          <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            Returns
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo SITE_URL.'pages/supply/adjustment.php';?>">Supply Return</a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL.'pages/orders/adjustment.php';?>">Sale Return</a></li>
          </ul>
        </li>
        <li class="dropdown" style="padding: 0">
          <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
            Create
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/product/create.php"><span class="nav-menu-text">+ Product</span></a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL.'pages/supply';?>">+ Supply</a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/demand/create.php">+ Demand</a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/recipt/" target="_blank">+ Recipt</a></li>
          </ul>
        </li>
        <li><a style="padding-left: 8px; padding-right: 8px" uib-tooltip="Reports" tooltip-placement="bottom" title=""  href="<?php echo SITE_URL.'pages/reports';?>"><small><small class="nav-menu-text text-small"><img class="fa" width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /></small></small></a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/product/running.php"><img width="22" uib-tooltip="Running Products" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /></a></li>
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
    <li class="<?php if($params['page'] == 'product-create') { echo 'active'; }?>"><a uib-tooltip="Add New Product" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">New Product</span></a></li>
      <li class="<?php if($params['page'] == 'product') { echo 'active'; }?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
      <li class="<?php if($params['page'] == 'coa') { echo 'active'; }?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Accounts</span></a></li>
      <li class="<?php if($params['page'] == 'demand') { echo 'active'; }?>"><a uib-tooltip="Demands" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/demand/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Invoicing</span></a></li>
      <li class="<?php if($params['page'] == 'publisher') { echo 'active'; }?>"><a uib-tooltip="Publisher" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/publisher"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/publisher.svg" alt="" /><span class="nav-menu-text">Publisher</span></a></li>
      <li class="<?php if($params['page'] == 'program') { echo 'active'; }?>"><a uib-tooltip="Programs" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/program"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/016-books-stack-of-three.svg" alt="" /> <span class="nav-menu-text">Programs</span></a></li>
        <li class="<?php if($params['page'] == 'customers') { echo 'active'; }?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/customer.svg" alt="" /><span class="nav-menu-text">Customers</span></a></li>
        <li class="<?php if($params['page'] == 'suppliers') { echo 'active'; }?>"><a uib-tooltip="Suppliers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/suppliers/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/supplier.svg" alt="" /><span class="nav-menu-text">Suppliers</span></a></li>
        <li class="<?php if($params['page'] == 'expense') { echo 'active'; }?>"><a uib-tooltip="Expenses" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/expenses"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/expense.svg" alt="" /><span class="nav-menu-text">Expenses</span></a></li>
        <li class="<?php if($params['page'] == 'barcode') { echo 'active'; }?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/barcode"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /> <span class="nav-menu-text">Barcode</span></a></li>
        <li class="<?php if($params['page'] == 'category') { echo 'active'; }?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /><span class="nav-menu-text">Categories</span></a></li>
        <li class="<?php if($params['page'] == 'return') { echo 'active'; }?>"><a uib-tooltip="Return to Lahore" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders/faultyOrders.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /> <span class="nav-menu-text">Return to Lahore</span></a></li>
        <li class="<?php if($params['page'] == 'reports') { echo 'active'; }?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/reports/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /><span class="nav-menu-text">Reports</span></a></li>
    </ul>
    <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
  </div>
</div>
<script>
function createCustomer () {
    window.open("<?php echo SITE_URL;?>pages/customers/create.php", "", "width=300,height=400"); 
}

app.controller('headerController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window) {
  $scope.list = <?php echo safe_json_encode($list);?>;
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