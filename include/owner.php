<?php
global $shopData;
global $userData;
global $shop;
$productCls = new Products();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
// $list = $productCls->getOwnerProducts($ownerId);
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
$$lockersList = [];
$customerObj = new Customers();
$customersList = $customerObj->getCustomers($shop['id']);
$lockersList = $customerObj->getCustomers($shop['id'], 2);


?>
<div ng-controller="headerController">
  <nav class="navbar navbar-fixed-top" style="z-index: 1031">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <div class="logo pull-left">
          <div class="visible-xs" style="float: left; left: 0; top: 0; padding: 6px 10px; text-align: center; line-height: 45px; width: 57px">
            <a ng-click="showSidebar = !showSidebar" href="javascript:void(0)"><span class="fa fa-list" style="color: #000"></span></a>
          </div>
          <a href="<?php echo SITE_URL; ?>" title="">
            <?php if (!empty($shop['image'])) { ?>
              <img style="width: 110px; max-height: 45px" style="vertical-align: middle; filter: grayscale(100%);" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shop['image']; ?>" />
            <?php } else { ?>
              <img width="60" src="<?php echo SITE_URL; ?>assets/img/logo.png" alt="" />
            <?php } ?>
          </a>
        </div>
        <div class="pull-left welcome-header-section">
          <span class="hidden-xs"><strong><?php echo $userData['full_name']; ?> (<?php echo $shop['full_name']; ?>)</strong></span>
          <a href="javascript:void(0)" uib-tooltip="Refresh Products" tooltip-placement="right" ng-click="loadProduct('', true)" class="btn btn-primary btn-xs" style="margin-left: 10px"><span class="fa fa-refresh"></span></a>
        </div>

        <ul class="list-inline navbar-right navbar-nav nav">
          <li class="hidden-xs dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              <img src="<?php echo SITE_URL . 'assets/img/stationary.png'; ?>" alt="" width="40" height="40" style="margin: -10px 0" />
            </a>
            <ul class="dropdown-menu" style="width: 500px">
              <div style="display: flex; flex-wrap: wrap">
                <?php foreach ($productCategories as $key => $value) { ?>
                  <li class="dropdown" style="min-width: 150px">
                    <a style="padding: 3px 6px; display: block" class="dropdown-item" href="#" data-toggle="dropdown"><span class="nav-menu-icon" style="margin-right: 6px"><img width="30" height="30" src="<?php echo SITE_URL . 'uploads/products/' . $value['image']; ?>" alt="" /></span><span class="nav-menu-text"><?php echo $value['full_name']; ?></span>
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
              </div>
            </ul>
          </li>
          <li class="hidden-xs dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Lockers" tooltip-placement="bottom" title="">
              <img class="fa" width="40" height="40" src="<?php echo SITE_URL; ?>assets/img/safe-icon-32457.jpg" style="margin: -10px 0" alt="" />
            </a>
            <form ng-submit="directLocker()" class="dropdown-menu" style="padding: 15px; width: 320px">
              <div class="form-group">
                <ui-select custom-dropdown ng-model="payment.customer" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a locker">
                  <ui-select-match placeholder="Enter a Locker...">{{$select.selected.full_name}}</ui-select-match>
                  <ui-select-choices repeat="address in lockersList track by $index" refresh="refreshCustomers($select.search)" refresh-delay="0">
                    <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                  </ui-select-choices>
                </ui-select>
              </div>
              <div class="form-group">
                <input placeholder="Description" ng-model="payment.summery" type="text" class="form-control input-lg">
              </div>
              <label class="text-success">Cash In - Deposit</label>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.cashIn[mode.id]" type="text" class="form-control input-lg">
                </div>
              </div>
              <label class="text-danger">Cash Out - Withdrawal</label>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.cashOut[mode.id]" type="text" class="form-control input-lg">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
            </form>
          </li>
          <li class="hidden-xs dropdown" style="padding: 0">
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
                <input placeholder="Description" ng-model="payment.summery" type="text" class="form-control input-lg">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.mode[mode.id]" type="text" class="form-control input-lg">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
              <label class="pull-right"><input type="checkbox" name="adjustment" ng-model="payment.adjustment"> Adjustment</label>
            </form>
          </li>
          <li class="hidden-xs dropdown" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown" uib-tooltip="Payments" tooltip-placement="bottom" title="">
              Pay
            </a>
            <form ng-submit="directPayment()" class="dropdown-menu" style="padding: 15px; width: 320px">
              <div class="form-group">
                <label>
                  <input type="checkbox" ng-model="directpaymenttocustomer">
                  To Customer
                </label>
                <ui-select ng-if="directpaymenttocustomer" custom-dropdown ng-model="payment.supplier" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a customer">
                  <ui-select-match placeholder="Enter a customer...">{{$select.selected.name}}</ui-select-match>
                  <ui-select-choices repeat="address in customersList track by $index" refresh="refreshSuppliers($select.search)" refresh-delay="0">
                    <div style="white-space: wrap;" ng-bind-html="address.name | highlight: $select.search"></div>
                  </ui-select-choices>
                </ui-select>
                <ui-select ng-if="!directpaymenttocustomer" custom-dropdown ng-model="payment.supplier" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a supplier">
                  <ui-select-match placeholder="Enter a supplier...">{{$select.selected.name}}</ui-select-match>
                  <ui-select-choices repeat="address in suppliersList track by $index" refresh="refreshSuppliers($select.search)" refresh-delay="0">
                    <div style="white-space: wrap;" ng-bind-html="address.name | highlight: $select.search"></div>
                  </ui-select-choices>
                </ui-select>
              </div>
              <div class="form-group">
                <input placeholder="Description" ng-model="payment.summery" type="text" class="form-control input-lg">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="payment.mode[mode.id]" type="text" class="form-control input-lg">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
              <div class="pull-right">
                <label style="margin-right: 10px;"><input type="checkbox" name="royalty" ng-model="payment.royalty"> Royalty</label>
                <label><input type="checkbox" name="adjustment" ng-model="payment.adjustment"> Adjustment</label>
              </div>
            </form>
          </li>
          <li class="hidden-xs dropdown" style="padding: 0">
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
                <input placeholder="Description" ng-model="exp.description" type="text" class="form-control input-lg">
              </div>
              <div class="row" style="margin: 0 -6px">
                <div class="form-group col-sm-4" ng-repeat="mode in modes" style="padding: 0 3px">
                  <input placeholder="{{mode.title}}" ng-model="exp.mode[mode.id]" type="text" class="form-control input-lg">
                </div>
              </div>
              <input type="submit" value="Submit" class="btn btn-primary">
              <div class="pull-right">
                <label><input type="checkbox" name="adjustment" ng-model="exp.adjustment"> Adjustment</label>
              </div>
            </form>
          </li>
          <li class="dropdown hidden-xs" style="padding: 0">
            <a href="#" class="nav-menu-item btn btn-primary" data-toggle="dropdown">
              Create
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/product/create.php"><span class="nav-menu-text">+ Product</span></a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL . 'pages/supply'; ?>">+ Supply</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/demand/create.php">+ Demand</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/recipt/" target="_blank">+ Recipt</a></li>
              <li><a class="nav-menu-item" href="<?php echo SITE_URL . 'pages/orders/adjustment.php'; ?>">+ Returns</a></li>
              <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>pages/ob/" target="_blank">+ Opening Balance</a></li>
            </ul>
          </li>
          <li class="hidden-xs"><a style="padding-left: 8px; padding-right: 8px" uib-tooltip="Reports" tooltip-placement="bottom" title="" href="<?php echo SITE_URL . 'pages/reports'; ?>"><small><small class="text-small"><img class="fa" width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /></small></small></a></li>
          <li class="hidden-xs"><a href="<?php echo SITE_URL; ?>pages/product/running.php"><img width="22" uib-tooltip="Running Products" tooltip-placement="bottom" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/lightning-bolt.svg" alt="" /></a></li>
          <?php include_once dirname(__FILE__) . '/cart.php'; ?>
          <li class="profile-menu">
            <a title="" href="javascript:void(0)" data-toggle="dropdown" tooltip-placement="bottom" uib-tooltip="Settings"><span class="fa fa-cog"></span> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a title="" href="<?php echo SITE_URL; ?>pages/profile">Profile</a></li>
              <li><a title="" ng-click="makeClosing(currentShop.id, currentShop)" href="javascript:void(0)">Closing</a></li>
              <li><a href="<?php echo SITE_URL; ?>pages/product/reset.php">Reset Products</a></li>
              <li><a title="" href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
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
    <div class="sidebar" ng-class="{'showSidebar': showSidebar}">
      <ul class="nav">
        <li class="<?php if ($params['page'] == 'product-create') { echo 'active'; } ?>"><a uib-tooltip="Add New Product" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">New Product</span></a></li>
        <li class="<?php if ($params['page'] == 'recipt') { echo 'active'; } ?>"><a uib-tooltip="Cash Entry" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Recipt Generator</span></a></li>
        <li class="<?php if ($params['page'] == 'recipt-credit') { echo 'active'; } ?>"><a uib-tooltip="Credit Entry" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/recipt?credit=1" target="_blank"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/receipt.svg" alt="" /> <span class="nav-menu-text">Credit Entry</span></a></li>
        <li class="<?php if ($params['page'] == 'product') { echo 'active'; } ?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/products.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Products</span></a></li>
        <li class="<?php if ($params['page'] == 'order') { echo 'active'; } ?>"><a uib-tooltip="Sales" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Sales</span></a></li>
        <li class="<?php if ($params['page'] == 'customers') { echo 'active'; } ?>"><a uib-tooltip="Customers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/customer.svg" alt="" /><span class="nav-menu-text">Customers</span></a></li>
        <li class="<?php if ($params['page'] == 'suppliers') { echo 'active'; } ?>"><a uib-tooltip="Suppliers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/suppliers/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/supplier.svg" alt="" /><span class="nav-menu-text">Suppliers</span></a></li>
        <li class="<?php if ($params['page'] == 'lockers') { echo 'active'; } ?>"><a uib-tooltip="Lockers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/customers/lockers.php"><img class="fa" width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/safe-icon-32457.jpg" alt="" /><span class="nav-menu-text">Lockers</span></a></li>
        <li class="<?php if ($params['page'] == 'author') { echo 'active'; } ?>"><a uib-tooltip="Authors" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/authors"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/supplier.svg" alt="" /> <span class="nav-menu-text">Authors</span></a></li>
        <li class="<?php if ($params['page'] == 'sale_returns') { echo 'active'; } ?>"><a uib-tooltip="Sale Return" tooltip-placement="right" title="" href="<?php echo SITE_URL . 'pages/orders/returns.php'; ?>"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /><span class="nav-menu-text">Returns</span></a></li>
        <li class="<?php if ($params['page'] == 'ledger-transactions') { echo 'active'; } ?>"><a uib-tooltip="Today's Transactions" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts/ledger_transactions.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Today Entries</span></a></li>
        <li class="<?php if ($params['page'] == 'supplies') { echo 'active'; } ?>"><a uib-tooltip="Supplies" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/supply/list.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/sales.svg" alt="" /> <span class="nav-menu-text">Purchase Orders</span></a></li>
        <li class="<?php if ($params['page'] == 'expense') { echo 'active'; } ?>"><a uib-tooltip="Expenses" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/expenses"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/expense.svg" alt="" /> <span class="nav-menu-text">Expenses</span></a></li>
        <li class="<?php if ($params['page'] == 'program') { echo 'active'; } ?>"><a uib-tooltip="Programs" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/program"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/016-books-stack-of-three.svg" alt="" /> <span class="nav-menu-text">Programs</span></a></li>
        <li class="<?php if ($params['page'] == 'employees') { echo 'active'; } ?>"><a uib-tooltip="Employees" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/employees"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Employees</span></a></li>

        <li class="<?php if ($params['page'] == 'product') { echo 'active'; } ?>"><a uib-tooltip="Products" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/products.php?status=0"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">In-Active Products</span></a></li>
        <li class="<?php if ($params['page'] == 'product') { echo 'active'; } ?>"><a uib-tooltip="Products (Fix Assets)" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/products.php?product_type=4"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Shop Assets</span></a></li>
        <li class="<?php if ($params['page'] == 'racks' && (empty($_GET["status"]) && $_GET["status"] != '0')) { echo 'active'; } ?>"><a uib-tooltip="Product Racks" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/product/racks.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Product Racks</span></a></li>
        <li class="<?php if ($params['page'] == 'loans') { echo 'active'; } ?>"><a uib-tooltip="Loans" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/employees/loans.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/product.svg" alt="" /> <span class="nav-menu-text">Loans</span></a></li>
        <li class="<?php if ($params['page'] == 'coa') { echo 'active'; } ?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/chart-of-accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Accounts</span></a></li>
        <li class="<?php if ($params['page'] == 'mode') { echo 'active'; } ?>"><a uib-tooltip="Chart of Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/modes"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Payment Modes</span></a></li>
        <!-- <li class="<?php if ($params['page'] == 'demand') { echo 'active'; } ?>"><a uib-tooltip="Demands" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/demand/create.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Invoicing</span></a></li> -->
        <!-- <li class="<?php if ($params['page'] == 'shop_accounts') { echo 'active'; } ?>"><a uib-tooltip="Shop Accounts" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/shop_accounts"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/accounting.png" alt="" /> <span class="nav-menu-text">Shop Accounts</span></a></li> -->
        <li class="<?php if ($params['page'] == 'demand') { echo 'active'; } ?>"><a uib-tooltip="Demands" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/demand"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Demands</span></a></li>
        <!-- <li class="<?php if ($params['page'] == 'worker') { echo 'active'; } ?>"><a uib-tooltip="Workers" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/worker"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/justice-hammer.svg" alt="" /> <span class="nav-menu-text">Workers</span></a></li> -->
        <li class="<?php if ($params['page'] == 'publisher') { echo 'active'; } ?>"><a uib-tooltip="Publisher" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/publisher"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/publisher.svg" alt="" /><span class="nav-menu-text">Publisher</span></a></li>
        <li class="<?php if ($params['page'] == 'barcode') { echo 'active'; } ?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/barcode"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/qrcode.svg" alt="" /> <span class="nav-menu-text">Barcode</span></a></li>
        <li class="<?php if ($params['page'] == 'category') { echo 'active'; } ?>"><a uib-tooltip="Categories" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/category/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/category.svg" alt="" /><span class="nav-menu-text">Categories</span></a></li>
        <li class="<?php if ($params['page'] == 'return') { echo 'active'; } ?>"><a uib-tooltip="Return to Lahore" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/orders/faultyOrders.php"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /> <span class="nav-menu-text">Return to Lahore</span></a></li>
        <li class="<?php if ($params['page'] == 'reports') { echo 'active'; } ?>"><a uib-tooltip="Reports" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/reports/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /><span class="nav-menu-text">Reports</span></a></li>
        <li class="<?php if ($params['page'] == 'stock') { echo 'active'; } ?>"><a uib-tooltip="Stock History" tooltip-placement="right" title="" href="<?php echo SITE_URL; ?>pages/stock/"><img class="fa" width="20" height="20" src="<?php echo SITE_URL; ?>assets/img/svg/reports.svg" alt="" /><span class="nav-menu-text">Stock History</span></a></li>
      </ul>
      <a href="javascript:void(0)" ng-click="toggleSidebar()" class="toggle-sidebar"><img width="16" height="16" src="<?php echo SITE_URL; ?>assets/img/svg/left-arrow.svg" alt="" /></a>
    </div>
  <?php } ?>
</div>