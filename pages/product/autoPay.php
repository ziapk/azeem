<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

echo mainHeader(['page' => 'assign']);

?>
<div class="main" ng-controller="myCtrl">
    <div class="content-section">
        <h4 class="clearfix">Upload File</h4>
        <form novalidate="" id="uploadForm">
            <div class="row">
                <div class="col-sm-3 form-group">
                    <input name="form.AmountColumn" class="form-control" required placeholder="Enter Total Fee Column" value="Amount">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="form.StudentIdColumn" class="form-control" required placeholder="Enter Student ID Column" value="Student Id">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="form.ReciptId" class="form-control" required placeholder="Enter Invoice ID Column" value="Recipt No">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="form.SheetName" class="form-control" required placeholder="Enter Excel Sheet Name" value="Sheet1">
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
            $http.post(site_url + 'api/importRacks.php', fd, {
                transformRequest: angular.identity,
                headers: {
                    'Content-Type': undefined
                }
            }).success(function(d) {
                console.log(d);
            })
        }
    }]);
</script>