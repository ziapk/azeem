<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
echo mainHeader(['page' => 'publisher']);
?>

<div class="container" ng-controller="publisherController">
<a href="javascript:void(0)" style="margin-right: 10px" ng-click="addPublisher()" class="btn btn-primary btn-xs pull-right">Add Publisher</a>
<h4 class="section-title">All Publisher</h4>
<div class="form-group">
    <input class="form-control" ng-change="searchPublishers()" ng-model="search" placeholder="Type here for search..." />
</div>
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Discount Type</th>
            <th>Discount Amount</th>
            <th>Discount Allow</th>
            <th width="200"></th>
        </tr>
    </thead>
    <tbody>
            <tr ng-repeat="li in list">
                <td>{{li.full_name}}</td>
                <td>{{discountTypes[li.discount_type]}}</td>
                <td>{{li.discount_amount}}</td>
                <td>{{statusArr[li.discount_status]}}</td>
                <td>
                    <a class="btn btn-primary btn-xs" href="javascript:void(0)" ng-click="addPublisher(li)">Edit</a>
                    <?php if($userData['role'] === 'manager') {?><a class="btn btn-danger btn-xs" href="javascript:void(0)" ng-click="deletePublisher(li.id)">Delete</a><?php } ?>
                </td>
            </tr>
    </tbody>
</table>
<div style="display: flex; align-items: center; justify-content: space-between"><ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span></div>

<script type="text/ng-template" id="addPublisher.html">
    <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Publisher</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.full_name" class="form-control" placeholder="Publisher's Name">
            </div>
            <div class="form-group">
                <label for="stype">Discount Type</label>
                <select ng-model="form.discount_type" class="form-control">
                  <?php foreach($discountTypesArr as $key => $value) {?><option value="<?php echo $key;?>"><?php echo $value; ?></option><?php } ?> 
                </select>
            </div>
            <div class="form-group">
                <label for="sdiscount_amount">Discount Amount</label>
                <input id="sdiscount_amount" type="text" ng-model="form.discount_amount" class="form-control" placeholder="Discount amount">
            </div>
            <div class="form-group">
                <label for="stype">Discount Status</label>
                <select ng-model="form.discount_status" class="form-control">
                  <?php foreach($statusArr as $key => $value) {?><option value="<?php echo $key;?>"><?php echo $value; ?></option><?php } ?> 
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
app.controller('publisherController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
    $scope.currentPage = 1;
    $scope.discountTypes = <?php echo json_encode($discountTypesArr);?> 
    $scope.statusArr = <?php echo json_encode($statusArr);?> 
    $scope.data = { perPage: "10"}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.search = ""; //$scope.data.records;
    $scope.siteUrl = '<?php echo SITE_URL ?>';
    $scope.getPublishers = (page) => {
        $scope.loading = true;
        $http.get($scope.siteUrl+"api/getPublishers.php", {params: {page: page || 1, perPage: $scope.data.perPage, search: $scope.search}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
                $scope.list = response.data.records;
            }
        })
    }
    
    $scope.searchPublishers = () => {
        $scope.getPublishers(1);
    }
    
    $scope.perPage = () => {
        $scope.getPublishers($scope.currentPage);
    } 
    

    $scope.getPublishers($scope.currentPage);
    $scope.pageChanged = () => {
        $scope.getPublishers($scope.currentPage)
    }


    
    $scope.deletePublisher = function (id) {
        if($window.confirm('Are you sure?')) {
            $http.get('delete.php?id='+id).then(function(response) {
                $scope.getPublishers(1);
            });
        }
    }
    
    $scope.addPublisher = function (form) {
    
        $uibModal.open({
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'addPublisher.html',
            controller: 'ModalInstanceCtrl',
            resolve: {
                form: function() {
                    return form
                }
            }
        }).result.then(function (selectedItem) {
            $http.post(selectedItem && selectedItem.id ? 'update.php' : 'create.php', $httpParamSerializerJQLike(selectedItem), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
                $scope.getPublishers(1);
            });
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
});



app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, form) {
    $scope.form = {
        full_name: "",
        discount_amount: "",
        discount_type: "",
        discount_status: "",
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
<?php
echo mainFooter();