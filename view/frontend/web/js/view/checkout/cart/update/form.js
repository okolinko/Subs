/**
 * Based on Magento_Catalog/js/catalog-add-to-cart.js
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Toppik_Quickbuy/js/popup'
], function($, _) {
    "use strict";

    var lastRequest = false,
        lastPopup = false;
    var popup = new $.Popup({
        afterOpen : function() {
            if(lastPopup && ('trigger' in lastPopup)) {
                lastPopup.trigger('contentUpdated');
            }
        },
        afterClose : function() {
            if(lastRequest) {
                lastRequest.abort();
            }
        }
    });

    $.widget('toppik.subscriptionsupdateform', {
        _create : function() {
            this.element.click((function(e) {
                e.preventDefault();
                this.loadForm();
            }).bind(this));
        },
        loadForm : function () {
            var me = this;
            popup.open(function() {
                return $('<div class="product-vieq-quickbuy-loading">Loading ...</div>');
            });
            $.get(this.options.formUrl, function(d) {
                popup.open(function() {
                    lastPopup = $('<div class="container-cart-edit">' + d.content + '</div>');
                    var data = $('.item-extra-data');
                    data.each(function() {
                        var self = $(this);
                        var obj = lastPopup.find('#cart-item-' + self.attr('rel'));

                        if(obj.size() == 1) {
                            obj.html(self.html().replace('</dd>', '</dd><br/>'));
                        }
                    });
                    // bind
                    lastPopup.find('#cart-edit-button').click(function() {
                        var self = $(this);
                        var form = $('#cart-edit');

                        if(!form || !form.attr('action')) {
                            return false;
                        }

                        var action = function(data) {
                            self.attr('disabled', false);
                            $.each(data.convert, function(oldId, newId) {
                                $('[name="cart[' + oldId + '][qty]"]').attr('name', 'cart[' + newId + '][qty]');
                            });

                            popup.open(function() {
                                return $('<div class="product-vieq-quickbuy-loading">Updating cart</div>');
                            });

                            $('.update_cart_action.btn-update').click();
                        }

                        self.attr('disabled', 'disabled');
                        popup.open(function() {
                            return $('<div class="product-vieq-quickbuy-loading">Saving data... Please wait</div>');
                        });

                        $.post(
                            form.attr('action'),
                            form.serialize(),
                            function(r) {
                                action(r);
                            },
                            'json'
                        );
                    });
                    return lastPopup;
                });
            }, 'json');
        }
    });

    return $.toppik.subscriptionsupdateform;
});
