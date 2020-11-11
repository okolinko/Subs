// jscs:disable
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function(jQuery, modal) {
    window.SubscriptionProductConfigure = new Class.create();
    
    SubscriptionProductConfigure.prototype = {
        initialize : function() {
            this.container          = false;
            this.form               = false;
            this.gridProducts       = $H({});
            
            this.initEvents();
        },
        
        initEvents: function() {
            var __this = this;
            
            jQuery('.js-subscription_product_save').click(function() {
                __this.submit();
                return false;
            });
        },
        
        getContainer: function() {
            if(!this.container) {
                this.container = jQuery('.js-subscription_product_save');
            }
            
            return this.container;
        },
        
        getForm: function() {
            if(!this.form) {
                this.form = this.getContainer().find('form');
            }
            
            return this.form;
        },
        
        productGridRowInit : function(grid, row){
            var checkbox = $(row).select('.checkbox')[0] || $(row).select('.radio')[0];
            var inputs = $(row).select('.input-text');
            
            if(checkbox && inputs.length > 0) {
                checkbox.inputElements = inputs;
                
                for(var i = 0; i < inputs.length; i++) {
                    var input = inputs[i];
                    input.checkboxElement = checkbox;
                    
                    var product = this.gridProducts.get(checkbox.value);
                    
                    if(product) {
                        var defaultValue = product[input.name];
                        
                        if(defaultValue) {
                            input.value = defaultValue;
                        }
                    }
                    
                    // input.disabled = !checkbox.checked || input.hasClassName('input-inactive');
                    
                    Event.observe(input, 'keyup', this.productGridRowInputChange.bind(this));
                    Event.observe(input, 'change',this.productGridRowInputChange.bind(this));
                }
            }
        },
        
        productGridRowInputChange : function(event){
            var element = Event.element(event);
            
            if(element && element.checkboxElement && element.checkboxElement.checked){
                if(element.checked) {
                    this.gridProducts.get(element.checkboxElement.value)[element.name] = element.value;
                }
            }
        },
        
        productGridRowClick : function(grid, event) {
            var trElement = Event.findElement(event, 'tr');
            var qtyElement = trElement.select('input[name="qty"]')[0];
            var eventElement = Event.element(event);
            var isInputCheckbox = eventElement.tagName == 'INPUT' && (eventElement.type == 'checkbox' || eventElement.type == 'radio');
            var isInputQty = eventElement.tagName == 'INPUT' && (eventElement.name == 'qty' || eventElement.name == 'price');
            var isSingleMode = eventElement.tagName == 'INPUT' && eventElement.type == 'radio';
            
            if(trElement && !isInputQty) {
                var checkbox = Element.select(trElement, 'input[type="checkbox"]')[0] || Element.select(trElement, 'input[type="radio"]')[0];
                var confLink = Element.select(trElement, 'a')[0];
                var priceColl = Element.select(trElement, '.price')[0];
                
                if(checkbox) {
                    // processing non composite product
                    if(confLink.readAttribute('disabled')) {
                        var checked = isInputCheckbox ? checkbox.checked : !checkbox.checked;
                        grid.setCheckboxChecked(checkbox, checked);
                        // processing composite product
                    } else if(isInputCheckbox && !checkbox.checked) {
                        grid.setCheckboxChecked(checkbox, false);
                        // processing composite product
                    } else if(!isInputCheckbox || (isInputCheckbox && checkbox.checked)) {
                        var listType = confLink.readAttribute('list_type');
                        var productId = confLink.readAttribute('product_id');
                        
                        productConfigure.setConfirmCallback(listType, function() {
                            // sync qty of popup and qty of grid
                            var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                            if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                                qtyElement.value = confirmedCurrentQty.value;
                            }
                            // productPrice = parseFloat(Math.round(productPrice + "e+2") + "e-2");
                            // priceColl.innerHTML = this.currencySymbol + productPrice.toFixed(2);
                            // and set checkbox checked
                            grid.setCheckboxChecked(checkbox, true);
                        }.bind(this));
                        
                        productConfigure.setCancelCallback(listType, function() {
                            if (!$(productConfigure.confirmedCurrentId) || !$(productConfigure.confirmedCurrentId).innerHTML) {
                                grid.setCheckboxChecked(checkbox, false);
                            }
                        });
                        
                        productConfigure.setShowWindowCallback(listType, function() {
                            // sync qty of grid and qty of popup
                            var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                            
                            if(formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                                formCurrentQty.value = qtyElement.value;
                            }
                            
                            if(formCurrentQty) {
                                var c = jQuery(formCurrentQty);
                                c.parents('fieldset').hide();
                            }
                        }.bind(this));
                        
                        productConfigure.showItemConfiguration(listType, productId);
                    }
                }
            }
        },
        
        productGridCheckboxCheck : function(grid, element, checked) {
            var isSingleMode = element.tagName == 'INPUT' && element.type == 'radio';
            
            if(isSingleMode) {
                this.gridProducts = $H({});
                var tbody = jQuery(element).parents('tbody');
                tbody.find('input[name="qty"]').val(null).attr('disabled', true);
                tbody.find('input[name="price"]').val(null).attr('disabled', true);
            }
            
            if(checked) {
                if(element.inputElements) {
                    this.gridProducts.set(element.value, {});
                    var product = this.gridProducts.get(element.value);
                    
                    for(var i = 0; i < element.inputElements.length; i++) {
                        var input = element.inputElements[i];
                        
                        if(!input.hasClassName('input-inactive')) {
                            // input.disabled = false;
                            
                            if(input.name == 'qty' && !input.value) {
                                input.value = 1;
                            }
                            
                            if(input.name == 'price' && !input.value) {
                                input.value = 0;
                            }
                        }
                        
                        if (input.checked) {
                            product[input.name] = input.value;
                        } else if(product[input.name]) {
                            delete(product[input.name]);
                        }
                    }
                }
            } else {
                if(element.inputElements){
                    for(var i = 0; i < element.inputElements.length; i++) {
                        element.inputElements[i].disabled = true;
                    }
                }
                
                this.gridProducts.unset(element.value);
            }
            
            grid.reloadParams = {'products[]': this.gridProducts.keys()};
        },
        
        /**
         * Submit configured products to quote
         */
        productGridAddSelected : function() {
            var container = jQuery('#profile_edit_product_grid_table');
            var inputs = container.find('input[name="choose"]');
            
            if(this.productGridShowButton) {
                Element.show(this.productGridShowButton);
            }
            
            var fieldsPrepare = {};
            var itemsFilter = [];
            var products = this.gridProducts.toObject();
            
            var attributes = {};
            var selects = jQuery(productConfigure.blockConfirmed).find('select');
            
            if(selects.length > 0) {
                selects.each(function() {
                    var id = parseInt(jQuery(this).attr('id').replace('attribute', ''));
                    var value = jQuery(this).val();
                    
                    if(id && value) {
                        var label = jQuery(this).find(':selected').text();
                        attributes[id] = {label: label, value: value};
                    }
                });
            }
            
            for(var productId in products) {
                var input = inputs.filter(function() {return this.value == productId});
                var trElement = input.parents('tr');
                var name = jQuery.trim(trElement.find('.col-name').find('.value').text());
                var sku = jQuery.trim(trElement.find('.col-sku').text());
                var price = trElement.find('input[name="price"]').val();
                var qty = parseInt(trElement.find('input[name="qty"]').val());
                
                products[productId] = {name: name, sku: sku, price: (price && price > 0 ? price : 0), qty: (qty || 1), attributes: attributes};
            }
            
            return products;
        },
        
        submit : function() {
            var products = this.productGridAddSelected();
            
            if(Object.keys(products).length < 1) {
                alert(jQuery.mage.__('Please choose product'));
                return false;
            }
            
            var options = this.getForm().find('.options');
            
            options.html('');
            
            for(var productId in products) {
                options.append('<input type="hidden" name="product[' + productId + '][id]" value="' + productId + '" />');
                options.append('<input type="hidden" name="product[' + productId + '][qty]" value="' + products[productId]['qty'] + '" />');
                
                if(typeof products[productId]['attributes'] != 'undefined') {
                    for(attribute_id in products[productId]['attributes']) {
                        if(products[productId]['attributes'].hasOwnProperty(attribute_id)) {
                            if(typeof products[productId]['attributes'][attribute_id]['value'] != 'undefined') {
                                options.append('<input type="hidden" name="product[' + productId + '][super_attributes][' + attribute_id + ']" value="' + products[productId]['attributes'][attribute_id]['value'] + '" />');
                            }
                        }
                    }
                }
            }
            
            this.getForm().submit();
        }
    };
    
    var subscriptionProductConfigure = new SubscriptionProductConfigure();
    window.subscriptionProductConfigure = subscriptionProductConfigure;
});
/* jshint ignore:end */
