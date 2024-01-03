<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
echo mainHeader(['page' => 'customer']);
?>

<div class="container" ng-controller="customerController">
    <a href="javascript:void(0)" ng-click="addCustomer()" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Customer</a>
    <h2 class="section-title">Customers</h2>
    <h5 class="section-title">Total Amount: <strong style="font-size: 1.3em;">{{data.closing_total | number}}</strong>
        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?>
            <button class="btn btn-sm btn-primary mt-10" ng-click="bulkSendSummery()"><span class="fa fa-envelope"></span> Send Ledgers</button>
        <?php } ?>
    </h5>

    <div class="form-group">
        <input class="form-control" ng-change="searchCustomer()" ng-model="search" placeholder="Type here for search..." />
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="50"><input type="checkbox" ng-model="selectAll" ng-change="selectAllItems(selectAll)" /></th>
                    <th>Id</th>
                    <th>Contact</th>
                    <th>Title / Company / Address</th>
                    <th>On Closing Report</th>
                    <th>Balance</th>
                    <th width="300"></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in list">
                    <td><input type="checkbox" ng-model="li.selected" /></td>
                    <td>{{li.id}}</td>
                    <td><strong>{{li.full_name}}</strong> <br /> {{li.phoneNumber}}</td>
                    <td><strong>{{li.company}}</strong> - {{li.title}} <br />{{li.address}}</td>
                    <td>{{li.type == '2' ? 'NO': 'YES'}}</td>
                    <td style="text-align: right;" ng-class="{'text-danger': li.closing_balance < 0}">{{li.closing_balance | number}}</td>
                    <td>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-info btn-xs" href="javascript:void(0)" ng-click="assignBooks(li)">Disc.</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="../chart-of-accounts/summery.php?t=c&id={{li.account_id}}">Ledger</a><?php } ?>
                        <!-- <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-xs btn-primary" href="adjustment.php?id={{li.account_id}}">Receiving</a><?php } ?> -->
                        <?php if ($userData['role'] === 'manager') { ?><a class="btn btn-danger btn-xs" href="<?php echo SITE_URL; ?>pages/orders/customerOrders.php?id={{li.id}}">Orders</a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a class="btn btn-default btn-xs" href="<?php echo SITE_URL; ?>pages/customers/update.php?id={{li.id}}"><span class="fa fa-edit"><span></a><?php } ?>
                        <?php if ($userData['role'] === 'owner') { ?><a ng-click="deleteCustomer(li.id)" class="btn btn-danger btn-xs" href="javascript:void(0)"><span class="fa fa-remove"><span></a><?php } ?>
                        <?php if ($userData['role'] === 'owner' || $userData['role'] === 'manager') { ?><a ng-if="li.is_default == 0" class="btn btn-default btn-xs" ng-click="sendSummery(li.account_id)" href="javascript:void(0)"><span class="fa fa-envelope"></span>{{sending[li.account_id] ? 'Sending' : ''}}</a><?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-custom">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul>
        <span>
            Per Page
            <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </span>
        <span>Total Records <strong>{{data.totalRecords}}</strong></span>
    </div>

</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    function createCustomer() {
        window.open("<?php echo SITE_URL; ?>pages/customers/create.php", "", "width=300,height=400");
    }


    app.controller('customerController', function($scope, $http, $httpParamSerializerJQLike, $uibModal, $window, $log, toaster) {
        $scope.currentPage = 1;
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';
        $scope.sending = {};

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
                            type: 'c',
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
                type: 'c',
                customer_name: "<?php echo $user['full_name']; ?>",
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

        $scope.getCustomers = (page) => {
            $scope.loading = true;
            $http.get($scope.siteUrl + "api/getCustomers.php", {
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

        $scope.deleteCustomer = function(id) {
            if (window.confirm('Are you sure ?')) {
                window.open("<?php echo SITE_URL; ?>pages/customers/delete.php?id=" + id, "", "width=300,height=400");
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

        $scope.addCustomer = function(size, parentSelector) {
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

        $scope.assignBooks = function(item) {
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
            }).result.then(function(response) {
                $http.post($scope.siteUrl + 'api/assignDiscount.php', $httpParamSerializerJQLike(response), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function() {
                    //$scope.getPrograms(1);
                });
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike) {
        $scope.form = {
            full_name: "",
            phoneNumber: "",
            address: "",
            default_discount: 0,
            type: false
        }

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post('create.php', $httpParamSerializerJQLike($scope.form), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(res) {
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


    app.controller('AssignBooksModalInstanceCtrl', function($scope, $http, $uibModalInstance, parentData) {
        $scope.books = {}
        $scope.final = []
        $scope.parentInfo = parentData


        $scope.remove = function(row) {
            delete $scope.books[row.id];
            $scope.final = Object.values($scope.books);
        }

        $scope.getBooks = () => {
            return $http.get("<?php echo SITE_URL ?>api/getCustomerDiscounts.php", {
                    params: {
                        id: parentData.id
                    }
                })
                .then(function(response) {
                    console.log('response', response);
                    if (response.data && response.data.length) {
                        console.log('response.data', response.data);
                        $scope.books = {}
                        response.data.map(row => {
                            $scope.books[row.id] = {
                                ...row,
                                discount_value: parseFloat(row.discount_value)
                            };
                            $scope.final = Object.values($scope.books);
                        })
                    }
                    return response.data
                });
        }

        $scope.getBooks();


        $scope.searchProduct = function(search) {
            return $http.get("<?php echo SITE_URL ?>api/getPublishers.php", {
                    params: {
                        search
                    }
                })
                .then(function(response) {
                    return response.data.records
                });
        }

        $scope.selectProduct = (item) => {
            $scope.books[item.id] = {
                ...item,
                discount_value: parseFloat(item.discount_amount),
                discount_type: '1'
            }
            $scope.final = Object.values($scope.books)
            $scope.book = null
        }



        $scope.ok = function() {
            $uibModalInstance.close({
                books: $scope.final.map(({
                    publisher_id,
                    discount_type,
                    discount_value
                }) => ({
                    id: publisher_id,
                    discount_type: discount_type || 1,
                    discount_value: discount_value || null
                })),
                customer_id: parentData.id
            });
        };

        $scope.cancel = function() {
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
            <div class="row">
            <div class="form-group col-sm-6">
                <label for="sname">Name</label>
                <input id="sname" type="text" ng-model="form.full_name" class="form-control" placeholder="Customer's Name">
            </div>
            <div class="form-group col-sm-6">
                <label for="semail">Email</label>
                <input id="semail" type="text" ng-model="form.email" class="form-control" placeholder="Customer's Email">
            </div>
            <div class="form-group col-sm-6">
                <label for="scontact">Contact</label>
                <input id="scontact" type="text" ng-model="form.contact" class="form-control" placeholder="Customer's Contact">
            </div>
            <div class="form-group col-sm-6">
                <label for="stitle">Title</label>
                <input id="stitle" type="text" ng-model="form.title" class="form-control" placeholder="Customer's title">
            </div>
            <div class="form-group col-sm-6">
                <label for="scompany">Company</label>
                <input id="scompany" type="text" ng-model="form.company" class="form-control" placeholder="Customer's company">
            </div>
            <div class="form-group col-sm-6">
                <label for="saddress">Address</label>
                <input id="saddress" type="text" ng-model="form.address" class="form-control" placeholder="Customer's Address">
            </div>
            <div class="form-group col-sm-6">
                <label for="sopening_balance">Opening Balance</label>
                <input id="sopening_balance" type="text" ng-model="form.opening_balance" class="form-control" placeholder="Customer's Opening Balance">
            </div>
            <div class="form-group col-sm-6">
                <label for="sdefault_discount">Discount %</label>
                <input id="sdefault_discount" type="text" ng-model="form.default_discount" class="form-control" placeholder="Customer's Discount">
            </div>

        </div>
        <div class="form-group">
            <label><small><input name="type" ng-model="form.type" type="checkbox"> Select this if you want your customer's balance on closing report</small></label>
        </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>

<script type="text/ng-template" id="book.html">
    <a>
      <strong ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></strong><br>
      <span>Books: {{match.model.total}}</span>
  </a>
</script>

<script type="text/ng-template" id="assignBooks.html">
    <form ng-submit="ok()" autocomplete="off"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">{{parentInfo.full_name}} <small>{{parentInfo.phoneNumber}}</small></h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="form-group">
                <label for="sname">Search Publisher</label>
                <input id="sname" type="text" ng-model="book" placeholder="Search Publisher" typeahead-on-select="selectProduct($item)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="book.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="0">
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Disc. Type</th>
                        <th>Value</th>
                        <th></th>
                    </tr>
                <thead>
                <tbody>
                    <tr ng-repeat="row in final">
                        <td>{{row.full_name}}</td>
                        <td>
                            <select ng-model="row.discount_type">
                                <option ng-value="'2'">Fixed</option>
                                <option ng-value="'1'">Percent</option>
                            </select>
                        </td>
                        <td><input type="number" min="0" max="100" onKeyPress="if(this.value.length==2) return false;" ng-model="row.discount_value" /> {{row.discount_type == 1 ? 'Percent': 'Fixed'}} </td>
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