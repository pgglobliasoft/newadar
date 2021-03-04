define([
'underscore',
'Magento_Ui/js/grid/columns/select'
], function (_, Column) {
'use strict';

return Column.extend({
    defaults: {
        bodyTmpl: 'Sttl_Collectionsilder/form/productcollection'
    },
    getCustomFunction: function (row) {            
        console.log(row.product_collection);
    }
});
});