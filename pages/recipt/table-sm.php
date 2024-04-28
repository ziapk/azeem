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
    <th>Description</th>
    <th style="text-align: right;">
        <a href="#" class="btn btn-xs btn-danger" ng-click="deleteAll(indexes, items)">Del</a> |
        <a href="#" class="btn btn-xs btn-primary" ng-click="inActiveAll(indexes, items)">InAct</a>
    </th>
</tr>
</thead>
<tbody>
    <tr ng-repeat="cart in items track by $index" id="sm-product-{{cart.srno}}" ng-if="cart.product_type != 5">
        <td colspan="2">
            <div class="clearfix form-group">
                <label><input ng-change="setList(selectedList)" type="checkbox" ng-model="selectedList[cart.srno]"></label>
                <?php if ($userData['role'] === 'owner') { ?>
                    <span class="dropdown pull-right">
                        <button class="dropdown-toggle btn btn-xs btn-default" data-toggle="dropdown" style="padding-inline: 8px; padding-block: 2px"><span class="fa fa-caret-down"></span></button>
                        <form ng-submit="submitCode(cart)" class="dropdown-menu " style="padding: 10px; width: 300px">
                            <div class="input-group" style="width: 100%">
                                <input type="text" placeholder="Title" ng-model="cart.newTitle" type="text" class="form-control">
                                <span class="input-group-btn" style="width: 50%">
                                    <ui-select custom-dropdown ng-model="cart.publisher" theme="bootstrap" ng-disabled="disabled" reset-search-input="false" title="Choose a publisher">
                                        <ui-select-match placeholder="Enter a publisher...">{{$select.selected.full_name}}</ui-select-match>
                                        <ui-select-choices repeat="address in publishers track by $index" refresh="refreshPublishers($select.search)" refresh-delay="0">
                                            <div style="white-space: wrap;" ng-bind-html="address.full_name | highlight: $select.search"></div>
                                        </ui-select-choices>
                                    </ui-select>
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-btn" style="width: 50%">
                                    <input type="text" placeholder="Rack No" ng-model="cart.rackNo" type="text" class="form-control">
                                </span>
                                <span class="input-group-btn" style="width: 50%">
                                    <input type="text" placeholder="Author" ng-model="cart.author" type="text" class="form-control">
                                </span>
                            </div>
                            <div class="input-group">
                                <input type="text" placeholder="Bar Code" ng-model="cart.newBarCode" type="text" class="form-control">
                                <span class="input-group-btn" style="width: 50%">
                                    <input type="text" placeholder="WH Price" ng-model="cart.wh_price" ng-value="cart.wh_price" type="text" class="form-control">
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-btn" style="width: 50%">
                                    <input type="text" placeholder="Price" ng-model="cart.newPrice" ng-value="cart.price" type="text" class="form-control">
                                </span>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </span>
                            </div>
                        </form>
                    </span>
                <?php } ?>
                <?php if ($userData['role'] === 'manager') { ?>
                    <span class="dropdown pull-right">
                        <button class="dropdown-toggle btn btn-xs btn-default" data-toggle="dropdown" style="padding-inline: 8px; padding-block: 2px"><span class="fa fa-caret-down"></span></button>
                        <form ng-submit="submitCode(cart)" class="dropdown-menu " style="padding: 10px; width: 300px">
                            <div class="input-group">
                                <input type="text" placeholder="Bar Code" ng-model="cart.newBarCode" type="text" class="form-control">
                                <span class="input-group-btn" style="width: 40%">
                                    <input type="text" placeholder="Rack No" ng-model="cart.rackNo" type="text" class="form-control">
                                </span>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </span>
                            </div>
                        </form>
                    </span>
                <?php } ?>
                <a href="#" class="btn btn-xs btn-danger pull-right" style="margin-right: 4px" ng-click="remove(cart)"><span class="fa fa-remove"></span></a>
                {{cart.full_name}} | <strong class="text-danger">{{cart.rackNumbers}}</strong> | <strong class="text-success">{{cart.pack_size}}B</strong>
            </div>
            <input type="text" ng-change="calculateSum()" ng-model="cart.description" placeholder="Description" ng-if="cart.show" class="form-control form-group">
            <div class="quantity form-group" style="align-items: center; justify-content: flex-end;">
                <div class="input-group" ng-if="show_discount" style="margin-right: 16px">
                    <input type="number" class="form-control input-add-dist" ng-model="cart.discount_value" ng-change="calculateSum()" style="padding-right: 6px">
                    <span class="input-group-btn"><!-- class="dropdown input-group-btn" -->
                        <button class="btn btn-default" style="padding-inline: 8px" ng-click="cart.discount_type = (cart.discount_type == 1 ? 2 : 1); calculateSum();">{{cart.discount_type == 2 ? 'FIX' : '%'}}</button><!-- data-toggle="dropdown" -->
                    </span>
                </div>
                <a href="#" class="quantity__minus" ng-click="subQty(cart)"><span>-</span></a>
                <input class="quantity__input" type="number" ng-model="qty" ng-value=" cart.qty | number " ng-change="directlyAdd(qty, cart)" ng-keydown="initCheckKeypress($event)">
                <a href="#" class="quantity__plus" ng-click="addQty(cart)"><span>+</span></a>
            </div>
            <div style="display: flex; margin-left: 5px; justify-content: flex-end; flex: 1; font-size: 16px">
                <input ng-model="cart.price" class="form-control" ng-if="cart.product_type == 2 || wsp" ng-change="calculateSum()" />
                <span ng-if="cart.discount" ng-if="cart.product_type != 2 && !wsp">
                    {{cart.discount_percent ? cart.discount_percent : ''}}
                    <del class="text-danger">{{cart.price | number: 2}}</del> / </span>
                <span ng-if="cart.product_type != 2 && !wsp" class="text-success">{{(cart.price - cart.discount) | number: 2}}</span>
                <strong style="font-size: 20px; flex: 1; text-align: right">{{(cart.price - cart.discount) * cart.qty | number: 2}}</strong>
            </div>
        </td>
    </tr>

    <tr ng-repeat="cart in items track by $index" ng-if="cart.product_type == 5">
        <td>
            <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
        </td>
        <td class="text-right">{{cart.full_name}}</td>
        <td><input type="text" ng-model="cart.price" class="form-control" ng-change="calculateSum()"></td>
    </tr>
    <tr>
        <td colspan="2">
            <textarea class="form-control" rows="3" placeholder="Summery" ng-model="summery"></textarea>
        </td>
    </tr>
    <tr>
        <td class="text-right">Sub Total</td>
        <td class="text-right" style="font-weight: bold; font-size: 1.5em">{{(subTotal + discountPercentValue) | number: 2}}</td>
    </tr>
    <tr>
        <td class="text-right">Add Discount</td>
        <td td><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
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