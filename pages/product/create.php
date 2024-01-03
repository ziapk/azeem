<?php

include_once dirname(__FILE__) . '/../../include/settings.php';


$productObj = new Products();
$categoryObj = new Categories();
$publisherObj = new Publishers();

$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];

$publishers = $publisherObj->getPublishers($ownerId);


$error = "";
$message = "";
if (!empty($_POST) && isset($_POST['create'])) {

    $error = "";

    if (empty($_POST['full_name']) || empty($_POST['price'])) {

        $error = "Please fill all fields";
    } else {
        $photo = $_FILES['image'];
        $uploaded = false;
        $image = "";
        if (isset($photo) && count($photo)) {
            if ($photo['error'] == 0) {
                $img = explode('.', $photo['name']);
                $photo['dst_path']     = dirname(__FILE__) . '/../../uploads/products/';

                $image = time() . '.' . $img[1];

                if (!file_exists($photo['dst_path'])) {

                    mkdir($photo['dst_path'], 0777, true);
                }

                $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'] . $image);
                if ($moved) {
                    $uploaded = true;
                }
            }
        }

        $data = [
            'user_id' => $userId,
            'owner_id' => $ownerId,
            'full_name' => $_POST['full_name'],
            'code' => $_POST['code'],
            'cat_id' => !empty($_POST['cat_id']) ? $_POST['cat_id'] : NULL,
            'sub_cat' => !empty($_POST['subCat']) ? $_POST['subCat'] : NULL,
            'group' => $_POST['group'],
            'author' => $_POST['author'],
            'board' => $_POST['board'],
            'description' => $_POST['description'],
            'publisher_id' => !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : 0,
            'note' => !empty($_POST['note']) ? $_POST['note'] : '',
            'image' => $uploaded ? $image : "",
            'price' => !empty($_POST['price']) ? $_POST['price'] : "",
            'product_type' => !empty($_POST['product_type']) ? $_POST['product_type'] : "1",
            'gst' => !empty($_POST['gst']) ? $_POST['gst'] : null,
            'service_charges' => !empty($_POST['service_charges']) ? $_POST['service_charges'] : null,
            'expiry' => !empty($_POST['expiry']) ? $_POST['expiry'] : null,
            'pprice' => !empty($_POST['pprice']) ? $_POST['pprice'] : null,
        ];
        $create = $productObj->createProduct($data);

        if ($create) {
            $data = [
                'qty' => !empty($_POST['in_hand']) ? (($userData['role'] == 'owner' || $userData['role'] == 'manager') ? $_POST['in_hand'] : 0) : 0,
                'stock_out' => 0,
                'product_id' => $create,
                'location' => !empty($_POST['location']) ? $_POST['location'] : null,
                'shopId' => $shop['id'],
                'owner_id' => $ownerId,
            ];

            $assign = $productObj->assignProduct($data);

            $message = "Successfully created!";
        } else {
            $message = "Check form carefully!";
        }
    }
}
$product = [];
if (!empty($_GET['id'])) {
    $product = $productObj->getProduct($_GET['id'], $ownerId);
}

echo mainHeader(['page' => 'product-create', 'hideheader' => $_GET['headers']]);
$categories = $categoryObj->getCategories('pro', $ownerId);


?>
<div class="container" ng-controller="orderController">
    <div class="clearfix">

        <h3 class="section-title"><?php if (!empty($product)) {
                                        echo 'Duplicate Product';
                                    } else {
                                        echo 'Create Product';
                                    } ?></h3>
        <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
            <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
            <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
            <div class="row">
                <div class="col-sm-3 form-group">
                    <label>Title</label>
                    <input type="text" ng-model="form.full_name" name="full_name" class="form-control">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Description</label>
                    <input type="text" name="description" ng-model="form.description" placeholder="i.e Any thing about product" class="form-control">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Code</label>
                    <input type="text" name="code" ng-model="form.code" placeholder="i.e BTL, SRF" class="form-control">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Image</label>
                    <input name="image" type="file">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 form-group">
                    <label>Price (RETAIL)</label>
                    <input name="price" ng-model="form.price" type="text" class="form-control" placeholder="price">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Purchase Price</label>
                    <input type="text" ng-model="form.pprice" name="pprice" placeholder="i.e 100, 150" class="form-control">
                </div>

                <div class="col-sm-3 form-group">
                    <label>Board</label>
                    <input type="text" name="board" ng-model="form.board" class="type-ahead-input form-control" typeahead-on-select="selectBoard($item)" uib-typeahead="address as address.board for address in searchBoard($viewValue)" typeahead-template-url="board.html" typeahead-show-hint="true" typeahead-min-length="0">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Author</label>
                    <input type="text" name="author" placeholder="Author" ng-model="form.author" class="type-ahead-input form-control" typeahead-on-select="selectAuthor($item)" uib-typeahead="address as address.author for address in searchAuthor($viewValue)" typeahead-template-url="author.html" typeahead-show-hint="true" typeahead-min-length="0">
                </div>

                <div class="col-sm-3 form-group">
                    <label>Group</label>
                    <input type="text" class="type-ahead-input form-control" ng-model="form.group" name="group" placeholder="i.e Group Name" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.group for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Publisher</label>
                    <ui-select custom-dropdown ng-model="form.publisher" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a publisher">
                        <ui-select-match placeholder="Enter a publisher...">{{$select.selected.full_name}}</ui-select-match>
                        <ui-select-choices repeat="address in publishers track by $index" refresh="refreshPublishers($select.search)" refresh-delay="0">
                            <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                        </ui-select-choices>
                    </ui-select>

                    <input type="hidden" name="publisher_id" value={{form.publisher.id}} />
                </div>
                <div class="col-sm-3 form-group">
                    <label>Category</label>
                    <select class="form-control" name="cat_id" ng-model="form.cat_id">
                        <option value="">-- Select a Category --</option>
                        <?php foreach ($categories as $publisher) {
                            echo "<option value='$publisher[id]'>$publisher[full_name]</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-sm-3 form-group">
                    <label>Stock In Hand</label>
                    <input type="text" ng-model="form.in_hand" name="in_hand" placeholder="i.e 100, 150" class="form-control">
                </div>

                <div class="col-sm-3 form-group">
                    <label>Place in Store</label>
                    <input type="text" ng-model="form.location" name="location" placeholder="i.e any location" class="form-control">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Product Type</label>
                    <select ng-model="form.product_type" name="product_type" class="form-control">
                        <?php foreach ($productTypes as $key => $value) { ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-12 form-group text-right">
                    <input type="submit" name="create" value="Create" class="btn btn-primary btn-submit">
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/ng-template" id="row.html">
    <a>
      <span ng-bind-html="match.model.group | uibTypeaheadHighlight:query"></span>
  </a>
</script>
<script type="text/ng-template" id="author.html">
    <a>
      <span ng-bind-html="match.model.author | uibTypeaheadHighlight:query"></span>
  </a>
</script>
<script type="text/ng-template" id="board.html">
    <a>
      <span ng-bind-html="match.model.board | uibTypeaheadHighlight:query"></span>
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
        $scope.form = <?php echo json_encode($product); ?> || {};
        $scope.opublishers = <?php echo !empty($publishers) ? json_encode($publishers) : json_encode([]); ?>;
        $scope.publishers = <?php echo !empty($publishers) ? json_encode($publishers) : json_encode([]); ?>;
        $scope.form.product_type = $scope.form.product_type || '1';

        $scope.selectProduct = function(p) {
            let exists = false;
            $scope.items.map((pro) => {
                if (pro.id == p.id) {
                    exists = true;
                    pro.qty++;
                }
            })
            $scope.product = ""
            if (!exists) {
                $scope.items.push({
                    ...p,
                    qty: 1
                });
            }

        }

        $scope.refreshPublishers = search => {
            $scope.publishers = $scope.opublishers.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
        }

        $scope.searchGroup = function(term) {
            return $http.get("<?php echo SITE_URL ?>api/getGroups.php", {
                    params: {
                        term
                    }
                })
                .then(function(response) {
                    return response.data
                });
        }

        $scope.searchAuthor = function(term) {
            return $http.get("<?php echo SITE_URL ?>api/getAuthors.php", {
                    params: {
                        term
                    }
                })
                .then(function(response) {
                    return response.data
                });
        }

        $scope.searchBoard = function(term) {
            return $http.get("<?php echo SITE_URL ?>api/getBoards.php", {
                    params: {
                        term
                    }
                })
                .then(function(response) {
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
?>
<script type="text/javascript">
    $('.datepicker').daterangepicker({
        minDate: moment(),
        autoApply: true,
        singleDatePicker: true,
        parentEl: '.datepicker-parent',
    }, function(start, end, label) {
        $('#expiry').val(moment(start).format('YYYY-MM-DD'));

    });
</script>