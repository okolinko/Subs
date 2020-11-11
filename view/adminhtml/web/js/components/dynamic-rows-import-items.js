/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'Magento_Ui/js/lib/spinner',
    'mageUtils'
], function (DynamicRows, loader, utils) {
    'use strict';

    return DynamicRows.extend({
        defaults: {
            dataProvider: '',
            insertData: [],
            listens: {
                'insertData': 'processingInsertData'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            loader.get(this.name).hide();

            return this;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'insertData'
                ]);

            return this;
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        }
    });
});
