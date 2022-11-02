<?php
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
          <a href="<?php echo SITE_URL; ?>" title=""><?php if(!empty($shop['image'])) { ?>
            <span class="fa">&#xf260;</span> Smart Commerce
          <?php } else { ?>
            <img width="60" src="<?php echo SITE_URL; ?>assets/img/logo.png" alt="" />
          <?php } ?></a>
        </div>
        <div class="pull-left welcome-header-section"><span>Welcome to <strong><?php echo $shop['full_name'];?></strong></span></div>
        
      <ul class="list-inline navbar-right navbar-nav nav">
        <li><a href="<?php echo SITE_URL; ?>pages/product/running.php"><img width="22" uib-tooltip="Running Products" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /></a></li>
        <?php 
          include_once dirname(__FILE__).'/cart.php';
        ?>
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
    <li class="<?php if($params['page'] == 'recipt') { echo 'active'; }?>"><a uib-tooltip="Recipt Generator" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/018-application.svg" alt="" /> <span class="nav-menu-text">Recipt Generator</span></a></li>
      <li class="<?php if($params['page'] == 'product') { echo 'active'; }?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/004-storage.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
      <li class="<?php if($params['page'] == 'customer') { echo 'active'; }?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/014-group.svg" alt="" /> <span class="nav-menu-text">Customers</span></a></li>
      <!-- <li class="<?php if($params['page'] == 'program') { echo 'active'; }?>"><a uib-tooltip="Programs" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/program"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/016-books-stack-of-three.svg" alt="" /> <span class="nav-menu-text">Programs</span></a></li> -->
      <li class="<?php if($params['page'] == 'running') { echo 'active'; }?>"><a uib-tooltip="Running Items" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/running.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /> <span class="nav-menu-text">Running Items</span></a></li>
      <li class="<?php if($params['page'] == 'category') { echo 'active'; }?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/017-list.svg" alt="" /> <span class="nav-menu-text">Categories</span></a></li>
      <li class="<?php if($params['page'] == 'order') { echo 'active'; }?>"><a uib-tooltip="Sales" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/018-application.svg" alt="" /> <span class="nav-menu-text">Sales</span></a></li>
      <li class="<?php if($params['page'] == 'reports') { echo 'active'; }?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/reports"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/018-application.svg" alt="" /> <span class="nav-menu-text">Reports</span></a></li>
      <li class="<?php if($params['page'] == 'expense') { echo 'active'; }?>"><a uib-tooltip="Expenses" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/expenses"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/018-application.svg" alt="" /> <span class="nav-menu-text">Expenses</span></a></li>
    </ul>
    <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
  </div>
</div>
<script>
function createCustomer () {
  window.open("<?php echo SITE_URL;?>pages/customers/create.php", "", "width=300,height=400"); 
}
</script>