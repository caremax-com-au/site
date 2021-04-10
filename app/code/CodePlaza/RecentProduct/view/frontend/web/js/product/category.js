define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/list/column-status-validator'
], function (Column, columnStatusValidator) {
    'use strict';

    return Column.extend({

        /**
         * Get category name.
         *
         * @param {Object} row
         * @return {*}
         */
        getValue: function (row) {
            return row['category']['name'];
        },

        /**
         * @param row
         * @returns {*|boolean}
         */
        isAllowed: function (row) {
            return (columnStatusValidator.isValid(this.source(), 'category', 'show_attributes') && this.hasValue(row) );
        }

    });
});