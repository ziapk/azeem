<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
echo mainHeader(['page' => 'category']);
?>

<div class="container" ng-controller="categoryController">
    <a href="javascript:void(0)" style="margin-right: 10px" ng-click="addCategory()" class="btn btn-primary btn-xs pull-right">Add Status</a>
    <h4>All Status</h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchCategories()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Type</th>
                <th>Value</th>
                <th width="200"></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="li in list">
                <td width="50"><img ng-if="li.image" width="40" class="image" src={{"<?php echo SITE_URL; ?>uploads/products/"+li.image}} /></td>
                <td>{{li.title}}</td>
                <td>{{li.type}}</td>
                <td>{{li.progress_value}}</td>
                <td>
                    <?php if ($userData['role'] === 'manager' || $userData['role'] === 'owner') { ?>
                        <a class="btn btn-primary btn-xs" href="javascript:void(0)" ng-click="addCategory(li)">Edit</a>
                        <a class="btn btn-danger btn-xs" href="javascript:void(0)" ng-click="deleteCategory(li.id)">Delete</a>
                    <?php } ?>
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
        <form ng-submit="ok($event)"> 
        <div class="modal-header">
            <h4 class="modal-title" id="modal-title">Add Status</h4>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="title">Title</label>
                <input id="title" type="text" ng-model="form.title" name="title" class="form-control" placeholder="Status Name">
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <input id="type" type="text" ng-model="form.type" name="type" class="form-control" placeholder="Status Type">
            </div>
            <div class="form-group">
                <label for="progress_value">Progress Value</label>
                <input ng-model="form.progress_value" name="progress_value" class="form-control" placeholder="10, 20, 30" />
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
            $scope.catTypes = <?php echo json_encode($catTypesArr); ?>;
            $scope.data = {
                perPage: "10"
            }; //$scope.data.records;
            $scope.list = []; //$scope.data.records;
            $scope.search = ""; //$scope.data.records;
            $scope.siteUrl = '<?php echo SITE_URL ?>';
            $scope.getCategories = (page) => {
                $scope.loading = true;
                $http.get($scope.siteUrl + "api/getStatus.php", {
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
                        $scope.getCategories($scope.currentPage);
                    });
                }
            }

            $scope.linkAccount = (category) => {
                ;
                $http.post('./linkAccount.php', $httpParamSerializerJQLike(category), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function(res) {
                    alert(res.data.message);
                    window.location.reload();
                });
            }


            $scope.getCategories($scope.currentPage);
            $scope.pageChanged = (currentPage) => {
                $scope.currentPage = currentPage;
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
                }).result.then(function(selectedItem, data) {
                    console.log(selectedItem, form);
                    $http.post(selectedItem.form && selectedItem.form.id ? 'update.php' : 'create.php', $httpParamSerializerJQLike(selectedItem.form), {
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
                full_name: "",
                cat_type: "",
                ...form
            }
            $scope.ok = function(event) {
                var formData = new FormData(event.target);
                formData.append('id', $scope.form.id);
                console.log($scope.form);
                $uibModalInstance.close({
                    formData,
                    form: $scope.form
                });
            };

            $scope.cancel = function() {
                $uibModalInstance.dismiss('cancel');
            };
        });
    </script>
    <?php
    echo mainFooter();
