/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/record',
    'mageUtils',
    'uiRegistry',
    'underscore'
], function (Record, utils, registry, _) {
    'use strict';

    return Record.extend({
        defaults: {
            dataProvider: '',
            data : [],
            listens: {
                'data': 'onDataUpdate'
            }
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'data'
                ]);

            return this;
        },

        /**
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        onDataUpdate: function (data) {
            // Bug in rendering
            setTimeout((function() {
                var labelSource = this.name + '.' + this.labelSource;
                var component = registry.get(labelSource);
                if(! component) {
                    return this;
                }
                var options = component.options();
                var id = data.period_id;
                var label = _.reduce(options, function(mem, option) {
                    if(option.value === id) {
                        return option.label;
                    } else {
                        return mem;
                    }
                }, '');
                label += (data.regular_price ? ' - ' + data.regular_price : '');
                this.label(label);
            }).bind(this), 0);
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        }
    });
});
