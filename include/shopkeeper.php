<?php
global $shop;
global $userData;
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$list = $productCls->getOwnerProducts($ownerId);
$categoryObj = new Categories();
$categories = $categoryObj->getCategories('pro', $ownerId);
$ids = [];
foreach ($categories as $v) {
  $ids[] = $v['id'];
}
$categoryProducts = $productCls->getCategoryProducts($shop['owner_id'], $ids, $shop['id']);
?>
<div ng-controller="headerController">
  <nav class="navbar navbar-fixed-top" style="z-index: 1031">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <div class="logo pull-left">
          <a href="<?php echo SITE_URL; ?>" title="">
            <?php if (!empty($shop['image'])) { ?>
              <img style="width: 120px; max-height: 45px" style="vertical-align: middle; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shop['image']; ?>" />
            <?php } else { ?>
              <img width="60" src="<?php echo SITE_URL; ?>assets/img/logo.png" alt="" />
            <?php } ?>
          </a>
        </div>
        <div class="pull-left welcome-header-section">
          <span>Welcome to <strong><?php echo $shop['full_name']; ?></strong></span>
          <a href="javascript:void(0)" uib-tooltip="Refresh Products" tooltip-placement="right" ng-click="loadProduct('', true)" class="btn btn-primary btn-xs" style="margin-left: 10px"><span class="fa fa-refresh"></span></a>
        </div>

        <ul class="list-inline navbar-right navbar-nav nav">
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              <img src="<?php echo SITE_URL . 'assets/img/stationary.png'; ?>" alt="" width="40" height="40" style="margin: -10px 0" />
            </a>
            <ul class="dropdown-menu">
              <?php foreach ($categories as $key => $value) { ?>
                <li class="dropdown">
                  <a style="padding: 3px 6px" class="dropdown-item" href="#" data-toggle="dropdown"><span class="nav-menu-icon" style="margin-right: 6px"><img width="30" height="30" src="<?php echo SITE_URL . 'uploads/products/' . $value['image']; ?>" alt="" /></span><span class="nav-menu-text"><?php echo $value['full_name']; ?></span>
                    <div class="fa fa-caret-right"></div>
                  </a>
                  <?php if (!empty($categoryProducts[$value['id']])) { ?>
                    <ul class="dropdown-menu dropdown-submenu" style="min-width: 250px; max-height: 300px; overflow: auto">
                      <?php foreach ($categoryProducts[$value['id']] as $c) { ?>
                        <li>
                          <a ng-click='addToCart(<?php echo safe_json_encode($c); ?>)' style="padding: 3px 6px" class="dropdown-item" href="#"><code class="nav-menu-text"><?php echo $c['price']; ?></code><span class="nav-menu-text" style="white-space: normal"><?php echo $c['full_name']; ?></span></a>
                        </li>
                      <?php } ?>
                    </ul>
                  <?php } ?>
                </li>
              <?php } ?>
            </ul>
          </li>
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              Calculator
            </a>
            <form ng-submit="" class="dropdown-menu" style="padding: 20px; width: 300px">
              <div class="row">
                <div class="col-xs-6">
                  <div class="form-group">
                    <input type="number" placeholder="Qty" ng-model="frm.qty" type="text" class="form-control input-lg">
                  </div>
                </div>
                <div class="col-xs-6">
                  <div class="form-group">
                    <input type="number" placeholder="Price" ng-model="frm.price" type="text" class="form-control input-lg">
                  </div>
                </div>
              </div>
              Total: {{(frm.price * frm.qty || 0) | number:2}}
            </form>
          </li>
          <li><a href="<?php echo SITE_URL; ?>pages/product/running.php"><img width="22" uib-tooltip="Running Products" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /></a></li>
          <?php
          include_once dirname(__FILE__) . '/cart.php';
          ?>
          <li>
            <a title="" href="javascript:void(0)" data-toggle="dropdown" tooltip-placement="bottom" uib-tooltip="Settings"><span class="fa fa-cog"></span> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a title="" href="<?php echo SITE_URL; ?>pages/profile">Profile</a></li>
              <li class="divider"></li>
              <li><a title="" href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
              <li class="divider"></li>
              <li class="fontsizer">
                <table width="100%" style="margin: auto;">
                  <tr>
                    <td>
                      font-size: <strong>{{fontsize}}</strong>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="range" ng-model="fontsize" min="13" max="40" ng-change="updateFont(fontsize)" />
                    </td>
                  </tr>
                </table>
              </li>
            </ul>
          </li>
        </ul>
      </div><!-- /.container-fluid -->
  </nav>
  <?php
  if (empty($params['hideSidebar'])) { ?>
    <div class="sidebar">
      <ul class="nav">
        <li class="<?php if ($params['page'] == 'product-create') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Add New Product" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">New Product</span></a></li>
        <li class="<?php if ($params['page'] == 'recipt') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Recipt Generator" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Recipt Generator</span></a></li>
        <li class="<?php if ($params['page'] == 'product') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
        <li class="<?php if ($params['page'] == 'racks' && (empty($_GET["status"]) && $_GET["status"] != '0')) {
                      echo 'active';
                    } ?>"><a uib-tooltip="Product Racks" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/racks.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Product Racks</span></a></li>
        <!-- <li class="<?php if ($params['page'] == 'program') {
                          echo 'active';
                        } ?>"><a uib-tooltip="Programs" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/program"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/016-books-stack-of-three.svg" alt="" /> <span class="nav-menu-text">Programs</span></a></li> -->
        <li class="<?php if ($params['page'] == 'running') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Running Items" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/running.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /> <span class="nav-menu-text">Running Items</span></a></li>
        <?php
        if ($userData['role'] == 'owner' || $userData['role'] == 'manager') { ?>
          <li class="<?php if ($params['page'] == 'order') {
                        echo 'active';
                      } ?>"><a uib-tooltip="Sales" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Sales</span></a></li>
        <?php } ?>
        <!-- <li class="<?php if ($params['page'] == 'reports') {
                          echo 'active';
                        } ?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/reports"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /> <span class="nav-menu-text">Reports</span></a></li> -->
        <li class="<?php if ($params['page'] == 'customer') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/customer.svg" alt="" /> <span class="nav-menu-text">Customers</span></a></li>
        <li class="<?php if ($params['page'] == 'category') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /> <span class="nav-menu-text">Categories</span></a></li>
        <li class="<?php if ($params['page'] == 'expense') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Expenses" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/expenses"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/expense.svg" alt="" /> <span class="nav-menu-text">Expenses</span></a></li>
        <li class="<?php if ($params['page'] == 'demand') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Demands" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/demand" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Demands</span></a></li>
        <li class="<?php if ($params['page'] == 'barcode') {
                      echo 'active';
                    } ?>"><a uib-tooltip="Barcode for Print" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/barcode" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /> <span class="nav-menu-text">Barcode</span></a></li>
      </ul>
      <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
    </div>
  <?php } ?>
</div>
<script>
  function createCustomer() {
    window.open("<?php echo SITE_URL; ?>pages/customers/create.php", "", "width=300,height=400");
  }
</script>