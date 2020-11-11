define([
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/filters/range'
], function($, _, Component) {
    return Component.extend({
        defaults : {
            templates : {
                period : {},
                ranges: {
                    from_length: {
                        label: 'from',
                        dataScope: 'from_length',
                        component: 'Magento_Ui/js/form/element/abstract'
                    },
                    from_unit: {
                        dataScope: 'from_unit',
                        component: 'Magento_Ui/js/form/element/select'
                    },
                    to_length: {
                        label: 'to',
                        dataScope: 'to_length',
                        component: 'Magento_Ui/js/form/element/abstract'
                    },
                    to_unit: {
                        dataScope: 'to_unit',
                        component: 'Magento_Ui/js/form/element/select'
                    },
                }
            }
        },

        /**
         * Initializes range component.
         *
         * @returns {Range} Chainable.
         */
        initialize: function (params) {
            params.options = JSON.parse(params.options);
            params.templates = params.templates || {};
            params.templates.period = params.templates.period || {};
            params.templates.period.options = params.options;

            this._super();

            return this;
        },

        /**
         * Returns an array of child components previews.
         *
         * @returns {Array}
         */
        getPreview: function () {
            var ret = this.elems.map('getPreview');
            if(ret.length === 4) {
                ret = [
                    (ret[0] ? ret[0] + ' ' + ret[1] + '(s)' : ''),
                    (ret[2] ? ret[2] + ' ' + ret[3] + '(s)' : '')
                ];
            }
            if(ret.length === 2) {
                if(! ret[0] && ! ret[1]) {
                    ret = [];
                }
            }
            if(! ret.length) {
                return false;
            }
            return ret;
        }
    });
});