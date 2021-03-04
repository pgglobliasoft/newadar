define([
    'jquery',
    'Magento_Ui/js/form/element/boolean'
], function ($, Select) {
    'use strict';
    return Select.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },        
        /**
         * Change currently boolean option
         *
         * @param {String} id
         */
        onUpdate: function (value) { 
                     
        }, 
    });
});