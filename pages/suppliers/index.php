<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getOwnerCategories($userData['created_by']);
echo mainHeader(['page' => 'suppliers']);
?>

<div class="container" ng-controller="productController">

    <a href="<?php echo SITE_URL . 'pages/supply'; ?>" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Supply</a>
    <a href="javascript:void(0)" style="margin-right: 10px" ng-click="addSuppliers()" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Supplier</a>
    <h4 class="section-title">All Suppliers</h4>
    <h5 class="section-title">Total Amount: <strong style="font-size: 1.3em;">{{data.closing_total | number}}</strong> <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?>
            <button class="btn btn-sm btn-primary mt-10" ng-click="bulkSendSummery()"><span class="fa fa-envelope"></span> Send Ledgers</button>
        <?php } ?>
    </h5>
    <div class="form-group">
        <input class="form-control" ng-change="searchSupplier()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" ng-model="selectAll" ng-change="selectAllItems(selectAll)" /></th>
                    <th></th>
                    <th>Contact</th>
                    <th>Company / Title / Address</th>
                    <th>Balance</th>
                    <th width="300"></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in list">
                    <td width="50"><input type="checkbox" ng-model="li.selected" /></td>
                    <td width="50">{{li.id}}</td>
                    <td><strong>{{li.name}}</strong> <br /> {{li.contact}}</td>
                    <td><strong>{{li.company}}</strong> - {{li.title}} <br />{{li.address}}</td>
                    <td style="text-align: right;" ng-class="{'text-danger': li.closing_balance < 0}">{{li.closing_balance | number}}</td>
                    <!-- <td>{{li.wallet}}</td> -->
                    <td>
                        <a class="btn btn-primary btn-xs" href="<?php echo SITE_URL . "pages/suppliers/update.php?id=" ?>{{li.id}}">Edit</a>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="../chart-of-accounts/summery.php?t=s&id={{li.account_id}}">Ledger</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a ng-click="deleteCustomer(li.id)" class="btn btn-danger btn-xs" href="javascript:void(0)"><span class="fa fa-remove"><span></a><?php } ?>
                        <a class="btn btn-danger btn-xs" href="<?php echo SITE_URL . "pages/suppliers/invoices.php?id=" ?>{{li.id}}">Bills</a>
                        <a class="btn btn-danger btn-xs" href="<?php echo SITE_URL . "pages/suppliers/adjustment.php?id=" ?>{{li.id}}">Payment</a>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" ng-click="sendSummery(li.account_id)" href="javascript:void(0)"><span class="fa fa-envelope"></span>{{sending[li.account_id] ? 'Sending' : ''}}</a><?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="pagination-custom">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul>
        <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select></span> <span>Total Records <strong>{{data.totalRecords}}</strong></span>
    </div>
</div>

<script type="text/ng-template" id="addSupplier.html">
    <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Supplier</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="form-group">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.name" class="form-control" placeholder="Supplier's Name">
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="scontact">Contact</label>
                        <input id="scontact" type="text" ng-model="form.contact" class="form-control" placeholder="Supplier's Contact">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="semail">Email</label>
                        <input id="semail" type="text" ng-model="form.email" class="form-control" placeholder="Supplier's Email">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="stitle">Title</label>
                        <input id="stitle" type="text" ng-model="form.title" class="form-control" placeholder="Supplier's Title">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="scompany">Company</label>
                        <input id="scompany" type="text" ng-model="form.company" class="form-control" placeholder="Supplier's company">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="sopening_balance">Opening Balance</label>
                        <input id="sopening_balance" type="text" ng-model="form.opening_balance" class="form-control" placeholder="Supplier's Opening Balance">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="saddress">Address</label>
                <input id="saddress" type="text" ng-model="form.address" class="form-control" placeholder="Supplier's Address">
            </div>
            
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>


<script>
    app.controller('productController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, toaster) {
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        $scope.sending = {};
        $scope.currentPage = 1;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';
        $scope.getSuppliers = (page) => {
            $scope.loading = true;
            $http.get($scope.siteUrl + "api/getSuppliers.php", {
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

        $scope.selectAllItems = (value) => {
            $scope.list.map(row => row.selected = value)
        }

        $scope.bulkSendSummery = async () => {
            const getList = $scope.list.filter(row => row.selected).map(row => row.account_id);
            if (getList?.length) {
                for (const account_id of getList) {
                    try {
                        $scope.sending[account_id] = true;
                        const res = await $http.post($scope.siteUrl + 'pages/chart-of-accounts/sendSummery.php', $httpParamSerializerJQLike({
                            account_id: [account_id],
                            from: '',
                            to: '',
                            type: 's',
                        }), {
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        });

                        if (res.data.success) {
                            toaster.success({
                                body: res.data.message
                            })
                        } else {
                            console.log({
                                type: 'danger',
                                message: res.data.message
                            })
                        }
                    } catch (err) {
                        console.log({
                            type: 'danger',
                            message: err?.message || err
                        })
                    }

                    $scope.sending[account_id] = false;

                }


            }

        }

        $scope.sendSummery = (account_id) => {
            $scope.sending[account_id] = true;
            $http.post($scope.siteUrl + 'pages/chart-of-accounts/sendSummery.php', $httpParamSerializerJQLike({
                account_id: [account_id],
                from: '',
                to: '',
                type: 's',
            }), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                $scope.sending[account_id] = false;
                if (res.data.success) {
                    $scope.alert = {
                        type: 'success',
                        message: res.data.message
                    }
                } else {
                    $scope.alert = {
                        type: 'danger',
                        message: res.data.message
                    }
                }
                // $uibModalInstance.close($scope.form);
            }).catch(err => {

                $scope.alert = {
                    type: 'danger',
                    message: err?.message || err
                }

                $scope.sending[account_id] = false;
            });
        }

        $scope.perPage = () => {
            $scope.getSuppliers($scope.currentPage);
        }

        $scope.searchSupplier = () => {
            $scope.getSuppliers(1);
        }

        $scope.deleteCustomer = function(id) {
            if (window.confirm('Are you sure ?')) {
                window.open("<?php echo SITE_URL; ?>pages/suppliers/delete.php?id=" + id, "", "width=300,height=400");
                window.location.reload();
            }
        }

        $scope.getSuppliers($scope.currentPage);
        $scope.pageChanged = (page) => {
            $scope.currentPage = page;
            $scope.getSuppliers(page)
        }
        $scope.addToCard = function(id) {
            if ($window.sessionStorage.getItem('shopping')) {
                const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));
                let found = false;
                shopCart.map(row => {
                    if (row.id == id) {
                        found = true
                        row.qty++;
                    }
                });

                if (!found) {
                    $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart, ...[{
                        id,
                        qty: 1
                    }]]))
                } else {
                    $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart]))
                }
            } else {
                $window.sessionStorage.setItem('shopping', JSON.stringify([{
                    qty: 1,
                    id
                }]))
            }
        }

        $scope.addSuppliers = function(size, parentSelector) {

            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'addSupplier.html',
                controller: 'ModalInstanceCtrl',
                size: size,
                resolve: {
                    siteUrl: function() {
                        return $scope.siteUrl
                    }
                }
            }).closed.then(function(selectedItem) {
                $scope.getSuppliers(1);
            });
        };

    });



    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike, siteUrl) {
        $scope.form = {
            name: "",
            contact: "",
            email: "",
            address: "",
            wallet: 0
        }
        $scope.alert = null;
        $scope.siteUrl = siteUrl;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post($scope.siteUrl + 'api/createSupplier.php', $httpParamSerializerJQLike($scope.form), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
                console.log(res)
                if (res.data.success) {
                    $scope.alert = {
                        type: 'success',
                        message: res.data.message
                    }
                } else {
                    $scope.alert = {
                        type: 'danger',
                        message: res.data.message
                    }
                }
                // $uibModalInstance.close($scope.form);
            });
        };


        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    });
</script>

<?php
echo mainFooter();
