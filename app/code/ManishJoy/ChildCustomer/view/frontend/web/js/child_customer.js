define([
    'jquery',
    'mage/template',
    'text!ManishJoy_ChildCustomer/template/childusertable.html',
], function ($, mageTemplate,childcustomertemp) {
    'use strict';
    $.widget('mage.SwatchRenderer', {
        options: {
            result: {},
            actionDurl: {},
            dataurl: {}
        },
        _init: function () {
            var $widget = this
            $widget.childcustomerhtml($widget)    
            this._EventListener();
        },
        childcustomerhtml: function($widget){
            console.log($widget.options.result)
            var childcustomer = mageTemplate(childcustomertemp, {
                       result: $widget.options.result  
                    });
            $("#user-management").html(childcustomer)
        },
        _create: function() {
        },
        _EventListener: function(){
        },
    });
    return $.mage.SwatchRenderer; //list
});