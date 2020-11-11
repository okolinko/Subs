// jscs:disable
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function(jQuery, modal) {
    window.SubscriptionSaveTheSale = new Function();
    
    SubscriptionSaveTheSale.prototype = {
        initialize : function() {
            this.container          = false;
            this.form               = false;
            this.productContainer   = false;
            this.loadBaseUrl        = false;
            this.optionId           = false;
            this.gridProducts       = $H({});
            this.overlayData        = $H({});
            this.productPriceBase   = {};
            
            this.initEvents();
            this.initPopup();
            this.initProductPopup();
            
            jQuery('#profile_points_grid_table').find('.col-points').hide();
        },
        
        initEvents: function() {
            var __this = this;
            
            jQuery(document).on('subscription:points:select:option', function(e) {
                __this.update();
            });
        },
        
        initPopup: function() {
            var __this = this;
            
            var buttons = [{
                text: jQuery.mage.__('Go Back'),
                class: 'back',
                click: function () {
                    this.closeModal();
                }
            }];
            
            if(subscriptionPointsConfig.can_cancel === true) {
                buttons.push({
                    text: jQuery.mage.__('Cancel Subscription'),
                    class: '',
                    click: function () {
                        this.closeModal();
                        window.location.href = subscriptionPointsConfig.cancel_url;
                        return false;
                    }
                });
            }
            
            if(subscriptionPointsConfig.can_save === true) {
                buttons.push({
                    text: jQuery.mage.__('Confirm'),
                    class: 'primary',
                    click: function () {
                        var ids = [];
                        var points = 0;
                        
                        for(option_id in window.subscriptionPointsRegistry) {
                            if(window.subscriptionPointsRegistry.hasOwnProperty(option_id)) {
                                var item = window.subscriptionPointsRegistry[option_id];
                                
                                if(item && item.label != undefined && item.value != undefined) {
                                    points = points + parseInt(item.value);
                                    ids.push(option_id);
                                }
                            }
                        }
                        
                        if(ids.length < 1) {
                            alert(jQuery.mage.__('Please choose an option!'));
                            return false;
                        }
                        
                        if(subscriptionPointsConfig.available_onetime_points !== -1 && points > subscriptionPointsConfig.available_onetime_points) {
                            alert(jQuery.mage.__('Selected options exceed number of available points!'));
                            return false;
                        }
                        
                        __this.submit();
                        
                        return false;
                    }
                });
            }
            
            var popup = modal(
                {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    clickableOverlay: true,
                    title: jQuery.mage.__('Save The Sale Or Cancel'),
                    buttons: buttons
                },
                __this.getContainer()
            );
            
            jQuery('.js-save_the_sale').on('click', function() {
                __this.getContainer().modal('openModal');
                return false;
            });
        },
        
        initProductPopup: function() {
            var __this = this;
            
            var options = {
                type: 'slide',
                responsive: true,
                innerScroll: true,
                clickableOverlay: true,
                title: jQuery.mage.__('Choose Product'),
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
                            var option_id = subscriptionSaveTheSale.getOptionId();
                            var products = subscriptionSaveTheSale.productGridAddSelected();
                            
                            if(!option_id || option_id < 1) {
                                alert(jQuery.mage.__('Unknown option id'));
                                return false;
                            }
                            
                            if(Object.keys(products).length < 1) {
                                alert(jQuery.mage.__('Please choose product'));
                                return false;
                            }
                            
                            for(_option_id in window.subscriptionPointsRegistry) {
                                if(option_id == _option_id) {
                                    if(window.subscriptionPointsRegistry.hasOwnProperty(option_id)) {
                                        if(window.subscriptionPointsRegistry[option_id]) {
                                            for(var productId in products) {
                                                window.subscriptionPointsRegistry[option_id].product = [];
                                                window.subscriptionPointsRegistry[option_id].product.push({id: productId, name: products[productId]['name'], sku: products[productId]['sku'], price: products[productId]['price'], qty: products[productId]['qty'], attributes: (products[productId]['attributes'] || {})});
                                            }
                                        }
                                    }
                                }
                            }
                            
                            __this.update();
                            this.closeModal();
                            
                            return false;
                        }
                    }
                ]
            };
            
            var popup = modal(options, __this.getProductContainer());
        },
        
        getContainer: function() {
            if(!this.container) {
                this.container = jQuery('#container-save_the_sale');
            }
            
            return this.container;
        },
        
        getForm: function() {
            if(!this.form) {
                this.form = this.getContainer().find('form');
            }
            
            return this.form;
        },
        
        getProductContainer: function() {
            if(!this.productContainer) {
                this.productContainer = jQuery('#container-save_the_sale-search-product');
            }
            
            return this.productContainer;
        },
        
        update: function() {
            var showSelectedCoupons = false;
            var showSelectedProducts = false;
            var selectedOptions = this.getContainer().find('.select-options-block');
            var selectedCoupons = jQuery('#container-save_the_sale-selected-coupons');
            var selectedProducts = jQuery('#container-save_the_sale-selected-products');
            var couponsTbody = selectedCoupons.find('tbody');
            var productsTbody = selectedProducts.find('tbody');
            
            couponsTbody.html('');
            productsTbody.html('');
            selectedOptions.hide();
            selectedCoupons.hide();
            selectedProducts.hide();
            
            var points = 0;
            var labels = [];
            
            for(option_id in window.subscriptionPointsRegistry) {
                if(window.subscriptionPointsRegistry.hasOwnProperty(option_id)) {
                    var item = window.subscriptionPointsRegistry[option_id];
                    
                    if(item && item.label != 'undefined' && item.value != 'undefined') {
                        points = points + parseInt(item.value);
                        
                        if(typeof item['coupon'] != 'undefined') {
                            showSelectedCoupons = true;
                            couponsTbody.append('<tr><td>' + item['coupon']['name'] + '</td><td>' + item['coupon']['code'] + '</td></tr>');
                        }
                        
                        if(typeof item['product'] != 'undefined') {
                            var names = [];
                            
                            for(var i = 0; i < item['product'].length; i++) {
                                showSelectedProducts = true;
                                var attributes = [];
                                
                                if(typeof item['product'][i]['attributes'] != 'undefined') {
                                    for(attribute_id in item['product'][i]['attributes']) {
                                        if(item['product'][i]['attributes'].hasOwnProperty(attribute_id)) {
                                            if(typeof item['product'][i]['attributes'][attribute_id]['label'] != 'undefined') {
                                                attributes.push(item['product'][i]['attributes'][attribute_id]['label']);
                                            }
                                        }
                                    }
                                }
                                
                                productsTbody.append('<tr><td>' + item['product'][i]['name'] + ' ' + attributes.join(' ') + '</td><td>' + item['product'][i]['price'] + '</td><td>' + item['product'][i]['qty'] + '</td><td>' + (item['product'][i]['price'] * item['product'][i]['qty']) + '</td></tr>');
                            }
                        }
                        
                        labels.push(item.label);
                    }
                }
            }
            
            var max = (subscriptionPointsConfig.available_onetime_points !== -1) ? subscriptionPointsConfig.available_onetime_points : 0;
            var earned = (max && max > 0) ? Math.max(0, (max - points)) : 0;
            
            this.getContainer().find('.selected-points').find('.value').html((points > 0 ? points : 0));
            this.getContainer().find('.earned-points').find('.value').html(earned);
            
            if(labels.length > 0) {
                var html = '<ul>';
                
                for(var i = 0; i < labels.length; i++) {
                    html += '<li>' + labels[i] + '</li>';
                }
                
                html += '</ul>';
                
                selectedOptions.find('.selected-labels').find('.value').html(html);
                selectedOptions.show();
            }
            
            if(showSelectedCoupons === true) {
                selectedCoupons.show();
            }
            
            if(showSelectedProducts === true) {
                selectedProducts.show();
            }
        },
        
        setLoadBaseUrl : function(url) {
            this.loadBaseUrl = url;
        },
        
        setOptionId : function(id) {
            this.optionId = id;
        },
        
        getOptionId : function() {
            return this.optionId;
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
        
        productLinkRowClick: function(el) {
            if(typeof window['profile_points_product_search_gridJsObject'] != 'undefined') {
                profile_points_product_search_gridJsObject.rowClickCallback = this.productGridRowClick.bind(this);
                profile_points_product_search_gridJsObject.checkboxCheckCallback = this.productGridCheckboxCheck.bind(this);
                profile_points_product_search_gridJsObject.initRowCallback = this.productGridRowInit.bind(this);
                profile_points_product_search_gridJsObject.initGridRows();
            }
            
            var id = parseInt(jQuery(el).attr('rel'));
            this.setOptionId(id);
            this.getProductContainer().modal('openModal');
            return false;
        },
        
        ruleLinkRowClick: function(el) {
            var __this  = this;
            var button  = jQuery(el);
            var id      = parseInt(jQuery(el).attr('rel'));
            var url     = subscriptionPointsConfig.coupon_url;
            
            if(!id || id < 1) {
                alert(jQuery.mage.__('Cannot extract option ID!'));
            }
            
            if(!url) {
                alert(jQuery.mage.__('URL is not specified!'));
            }
            
            button.attr('disabled', true);
            
            jQuery.ajax({
                url: url,
                data: 'id=' + id,
                type: 'GET',
                success: function(r) {
                    button.attr('disabled', false);
                    
                    if(r.errors && r.errors.length > 0) {
                        alert(r.errors.join("\n"));
                    } else {
                        if(r.code && r.name) {
                            for(_option_id in window.subscriptionPointsRegistry) {
                                if(id == _option_id) {
                                    if(window.subscriptionPointsRegistry.hasOwnProperty(_option_id)) {
                                        if(window.subscriptionPointsRegistry[_option_id]) {
                                            window.subscriptionPointsRegistry[_option_id].coupon = {code: r.code, name: r.name};
                                        }
                                    }
                                }
                            }
                            
                            __this.update();
                        } else {
                            alert(jQuery.mage.__('Cannot extract coupon code'))
                        }
                    }
                },
                error: function(r) {
                    button.attr('disabled', false);
                    
                    if(r.errors && r.errors.length > 0) {
                        alert(r.errors.join("\n"));
                    }
                }
            });
            
            return false;
        },
        
        /**
         * Submit configured products to quote
         */
        productGridAddSelected : function() {
            var container = jQuery('#profile_points_product_search_grid_table');
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
        
        prepareParams : function(params){
            if(!params) {
                params = {};
            }
            
            if(!params.form_key) {
                params.form_key = FORM_KEY;
            }
            
            return params;
        },
        
        submit : function() {
            var options = this.getContainer().find('.selected-options');
            
            options.html('');
            
            for(option_id in window.subscriptionPointsRegistry) {
                if(window.subscriptionPointsRegistry.hasOwnProperty(option_id)) {
                    var item = window.subscriptionPointsRegistry[option_id];
                    
                    if(item && item.label != 'undefined' && item.value != 'undefined') {
                        options.append('<input type="hidden" name="options[]" value="' + option_id + '" />');
                        
                        if(typeof item['product'] != 'undefined') {
                            for(var i = 0; i < item['product'].length; i++) {
                                options.append('<input type="hidden" name="option[' + option_id + '][product][' + item['product'][i]['id'] + '][price]" value="' + item['product'][i]['price'] + '" />');
                                options.append('<input type="hidden" name="option[' + option_id + '][product][' + item['product'][i]['id'] + '][qty]" value="' + item['product'][i]['qty'] + '" />');
                                
                                if(typeof item['product'][i]['attributes'] != 'undefined') {
                                    for(attribute_id in item['product'][i]['attributes']) {
                                        if(item['product'][i]['attributes'].hasOwnProperty(attribute_id)) {
                                            if(typeof item['product'][i]['attributes'][attribute_id]['value'] != 'undefined') {
                                                options.append('<input type="hidden" name="option[' + option_id + '][product][' + item['product'][i]['id'] + '][super_attributes][' + attribute_id + ']" value="' + item['product'][i]['attributes'][attribute_id]['value'] + '" />');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        if(typeof item['coupon'] != 'undefined' && typeof item['coupon']['code'] != 'undefined') {
                            options.append('<input type="hidden" name="option[' + option_id + '][coupon]" value="' + item['coupon']['code'] + '" />');
                        }
                    }
                }
            }
            
            this.getForm().submit();
        }
    };
    
    var subscriptionSaveTheSale = new SubscriptionSaveTheSale();
    
    subscriptionSaveTheSale.initialize();
    
    window.subscriptionSaveTheSale = subscriptionSaveTheSale;
});
/* jshint ignore:end */
