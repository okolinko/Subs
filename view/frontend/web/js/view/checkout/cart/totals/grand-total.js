/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Tax/js/view/checkout/cart/totals/grand-total',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        "use strict";
        return Component.extend({
            getValue: function() {
                var price = 0;
                var discount = 0;
                if (this.totals()) {
                    price = parseFloat(totals.getSegment('subtotal').value);
                    if(totals.getSegment('discount')) {
                        discount = parseFloat(totals.getSegment('discount').value);
                    }
                }
                return this.getFormattedPrice(price + discount);
            }
        });
    }
);
