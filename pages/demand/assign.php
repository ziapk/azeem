<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $id = $_GET['id'];
    $productObj = new Products();
    $demandObj = new Demands();
    $categoryObj = new Categories();
    $stores = new Store();


    $demandDetail = $demandObj->getDemandDetail($id, $shop['owner_id']);


    $ownerId = $shop['owner_id'];
    $userId = $userData['id'];


    $error = "";
    $message = "";





    echo mainHeader();  
    $categories = $categoryObj->getOwnerCategories($ownerId);

    $all = false;
    $products = [];
    $productsObj = new Products();
    $storeObj = new Store();
    
    $ownerStores = $storeObj->getOwnerStores($ownerId);

    if(!empty($_GET['all']) && $_GET['all'] == '1') {
        $shopId = $_GET['shopId'];
        $products = $productsObj->getOwnerProductsPagination($ownerId, ['page' => 1, 'perPage' => 100000], $shopId);
    }

?>
<div class="container" ng-controller="categoryController">
    <form method="POST" action="" autocomplete="off" ng-submit="submitForm($event)">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <h4>Demand From <?php echo $demandDetail['demand_date'];?></h4>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="">Demand Title</label>
                <input ng-model="form.title" class="form-control" value="" />
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Assign Date</label>
                <input name="demand_date_piker" type="text" class="form-control datepicker-single" placeholder="YYYY-MM-DD">
                <input id="demand_date" type="hidden" class="form-control datepicker-hidden">
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Assign Status</label>
                <select name="flag" class="form-control" ng-model="form.flag">
                    <?php foreach ($demandStatusArr as $type) {?>
                        <option value="<?php echo $type['id'];?>" <?php if($demand['flag'] == $type['id']) {echo 'selected';}; ?>><?php echo $type['full_name'];?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="row" ng-repeat="li in form.items track by $index">
            <div class="col-sm-3 form-group">
                <label for="">Product</label>
                <span class="form-control">{{li.full_name}}</span>
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Requested Qty</label>
                <span class="form-control">{{li.product_qty}}</span>
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Assign Qty</label>
                <input type="number" ng-model="li.product_assign_qty" class="form-control" placeholder="Qty">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 form-group">
                <input type="submit" name="create" value="Approve Demand" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<script>
app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout) {
    $scope.list = [];

    $scope.form = <?php echo json_encode($demandDetail);?>;

    $scope.form.items.map(row => {
        row.product_assign_qty = parseFloat(row.product_qty);
    });
    
    $scope.submitForm = ($event) => {
        $event.preventDefault();
        $scope.form.assign_date = $('#demand_date').val();
        console.log($scope.form);
        
        $http.post("./assignDemand.php", $httpParamSerializerJQLike($scope.form), { headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            alert(response.data.message);
        });
    }

    $scope.deleteItem = (index) => {
        $scope.formList = $scope.formList.filter((r, i) => i !== index);
    }

    $scope.siteUrl = '<?php echo SITE_URL ?>';
    
    $scope.books = <?php echo json_encode($products);?>;

    $scope.items = $scope.books?.records || [];


    $scope.searchProduct = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getStores.php", {params: {term}})
        .then(function(response) {
            
            $scope.list = response.data;
            $scope.priceList = response.data;
            return response.data
        });
    }

    $scope.deleteCategory = function (id) {
        $scope.items = $scope.items.filter(r => r.id !== id);
    }
    
    $scope.printTags = function (form) {
        $http.post('print.php', $httpParamSerializerJQLike($scope.items), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
            // $scope.getCategories(1);
        });
    };
});

app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, form) {
    $scope.form = {
        full_name: "",
        cat_type: "",
        ...form
    }
    $scope.ok = function () {
        $uibModalInstance.close($scope.form);
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});
</script>

<script type="text/ng-template" id="row.html">
  <a>
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
      <span class="pull-right">{{match.model.price}}</span>
  </a>
</script>

<?php
echo mainFooter();
?>
<script type="text/javascript">
$('.datepicker-hidden').val(moment().format('YYYY-MM-DD'));
$('.datepicker-single').daterangepicker({
   minDate: moment(),
   singleDatePicker: true,
}, function(date) {
    $('.datepicker-hidden').val(moment(date).format('YYYY-MM-DD'));
});
</script>
