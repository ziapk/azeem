/**
 * BillCalc — the single source of truth for cart / bill arithmetic.
 *
 * This math used to be copy-pasted into every screen that shows a cart
 * (pages/recipt/index.php, pages/recipt/edit.php, pages/recipt/order.php,
 * pages/cart/index.php), and the copies drifted. Every totalling bug we have
 * chased was a copy that had not received a fix its siblings had:
 *
 *   - the size breakdown set the line quantity AFTER the subtotal had read it,
 *     so the saved order price came out below the sum of its own lines;
 *   - FIX-mode line discounts copied the customer's default PERCENTAGE straight
 *     into a rupee AMOUNT, zeroing the line;
 *   - the bill discount silently stopped being a percentage above 100 and was
 *     deducted as rupees instead.
 *
 * Keep the arithmetic here. Controllers own their own UI concerns (payment-mode
 * autofill, sessionStorage, focus) and simply hand their state in.
 *
 * compute() MUTATES the item objects it is given — Angular's two-way bindings
 * depend on that — and returns the bill-level totals.
 */
(function (global) {
    'use strict';

    // Ceiling for the quantity on a single cart line. Applied here rather than as a
    // max= attribute on the input, because AngularJS treats max= as a validator: the
    // moment it trips, ngModel sets the model to undefined and what the cashier typed
    // disappears with no message. Clamping caps the value while leaving it on screen.
    var MAX_QTY = 99999;

    function clampQty(val) {
        var n = parseFloat(val);
        if (!(n > 0)) return 0;
        return Math.min(n, MAX_QTY);
    }

    // Resolve the quantity a line is billed at. Bundles and the size breakdown both
    // OWN product.qty, so they must both settle before the quantity is read.
    function resolveQty(product, showBundle) {
        if (product.pack_qty && showBundle) {
            product.qty = clampQty((product.pack_size || 1) * product.pack_qty);
        }

        if (product.sizes && product.sizes.length) {
            var sizesQty = 0;
            product.sizes.map(function (r) { sizesQty += parseInt(r.qty || 0); });
            if (sizesQty) {
                product.qty = Math.min(parseInt(sizesQty), MAX_QTY);
            }
        }

        var qty = showBundle ? (product.qty + (product.unpack_qty || 0)) : product.qty;

        if (!showBundle) {
            product.unpack_qty = 0;
            product.pack_qty = 0;
            // product.pack_size is deliberately left alone
        }
        return qty;
    }

    // A line priced from its own services / raw items rather than a unit price.
    function isComposite(product) {
        if (product.product_type == 1) return false;
        var hasServices = product.services && product.services.length;
        var hasRaw = product.raw_items && product.raw_items.length;
        return !!(hasServices || hasRaw);
    }

    /**
     * Set product.discount (a per-unit AMOUNT) from whichever discount rule applies,
     * and return the amount of percentage-based discount to report on the bill.
     *
     * discount_type: 1 = percent, 2 = fixed amount.
     */
    function applyLineDiscount(product, customerData, qty) {
        var price = parseFloat(product.price) || 0;
        var defaultDiscount = parseFloat(customerData.default_discount);
        var discountArray = customerData.discount_array;

        if (product.discount_type == 2) {
            // FIX mode holds a per-unit AMOUNT, but customers.default_discount is a
            // PERCENTAGE (the customer form labels it "Discount %"), so convert it.
            if (defaultDiscount) {
                product.discount_value = price * (defaultDiscount / 100);
            }
            product.discount = (product.discount_value || 0);
            product.discount_value = parseFloat(product.discount_value || 0);
            product.discount_percent = product.discount_value || 0;
            return 0;
        }

        var publisherRow = null;
        if (!product.discount_value && discountArray && discountArray.length) {
            publisherRow = discountArray.filter(function (r) {
                return r.publisher_id == product.publisher_id;
            })[0] || null;
        }

        if (publisherRow) {
            product.discount = price * (parseFloat(publisherRow.discount_value) / 100);
            product.discount_value = publisherRow.discount_value;
            product.discount_percent = publisherRow.discount_value + '%';
            return 0;
        }

        if (defaultDiscount) {
            product.discount_value = customerData.default_discount;
        }

        if (product.discount_value) {
            product.discount = price * (parseFloat(product.discount_value || 0) / 100);
            product.discount_percent = product.discount_value + '%';
            product.discount_value = parseFloat(product.discount_value);
            return product.discount * qty;
        }

        product.discount_percent = '';
        product.discount_value = '';
        product.discount = 0;
        return 0;
    }

    /**
     * The bill-level discount box: what a typed value is actually worth.
     *
     * total_discount_type: 1 = percent, 2 = fixed amount. The percent path used to
     * give up above 100 and leave the raw number in place, so a "%" discount over 100
     * was quietly deducted as RUPEES while the button still read "%". Clamp the
     * percentage instead, and never let the running discount outgrow the bill, which
     * is what sent the total negative when a large fixed amount was keyed in.
     */
    function billDiscountValue(typed, type, subTotal, alreadyDiscounted) {
        var typedDiscount = parseFloat(typed);
        var room = Math.max((subTotal || 0) - (parseFloat(alreadyDiscounted) || 0), 0);

        if (isNaN(typedDiscount)) return '';
        // negative entries walk back an over-applied discount; addDiscount validates them
        if (typedDiscount < 0) return typedDiscount;
        if (type === 1) {
            return Math.min(subTotal * (Math.min(typedDiscount, 100) / 100), room);
        }
        return Math.min(typedDiscount, room);
    }

    function compute(state) {
        var items = state.items || [];
        var customerData = state.customerData || {};
        var showBundle = !!state.show_bundle;
        var billDiscount = parseFloat(state.discount) || 0;
        var gst = parseFloat(state.gst) || 0;
        var serviceCharges = parseFloat(state.service_charges) || 0;

        var subtotal = 0;
        var discountPercentValue = 0;
        var counter = 1;
        var forCounter = 1;

        for (var i = 0; i < items.length; i++) {
            var product = items[i];
            product.price = product.price || 0;

            if (product.product_type != 5) {
                product.srno = counter++;
            } else {
                product.frsrno = forCounter++;
            }

            var qty = resolveQty(product, showBundle);

            if (isComposite(product)) {
                product.price = 0;
                if (product.services) {
                    product.services.forEach(function (row) {
                        product.price += (row.price || 0) * (row.qty || 1);
                    });
                }
                if (product.raw_items) {
                    product.raw_items.forEach(function (row) {
                        product.price += (row.price || 0) * (row.qty || 1);
                    });
                }
                subtotal += product.price * qty;
            } else {
                discountPercentValue += applyLineDiscount(product, customerData, qty);
                subtotal += ((parseFloat(product.price) || 0) - product.discount) * qty;
            }
        }

        var beforeTax = subtotal - billDiscount;
        var grandTotal = beforeTax
            + Math.round(beforeTax * (gst / 100))
            + Math.round(beforeTax * (serviceCharges / 100));

        return {
            subTotal: subtotal,
            discountPercentValue: discountPercentValue,
            payment_amount_before_tax: beforeTax,
            payment_amount: grandTotal,
            grandTotal: grandTotal,
            total_discount_value: billDiscountValue(
                state.discountAmount, state.total_discount_type, subtotal, billDiscount
            )
        };
    }

    global.BillCalc = {
        MAX_QTY: MAX_QTY,
        clampQty: clampQty,
        compute: compute
    };
})(window);
