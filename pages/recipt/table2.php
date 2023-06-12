<?php
$statusObj = new Statuses();
$statuses = $statusObj->getOwnerStatus($shop['id']);
$itemStatus = [];
$orderStatus = [];
$serviceStatus = [];

foreach ($statuses as  $value) {
    if ($value['type'] == 'ORDER') {
        $orderStatus[] = $value;
    }
    if ($value['type'] == 'ITEM') {
        $itemStatus[] = $value;
    }
    if ($value['type'] == 'SERVICE') {
        $serviceStatus[] = $value;
    }
}

?>
<?php if ($mode === 'edit') { ?>
    <a href="#" class="btn btn-primary" ng-click="checkout()"><img width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/001-checkout.svg" alt="" /> Checkout</a>
<?php } ?>
<table class="table table-striped recipt-table">
    <thead>
        <tr>
            <th>Sr.# 111</th>
            <th>Description</th>
            <th ng-if="show_discount">Discount %</th>
            <th>Unit Price</th>
            <th>Add Qty</th>
            <th>Qty</th>
            <th>Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr ng-repeat-start="cart in items track by $index" id="product-{{$index + 1}}">
            <td>{{$index + 1}}</td>
            <td>
                {{cart.full_name}}
                <input type="text" ng-change="calculateSum()" ng-model="cart.description" placeholder="Description" ng-if="cart.show" class="form-control">
            </td>
            <td width="100" ng-if="show_discount">
                <input type="number" class="form-control input-add-dist" ng-model="cart.discount_value" ng-change="calculateSum()">
            </td>
            <td>
                <span ng-if="cart.discount">
                    {{cart.discount_percent ? cart.discount_percent : ''}}
                    <del class="text-danger">{{cart.price | number: 0}}</del> / </span>
                <span class="text-success" ng-if="cart.product_type == 1">{{(cart.price - cart.discount) | number: 0}}</span>
                <input style="max-width: 120px" ng-model="cart.price" ng-if="cart.product_type == 2 || cart.product_type == 3" ng-change="calculateSum()" class="form-control" />
            </td>
            <td width="100"><input type="search" ng-model="newqty" class="form-control input-qty" on-enter-press="addMoreQty(cart, newqty, $event)"></td>
            <td>
                <div class="quantity">
                    <a href="#" class="quantity__minus" ng-click="subQty(cart)"><span>-</span></a>
                    <input class="quantity__input" type="text" ng-model="qty" ng-value=" cart.qty | number " ng-change="directlyAdd(qty, cart)">
                    <a href="#" class="quantity__plus" ng-click="addQty(cart)"><span>+</span></a>
                </div>
            </td>
            <td width="130">
                <input class="form-control text-center" type="number" ng-model="addprice" ng-change="directlyPrice(addprice, cart)" ng-keydown="initCheckKeypress($event)">
            </td>
            <td>
                {{(cart.price - cart.discount) * cart.qty | number: 0}}
                <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
            </td>
        </tr>
        <tr ng-if="cart.product_type == 2">
            <td colspan="2"><strong>Services Items</strong></td>
            <td>
                <input type="text" class="form-control" ng-model="cart.service" placeholder="Add Service" uib-typeahead="address as address.full_name for address in searchServices($viewValue)" typeahead-on-select="selectService($item, cart)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
            </td>
            <td colspan="2">
                <input type="text" class="form-control" ng-model="cart.raw" placeholder="Add Raw material" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, 3)" typeahead-on-select="selectRaw($item, cart)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
            </td>
            <td colspan="2"></td>
        </tr>
        <tr ng-repeat="service in cart.services track by $index">
            <td>S.#{{$index + 1}}</td>
            <td>
                <input type="text" class="form-control" ng-model="service.service" placeholder="Search Service" uib-typeahead="address as address.full_name for address in searchServices($viewValue)" typeahead-on-select="selectService($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
            </td>
            <td>
                <input type="text" class="form-control" ng-model="service.employeeSelect" placeholder="Search Employee" uib-typeahead="address as address.full_name for address in searchEmployee($viewValue)" typeahead-on-select="selectEmployee($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
            </td>
            <td>
                <input type="text" class="form-control" ng-model="service.cost" placeholder="COST" />
            </td>
            <td>
                <input type="text" class="form-control" ng-model="service.price" placeholder="PRICE" />
            </td>
            <td>
                <select class="form-control" ng-model="service.status" placeholder="status">
                    <option value="">-- status --</option>
                    <?php
                    foreach ($serviceStatus as $value) { ?>
                        <option value="<?php echo $value['id']; ?>"><?php echo $value['title']; ?></option>
                    <?php }
                    ?>
                </select>
            </td>
        </tr>
        <tr ng-repeat="service in cart.raw_items track by $index">
            <td>Raw #{{$index + 1}}</td>
            <td>
                <input type="text" class="form-control" ng-model="service.product" placeholder="Search Raw" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, 3)" typeahead-on-select="selectService($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
            </td>
            <td>
                <input type="text" class="form-control" ng-model="service.price" placeholder="Price" />
            </td>
            <td>
                <input type="text" class="form-control" ng-model="service.qty" placeholder="QTY" />
            </td>
            <td>
                {{service.price * service.qty | number: 2 }}
            </td>
        </tr>
        <tr ng-if="cart.product_type == 2" ng-repeat-end="cart in items track by $index" id="product-{{$index + 1}}">
            <td>
                Delivery
            </td>
            <td>
                <input placeholder="Expected Dates" min="minDate" type="text" date-range-picker class="form-control" ng-model="cart.expected_dates" options="{ autoApply: true, changeCallback: calculateSum(), startDate: minDate }">
            </td>
            <td colspan="2">
                <input type="text" class="form-control" ng-model="cart.description" placeholder="Instructions">
            </td>
            <td>
                <select name="item_status" id="item_status" class="form-control" ng-model="cart.item_status" placeholder="item_status">
                    <option value="">-- status --</option>
                    <?php
                    foreach ($itemStatus as $value) { ?>
                        <option value="<?php echo $value['id']; ?>"><?php echo $value['title']; ?></option>
                    <?php }
                    ?>
                </select>
            </td>
            <td>
                <select name="priority" id="priority" class="form-control" ng-model="cart.priority" placeholder="priority">
                    <option value="">-- priority --</option>
                    <?php
                    foreach ($orderPriority as $key => $value) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php }
                    ?>
                </select>
            </td>
            <td></td>
        </tr>
        <tr>
            <td colspan="{{show_discount ?  (6) : (5)}}" rowspan="{{8 + modes.length}}">
                <div class="row">
                    <div class="col-md-4">
                        <p>
                            <label>Order Status</label>
                            <select name="status_id" id="status_id" ng-model="status_id" class="form-control">
                                <?php
                                foreach ($orderStatus as $value) { ?>
                                    <option value="<?php echo $value['id']; ?>"><?php echo $value['title']; ?></option>
                                <?php }
                                ?>
                            </select>
                        <p>
                    </div>
                    <div class="col-md-4">
                        <label>Expected Date</label>
                        <p><input class="form-control" date-range-picker placeholder="Expected Date" ng-model="expected_delivery_date" options="{autoApply: true, singleDatePicker: true, minDate: minDate}" /></p>
                    </div>
                    <div class="col-md-4">
                        <label>Order Reference</label>
                        <p><input class="form-control" placeholder="Reference No" ng-model="ref_no" /></p>
                    </div>
                </div>
                <textarea class="form-control" rows="10" placeholder="Summery" ng-model="summery"></textarea>
            </td>
            <td class="text-right">Sub Total</td>
            <td>{{(subTotal + discountPercentValue) | number: 0}}</td>
        </tr>
        <tr>
            <td width="150" class="text-right">Add Discount</td>
            <td width="150"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
        </tr>
        <tr>
            <td class="text-right" style="color: red; front-weight: bold;">Additional Discount</td>
            <td style="color: red; front-weight: bold;"><strong>{{discount | number: 0}}</strong></td>
        </tr>
        <tr>
            <td class="text-right" style="color: red; front-weight: bold;">Total Discount</td>
            <td style="color: red; front-weight: bold;"><strong>{{(discount + discountPercentValue) | number: 0}}</strong></td>
        </tr>
        <tr>
            <td class="text-right">Grand Total</td>
            <td class="text-success">{{grandTotal | number: 0}}</td>
        </tr>
        <tr ng-repeat="m in modes">
            <td class="text-right text-success" style="front-weight: bold;">Pay with {{modeNames[m.id]}}</td>
            <td>
                <input class="form-control" type="number" ng-change="calculatePayment(payWith)" ng-model="payWith[m.id].amount" />
            </td>
        </tr>
        <tr>
            <td class="text-right">Balance</td>
            <td>{{grandTotal - payment_total | number: 0}}</td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <th colspan="{{show_discount ? 8 : 7 }}" class="text-right">
                <a href="#" class="btn btn-success pull-left" ng-click="park()">Park For Now</a>
                <!-- <div class="btn-group">
                    <label class="btn btn-default" ng-repeat="li in modes">
                        <input type="radio" name="mode" ng-model="payment_mode" ng-value="li.id" ng-change="printValue(li)">
                        {{li.title}}
                    </label>
                </div> -->
                <?php if ($mode === 'edit') { ?>
                    <a href="#" class="btn btn-success" ng-disabled="loading" ng-click="park()">Park For Now</a>
                <?php } else { ?>
                    <a href="#" class="btn btn-primary" ng-disabled="loading" ng-click="checkout()"><img width="24" height="24" src="<?php echo SITE_URL; ?>assets/img/svg/001-checkout.svg" alt="" /> Checkout</a>
                <?php } ?>
            </th>
        </tr>
    </tbody>
</table>