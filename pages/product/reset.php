<?php
if (empty($disableHeader)) {
    include_once dirname(__FILE__) . '/../../include/settings.php';

    echo mainHeader(['page' => 'product']);
}
$programObj = new Store();
$programs = $programObj->getOwnerStores($userData['id']);
?>

<div class="container" ng-controller="productsController">
    <div class="row">
        <div class="col-sm-4">
            <select ng-model="shopId" class="form-control c-select">
                <?php foreach ($programs as $value) { ?>
                    <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-4">
            <a href="#" ng-click="resetCounters()" class="btn btn-primary">Submit</a>
        </div>
    </div>
</div>
<script>
    app.controller('productsController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
        $scope.shopId = '';
        $scope.resetCounters = (page) => {
            if ($scope.shopId) {
                if ($window.confirm('Are you really want to perform this action, this action isn\'t irreversable')) {
                    $http.get("<?php echo SITE_URL ?>api/resetCounters.php", {
                            params: {
                                shopId: $scope.shopId,
                            }
                        })
                        .then(function(response) {
                            alert(response.message)
                        })
                }
            } else {
                alert('Please select a shop first');
            }
        }

    })
</script>

<?php
echo mainFooter();
