<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$customers = new  Customers();
$customersData = $customers->getCustomers($userData['shopId']);
echo mainHeader(['page'=> 'customer']);
?>

<div class="container" ng-controller="customerController">
    <a href="javascript:void(0)" ng-click="addCustomer()" class="btn btn-primary btn-xs pull-right">Add New</a>    
    <h4 class="section-title">Customers</h4>
    <div class="form-group">
        <input class="form-control" ng-change="searchCustomer()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Wallet</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="li in list">
                <td>{{li.id}}</td>
                <td>{{li.full_name}}</td>
                <td>{{li.phoneNumber}}</td>
                <td>{{li.address}}</td>
                <td>{{li.wallet}}</td>
                <td>
                    <?php if($userData['role'] === 'manager') {?><a ng-click="deleteCustomer(li.id)" class="btn btn-primary btn-xs" href="javascript:void(0)">Delete</a><?php }?>
                    <?php if($userData['role'] === 'manager') {?><a class="btn btn-danger btn-xs" href="<?php echo SITE_URL;?>pages/orders/customerOrders.php?id={{li.id}}">View Orders</a><?php }?>
                    <?php if($userData['role'] === 'owner') {?><a class="btn btn-danger btn-xs" href="<?php echo SITE_URL;?>pages/customers/update.php?id={{li.id}}">Update</a><?php }?>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="display: flex; align-items: center; justify-content: space-between"><ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></span> <span>Total number of Records <strong>{{data.totalRecords}}</strong></span></div>

</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
function createCustomer () {
    window.open("<?php echo SITE_URL;?>pages/customers/create.php", "", "width=300,height=400"); 
}


app.controller('customerController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window) {
    $scope.currentPage = 1; 
    $scope.data = { perPage: "10" }; //$scope.data.records;
    $scope.list = []; //$scope.data.records;
    $scope.search = ""; //$scope.data.records;
    $scope.siteUrl = '<?php echo SITE_URL ?>';
    
    $scope.getCustomers = (page) => {
        $scope.loading = true;
        $http.get($scope.siteUrl+"api/getCustomers.php", {params: {page: page || 1, perPage: $scope.data.perPage, search: $scope.search}})
        .then(function(response) {
            $scope.loading = false;
            if(response.status === 200) {
                $scope.data = response.data;
                $scope.list = response.data.records;
            }
        })
    }

    $scope.deleteCustomer = function (id) {
        if(window.confirm('Are you sure ?')) {
            window.open("<?php echo SITE_URL;?>pages/customers/delete.php?id="+id, "", "width=300,height=400"); 
            window.location.reload();
        }
    }


    $scope.searchCustomer = () => {
        $scope.getCustomers(1);
    }

    $scope.perPage = () => {
        $scope.getCustomers($scope.currentPage);
    }

    $scope.getCustomers($scope.currentPage);

    $scope.pageChanged = (page) => {
        $scope.currentPage = page;
        $scope.getCustomers(page)
    }

    $scope.addCustomer = function (size, parentSelector) {
        $scope.form = null
        $uibModal.open({
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'addCustomer.html',
            controller: 'ModalInstanceCtrl',
            size: size
        }).closed.then(function() {
            $scope.getCustomers(1);
        });
    };
});

app.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, $http, $httpParamSerializerJQLike) {
    $scope.form = {
        full_name: "",
        phoneNumber: "",
        address: "",
        type: false
    }

    $scope.alert = null;

    $scope.closeAlert = function(index) {
        $scope.alert = null;
    };
    
    $scope.ok = function () {
        $http.post('create.php', $httpParamSerializerJQLike($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'} }).then(function(res) {
            if(res.data.success) {
                $scope.alert = {type: 'success', message: res.data.message}
            } else {
                $scope.alert = {type: 'danger', message: res.data.message}
            }
            // $uibModalInstance.close($scope.form);
        });
    };

    

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});

</script>

<script type="text/ng-template" id="addCustomer.html">
    <form ng-submit="ok()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Customer</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="form-group">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.full_name" class="form-control" placeholder="Customer's Name">
            </div>
            <div class="form-group">
                <label for="scontact">Contact</label>
                <input id="scontact" type="text" ng-model="form.contact" class="form-control" placeholder="Customer's Contact">
            </div>
            <div class="form-group">
                <label for="saddress">Address</label>
                <input id="saddress" type="text" ng-model="form.address" class="form-control" placeholder="Customer's Address">
            </div>
            <div class="form-group">
            <label><small><input name="type" ng-model="form.type" type="checkbox"> Select this if you want your invoices to show this customer’s basic information</small></label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>
