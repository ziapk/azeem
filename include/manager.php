<?php
  global $shopData;
  global $shop;
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
        <div class="pull-left welcome-header-section sale-date hide-temp" ng-show="getClass()"><button class="btn btn-danger" ng-click="applyClosing()"><span ng-class="{'blink': getClass()}">Sale Close</span></button></div>
        <li class="dropdown">
          <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            Returns
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo SITE_URL.'pages/supply/adjustment.php';?>">Supply Return</a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL.'pages/orders/adjustment.php';?>">Sale Return</a></li>
          </ul>
        </li>
        
        <li><a style="padding-left: 8px; padding-right: 8px" uib-tooltip="Add Purchase" tooltip-placement="bottom" title=""  href="<?php echo SITE_URL.'pages/supply';?>"><small><small class="nav-menu-text text-small">Supply Bill</small></small></a></li>
        <li><a style="padding-left: 8px; padding-right: 8px" uib-tooltip="Reports" tooltip-placement="bottom" title=""  href="<?php echo SITE_URL.'pages/reports';?>"><small><small class="nav-menu-text text-small"><img class="fa" width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /></small></small></a></li>
        <li><a style="padding-left: 8px; padding-right: 8px" uib-tooltip="Demands" tooltip-placement="bottom" title="" href="<?php echo SITE_URL; ?>pages/demand/create.php"><small><small class="nav-menu-text text-small"><img class="fa" width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /></small></small></a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/product/running.php"><img width="22" uib-tooltip="Running Products" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /></a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/recipt/"><img width="22" uib-tooltip="Add Bill" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/book.svg" alt="" /></a></li>
        <?php include_once dirname(__FILE__).'/cart.php';?>
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
      <li class="<?php if($params['page'] == 'recipt') { echo 'active'; }?>"><a uib-tooltip="Recipt Generator" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Recipt Generator</span></a></li>
      <li class="<?php if($params['page'] == 'product') { echo 'active'; }?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
      <li class="<?php if($params['page'] == 'customer') { echo 'active'; }?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/customer.svg" alt="" /> <span class="nav-menu-text">Customers</span></a></li>
      <li><a uib-tooltip="Sale Return" tooltip-placement="right" title=""  href="<?php echo SITE_URL.'pages/orders/adjustment.php';?>"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /><span class="nav-menu-text">Supply Return</span></a></li>
      <li class="<?php if($params['page'] == 'coa') { echo 'active'; }?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Accounts</span></a></li>
      <li class="<?php if($params['page'] == 'mode') { echo 'active'; }?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/modes"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Payment Modes</span></a></li>
      <li class="<?php if($params['page'] == 'assign') { echo 'active'; }?>"><a uib-tooltip="Assign Publishers to Products at Once" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/correction.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Assign Publishers</span></a></li>
      <li class="<?php if($params['page'] == 'demand') { echo 'active'; }?>"><a uib-tooltip="Demands" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/demand"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Demands</span></a></li>
      <li class="<?php if($params['page'] == 'running') { echo 'active'; }?>"><a uib-tooltip="Running Items" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/running.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /> <span class="nav-menu-text">Running Items</span></a></li>
      <li class="<?php if($params['page'] == 'order') { echo 'active'; }?>"><a uib-tooltip="Sales" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Sales</span></a></li>
      <li class="<?php if($params['page'] == 'supplier') { echo 'active'; }?>"><a uib-tooltip="Suppliers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/suppliers"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/supplier.svg" alt="" /> <span class="nav-menu-text">Suppliers</span></a></li>
      <li><a uib-tooltip="Purchase Return" tooltip-placement="right" title=""  href="<?php echo SITE_URL.'pages/supply/adjustment.php';?>"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /><span class="nav-menu-text">Supply Return</span></a></li>
      <li class="<?php if($params['page'] == 'reports') { echo 'active'; }?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/reports"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /> <span class="nav-menu-text">Reports</span></a></li>
      <li class="<?php if($params['page'] == 'program') { echo 'active'; }?>"><a uib-tooltip="Programs" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/program"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/016-books-stack-of-three.svg" alt="" /> <span class="nav-menu-text">Programs</span></a></li>
      <!-- <li class="<?php if($params['page'] == 'return') { echo 'active'; }?>"><a uib-tooltip="Return to Lahore" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders/faultyOrders.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/018-application.svg" alt="" /> <span class="nav-menu-text">Return to Lahore</span></a></li> -->
      <li class="<?php if($params['page'] == 'publisher') { echo 'active'; }?>"><a uib-tooltip="Publisher" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/publisher"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/publisher.svg" alt="" /> <span class="nav-menu-text">Publisher</span></a></li>
      <li class="<?php if($params['page'] == 'category') { echo 'active'; }?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /> <span class="nav-menu-text">Categories</span></a></li>
      <li class="<?php if($params['page'] == 'expense') { echo 'active'; }?>"><a uib-tooltip="Expenses" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/expenses"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/expense.svg" alt="" /> <span class="nav-menu-text">Expenses</span></a></li>
      <li class="<?php if($params['page'] == 'barcode') { echo 'active'; }?>"><a uib-tooltip="Barcode for Print" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/barcode"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /> <span class="nav-menu-text">Barcode</span></a></li>
    </ul>
    <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
  </div>
</div>
<script>
function createCustomer () {
    window.open("<?php echo SITE_URL;?>pages/customers/create.php", "", "width=300,height=400"); 
}
</script>