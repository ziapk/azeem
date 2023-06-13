<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$doubleEntryObj = new DoubleEntry();
$ids = [];
if ($userData['role'] === 'owner') {
    $storeObj = new Store();
    $ownerStores = $storeObj->getOwnerStores($shop['owner_id']);
    foreach ($ownerStores as $v) {
        $ids[] = $v['id'];
    }
} else {
    $ids[] = $shop['id'];
}

$demands = $doubleEntryObj->getJournals(['from' => $shop['sale_date'], 'to' => $shop['sale_date'], 'type' => ["DIRECT_PAYMENT", "DIRECT_RECEIVING", "ADJUSTMENT"]], $ids);
$acd_ids = [];
$grouping = [];
foreach ($demands as $value) {
    $acd_ids[] = $value['account_id'];
    $grouping[$value['transaction_id']][] = $value;
}
$accountAssoc = [];
if (!empty($acd_ids)) {
    $accounts = $doubleEntryObj->getAccountsByIds(array_unique($acd_ids));
    foreach ($accounts as $value) {

        $accountAssoc[$value['id']] = $value;
    }
}


echo mainHeader(['page' => 'coa']);

?>
<div class="container" ng-controller="coaController">
    <div class="content-section">
        <a href="<?php echo $commonArray['site_url'] . 'journal.php'; ?>" class="btn btn-primary btn-sm pull-right">
            Create
        </a>
        <h4 class="clearfix" style="margin-top: 0">General Journal</h4>
        <table class="table table-sm table-func table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Account Code</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Account Code</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th></th>
                </tr>
            </tfoot>
            <tbody>
                <?php $count = 1;
                foreach ($grouping as $id => $rows) {  ?>
                    <tr>
                        <td>
                            <?php echo $count; ?>
                        </td>
                        <td>
                            <?php echo $rows[0]['transaction_date']; ?>
                        </td>
                        <td colspan="6">
                            TID: <?php echo $rows[0]['transaction_id']; ?> |
                            <?php echo $rows[0]['reference']; ?> [ <?php echo $rows['0']['v_description']; ?> ]
                            <?php if ($userData['role'] === 'owner') { ?><a href="javascript:void(0)" class="text-danger" ng-click="deleteTransaction(<?php echo $id; ?>)">Delete Transaction</a><?php } ?>
                            <?php if ($userData['role'] === 'owner') { ?><a href="javascript:void(0)" class="text-danger" ng-click='addCustomer(<?php echo json_encode($rows['0']); ?>)'>EDIT</a><?php } ?>
                        </td>
                    </tr>
                    <?php foreach ($rows as $key => $product) { ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td><?php echo $accountAssoc[$product['account_id']]['title']; ?></td>
                            <td><?php echo $accountAssoc[$product['account_id']]['code']; ?></td>
                            <td><?php echo $product['description']; ?></td>
                            <td><?php echo ($product['entry_type'] == 'D') ? $product['amount'] : '' ?></td>
                            <td><?php if ($product['entry_type'] == 'C') {
                                    echo $product['amount'];
                                } ?></td>
                            <td></td>
                        </tr>
                <?php }
                    $count++;
                } ?>
            </tbody>
        </table>
    </div>
</div>
<script type="text/ng-template" id="addCustomer.html">
    <form ng-submit="ok()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Customer</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div uib-alert ng-if="alert" ng-class="'alert-'+(alert.type || 'warning')" close="closeAlert()">{{alert.message}}</div>
            <div class="form-group">
                <label for="samount">Transaction Amount [TID: {{form.transaction_id}}]</label>
                <input id="samount" type="text" ng-model="form.amount" class="form-control" placeholder="Amount">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>
<script type="text/javascript">
    app.controller('coaController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, $document, $uibModal, $log) {
        var site_url = '<?php echo SITE_URL ?>';
        $scope.deleteTransaction = (id) => {
            var site_url = '<?php echo $commonArray["site_url"] ?>';
            const params = 'id=' + id;
            console.log('id', id, params, site_url);
            if (confirm('Are you sure you want to delete this transaction')) {
                var url = './deleteTransaction.php';
                $http.post(url, $httpParamSerializerJQLike({
                        id
                    }), {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(res => {
                        alert('Transaction Deleted!');
                        window.location.reload();
                    });
            }
        }

        $scope.addCustomer = function(item) {
            $scope.form = null
            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'addCustomer.html',
                controller: 'ModalInstanceCtrl',
                resolve: {
                    parentData: function() {
                        return item
                    }
                }
            }).closed.then(function() {
                $window.location.reload();
            });
        };
    });
    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike, parentData) {
        $scope.form = {
            transaction_id: parentData.transaction_id,
            amount: parentData.amount
        }

        $scope.alert = null;

        $scope.closeAlert = function(index) {
            $scope.alert = null;
        };

        $scope.ok = function() {
            $http.post('updateTransaction.php', $httpParamSerializerJQLike($scope.form), {
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
</script>
<?php echo mainFooter(['page' => 'coa']); ?>
<script type="text/javascript">
    function fillModifyForm(obj) {
        // var id = $(element).parent().children('.category_id').val();
        // var title = $(element).parent().children('.category_title').val();
        // var code = $(element).parent().children('.category_code').val();
        // var account_type = $(element).parent().children('.category_account_type').val();
        // var group_id = $(element).parent().children('.category_group_id').val();
        // var status = $(element).parent().children('.category_status').val();
        console.log(obj);
        $('#mid').val(obj.id);
        $('#mtitle').val(obj.title);
        $('#mcode').val(obj.code);
        $('#maccount_type').val(obj.account_type);
        $('#mgroup_id').val(obj.group_id);
        $('#mstatus').val(obj.status);
    }

    function fillModifyForm2(obj) {
        // var id = $(element).parent().children('.category_id').val();
        // var title = $(element).parent().children('.category_title').val();
        // var code = $(element).parent().children('.category_code').val();
        // var account_type = $(element).parent().children('.category_account_type').val();
        // var group_id = $(element).parent().children('.category_group_id').val();
        // var status = $(element).parent().children('.category_status').val();
        $('#parent_id').val(obj.id);
        $('#parent_title').val(obj.code + " " + obj.title);
        // $('#title').val(obj.title);
        $('#code').val(obj.code + '-');
        $('#account_type').val(obj.account_type);
        $('#group_id').val(obj.group_id);
        $('#status').val(obj.status);
    }

    var site_url = '<?php echo $commonArray["site_url"] ?>';

    $(document).ready(function() {
        $('#newAccountForm').validate({
            debug: false,
            errorClass: "text-help",
            errorElement: "span",
            // default error placement
            errorPlacement: function(error, element) {
                $(element).parent().append(error);
            },
            submitHandler: function(form) {
                var params = $(form).serialize();
                var url = site_url + 'actions/double-entry/newAccount.php';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: params,
                    processData: false,
                    async: false,
                    success: function(result) {
                        form.reset();
                        window.location.reload();
                    }
                })
            },
            highlight: function(element) {
                $(element).parent().addClass("has-danger");
            },
            unhighlight: function(element) {
                $(element).parent().removeClass("has-danger");
            }
        });
    })


    $(document).ready(function() {
        $('#modifyCategoryForm').validate({
            debug: false,
            errorClass: "text-help",
            errorElement: "span",
            // default error placement
            errorPlacement: function(error, element) {
                $(element).parent().append(error);
            },
            submitHandler: function(form) {
                var params = $(form).serialize();
                var url = site_url + 'actions/double-entry/updateAccount.php';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: params,
                    processData: false,
                    async: false,
                    success: function(result) {
                        form.reset();
                        window.location = window.location;
                    }
                })
            },
            highlight: function(element) {
                $(element).parent().addClass("has-danger");
            },
            unhighlight: function(element) {
                $(element).parent().removeClass("has-danger");
            }
        });
        $(function() {
            $('#tree').dataTree({
                delimeter: "-"
            });
        });
    })
</script>