// jscs:disable
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function(jQuery, modal) {
    window.SubscriptionSimpleProduct = new Function();
    
    SubscriptionSimpleProduct.prototype = {
        initialize : function() {
            this.container  = false;
            this.form       = false;
            
            this.initPopup();
        },
        
        initPopup: function() {
            var __this = this;
            
            var popup = modal(
                {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    clickableOverlay: true,
                    title: jQuery.mage.__('Change Simple Product'),
                    buttons: [
                        {
                            text: jQuery.mage.__('Go Back'),
                            class: 'back',
                            click: function () {
                                this.closeModal();
                            }
                        },
                        {
                            text: jQuery.mage.__('Confirm'),
                            class: 'primary',
                            click: function () {
                                this.closeModal();
                                __this.submit();
                                return false;
                            }
                        }
                    ]
                },
                __this.getContainer()
            );
            
            jQuery('.js-change_simple').on('click', function() {
                __this.getContainer().modal('openModal');
                return false;
            });
        },
        
        getContainer: function() {
            if(!this.container) {
                this.container = jQuery('#container-change_simple');
            }
            
            return this.container;
        },
        
        getForm: function() {
            if(!this.form) {
                this.form = this.getContainer().find('form');
            }
            
            return this.form;
        },
        
        submit : function() {
            this.getForm().submit();
        }
    };
    
    var subscriptionSimpleProduct = new SubscriptionSimpleProduct();
    
    subscriptionSimpleProduct.initialize();
    
    window.subscriptionSimpleProduct = subscriptionSimpleProduct;
});
/* jshint ignore:end */
