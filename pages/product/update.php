<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

if (empty($_POST) && empty($_POST['createCode'])) {
    if ($userData['role'] != 'owner') {
        echo '[403] ACCESS ISSUE, Please ask shop owner';
        exit;
    }
}
$productObj = new Products();
$programObj = new Programs();
$categoryObj = new Categories();
$publisherObj = new Publishers();
$publishers = $publisherObj->getPublishers($shop['owner_id']);

$ownerId = $userData['role'] == 'owner' || $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$userId = $userData['id'];

if (!empty($_POST['rackNo'])) {
    $rackNo = $_POST['rackNo'];
    foreach (explode(',', $rackNo) as $rackTitle) {
        $currentRack = trim($rackTitle);
        if (!empty($currentRack)) {
            $products = $productObj->getRackByTitle($currentRack, $shop['id']);
            $exits = [];
            $id = '';
            foreach ($products as $value) {
                if ($value['product_id'] == $_GET['id']) {
                    $exits = $value;
                }
                $id = $value['rack_id'];
            }
            if (!empty($currentRack) && empty($exits)) {
                if (empty($products)) {
                    $data = [
                        'title' => $currentRack,
                        'shop_id' => $shop['id'],
                        'owner_id' => $shop['owner_id'],
                        'status' => 1,
                    ];
                    $id = $productObj->createRack($data);
                }
                $childData = [
                    'product_id' => $_GET['id'],
                    'rack_id' => $id,
                    'status' => 1
                ];
                $childId = $productObj->createRackProducts($childData);
            }
        }
    }

    echo json_encode(['status' => 200, "message" => "Product racks updated successfully!"]);
    exit;
}

$error = "";
$message = "";
if (!empty($_POST) && isset($_POST['createCode'])) {
    $error = "";
    $data = [
        'product_id' => $_GET['id']
    ];

    if (!empty($_POST['code'])) {
        $data['code'] = $_POST['code'];
        $create = $productObj->createProductCode($data);
    }
    if (!empty($_POST['price'])) {
        $data['price'] = $_POST['price'];
        $create = $productObj->updateProductPrice($data);
    }
    if (!empty($_POST['wh_price'])) {
        $data['wh_price'] = $_POST['wh_price'];
        $create = $productObj->updateProductWHPrice($data);
    }
    if (!empty($_POST['author'])) {
        $data['author'] = $_POST['author'];
        $create = $productObj->updateProductAuthor($data);
    }

    if (!empty($_POST['full_name'])) {
        $data['full_name'] = $_POST['full_name'];
        $create = $productObj->updateProductName($data);
    }

    if (!empty($_POST['publisher_id'])) {
        $data['publisher_id'] = $_POST['publisher_id'];
        $create = $productObj->updateProductPublisherId($data);
    }

    if ($create) {
        $message = "Successfully Added!";
    } else {
        $message = "Nothing Added";
    }
    if (!empty($_POST['json_response'])) {
        echo json_encode(["status" => 200, "message" => $message]);
        die();
    }
}
if (!empty($_POST) && isset($_POST['updateCode'])) {
    $error = "";
    if (empty($_POST['code'])) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'id' => $_POST['id'],
            'code' => $_POST['code']
        ];

        $update = $productObj->updateProductCode($data);

        if ($update) {
            $message = "Successfully saved!";
        } else {
            $message = "Nothing change";
        }
    }
}

if (!empty($_POST) && isset($_POST['deleteCode'])) {
    $error = "";
    $data = [
        'id' => $_POST['id'],
        'code' => $_POST['code']
    ];

    $delete = $productObj->deleteProductCode($data);

    if ($delete) {
        $message = "Successfully deleted!";
    } else {
        $message = "Nothing deleted";
    }
}

if (!empty($_POST) && !empty($_POST['assignProgram'])) {
    $error = "";
    if (empty($_POST['program_id'])) {
        $error = "Please fill all fields";
    } else {

        $data = [
            'product_id' => $_GET['id'],
            'program_id' => $_POST['program_id']
        ];

        $create = $programObj->createProgramBook($data);


        if ($create) {
            $message = "Successfully Added!";
        } else {
            $message = "Nothing Added";
        }
    }
}

if (!empty($_POST) && isset($_POST['deleteProgram'])) {
    $error = "";
    $data = [
        'id' => $_POST['program_book_id']
    ];

    $delete = $programObj->deleteProgramBook($data);

    if ($delete) {
        $message = "Successfully deleted!";
    } else {
        $message = "Nothing deleted";
    }
}
if (!empty($_POST) && isset($_POST['update'])) {

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

        $p = $productObj->getProduct($_GET['id'], $ownerId);

        $data = [
            'id' => $_GET['id'],
            'owner_id' => $ownerId,
            'userId' => $userId,
            'full_name' => $_POST['full_name'],
            'code' => $_POST['code'],
            'publisher_id' => !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : null,
            'cat_id' => !empty($_POST['cat_id']) ? $_POST['cat_id'] : null,
            'pack_size' => !empty($_POST['pack_size']) ? $_POST['pack_size'] : 0,
            'pack_qty' => !empty($_POST['pack_qty']) ? $_POST['pack_qty'] : 0,
            'product_type' => !empty($_POST['product_type']) ? $_POST['product_type'] : "1",
            'description' => $_POST['description'],
            'group' => $_POST['group'],
            'author' => $_POST['author'],
            'board' => $_POST['board'],
            'price' => $_POST['price'],
            'pprice' => $_POST['pprice'],
            'wh_price' => !empty($_POST['wh_price']) ? $_POST['wh_price'] : null,
            'image' => $uploaded ? $image : $p['image'],
        ];

        $update = $productObj->updateProduct($data);

        if ($update) {
            $message = "Successfully saved!";
        } else {
            $message = "Nothing change";
        }
    }
}


if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: ' . SITE_URL . '');
}

$store = $productObj->getProduct($_GET['id'], $ownerId);
if (empty($store)) {
    header('location: ' . SITE_URL . '');
}

$categories = $categoryObj->getCategories('pro', $ownerId);

$programs = $programObj->getPrograms();



echo mainHeader(['page' => 'product']);

?>
<div class="container" ng-controller="orderController">
    <div class="btn-group btn-group-sm pull-right">
        <a href="<?php echo SITE_URL; ?>pages/product" class="btn btn-danger"><i class="fa fa-th" aria-hidden="true"></i></a>
        <a href="<?php echo SITE_URL; ?>" class="btn btn-danger"><i class="fa fa-bars" aria-hidden="true"></i></a>
        <?php if ($userData['role'] == 'owner') { ?><a href="<?php echo SITE_URL . "pages/product/create.php" ?>" class="btn btn-info active">Create Product</a><?php } ?>
    </div>
    <h3 class="section-title">Update Product</h3>

    <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
        <?php if (!empty($message)) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>
        <?php if (!empty($error)) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
        <div class="product-image"><img src="<?php echo SITE_URL . 'uploads/products/' . $store['image']; ?>" alt="" /></div>
        <div class="row">
            <div class="col-sm-3 form-group">
                <label>Title</label>
                <input type="text" ng-model="form.full_name" name="full_name" class="form-control">
            </div>
            <!-- <div class="col-sm-3 form-group">
                    <label>Stock In Hand</label>
                    <input type="text" ng-model="form.in_hand" name="in_hand" placeholder="i.e 100, 150" class="form-control">
                </div> -->
            <div class="col-sm-3 form-group">
                <label>Code</label>
                <input type="text" name="code" ng-model="form.code" placeholder="i.e BTL, SRF" class="form-control">
            </div>
            <div class="col-sm-3 form-group">
                <label>Image</label>
                <input name="image" type="file">
            </div>
        </div>

        <!-- <h4 class="text-danger"><strong>Prices</strong></h4> -->
        <div class="row">
            <div class="col-sm-3 form-group">
                <label>Price (RETAIL)</label>
                <input name="price" ng-model="form.price" type="text" class="form-control" placeholder="price">
            </div>
            <div class="col-sm-3 form-group">
                <label>Price (WholeSale)</label>
                <input name="wh_price" ng-model="form.wh_price" type="text" class="form-control" placeholder="Whole Sale Price">
            </div>
            <div class="col-sm-3 form-group">
                <label>Purchase Price</label>
                <input type="text" ng-model="form.pprice" name="pprice" placeholder="i.e 100, 150" class="form-control">
            </div>
            <div class="col-sm-3 form-group">
                <label>Description</label>
                <input type="text" name="description" ng-model="form.description" placeholder="i.e Any thing about product" class="form-control">
            </div>
            <div class="col-sm-3 form-group">
                <label>Product Type</label>
                <select ng-model="form.product_type" name="product_type" class="form-control">
                    <?php foreach ($productTypes as $key => $value) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <!-- <h4 class="text-danger"><strong>Tax Information</strong></h4>
            <div class="row">
                <div class="col-sm-3 form-group">
                    <label>GST %</label>
                    <input type="text" ng-model="form.gst" name="gst" placeholder="5, 17" class="form-control">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Service Charges %</label>
                    <input type="text" ng-model="form.service_charges" name="service_charges" placeholder="5, 10" class="form-control">
                </div>
            </div> -->
        <!-- <h4>Other Information</h4> -->
        <div class="row">
            <!-- <div class="col-sm-3 form-group">
                    <label>Min Qty (Reminder)</label>
                    <input type="text" ng-model="form.min_qty" name="min_qty" placeholder="i.e 6, 12" class="form-control">
                </div> -->
            <div class="col-sm-3 form-group">
                <label>No of Bundles</label>
                <input type="text" ng-model="form.pack_qty" name="pack_qty" placeholder="i.e 1, 2, 3..." class="form-control">
            </div>
            <div class="col-sm-3 form-group">
                <label>Products In a Bundle</label>
                <input type="text" ng-model="form.pack_size" name="pack_size" placeholder="i.e 1, 2, 3..." class="form-control">
            </div>
            <div class="col-sm-3 form-group">
                <label>Board</label>
                <input type="text" name="board" ng-model="form.board" class="type-ahead-input form-control" typeahead-on-select="selectBoard($item)" uib-typeahead="address as address.board for address in searchBoard($viewValue)" typeahead-template-url="board.html" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="col-sm-3 form-group">
                <label>Author</label>
                <input type="text" name="author" placeholder="Author/Company" ng-model="form.author" class="type-ahead-input form-control" typeahead-on-select="selectAuthor($item)" uib-typeahead="address as address.author for address in searchAuthor($viewValue)" typeahead-template-url="author.html" typeahead-show-hint="true" typeahead-min-length="0">
            </div>

            <div class="col-sm-3 form-group">
                <label>Group</label>
                <input type="text" class="type-ahead-input form-control" ng-model="form.group" name="group" placeholder="i.e Group Name" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.group for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
            </div>

            <div class="col-sm-3 form-group">
                <label>Publisher</label>
                <select class="form-control" name="publisher_id" ng-model="form.publisher_id">
                    <option value="">-- Select a Publisher --</option>
                    <?php foreach ($publishers as $publisher) {
                        echo "<option value='$publisher[id]'>$publisher[full_name]</option>";
                    } ?>
                </select>
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
            <div class="col-sm-12 form-group text-right">
                <input type="submit" name="update" value="Update Product" class="btn btn-primary btn-submit">
            </div>
        </div>
    </form>
    <form method="POST" action="" autocomplete="off">
        <h4>Add BarCode/QRCode</h4>
        <div class="row">
            <div class="col-sm-12 form-group">
                <textarea name="code" class="form-control" rows="5" placeholder="Move Cursor here, and scane code" id="barcode" ng-mouseover="setFocus()"></textarea>
            </div>
            <div class="col-sm-12 form-group">
                <input type="submit" name="createCode" value="Add" class="btn btn-success">
            </div>
        </div>
    </form>
    <h4>Assigned BarCode/QRCode</h4>
    <form ng-if="form && form.codes" ng-repeat="cp in form.codes track by $index" method="POST" action="" autocomplete="off">
        <div class="row">
            <div class="col-sm-5 form-group">
                <input type="hidden" name="id" ng-value="cp.id" />
                <input class="form-control" name="code" placeholder="code" ng-value="cp.code" />
            </div>
            <div class="col-sm-5 form-group">
                <input type="submit" name="updateCode" value="Update" class="btn btn-success">
                <input type="submit" name="deleteCode" value="Delete" class="btn btn-danger">
            </div>
        </div>
    </form>
    <!-- <h4>Assign In Cources</h4>
    <form ng-if="form && form.programs" ng-repeat="program in form.programs track by $index" method="POST" action="" autocomplete="off">
        <div class="row">
            <div class="col-sm-5 form-group">
                <input type="hidden" name="program_book_id" ng-value="program.program_book_id" />
                <div class="form-control">{{program.degree}} > <strong> {{program.program}}</strong> > <strong>{{program.class}}</strong></div>
            </div>
            <div class="col-sm-5 form-group">
                <input type="submit" name="deleteProgram" value="Delete" class="btn btn-danger">
            </div>
        </div>
    </form>
    <form method="POST" action="" autocomplete="off">
        <div class="row">
            <div class="col-sm-5 form-group">
                <select class="form-control" name="program_id">
                    <option value="">-- Select a Cource --</option>
                    <?php foreach ($programs as $program) {
                        echo "<option value='$program[id]'>$program[degree] >  $program[program] > $program[class]</option>";
                    } ?>
                </select>
            </div>
            <div class="col-sm-5 form-group">
                <input type="submit" name="assignProgram" value="Assign" class="btn btn-success">
            </div>
        </div>
    </form> -->
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
    app.controller('orderController', function($scope, $http, $window) {
        $scope.list = [];
        $scope.priceList = [];
        $scope.items = [];
        $scope.form = <?php echo json_encode($store); ?>

        $scope.selectProduct = function(p) {}

        $scope.setFocus = () => {
            const field = $window.document.getElementById('barcode');
            field.focus()
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
    });
</script>

<?php
echo mainFooter();
