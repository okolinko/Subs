define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'ko'
    ],
    function ($, Component, quote, totals, ko) {
        "use strict";

        var segment = 'subscription_subtotal';
        var segmentData = ko.observable(getSegment(quote.getTotals()(), segment));

        quote.getTotals().subscribe(function(newTotals) {
            segmentData(getSegment(newTotals, segment));
        });

        function getSegment(totals, code) {
            if (! totals) {
                return null;
            }
            for (var i in totals.total_segments) {
                var total = totals.total_segments[i];

                if (total.code == code) {
                    return total;
                }
            }
            return null;
        }

        return Component.extend({
            isDisplayed : function(){
                var segment = segmentData();
                if(segment &&
                    ('extension_attributes' in segment) &&
                    ('items' in segment.extension_attributes) &&
                    (segment.extension_attributes.items.length)) {
                    return true;
                }
                return false;
            },
            totals : segmentData,
            visibleSubscriptions : ko.observable(false),
            toggleSubscriptions : function() {
                this.visibleSubscriptions(! this.visibleSubscriptions());
            },
            items: segmentData().extension_attributes.items
        });
    }
);