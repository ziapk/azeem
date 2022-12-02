<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';


    $productObj = new Products();
    $demandObj = new Demands();
    $categoryObj = new Categories();
    $stores = new Store();

    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];


    $error = "";
    $message = "";





    echo mainHeader();  
    $categories = $categoryObj->getOwnerCategories($ownerId);

    $all = false;
    $products = [];
    $productsObj = new Products();
    $storeObj = new Store();
    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['owner_id'];
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
        <h4>Demand From</h4>
        <div class="row">
            <div class="col-sm-9 form-group">
                <label for="">Demand Title</label>
                <input id="demand_title" class="form-control" value="" />
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Demand Date</label>
                <input name="demand_date_piker" type="text" class="form-control datepicker-single" placeholder="YYYY-MM-DD">
                <input id="demand_date" type="hidden" class="form-control datepicker-hidden">
            </div>
            <?php if($userData['role'] == 'owner') {?>
                <div class="col-sm-3 form-group">
                    <label for="">Select Store</label>
                    <select id="shop_id" class="form-control">
                        <?php foreach ($ownerStores as $type) {?>
                            <option value="<?php echo $type['id'];?>"><?php echo $type['full_name'];?></option>
                        <?php }?>
                    </select>
                </div>
            <?php } else { ?>
                <input type="hidden" id="shop_id" value="<?php echo $userData['shopId'];?>" >
            <?php }?>
        </div>
        <div class="row" ng-repeat="li in formList track by $index">
            <div class="col-sm-3 form-group">
                <label for=""><input type="checkbox" ng-model="li.searchBy" style="vertical-align: top"> Search by code</label>
                <input type="text" class="form-control" ng-model="li.product" placeholder="Search Products" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, li.searchBy)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="col-sm-3 form-group">
                <label for="">Demand Qty</label>
                <input name="demand_qty" type="number" ng-model="li.qty" class="form-control" placeholder="Qty">
            </div>
            <label>&nbsp;</label><br />
            <button type="button" ng-if="formList.length > 1" ng-click="deleteItem($index)" class="btn btn-danger btn-sm">Delete</button>
            <button type="button" ng-if="$index == 0" ng-click="addItem()" class="btn btn-warning btn-sm">Add more</button>
        </div>
        <div class="row">
            <div class="col-sm-3 form-group">
                <input type="submit" name="create" value="Demand Create" class="btn btn-primary">
            </div>
        </div>
    </form>
</div>

<script>
app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, $location, $anchorScroll, $timeout) {
    $scope.list = [];

    $scope.form = {
        demand_title: '',
        demand_date: '',
        shop_id: '',
    }
    $scope.formList = [{
        qty: 0
    }];

    $scope.form.shop_id = $('#shop_id').val();

    $scope.addItem = () => {
        $scope.formList.push({
            qty: 0
        })
    }

    $scope.submitForm = ($event) => {

        $event.preventDefault();

        $scope.form.demand_title = $('#demand_title').val();
        $scope.form.demand_date = $('#demand_date').val();
        $scope.form.shop_id = $('#shop_id').val();
        $scope.form.items = [];
        $scope.form.create = true;

        $scope.formList.forEach(row => {
            $scope.form.items.push({ id: row.product.id, qty: row.qty })
        });

        $http.post("./createDemand.php", $httpParamSerializerJQLike($scope.form), { headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(function(response) {
            alert(response.data.message);
            if(response.data.status == 200) {
                $window.location.assign('./index.php');
            }
        });
    }

    $scope.deleteItem = (index) => {
        $scope.formList = $scope.formList.filter((r, i) => i !== index);
    }

    $scope.siteUrl = '<?php echo SITE_URL ?>';
    
    $scope.books = <?php echo json_encode($products);?>;

    $scope.items = $scope.books?.records || [];


    $scope.searchProduct = function (term, isCodeEnable) {
        let searchBy;
        if(isCodeEnable) {
            searchBy = 'id';
        }
        return $http.get("<?php echo SITE_URL?>api/getStores.php", {params: {term, searchBy}})
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
  <a style="min-width: 250px">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
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
