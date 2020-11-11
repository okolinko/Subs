define([
    'jquery',
    'Toppik_Subscriptions/js/save/scripts'
], function (jQuery) {
    'use strict';

    var $el = jQuery('#profile_points_form');
    
    if(!$el.length || !$el.data('load-base-url')) {
        return;
    }
    
    var baseUrl = $el.data('load-base-url');
    
    var subscriptionSaveTheSale = new SubscriptionSaveTheSale();
    subscriptionSaveTheSale.setLoadBaseUrl(baseUrl);
    
    window.subscriptionSaveTheSale = subscriptionSaveTheSale;
});
