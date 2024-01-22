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
<tr>
    <th width="70">Sr.#</th>
    <th width="450">Description</th>
    <th width="120" ng-if="show_discount">Discount</th>
    <th width="100">Unit Price</th>
    <th width="180" style="text-align: center;">Qty</th>
    <th width="180" style="text-align: right;">
        <a href="#" class="btn btn-xs btn-danger" ng-click="deleteAll(indexes, items)">Del</a> |
        <a href="#" class="btn btn-xs btn-primary" ng-click="inActiveAll(indexes, items)">InAct</a>
    </th>
</tr>
</thead>
<tbody>
    <tr ng-repeat-start="cart in items track by $index" id="product-{{cart.srno}}" ng-if="cart.product_type != 5">
        <td width="70">
            <label><input ng-change="setList(selectedList)" type="checkbox" ng-model="selectedList[cart.srno]">{{cart.srno}}</label>
        </td>
        <td width="400">
            {{cart.full_name}} | <strong class="text-danger">{{cart.rackNumbers}}</strong> | <strong class="text-success">{{cart.pack_size}}B</strong>
            <?php if ($userData['role'] === 'owner') { ?>
                <span class="dropdown">
                    <button class="dropdown-toggle btn btn-default" data-toggle="dropdown" style="padding-inline: 8px"><span class="fa fa-caret-down"></span></button>
                    <form ng-submit="submitCode(cart)" class="dropdown-menu" style="padding: 10px; width: 450px">
                        <div class="input-group" style="width: 100%">
                            <input type="text" placeholder="Title" ng-model="cart.newTitle" type="text" class="form-control">
                            <span class="input-group-btn" style="width: 100px">
                                <ui-select custom-dropdown ng-model="cart.publisher" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a publisher">
                                    <ui-select-match placeholder="Enter a publisher...">{{$select.selected.full_name}}</ui-select-match>
                                    <ui-select-choices repeat="address in publishers track by $index" refresh="refreshPublishers($select.search)" refresh-delay="0">
                                        <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                                    </ui-select-choices>
                                </ui-select>
                            </span>
                            <span class="input-group-btn" style="width: 104px">
                                <input type="text" placeholder="Rack No" ng-model="cart.rackNo" type="text" class="form-control">
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-btn" style="width: 100px">
                                <input type="text" placeholder="Author" ng-model="cart.author" type="text" class="form-control">
                            </span>
                            <input type="text" placeholder="Bar Code" ng-model="cart.newBarCode" type="text" class="form-control">
                            <span class="input-group-btn" style="width: 80px">
                                <input type="text" placeholder="WH Price" ng-model="cart.wh_price" ng-value="cart.wh_price" type="text" class="form-control">
                            </span>
                            <span class="input-group-btn" style="width: 80px">
                                <input type="text" placeholder="Price" ng-model="cart.newPrice" ng-value="cart.price" type="text" class="form-control">
                            </span>
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </span>
                        </div>
                    </form>
                </span>
            <?php } ?>
            <input type="text" ng-change="calculateSum()" ng-model="cart.description" placeholder="Description" ng-if="cart.show" class="form-control">
        </td>
        <td width="120" ng-if="show_discount">
            <div class="input-group">
                <input type="number" class="form-control input-add-dist" ng-model="cart.discount_value" ng-change="calculateSum()" style="padding-right: 6px">
                <span class="input-group-btn"><!-- class="dropdown input-group-btn" -->
                    <button class="btn btn-default" style="padding-inline: 8px" ng-click="cart.discount_type = (cart.discount_type == 1 ? 2 : 1); calculateSum();">{{cart.discount_type == 2 ? 'FIX' : '%'}}</button><!-- data-toggle="dropdown" -->
                    <!-- <ul class="dropdown-menu">
                        <li><a href="javascript:void(0)" ng-click="cart.discount_type = 1; calculateSum()">%</a></li>
                        <li><a href="javascript:void(0)" ng-click="cart.discount_type = 2; calculateSum()">Fix</a></li>
                    </ul> -->
                </span>
            </div>
        </td>
        <td width="100">
            <div style="display: flex;">
                <input ng-model="cart.price" class="form-control" ng-if="cart.product_type == 2 || wsp" ng-change="calculateSum()" />
                <span ng-if="cart.discount" ng-if="cart.product_type != 2 && !wsp">
                    {{cart.discount_percent ? cart.discount_percent : ''}}
                    <del class="text-danger">{{cart.price | number: 2}}</del> / </span>
                <span ng-if="cart.product_type != 2 && !wsp" class="text-success">{{(cart.price - cart.discount) | number: 2}}</span>
            </div>
        </td>
        <!-- <td width="120"><input type="search" ng-model="newqty" class="form-control input-qty" on-enter-press="addMoreQty(cart, newqty, $event)"></td> -->
        <td width="180">
            <div class="quantity">
                <a href="#" class="quantity__minus" ng-click="subQty(cart)"><span>-</span></a>
                <input class="quantity__input" type="number" ng-model="qty" ng-value=" cart.qty | number " ng-change="directlyAdd(qty, cart)" ng-keydown="initCheckKeypress($event)">
                <a href="#" class="quantity__plus" ng-click="addQty(cart)"><span>+</span></a>
            </div>
        </td>
        <!-- <td width="100">
            <input class="form-control text-center" type="number" ng-model="addprice" ng-change="directlyPrice(addprice, cart)" ng-keydown="initCheckKeypress($event)">
        </td> -->
        <td width="180" style="text-align: right;">
            <strong>{{(cart.price - cart.discount) * cart.qty | number: 2}}</strong>
            <a style="margin-left: 8px;" href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
        </td>
    </tr>
    <tr ng-if="show_bundle && cart.product_type != 5" ng-repeat-end="cart in items track by $index" class="row-expected">
        <td colspan="8">
            <table style="margin-left: auto;" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="100px" style="padding: 8px" class="text-right">Bundles</td>
                    <td colspan="{{show_discount ? 2 : 1}}">
                        <input type="number" class="form-control" ng-model="cart.pack_qty" placeholder="No of Bundles" ng-change="calculateSum()" />
                    </td>
                    <td class="text-right" style="padding: 8px">Bundle Size</td>
                    <td>
                        <input type="number" class="form-control" ng-model="cart.pack_size" placeholder="Products In Bundle" ng-change="calculateSum()" />
                    </td>
                    <td class="text-right" style="padding: 8px">Ex. Items</td>
                    <td>
                        <input type="number" class="form-control" ng-model="cart.unpack_qty" placeholder="Extra Products" ng-change="calculateSum()" />
                    </td>
                    <td style="padding: 8px">Total Qty</td>
                    <td style="padding: 8px; font-weight: bold; font-size: 1.5em">{{cart.qty + cart.unpack_qty}}</td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- <tr ng-if="cart.product_type == 2">
        <td colspan="2"><strong>Services Items</strong></td>
        <td>
            <input type="text" class="form-control" ng-model="cart.service" placeholder="Add Service" uib-typeahead="address as address.full_name for address in searchServices($viewValue)" typeahead-on-select="selectService($item, cart)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
        </td>
        <td colspan="2">
            <input type="text" class="form-control" ng-model="cart.raw" placeholder="Add Raw material" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, 3)" typeahead-on-select="selectRaw($item, cart)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
        </td>
        <td colspan="6"></td>
    </tr> -->
    <!-- <tr ng-repeat="service in cart.services track by $index" class="row-service">
        <td style="padding-left: 40px; text-align: right">S.#{{$index + 1}}</td>
        <td>
            <input type="text" class="form-control" ng-model="service.service" placeholder="Search Service" uib-typeahead="address as address.full_name for address in searchServices($viewValue)" typeahead-on-select="selectService($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
        </td>
        <td>
            <input type="text" class="form-control" ng-model="service.employeeSelect" placeholder="Search Employee" uib-typeahead="address as address.full_name for address in searchEmployee($viewValue)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
        </td>
        <td>
            <input type="text" class="form-control" ng-model="service.cost" placeholder="COST" />
        </td>
        <td>
            <input type="text" class="form-control" ng-change="calculateSum()" ng-model="service.price" placeholder="PRICE" />
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
        <td colspan="4"></td>
    </tr>
    <tr ng-repeat="service in cart.raw_items track by $index" class="row-raw">
        <td style="padding-left: 40px; text-align: right">Raw #{{$index + 1}}</td>
        <td>
            <input type="text" class="form-control" ng-model="service.product" placeholder="Search Raw" uib-typeahead="address as address.full_name for address in searchProduct($viewValue, 3)" typeahead-on-select="selectService($item)" ng-model-options="{debounce: 500}" typeahead-template-url="row.html" class="form-control" typeahead-show-hint="true" typeahead-min-length="1">
        </td>
        <td>
            <input type="text" class="form-control" ng-change="calculateSum()" ng-model="service.price" placeholder="Price" />
        </td>
        <td>
            <input type="text" class="form-control" ng-change="calculateSum()" ng-model="service.qty" placeholder="QTY" />
        </td>
        <td>
            {{service.price * service.qty | number: 2 }}
        </td>
        <td colspan="6"></td>
    </tr> -->
    <!-- <tr ng-if="cart.product_type == 2" ng-repeat-end="cart in items track by $index" id="product-{{$index + 1}}" class="row-expected">
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
    </tr> -->
    <tr ng-repeat="cart in items track by $index" ng-if="cart.product_type == 5">
        <td colspan="{{show_discount ?  (4) : (3)}}">
            <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
        </td>
        <td width="150" class="text-right">{{cart.full_name}}</td>
        <td width="150"><input type="text" ng-model="cart.price" class="form-control" ng-change="calculateSum()"></td>
    </tr>
    <tr>
        <td colspan="{{show_discount ?  (4) : (3)}}" rowspan="{{6 + modes.length}}">
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
        <td class="text-right" style="font-weight: bold; font-size: 1.5em">{{(subTotal + discountPercentValue) | number: 2}}</td>
    </tr>
    <tr>
        <td width="150" class="text-right">Add Discount</td>
        <td width="150"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
    </tr>
    <tr>
        <td class="text-right" style="color: red; font-weight: bold;">Additional Discount</td>
        <td style="color: red; font-weight: bold; font-size: 1.5em" class="text-right"><strong>{{discount | number: 2}}</strong></td>
    </tr>
    <tr>
        <td class="text-right" style="color: red; font-weight: bold;">Total Discount</td>
        <td style="color: red; font-weight: bold; font-size: 1.5em" class="text-right"><strong>{{(discount + discountPercentValue) | number: 2}}</strong></td>
    </tr>
    <tr ng-if="gst">
        <td class="text-right text-mute">GST {{gst}}%</td>
        <td style="font-size: 1.5em" class="text-right text-mute"><strong>{{(payment_amount_before_tax * (gst / 100)).toFixed(0) | number: 2}}</strong></td>
    </tr>
    <tr ng-if="service_charges">
        <td class="text-right text-mute">Service Charges {{service_charges}}%</td>
        <td style="font-size: 1.5em" class="text-right text-mute"><strong>{{payment_amount_before_tax * (service_charges / 100) | number: 2}}</strong></td>
    </tr>
    <tr>
        <td class="text-right">Grand Total</td>
        <td class="text-success text-right" style="font-weight: bold; font-size: 1.5em">{{grandTotal | number: 2}}</td>
    </tr>
    <tr ng-repeat="m in modes">
        <td class="text-right text-success" style="font-weight: bold;">Pay with {{modeNames[m.id]}}</td>
        <td>
            <input class="form-control" type="number" ng-change="calculatePayment(payWith)" ng-model="payWith[m.id].amount" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Balance</td>
        <td class="text-right" style="font-weight: bold; font-size: 1.5em">{{grandTotal - payment_total | number: 2}}</td>
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


<script type="text/ng-template" id="row.html">
    <a style="display: flex; align-items: center">
        <span style="margin-right: auto; flex: 1" class="{{match.model.code ? 'text-danger' : ''}}" ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span>
        <span ng-if="match.model.wh_price" class="label" style="font-size: 14px">{{match.model.wh_price}}</span><span ng-if="match.model.wh_price">|</span><span ng-if="match.model.pprice" class="label" style="font-size: 14px">{{match.model.pprice}}</span><span ng-if="match.model.pprice">|</span><span ng-if="match.model.pack_size" class="label label-primary" style="font-size: 14px">{{match.model.pack_size}}B</span><span ng-if="match.model.pack_size">|</span><span class="label label-success" style="margin-left: auto; font-size: 14px">{{match.model.qty}}</span> | <span class="label label-danger" style="font-size: 14px">{{match.model.price}}</span>
    </a>
</script>
<script type="text/ng-template" id="customer.html">
    <a class="clearfix" style="border-bottom: 1px solid #ccc; display: block">
      <span ng-bind-html="match.model.full_name | uibTypeaheadHighlight:query"></span><br />
      <small><em>{{match.model.company}}</em></small>
  </a>
</script>