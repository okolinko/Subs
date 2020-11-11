/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (ko, _, registry, Select) {
    'use strict';

    ko.bindingHandlers.optionsBind = {
        preprocess : function(value, key, addBinding) {
            addBinding('optionsAfterRender', 'function(option, item) { ko.bindingHandlers.optionsBind.applyBindings(option, item, ' + value + ') }');
        },
        applyBindings: function(option, item, bindings) {
            if (item !== undefined) {
                option.setAttribute('data-bind', bindings);
                ko.applyBindings(ko.contextFor(option).createChildContext(item), option);
            }
        }
    };

    function SyncDisabled(disables) {
        disables = disables || [];
        this.fields = [];
        this.options = [];
        this.disables = disables;
    }

    SyncDisabled.prototype = {
        addField : function(f) {
            this.fields.push(f);
        },
        addOption : function(o) {
            this.options.push(o);
        },
        update : function() {
            var used = [];
            var selected = _.reduce(this.fields, function(mem, field) {
                if(! field.deleted) {
                    mem.push(field.value());
                }
                return mem;
            }, []);
            var picked = [], total = [];
            var pickNext = false;
            _.each(this.options, function(opt) {
                if(opt.parent.deleted) {
                    return;
                }
                var disabled = true;
                if(opt.option.value === opt.parent.value() && used.indexOf(opt.option.value) < 0) {
                    disabled = false;
                    used.push(opt.option.value);
                } else if(selected.indexOf(opt.option.value) < 0) {
                    disabled = false;
                    used.push(opt.option.value);
                }
                if(pickNext && ! disabled) {
                    opt.parent.value(opt.option.value);
                    pickNext = false;
                }
                if(disabled && opt.parent.value() === opt.option.value) {
                    pickNext = true;
                }
                if(! disabled && opt.parent.value() === opt.option.value) {
                    picked.push(opt.option.value);
                }
                if(total.indexOf(opt.option.value) < 0) {
                    total.push(opt.option.value);
                }
                opt.option.enabled(! disabled);
            });
            var disableEls = total.length === picked.length;
            _.each(this.disables, function(elName) {
                console.log(elName);
                registry.get(elName).disabled(disableEls);
            });
        }
    };

    return Select.extend({

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            console.log('init');

            var self = this;

            this.deleted = false;

            var sync = registry.get('select_registry');
            sync = sync || new SyncDisabled([config.disableButton]);
            sync.addField(this);

            _.map(config.options, function(option) {
                if(typeof option.disabled === 'boolean') {
                    option.enabled = ko.observable(! option.disabled);
                } else {
                    option.enabled = ko.observable(true);
                }
                if(option.value) {
                    sync.addOption({
                        parent : self,
                        option : option
                    });
                }
            });

            registry.set('select_registry', sync);

            window.sy = sync;

            setTimeout(function() {
                self.value.subscribe(sync.update.bind(sync));
                sync.update();
            }, 0);

            this._super();

            return this;
        },

        destroy : function() {
            var sync = registry.get('select_registry');
            this.deleted = true;
            sync.update();
            this._super();
        }
        
    });
});
