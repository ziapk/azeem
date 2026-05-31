<?php
include_once dirname(__FILE__) . '/../../include/settings.php';


$cat = new Categories();
$stores = new Store();
$publisherObj = new Publishers();

$publishers = $publisherObj->getPublishersPagination(['page' => 1, 'perPage' => 100000, 'shopId' => $shop['id']]);

$groupNames = $cat->getGroupNames($shop['owner_id']);
$ownerStores = $stores->getOwnerStores($userData['id']);

$de = new DoubleEntry();
$shopAccounts = new ShopAccounts();
$accountsData = $shopAccounts->getSAs($shop['id']);
$store = [];
foreach ($accountsData as $a) {
    $store[$a['key_value']] = $a['account_id'];
}

$params['parent_ids'][] = $store['receivable'];
$params['parent_ids'][] = $store['payable'];


$accounts = $de->getAccountsByParentIds($params['parent_ids']);
foreach ($accounts as $k => $val) {
    $accounts[$k] = $val;
    $accounts[$k]['parent'] = $val['parent_id'] == $store['receivable'] ? 'Customers' : 'Suppliers';
}

echo mainHeader(['page' => 'reports']);

?>
<style>
    .uib-typeahead-match.active span.text-danger {
        background-color: #fff !important
    }
</style>
<div class="container" ng-controller="reportController">
    <form method="POST" action="print.php" target="_blank">
        <h4>Reports</h4>
        <div class="row datepicker-parent">
            <div class="col-sm-4 col-md-3 form-group">
                <label>Select Date/Range</label>
                <input class="form-control datepicker" type="text" />
                <input type="hidden" name="from" id="from">
                <input type="hidden" name="to" id="to">
            </div>
            <?php

            if ($userData['role'] == 'owner') { ?>
                <div class="col-sm-4 col-md-3 form-group">
                    <label>Select Shop</label>
                    <select class="form-control c-select" name="shopId">
                        <?php foreach ($ownerStores as $value) { ?>
                            <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
            <?php

            if ($userData['role'] == 'owner' || $userData['role'] == 'manager') { ?>
                <div class="col-sm-4 col-md-3 form-group">
                    <label>Select Account</label>
                    <ui-select custom-dropdown ng-model="account.selected" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose an Account">
                        <ui-select-match placeholder="Enter an account...">{{$select.selected.title}}</ui-select-match>
                        <ui-select-choices group-by="'parent'" repeat="address in accountsList track by $index" refresh="refreshAccounts($select.search)" refresh-delay="0">
                            <div style="white-space: wrap;" ng-bind-html="address.title | highlight: $select.search"></div>
                        </ui-select-choices>
                    </ui-select>
                    <input type="hidden" name="account_id" value="{{account.selected.id}}">
                </div>
                <div class="col-sm-4 col-md-3 form-group">
                    <label>Select Publisher</label>
                    <ui-select custom-dropdown ng-model="publisher.selected" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a publisher">
                        <ui-select-match placeholder="Enter a customer...">{{$select.selected.full_name}}</ui-select-match>
                        <ui-select-choices repeat="address in publisherList track by $index" refresh="refreshPublishers($select.search)" refresh-delay="0">
                            <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                        </ui-select-choices>
                    </ui-select>
                    <input type="hidden" name="publisher_id" value="{{publisher.selected.id}}">
                </div>

            <?php } ?>
            <div class="clearfix"></div>
            <div class="col-sm-4 col-md-3 form-group">
                <label>Select Product (Product Bill)</label>
                <input type="hidden" name="product_id" value="{{product.id}}">
                <input type="text" class="form-control" id="searchProduct" ng-model="product" placeholder="Search Products" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" ng-model-options="{debounce: 100}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
            </div>
            <div class="col-sm-4 col-md-3 form-group">
                <label>Select Report</label>
                <select class="form-control" name="reportType" ng-change="checkReport(reportType)" ng-model="reportType">
                    <option value="">Select a Report</option>
                    <?php 
                    
                    foreach ($groupedReports as $category => $reports) {
                        echo '<optgroup label="' . htmlspecialchars($category) . '">';
                        foreach ($reports as $key => $report) {
                            echo '<option value="' . $report['id'] . '">' . htmlspecialchars($report['title']) . '</option>';
                        }
                        echo '</optgroup>';
                    }
                    
                    ?>
                </select>
            </div>
        </div>
        <div class="row" ng-if="reportType == 8 || reportType == 9">
            <?php foreach ($groupNames as $group) { ?>
                <div class="col-md-3">
                    <label>
                        <input type="checkbox" name="groupName[]" value="<?php echo $group['groupName']; ?>">
                        <?php echo $group['groupName']; ?>
                    </label>
                </div>
            <?php } ?>
        </div>

        <div class="row" ng-if="reportType == 24">
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Select Products <small class="text-muted">(search and add multiple — or leave blank for all)</small></label>
                    <div class="form-control" style="height:auto; min-height:34px; padding:4px 6px;" ng-click="$event.target.querySelector && $event.target.tagName !== 'INPUT' && $event.currentTarget.querySelector('input').focus()">
                        <span class="label label-primary" ng-repeat="p in selectedProducts" style="display:inline-block; margin:2px; padding:4px 6px; font-size:13px;">
                            {{p.full_name}}
                            <a href="" ng-click="removeProduct($index); $event.preventDefault()" style="color:#fff; margin-left:4px; text-decoration:none;">&times;</a>
                        </span>
                        <input type="text"
                            ng-model="productIdsSearch"
                            placeholder="Search Products"
                            uib-typeahead="address as address.full_name for address in searchProduct($viewValue)"
                            ng-model-options="{debounce: 100}"
                            typeahead-template-url="row.html"
                            typeahead-show-hint="true"
                            typeahead-min-length="1"
                            typeahead-on-select="addProduct($item)"
                            style="border:none; outline:none; box-shadow:none; min-width:160px; display:inline-block; width:auto;">
                    </div>
                    <input type="hidden" name="product_ids" ng-value="selectedProductIds()">
                </div>
            </div>
        </div>

        <div class="input-group">
            <div class="input-group-btn">
                <input type="submit" value="Submit" name="report" class="btn btn-primary" />
            </div>
        </div>
    </form>
</div>


<script type="text/javascript">
    app.controller('reportController', function($scope, $http, $httpParamSerializerJQLike, $filter) {
        const a = $('.datepicker').daterangepicker({
            minDate: moment().subtract(2, 'year'),
            maxDate: moment().add(1, 'week'),
            parentEl: '.datepicker-parent',
        }, function(start, end, label) {
            $('#from').val(moment(start).format('YYYY-MM-DD'));
            $('#to').val(moment(end).format('YYYY-MM-DD'));

        });
        $('#from').val(moment(a.data().daterangepicker.startDate).format('YYYY-MM-DD'));
        $('#to').val(moment(a.data().daterangepicker.endDate).format('YYYY-MM-DD'));
        $scope.publisher = {};
        $scope.publisherList = <?php echo json_encode($publishers['records']); ?>;
        $scope.opublishersList = <?php echo json_encode($publishers['records']); ?>;
        $scope.account = {};
        $scope.accountsList = <?php echo json_encode($accounts); ?>;
        $scope.oaccountsList = <?php echo json_encode($accounts); ?>;
        $scope.refreshPublishers = search => {
            $scope.publisherList = $scope.opublishersList.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
        }
        $scope.refreshAccounts = search => {
            $scope.accountsList = $scope.oaccountsList.filter(r => r.title.toLowerCase().includes(search.toLowerCase()));
        }
        $scope.partialSearch = (name, query) => {
            const lowerQuery = query.toLowerCase();
            const lowerName = name.toLowerCase();
            let queryIndex = 0;
            for (let i = 0; i < lowerName.length; i++) {
                if (lowerName[i] === lowerQuery[queryIndex]) {
                    queryIndex++;
                    if (queryIndex === lowerQuery.length) return true;
                }
            }
            return false;
        }
        $scope.product = '';
        $scope.searchProduct = function(term) {
            const params = {};
            if ($scope.focus === true) {
                params.term = parseFloat(term.split('-')[0]);
                const item = window.mainList.records.filter(r => r.is_active == 1).find(r => r.id == params.term || r.code == params.term || r.barcode == params.term);
                return [];
            } else {
                const filteredArray = window.mainList.records.filter(r => r.is_active == 1).filter(r => r.id == term || r.code == term || r.searchString.split('|').pop()?.toLowerCase().includes(term?.toLowerCase()) || r.searchString.includes(term + '|') || r.searchString.includes('|' + term) || r.searchString.includes('|' + term + '|'));
                const secondfilteredArray = !filteredArray.length ? window.mainList.records.filter(r => r.is_active == 1).filter(obj => obj.searchString.toLowerCase().includes(term?.toLowerCase() || term)) : filteredArray;
                return secondfilteredArray.slice(0, 30);
            }
        }

        // Multiple product selection for reportType 24 (reuses searchProduct as the source)
        $scope.selectedProducts = [];
        $scope.productIdsSearch = '';
        $scope.addProduct = function(item) {
            if (item && !$scope.selectedProducts.some(p => p.id == item.id)) {
                $scope.selectedProducts.push(item);
            }
            $scope.productIdsSearch = '';
        };
        $scope.removeProduct = function(index) {
            $scope.selectedProducts.splice(index, 1);
        };
        $scope.selectedProductIds = function() {
            return $scope.selectedProducts.map(p => p.id).join(',');
        };
    });
</script>
<script type="text/ng-template" id="row.html">
    <a style="display: flex; justify-content: space-between; align-items: center">
        <span class="{{match.model.code ? 'text-danger' : ''}}" ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
        <span class="label label-danger" style="font-size: 14px">{{match.model.price}}</span>
    </a>
</script>

<?php echo mainFooter(); ?>