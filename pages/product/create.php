<?php

    include_once dirname(__FILE__).'/../../include/settings.php';


    $productObj = new Products();
    $categoryObj = new Categories();

    $ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
    $userId = $userData['id'];

    
    
    $error = "";
    $message = "";
    if(!empty($_POST) && isset($_POST['create'] )) {
        
        $error = "";
        
        if(empty($_POST['full_name']) || empty($_POST['price'])) {
            
            $error = "Please fill all fields";
        }
        else {
            $photo = $_FILES['image'];
            $uploaded = false;
            $image = "";
            if(isset($photo) && count($photo) ) {
                if($photo['error'] == 0) {
                    $img = explode('.', $photo['name']);
                    $photo['dst_path'] 	= dirname(__FILE__).'/../../uploads/products/';
                    
                    $image = time().'.'.$img[1];

                    if (!file_exists($photo['dst_path'])) {

					    mkdir($photo['dst_path'], 0777, true);

                    }
                    
                    $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'].$image);
					if($moved) {	
						$uploaded = true;

					}
            
                }
            }
            
            $data = [                
                'user_id' => $userId,
                'owner_id' => $ownerId,
                'full_name' => $_POST['full_name'],
                'code' => $_POST['code'],
                'group' => $_POST['group'],
                'description' => $_POST['description'],
                'in_hand' => $_POST['in_hand'],
                'min_qty' => $_POST['min_qty'],
                'pack_size' => $_POST['pack_size'],
                'pack_price' => $_POST['pack_price'],
                'image' => $uploaded ? $image : "",
                'price' => !empty($_POST['price']) ? $_POST['price'] : "",
                'pprice' => !empty($_POST['pprice']) ? $_POST['pprice'] : null,
            ];
            
            $create = $productObj->createProduct($data);

            if($create) {
                $message = "Successfully created!";
            } else {
                $message = "Check form carefully!";
            }
        }
    }

    echo mainHeader();  
    $categories = $categoryObj->getOwnerCategories($ownerId);
    

?>
<div class="container" ng-controller="orderController">
    <h4>Create Product</h4>
    
    <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
        <?php if(!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if(!empty($error)) { ?><div  class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="row">
            <div class="col-sm-4 form-group">
                <label>Title *</label>
                <input type="text" name="full_name" placeholder="Example: Tea, Coffee" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Unit Price *</label>
                <input name="price" type="text" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Image</label>
                <input name="image" type="file" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 form-group">
                <label>Stock In Hand</label>
                <input type="text" name="in_hand" placeholder="i.e 100, 150" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Purchase Price</label>
                <input type="text" name="pprice" placeholder="i.e 100, 150" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Code</label>
                <input type="text" name="code" placeholder="i.e BTL, SRF" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Group</label>
                <input type="text" class="form-control" ng-model="group" name="group" placeholder="i.e Oil, Shampoo, Soap" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.group for address in searchProduct($viewValue)" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="col-sm-4 form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="i.e Any thing about product" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 form-group">
                <label>Pack Size</label>
                <input type="text" name="pack_size" placeholder="i.e 6, 12" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Pack Price</label>
                <input type="text" name="pack_price" placeholder="i.e 150, 300" class="form-control">
            </div>
            <div class="col-sm-4 form-group">
                <label>Min Qty</label>
                <input type="text" name="min_qty" placeholder="i.e 6, 12" class="form-control">
            </div>
            <div class="col-sm-12 form-group">
                <input type="submit" name="create" value="Create" class="btn btn-success">
            </div>
        </div>
    </form>
</div>

<script type="text/ng-template" id="row.html">
  <a>
      <span ng-bind-html="match.model.group | uibTypeaheadHighlight:query"></span>
  </a>
</script>

<script type="text/javascript">
app.controller('orderController', function($scope, $http, $httpParamSerializerJQLike, $filter) {
    $scope.list = [];
    $scope.priceList = [];
    $scope.items = [];
    $scope.customerData = {};
    $scope.subTotal = 0;
    $scope.grandTotal = 0;
    $scope.discount = 0;

    $scope.selectProduct = function (p) {
        let exists = false;
        $scope.items.map((pro) => {
            if(pro.id == p.id) {
                exists = true;
                pro.qty++;
            }
        })
        $scope.product = ""
        if(!exists) {
            $scope.items.push({...p, qty: 1});
        }
        
    }

    $scope.searchProduct = function (term) {
        return $http.get(<?php echo SITE_URL?>+"api/getGroups.php", {params: {term}})
        .then(function(response) {
            $scope.list = response.data;
            $scope.priceList = response.data;
            return response.data
        });
    }
    
    $scope.clearSearch = () => {
        $scope.product = "";
        $scope.list = [];
    }
});
</script>
<?php
echo mainFooter();  