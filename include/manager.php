<?php
global $shopData;
global $userData;
global $shop;
$storeDD = new Store();
$shop = $storeDD->getStore($shop['id']);
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$categoryObj = new Categories();
$categories = $categoryObj->getCategories('exp', $ownerId);
// $list = $productCls->getOwnerProducts($ownerId);

$categoryList = $categoryObj->getCategories('pro', $ownerId);
$ids = [];
$productCategories = [];
foreach ($categoryList as $v) {
  $productCategories[] = $v;
  $ids[] = $v['id'];
}
$categoryProducts = $productCls->getCategoryProducts($shop['owner_id'], $ids, $shop['id']);

$suppliersList = [];
$suppliersObj = new Suppliers();
$suppliersList = $suppliersObj->getSuppliers(['shopId' => $shop['id'], 'type' => 1]);
$authorsList = $suppliersObj->getSuppliers(['shopId' => $shop['id'], 'type' => 2]);

$customersList = [];
$customerObj = new Customers();
$customersList = $customerObj->getCustomers($shop['id']);

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
          <?php if ($shop['sale_date_show']) { ?><div class="pull-left welcome-header-section sale-date"><button class="btn btn-danger" ng-click="applyClosing()"><span>Sale Close</span></button></div><?php } ?>
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              <img src="<?php echo SITE_URL . 'assets/img/stationary.png'; ?>" alt="" width="40" height="40" style="margin: -10px 0" />
            </a>
            <ul class="dropdown-menu">
              <?php foreach ($productCategories as $key => $value) { ?>
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
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Calculator" tooltip-placement="bottom" title="">
              Calc
            </a>
            <form ng-submit="" class="dropdown-menu" style="padding: 20px; width: 300px">
              <div class="row">
                <div class="col-xs-6">
                  <div class="form-group">
                    <input type="number" placeholder="Qty" ng-model="frm.qty" type="text" class="form-control">
                  </div>
                </div>
                <div class="col-xs-6">
                  <div class="form-group">
                    <input type="number" placeholder="Price" ng-model="frm.price" type="text" class="form-control">
                  </div>
                </div>
              </div>
              Total: {{(frm.price * frm.qty || 0) | number:2}}
            </form>
          </li>
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Receivings" tooltip-placement="bottom" title="">
              Re.
            </a>
            <form ng-submit="directReceiving()" class="dropdown-menu" style="padding: 15px; width: 320px">
              <div class="form-group">
                <ui-select custom-dropdown ng-model="payment.customer" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a customer">
                  <ui-select-match placeholder="Enter a customer...">{{$select.selected.full_name}}</ui-select-match>
                  <ui-select-choices repeat="address in customersList track by $index" refresh="refreshCustomers($select.search)" refresh-delay="0">
                    <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                  </ui-select-choices>
                </ui-select>
              </div>
              <div class="form-group">
                <input placeholder="Description" ng-model="payment.summery" type="text" class="form-control">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.mode[mode.id]" type="text" class="form-control">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
              <label class="pull-right"><input type="checkbox" name="adjustment" ng-model="payment.adjustment"> Adjustment</label>
            </form>
          </li>
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Payments" tooltip-placement="bottom" title="">
              Pay
            </a>
            <form ng-submit="directPayment()" class="dropdown-menu" style="padding: 15px; width: 320px">
              <div class="row">
                <div class="form-group" ng-class="{'col-sm-6': payment.royalty, 'col-sm-12': !payment.royalty}">
                  <ui-select custom-dropdown ng-model="payment.supplier" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a supplier">
                    <ui-select-match placeholder="Enter a supplier...">{{$select.selected.name}}</ui-select-match>
                    <ui-select-choices repeat="address in suppliersList track by $index" refresh="refreshSuppliers($select.search)" refresh-delay="0">
                      <div style="white-space: wrap;" ng-bind-html="address.name | highlight: $select.search"></div>
                    </ui-select-choices>
                  </ui-select>
                </div>
                <div class="form-group" ng-if="payment.royalty" ng-class="{'col-sm-6': payment.royalty, 'col-sm-12': !payment.royalty}">
                  <ui-select custom-dropdown ng-model="payment.author" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose an author">
                    <ui-select-match placeholder="Enter a author...">{{$select.selected.name}}</ui-select-match>
                    <ui-select-choices repeat="address in authorsList track by $index" refresh="refreshAuthors($select.search)" refresh-delay="0">
                      <div style="white-space: wrap;" ng-bind-html="address.name | highlight: $select.search"></div>
                    </ui-select-choices>
                  </ui-select>
                </div>
              </div>
              <div class="form-group">
                <input placeholder="Description" ng-model="payment.summery" type="text" class="form-control">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.mode[mode.id]" type="text" class="form-control">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
              <div class="pull-right">
                <label style="margin-right: 10px;"><input type="checkbox" name="royalty" ng-model="payment.royalty"> Royalty</label>
                <label><input type="checkbox" name="adjustment" ng-model="payment.adjustment"> Adjustment</label>
              </div>
            </form>
          </li>
          <!-- <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Royalty" tooltip-placement="bottom" title="">
              Roy.
            </a>
            <form ng-submit="directPayment(2)" class="dropdown-menu" style="padding: 15px; width: 320px">
              <div class="form-group">
                <div class="form-group">
                  <ui-select custom-dropdown ng-model="payment.supplier" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose an author">
                    <ui-select-match placeholder="Enter a supplier...">{{$select.selected.name}}</ui-select-match>
                    <ui-select-choices repeat="address in authorsList track by $index" refresh="refreshAuthors($select.search)" refresh-delay="0">
                      <div style="white-space: wrap;" ng-bind-html="address.name | highlight: $select.search"></div>
                    </ui-select-choices>
                  </ui-select>
                </div>
              </div>
              <div class="form-group">
                <input placeholder="Description" ng-model="payment.summery" type="text" class="form-control">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.mode[mode.id]" type="text" class="form-control">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
              <label class="pull-right"><input type="checkbox" name="adjustment" ng-model="payment.adjustment"> Adjustment</label>
            </form>
          </li> -->
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Expenses" tooltip-placement="bottom" title="">
              Exp
            </a>
            <form ng-submit="createExpense()" class="dropdown-menu" style="padding: 15px; width: 320px">
              <div class="form-group">
                <ui-select custom-dropdown ng-model="exp.expense" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose an expense">
                  <ui-select-match placeholder="Enter a expense...">{{$select.selected.full_name}}</ui-select-match>
                  <ui-select-choices repeat="address in expensesList track by $index" refresh="refreshExpenses($select.search)" refresh-delay="0">
                    <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                  </ui-select-choices>
                </ui-select>
              </div>
              <div class="form-group">
                <input placeholder="Description" ng-model="exp.description" type="text" class="form-control">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="exp.mode[mode.id]" type="text" class="form-control">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
            </form>
          </li>
          <li class="dropdown" style="padding: 0; margin-right: -1px">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
              Returns
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?php echo SITE_URL . 'pages/supply/adjustment.php'; ?>">Supply Return</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL . 'pages/orders/adjustment.php'; ?>">Sale Return</a></li>
            </ul>
          </li>
          <li class="dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              Create
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/product/create.php"><span class="nav-menu-text">+ Product</span></a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL . 'pages/supply'; ?>">+ Supply</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/demand/create.php">+ Demand</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/recipt/" target="_blank">+ Recipt</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/ob/" target="_blank">+ Opening Balance</a></li>
            </ul>
          </li>

          <li><a style="padding-left: 8px; padding-right: 8px" uib-tooltip="Reports" tooltip-placement="bottom" title="" href="<?php echo SITE_URL . 'pages/reports'; ?>"><small><small class="text-small"><img class="fa" width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /></small></small></a></li>
          <li><a href="<?php echo SITE_URL; ?>pages/product/running.php"><img width="22" uib-tooltip="Running Products" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /></a></li>
          <?php include_once dirname(__FILE__) . '/cart.php'; ?>
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
      <li class="<?php if ($params['page'] == 'product-create') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Add New Product" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">New Product</span></a></li>
      <li class="<?php if ($params['page'] == 'recipt') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Cash Entry" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Recipt Generator</span></a></li>
      <li class="<?php if ($params['page'] == 'recipt-credit') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Credit Entry" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt?credit=1" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Credit Entry</span></a></li>
      <li class="<?php if ($params['page'] == 'product') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
      <li class="<?php if ($params['page'] == 'least') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Least Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/least.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Min Products</span></a></li>
      <li class="<?php if ($params['page'] == 'product') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/?status=0"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">In-Active Products</span></a></li>
      <li class="<?php if ($params['page'] == 'product-create') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Today's Transactions" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts/ledger_transactions.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Today Entries</span></a></li>
      <li class="<?php if ($params['page'] == 'customer') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/customer.svg" alt="" /> <span class="nav-menu-text">Customers</span></a></li>
      <li><a uib-tooltip="Sale Return" tooltip-placement="right" title="" href="<?php echo SITE_URL . 'pages/orders/adjustment.php'; ?>"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /><span class="nav-menu-text">Sale Return</span></a></li>
      <li class="<?php if ($params['page'] == 'coa') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Accounts</span></a></li>
      <li class="<?php if ($params['page'] == 'mode') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/modes"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Payment Modes</span></a></li>
      <li class="<?php if ($params['page'] == 'assign') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Assign Publishers to Products at Once" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/correction.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Assign Publishers</span></a></li>
      <!-- <li class="<?php if ($params['page'] == 'shop_accounts') {
                        echo 'active';
                      } ?>"><a uib-tooltip="Shop Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/shop_accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Shop Accounts</span></a></li> -->
      <li class="<?php if ($params['page'] == 'demand') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Demands" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/demand"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Demands</span></a></li>
      <li class="<?php if ($params['page'] == 'running') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Running Items" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/running.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /> <span class="nav-menu-text">Running Items</span></a></li>
      <li class="<?php if ($params['page'] == 'dup') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Duplicate Items" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/duplicates.php"><span class="fa fa-copy text-danger"></span> <span class="nav-menu-text">Duplidate Items</span></a></li>
      <li class="<?php if ($params['page'] == 'order') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Sales" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Sales</span></a></li>
      <li class="<?php if ($params['page'] == 'supplies') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Supplies" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/supply/list.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Purchase Orders</span></a></li>
      <li class="<?php if ($params['page'] == 'supplier') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Suppliers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/suppliers"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/supplier.svg" alt="" /> <span class="nav-menu-text">Suppliers</span></a></li>
      <li class="<?php if ($params['page'] == 'author') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Authors" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/authors"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/supplier.svg" alt="" /> <span class="nav-menu-text">Authors</span></a></li>
      <li><a uib-tooltip="Purchase Return" tooltip-placement="right" title="" href="<?php echo SITE_URL . 'pages/supply/adjustment.php'; ?>"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /><span class="nav-menu-text">Supply Return</span></a></li>
      <li class="<?php if ($params['page'] == 'reports') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/reports"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /> <span class="nav-menu-text">Reports</span></a></li>
      <li class="<?php if ($params['page'] == 'program') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Programs" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/program"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/016-books-stack-of-three.svg" alt="" /> <span class="nav-menu-text">Programs</span></a></li>
      <!-- <li class="<?php if ($params['page'] == 'return') {
                        echo 'active';
                      } ?>"><a uib-tooltip="Return to Lahore" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders/faultyOrders.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/018-application.svg" alt="" /> <span class="nav-menu-text">Return to Lahore</span></a></li> -->
      <li class="<?php if ($params['page'] == 'publisher') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Publisher" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/publisher"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/publisher.svg" alt="" /> <span class="nav-menu-text">Publisher</span></a></li>
      <li class="<?php if ($params['page'] == 'category') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /> <span class="nav-menu-text">Categories</span></a></li>
      <li class="<?php if ($params['page'] == 'expense') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Expenses" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/expenses"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/expense.svg" alt="" /> <span class="nav-menu-text">Expenses</span></a></li>
      <li class="<?php if ($params['page'] == 'barcode') {
                    echo 'active';
                  } ?>"><a uib-tooltip="Barcode for Print" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/barcode"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /> <span class="nav-menu-text">Barcode</span></a></li>
    </ul>
    <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
  </div>
</div>
<script>
  function createCustomer() {
    window.open("<?php echo SITE_URL; ?>pages/customers/create.php", "", "width=300,height=400");
  }
</script>