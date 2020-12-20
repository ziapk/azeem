<?php
  global $shopData;
  global $userData;
  $productCls = new Products();
  $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
  $list = $productCls->getOwnerProducts($ownerId);
?>
<div class="container" ng-controller="headerController">
<?php if($shopData['id']) { ?>
  <h1><img height="60" src="<?php echo SITE_URL; ?>assets/clients/<?php echo $shopData['shopId'];?>.png" alt="" />  <small class="pull-right"><small>Point of Sale <sub>v0.1</sub></small></small></h1>
<?php } else { ?>
  <h1><img width="60" src="<?php echo SITE_URL; ?>assets/img/logo.png" alt="" /> Point of Sale <small><sub>v0.1</sub></small></h1>
<?php } ?>
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo $shopData['product_title'];?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="<?php echo SITE_URL; ?>pages/orders">Orders</a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/customers">Customers</a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/suppliers">Suppliers</a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/category">Categories</a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/expenses">Expenses</a></li>
        <li><a href="<?php echo SITE_URL; ?>pages/product">Catalog</a></li>
        <li><a href="javascript:void(0)" onclick="createCustomer()">New Customer</a></li>
        
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="<?php echo SITE_URL; ?>pages/configration">Configration</a></li>
        <?php include_once dirname(__FILE__).'/cart.php';?>
        <li><a href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
</div>
<script>
function createCustomer () {
    window.open("http://localhost/tea/pages/customers/create.php", "", "width=300,height=400"); 
}
</script>