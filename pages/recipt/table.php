<tr>
    <th width="70">Sr.#</th>
    <th width="320">Description</th>
    <th width="120" ng-if="show_discount">Discount</th>
    <th width="140">Unit Price</th>
    <th width="120">Add Qty</th>
    <th width="180" style="text-align: center;">Qty</th>
    <th width="150">Total</th>
    <th></th>
</tr>
</thead>
<tbody>
    <tr ng-repeat="cart in items track by $index" id="product-{{$index + 1}}">
        <td width="70">{{$index + 1}}</td>
        <td width="320">
            {{cart.full_name}}
            <input type="text" ng-change="calculateSum()" ng-model="cart.description" placeholder="Description" ng-if="cart.show" class="form-control">
        </td>
        <td width="120" width="120" ng-if="show_discount">

            <div class="input-group">
                <input type="number" class="form-control input-add-dist" ng-model="cart.discount_value" ng-change="calculateSum()" style="padding-right: 6px">
                <span class="dropdown input-group-btn">
                    <button class="dropdown-toggle btn btn-default" data-toggle="dropdown" style="padding-inline: 8px">{{cart.discount_type == 2 ? 'FIX' : '%'}}</button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0)" ng-click="cart.discount_type = 1; calculateSum()">%</a></li>
                        <li><a href="javascript:void(0)" ng-click="cart.discount_type = 2; calculateSum()">Fix</a></li>
                    </ul>
                </span>
            </div>


        </td>
        <td width="140">
            <span ng-if="cart.discount">
                {{cart.discount_percent ? cart.discount_percent : ''}}
                <del class="text-danger">{{cart.price | number: 2}}</del> / </span>
            <span class="text-success">{{(cart.price - cart.discount) | number: 2}}</span>
        </td>
        <td width="120"><input type="search" ng-model="newqty" class="form-control input-qty" on-enter-press="addMoreQty(cart, newqty, $event)"></td>
        <td width="180">
            <div class="quantity">
                <a href="#" class="quantity__minus" ng-click="subQty(cart)"><span>-</span></a>
                <input class="quantity__input" type="text" ng-model="qty" ng-value=" cart.qty | number " ng-change="directlyAdd(qty, cart)">
                <a href="#" class="quantity__plus" ng-click="addQty(cart)"><span>+</span></a>
            </div>
        </td>
        <td width="150">
            <input class="form-control text-center" type="number" ng-model="addprice" ng-change="directlyPrice(addprice, cart)" ng-keydown="initCheckKeypress($event)">
        </td>
        <td>
            {{(cart.price - cart.discount) * cart.qty | number: 2}}
            <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
        </td>
    </tr>
    <tr>
        <td class="text-right" colspan="{{show_discount ?  (6) : (5)}}" rowspan="{{8 + modes.length}}">
            <p><input class="form-control" placeholder="Reference No" ng-model="ref_no" /></p>
            <textarea class="form-control" rows="10" placeholder="Summery" ng-model="summery"></textarea>
        </td>
        <td class="text-right">Sub Total</td>
        <td>{{(subTotal + discountPercentValue) | number: 2}}</td>
    </tr>
    <tr>
        <td width="150" class="text-right">Add Discount</td>
        <td width="150"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
    </tr>
    <tr>
        <td class="text-right" style="color: red; front-weight: bold;">Additional Discount</td>
        <td style="color: red; front-weight: bold;"><strong>{{discount | number: 2}}</strong></td>
    </tr>
    <tr>
        <td class="text-right" style="color: red; front-weight: bold;">Total Discount</td>
        <td style="color: red; front-weight: bold;"><strong>{{(discount + discountPercentValue) | number: 2}}</strong></td>
    </tr>
    <tr>
        <td class="text-right">Grand Total</td>
        <td class="text-success">{{grandTotal | number: 2}}</td>
    </tr>
    <tr ng-repeat="m in modes">
        <td class="text-right text-success" style="front-weight: bold;">Pay with {{modeNames[m.id]}}</td>
        <td>
            <input class="form-control" type="number" ng-change="calculatePayment(payWith)" ng-model="payWith[m.id].amount" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Balance</td>
        <td>{{grandTotal - payment_total | number: 2}}</td>
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