<table class="table">
    <thead>
        <tr>
            <th>Pin</th>
            <th>M. Qty</th>
            <th width="200">Product Id</th>
            <th>Product Name</th>
            <th width="100">Dist %</th>
            <th width="100">P. Price</th>
            <th width="100">S. Price</th>
            <th width="100">Qty</th>
            <th width="100">Total</th>
            <th></th>
        </tr>
        <tr>
            <td colspan="9"><input type="text" id="searchProduct" class="form-control" ng-model="product" placeholder="Search Product to add" typeahead-on-select="selectProduct($item, row)" uib-typeahead="address as address.full_name for address in searchProduct($viewValue)" typeahead-template-url="row2.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1" class="form-control" ng-model="row.product_name" ng-model-options="{debounce: 100}" /></td>
            <th colspan="1"><label><span style="vertical-align: middle">Qty</span> <span style="vertical-align: middle; margin-left: 4px;"><input type="checkbox" name="qf" ng-model="qf"><span></label></th>
        </tr>
    </thead>
    <tbody>
        <tr ng-repeat="row in items track by $index" id="product-{{$index + 1}}">
            <td><input type="checkbox" ng-model="row.pin" /></td>
            <td><input type="text" class="form-control" ng-model="row.minQty" /></td>
            <td><input type="text" class="form-control" ng-model="row.id" /></td>
            <td>
                <input type="text" class="form-control" ng-model="row.full_name" placeholder="Product title" />
            </td>
            <td><input type="number" class="form-control discount-field" ng-change="calculateSum()" ng-model="row.discount" /></td>
            <td><input type="number" class="form-control" ng-change="calculatePercent(row)" ng-model="row.pprice" /></td>
            <td width="100"><input type="number" class="form-control" ng-model="row.price" /></td>
            <td width="100"><input type="number" class="form-control" ng-change="calculateSum()" ng-model="row.qty" ng-keydown="initCheckKeypress($event)" /></td>
            <td width="100">{{row.total | number: 0}}</td>
            <td width="60"><a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove($index)">Delete</a></td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <th rowspan="6" colspan="6"></th>
            <th class="text-right" colspan="2">Sub Total</th>
            <th colspan="2">{{subTotal}}</th>
        </tr>
        <tr>
            <th class="text-right" colspan="2">Disc.</th>
            <th colspan="2" width="200"><input type="number" ng-model="discount" class="form-control" ng-change="addDiscount(discount)"></th>
        </tr>
        <tr>
            <th class="text-right" colspan="2">Grand Total</th>
            <th colspan="2">{{grandTotal}}</th>
        </tr>
        <tr>
            <th class="text-right" colspan="2">Pay with Credit</th>
            <th colspan="2" width="200"><input type="number" ng-model="payment_with_credit" class="form-control"></th>
        </tr>
        <tr>
            <th class="text-right" colspan="2">Pay Direct</th>
            <th colspan="2" width="200"><input type="number" ng-model="payment_amount" class="form-control"></th>
        </tr>
        <tr>
            <th class="text-right" colspan="2">Balance</th>
            <th colspan="2" width="200">{{grandTotal - payment_amount - payment_with_credit}}</th>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <th colspan="4" class="text-left">
                <a href="#" class="btn btn-success" style="vertical-align: middle" ng-click="park()">Park For Now</a>
                <label class="text-danger" style="padding-inline: 10px; vertical-align: middle"><input type="checkbox" ng-model="createDemand"> Create Demand as well</label>
            </th>
            <th colspan="6" class="text-right">
                <div class="btn-group">
                    <label class="btn btn-default" ng-repeat="li in modes">
                        <input type="radio" name="mode" ng-model="payment_mode" ng-value="li.id" ng-change="printValue(li)">
                        {{li.title}}
                    </label>
                </div>
                <a href="#" class="btn btn-primary" ng-click="checkout()"><img width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/001-checkout.svg" alt="" /> Checkout</a>
            </th>
        </tr>
    </tbody>
</table>