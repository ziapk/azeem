<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getOwnerCategories($userData['created_by']);
echo mainHeader(['page' => 'stock']);
?>

<div class="container" ng-controller="stockController">

    <a href="<?php echo SITE_URL ?>pages/stock/exchange.php" style="margin-right: 10px" class="btn btn-primary btn-xs pull-right"><span class="fa fa-plus"></span> Create Exchange</a>
    <h4 class="section-title">Stock History</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Action By</th>
                    <th>Handover</th>
                    <th>Transfer Qty</th>
                    <th>From Product</th>
                    <th>Before Qty</th>
                    <th>After Qty</th>
                    <th>To Product</th>
                    <th>Before Qty</th>
                    <th>After Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="li in list">
                    <td width="50">{{li.created_at|date:'mm/dd/yyyy @ h:mma' }}</td>
                    <td width="50">{{li.createdBy}}</td>
                    <td width="50">{{li.handover}}</td>
                    <td class="text-success lead"><strong>{{li.to_qty}}</strong></td>
                    <td><strong class="text-danger">{{li.fromProduct}}</td>
                    <td><strong class="text-danger">{{li.from_ex_qty}}</td>
                    <td><strong class="text-danger">{{li.from_after_qty}}</td>
                    <td><strong class="text-success">{{li.toProduct}}</td>
                    <td><strong class="text-success">{{li.to_ex_qty}}</td>
                    <td><strong class="text-success">{{li.to_after_qty}}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="pagination-custom">
        <ul uib-pagination ng-if="data.perPage < data.totalRecords" items-per-page="data.perPage" total-items="data.totalRecords" ng-model="currentPage" ng-change="pageChanged(currentPage)"></ul> <span>Per Page <select ng-change="perPage()" ng-model="data.perPage">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select></span> <span>Total Records <strong>{{data.totalRecords}}</strong></span>
    </div>
</div>

<script>
    app.controller('stockController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log, toaster) {
        $scope.data = {
            perPage: "10"
        }; //$scope.data.records;
        $scope.sending = {};
        $scope.currentPage = 1;
        $scope.list = []; //$scope.data.records;
        $scope.search = ""; //$scope.data.records;
        $scope.siteUrl = '<?php echo SITE_URL ?>';
        $scope.getStockHistory = (page) => {
            $scope.loading = true;
            $http.get($scope.siteUrl + "api/getStockHistory.php", {
                    params: {
                        type: 2,
                        page: page || 1,
                        perPage: $scope.data.perPage,
                        type: 2,
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
            $scope.getStockHistory($scope.currentPage);
        }

        $scope.getStockHistory($scope.currentPage);
        $scope.pageChanged = (page) => {
            $scope.currentPage = page;
            $scope.getStockHistory(page)
        }
    });

</script>

<?php
echo mainFooter();
