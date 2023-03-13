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
        <tr ng-repeat="cart in items track by $index" id="item-{{cart.id}}">
            <td>{{$index + 1}}</td>
            <td>
                {{cart.full_name}}
                <input type="text" ng-change="calculateSum()" ng-model="cart.description" placeholder="Description" ng-if="cart.show" class="form-control">
            </td>
            <td width="100" ng-if="show_discount">
                <input type="number" class="form-control" ng-model="cart.discount_value" ng-change="calculateSum()">
            </td>
            <td>
                <span ng-if="cart.discount">
                    {{cart.discount_percent ? cart.discount_percent : ''}}
                    <del class="text-danger">{{cart.price | number: 2}}</del> / </span>
                    <span class="text-success">{{(cart.price - cart.discount) | number: 2}}</span>
            </td>
            <td width="100"><input type="search" ng-model="newqty" class="form-control" on-enter-press="addMoreQty(cart, newqty, $event)"></td>
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
                {{(cart.price - cart.discount) * cart.qty | number: 2}}
                <a href="#" class="btn btn-xs btn-danger pull-right" ng-click="remove(cart)">Delete</a>
            </td>
        </tr>
        <tr>
            <td class="text-right" colspan="{{show_discount ?  6: 5}}" rowspan="8">
                <p><input class="form-control" placeholder="Reference No" ng-model="ref_no" /></p>
                <textarea class="form-control" rows="10" placeholder="Summery" ng-model="summery"></textarea>
            </td>
            <td class="text-right">Sub Total</td>
            <td>{{(subTotal + discountPercentValue) | number: 2}}</td>
        </tr>
        <tr>
            <td class="text-right">Add Discount</td>
            <td width="200"><input type="search" ng-model="discountAmount" class="form-control" on-enter-press="addDiscount(discountAmount)"></td>
        </tr>
        <tr>
            <td class="text-right" style="color: red; front-weight: bold;">Total Discount</td>
            <td width="200" style="color: red; front-weight: bold;"><strong>{{(discount + discountPercentValue) | number: 2}}</strong></td>
        </tr>
        <tr>
            <td class="text-right">Grand Total</td>
            <td class="text-success">{{grandTotal | number: 2}}</td>
        </tr>
        <tr>
            <td class="text-right text-success" style="front-weight: bold;">Pay Amount</td>
            <td width="200"><input type="number" ng-model="payment_amount" class="form-control"></td>
        </tr>
        <tr>
            <td class="text-right">Balance</td>
            <td width="200">{{grandTotal - payment_amount | number: 2}}</td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <th colspan="{{show_discount ? 8 : 7 }}" class="text-right">
                <a href="#" class="btn btn-success pull-left" ng-click="park()">Park For Now</a>
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