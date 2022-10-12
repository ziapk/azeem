<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
echo mainHeader(['page' => 'program']);
?>

<div class="container" ng-controller="programController">
<a href="javascript:void(0)" style="margin-right: 10px" ng-click="addProgram()" class="btn btn-primary btn-xs pull-right">Add Program</a>
<h4>All Programs</h4>
<div class="form-group">
    <input class="form-control" ng-change="searchPrograms()" ng-model="search" placeholder="Type here for search..." />
</div>
<table class="table">
    <thead>
        <tr>
            <th>Degree</th>
            <th>Program</th>
            <th>Class/Part</th>
            <th width="200"></th>
        </tr>
    </thead>
    <tbody>
            <tr ng-repeat="li in list">
                <td>{{li.degree}}</td>
                <td>{{li.program}}</td>
                <td>{{li.class}}</td>
                <td>
                    <a class="btn btn-primary btn-xs" href="<?php echo SITE_URL."pages/program/update.php?id="?>{{li.id}}">Edit</a>
                    <?php if($userData['role'] === 'manager') {?><a class="btn btn-danger btn-xs" href="javascript:void(0)" ng-click="deleteProgram(li.id)">Delete</a><?php } ?>
                    <a class="btn btn-default btn-xs" href="javascript:void(0)" ng-click="assignBooks(li)">Assign</a>
                </td>
            </tr>
    </tbody>
</table>

<div style="display: flex; align-items: center; justify-content: space-between"><ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span></div>

<script type="text/ng-template" id="addProgram.html">
    <form ng-submit="ok()" autocomplete="off"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Program</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sdegree">Degree</label>
                <input id="sdegree" type="text" ng-model="form.degree" placeholder="Degree" class="type-ahead-input form-control" uib-typeahead="address as address.title for address in searchDegree($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="form-group">
                <label for="sprogram">Program</label>
                <input id="sprogram" type="text" ng-model="form.program" placeholder="Program" class="type-ahead-input form-control" uib-typeahead="address as address.title for address in searchProgram($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <div class="form-group">
                <label for="sclass">Class/Part</label>
                <input id="sclass" type="text" ng-model="form.class" placeholder="Class/Part" class="type-ahead-input form-control" uib-typeahead="address as address.title for address in searchClass($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit" ng-click="ok()">Submit Form</button>
        </div>
    </form>
</script>

<script>
app.controller('programController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
    $scope.currentPage = 1;
    $scope.catTypes = <?php echo json_encode($catTypesArr);?> 
    $scope.data = { perPage: "10"}; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.search = ""; //$scope.data.records;
    $scope.siteUrl = '<?php echo SITE_URL ?>';
    $scope.getPrograms = (page) => {
        $scope.loading = true;
        $http.get($scope.siteUrl+"api/getPrograms.php", {params: {page: page || 1, search: $scope.search, perPage: $scope.data.perPage}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
                $scope.list = response.data.records;
            }
        })
    }

    $scope.deleteProgram = function (id) {
        if(window.confirm('Are you sure ?')) {
            window.open("<?php echo SITE_URL;?>pages/program/delete.php?id="+id, "", "width=300,height=400"); 
            window.location.reload();
        }
    }
    
    $scope.searchPrograms = () => {
        $scope.getPrograms(1);
    }
    
    $scope.perPage = () => {
        $scope.getPrograms($scope.currentPage);
    }

    $scope.getPrograms($scope.currentPage);
    $scope.pageChanged = () => {
        $scope.getPrograms($scope.currentPage)
    }

    $scope.addProgram = function (size, parentSelector) {
    
        $uibModal.open({
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'addProgram.html',
            controller: 'ModalInstanceCtrl',
            size: size
        }).result.then(function (selectedItem) {
            
            $http.post('create.php', $httpParamSerializerJQLike(selectedItem), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
                $scope.getPrograms(1);
            });
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };

    $scope.currentCource = {};
    
    
    $scope.assignBooks = function (item) {
        $uibModal.open({
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'assignBooks.html',
            controller: 'AssignBooksModalInstanceCtrl',
            resolve: {
                parentData: function() {
                    return item
                }
            }
        }).result.then(function (response) {
            console.log(response);
            $http.post($scope.siteUrl+'api/assignBooks.php', $httpParamSerializerJQLike(response), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function() {
                //$scope.getPrograms(1);
            });
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
});



app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, $http) {
    $scope.form = {}
    
    $scope.ok = function () {
        $uibModalInstance.close({degree: $scope.form.degree.title || $scope.form.degree, program: $scope.form.program.title || $scope.form.program, class: $scope.form.class.title || $scope.form.class});
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };

    $scope.searchDegree = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getProgramsFields.php", {params: {term, view: 'degree'}})
        .then(function(response) {
            return response.data
        });
    }
    
    $scope.searchProgram = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getProgramsFields.php", {params: {term, view: 'program'}})
        .then(function(response) {
            return response.data
        });
    }
    
    $scope.searchClass = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getProgramsFields.php", {params: {term, view: 'class'}})
        .then(function(response) {
            return response.data
        });
    }
    
    
});

app.controller('AssignBooksModalInstanceCtrl', function ($scope, $http, $uibModalInstance, parentData) {
    $scope.books = {}
    $scope.final = []
    $scope.parentInfo = parentData


    $scope.remove = function (row) {
        delete $scope.books[row.id];
        $scope.final = Object.values($scope.books);
    }
    
    $scope.getBooks = () => {
        return $http.get("<?php echo SITE_URL?>api/getBooks.php", {params: {id: parentData.id}})
        .then(function(response) {
            if(response.data && response.data.length) {
                $scope.books = {}
                response.data.map(row => {
                    $scope.books[row.id] = row;
                    $scope.final = Object.values($scope.books);
                })
            }
            return response.data
        });
    }

    $scope.getBooks();

    
    $scope.searchProduct = function (term) {
        return $http.get("<?php echo SITE_URL?>api/getStores.php", {params: {term}})
        .then(function(response) {
            return response.data
        });
    }

    $scope.selectProduct = (item) => {
        $scope.books[item.id] = item
        $scope.final = Object.values($scope.books)
        $scope.book = null
    }

    
    
    $scope.ok = function () {
        $uibModalInstance.close({books: $scope.final, program: parentData.id});
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});
</script>
<script type="text/ng-template" id="row.html">
  <a>
      <span ng-bind-html="match.model.title | uibTypeaheadHighlight:query"></span>
  </a>
</script>

<script type="text/ng-template" id="book.html">
  <a>
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
      <span class="pull-right" ng-bind-html="match.model.price | uibTypeaheadHighlight:query"></span><br />
      <small ng-bind-html="match.model.group | uibTypeaheadHighlight:query"></small><br />
      <small ng-bind-html="match.model.author | uibTypeaheadHighlight:query"></small>
  </a>
</script>


<script type="text/ng-template" id="assignBooks.html">
    <form ng-submit="ok()" autocomplete="off"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">{{parentInfo.degree}} <small>{{parentInfo.program}} {{parentInfo.class}}</small></h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sname">Search Books</label>
                <input id="sname" type="text" ng-model="book" placeholder="Search Book" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="book.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Group</th>
                        <th>Price</th>
                        <th>Author</th>
                        <th></th>
                    </tr>
                <thead>
                <tbody>
                    <tr ng-repeat="row in final">
                        <td>{{row.full_name}}</td>
                        <td>{{row.group}}</td>
                        <td>{{row.price}}</td>
                        <td class="text-danger">{{row.author}}</td>
                        <td class="text-danger"><a href="javascript:void(0)" ng-click="remove(row)" class="btn btn-xs btn-danger"><span class="fa fa-remove"></span></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit" ng-click="ok()">Save</button>
            <button class="btn btn-warning" type="button" ng-click="cancel()">Close</button>
        </div>
    </form>
</script>


<?php
echo mainFooter();