<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
echo mainHeader(['page' => 'mode']);
?>

<div class="container" ng-controller="categoryController">
    <a href="javascript:void(0)" style="margin-right: 10px" ng-click="addCategory()" class="btn btn-primary btn-xs pull-right">Add Payment Mode</a>
    <h4>All Payment Modes</h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchCategories()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Sr</th>
                <th>Name</th>
                <th>Code</th>
                <th>Default</th>
                <th>Status</th>
                <th width="200"></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="li in list track by $index">
                <td>{{$index + 1}}</td>
                <td>{{li.title}}</td>
                <td>{{li.code}}</td>
                <td>{{li.is_default == 1 ? 'Yes' : 'No'}}</td>
                <td>{{catTypes[li.status]}}</td>
                <td>
                    <a class="btn btn-primary btn-xs" href="javascript:void(0)" ng-click="addCategory(li)">Edit</a>
                    <?php if ($userData['role'] === 'manager') { ?><a class="btn btn-danger btn-xs" href="javascript:void(0)" ng-click="deleteCategory(li.id)">Delete</a><?php } ?>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="display: flex; align-items: center; justify-content: space-between">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span>
    </div>

    <script type="text/ng-template" id="addCategory.html">
        <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Payment Mode</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sname">Title</label>
                <input id="sname" type="text" ng-model="form.title" class="form-control" placeholder="Title">
            </div>
            <div class="form-group">
                <label for="code">Code</label>
                <input id="code" type="text" ng-model="form.code" class="form-control" placeholder="Code">
            </div>
            <div class="form-group">
                <label for="is_default">Is Default</label>
                <select ng-model="form.is_default" class="form-control">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label for="swallet">Status</label>
                <select ng-model="form.status" class="form-control">
                  <?php foreach ($statusArr as $key => $value) { ?><option ng-value="'<?php echo $key; ?>'"><?php echo $value; ?></option><?php } ?> 
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit" ng-click="ok()">Submit Form</button>
        </div>
    </form>
</script>

    <script>
        app.controller('categoryController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
            $scope.currentPage = 1;
            $scope.catTypes = <?php echo json_encode($statusArr); ?>;
            $scope.data = {
                perPage: "10"
            }; //$scope.data.records;
            $scope.list = []; //$scope.data.records;
            $scope.search = ""; //$scope.data.records;
            $scope.siteUrl = '<?php echo SITE_URL ?>';
            $scope.getCategories = (page) => {
                $scope.loading = true;
                $http.get($scope.siteUrl + "api/getPaymentModes.php", {
                        params: {
                            page: page || 1,
                            perPage: $scope.data.perPage,
                            search: $scope.search
                        }
                    })
                    .then(function(response) {
                        $scope.loading = false;
                        if (response.status === 200) {
                            $scope.data = response.data;
                            $scope.list = response.data.records;
                        }
                    })
            }

            $scope.searchCategories = () => {
                $scope.getCategories();
            }

            $scope.perPage = () => {
                $scope.getCategories($scope.currentPage);
            }

            $scope.deleteCategory = function(id) {
                if ($window.confirm('Are you sure?')) {
                    $http.get('delete.php?id=' + id).then(function(response) {
                        $scope.getCategories(1);
                    });
                }
            }


            $scope.getCategories($scope.currentPage);
            $scope.pageChanged = () => {
                $scope.getCategories($scope.currentPage)
            }

            $scope.addCategory = function(form) {

                $uibModal.open({
                    ariaLabelledBy: 'modal-title',
                    ariaDescribedBy: 'modal-body',
                    templateUrl: 'addCategory.html',
                    controller: 'ModalInstanceCtrl',
                    resolve: {
                        form: function() {
                            return form
                        }
                    }
                }).result.then(function(selectedItem) {
                    $http.post(selectedItem && selectedItem.id ? 'update.php' : 'create.php', $httpParamSerializerJQLike(selectedItem), {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    }).then(function() {
                        $scope.getCategories(1);
                    });
                });
            };
        });

        app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, form) {
            $scope.form = {
                title: "",
                status: '1',
                ...form
            }
            $scope.ok = function() {
                $uibModalInstance.close($scope.form);
            };

            $scope.cancel = function() {
                $uibModalInstance.dismiss('cancel');
            };
        });
    </script>
    <?php
    echo mainFooter();
