<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

echo mainHeader(['page' => 'inventory']);

?>
<div class="main" ng-controller="myCtrl">
    <div class="content-section">
        <h4 class="clearfix">Upload File</h4>
        <form novalidate="" id="uploadForm">
            <div class="row">
                <div class="col-sm-3 form-group">
                    <input name="SheetName" ng-model="SheetName" class="form-control" required placeholder="Sheet Name" value="Sheet1">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="product_id" ng-model="product_id" class="form-control" required placeholder="Enter Product ID Column" value="Product Id">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="qty" ng-model="qty" class="form-control" required placeholder="Enter Qty Column" value="Qty">
                </div>
                <div class="col-sm-12 form-group">
                    <input type="file" file-model="files" />
                </div>
                <div class="col-sm-12 form-group">
                    <button ng-click="uploadFile()">upload me</button>
                </div>
            </div>
            <div id="showResult"></div>
        </form>
    </div>
</div>

<?php
echo mainFooter([]);
?>
<script type="text/javascript">
    app.directive('fileModel', ['$parse', function($parse) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                element.bind('change', function() {
                    $parse(attrs.fileModel).assign(scope, element[0].files)
                    scope.$apply();
                });
            }
        };
    }]);
    var site_url = '<?php echo SITE_URL ?>';
    app.controller('myCtrl', ['$scope', '$http', function($scope, $http) {

        $scope.uploadFile = function() {
            var fd = new FormData();
            console.log($scope.files);
            angular.forEach($scope.files, function(file) {
                fd.append('file', file);
            });
            fd.append('SheetName', $scope.SheetName);
            fd.append('product_id', $scope.product_id);
            fd.append('qty', parseFloat($scope.qty));
            $http.post(site_url + 'api/importInventory.php', fd, {
                transformRequest: angular.identity,
                headers: {
                    'Content-Type': undefined
                }
            }).then(function successCallback(d) {
                console.log(d);
                alert(d.data?.message)
            })
        }
    }]);
</script>