define([
    "jquery",
    "mage/template",
    "Magento_Customer/js/customer-data",
    "text!Sttl_Customerorder/template/neworder-style.html",
    "mage/validation/validation",
], function($, mageTemplate, customerData, thumbnailPreviewTemplate) {
    "use strict";
    var newArray = [];
    $.widget("mage.SwatchRenderer", {
        options: {
            configProSku: {}
        },
        _init: function() {
            var $widget = this;
            var sku = this.options.configProSku;
            
        },

        _userAction: async function(sku){
            var url = "https://dev.adaruniforms.com/rest/V1/ProductApi?id="+sku
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                  'Content-Type': 'application/json'
                }
              })
          const myJson = await response.json();
          console.log(myJson);
          return myJson;
        },

        _create: async function() {
            var options = this.options,
                element = this.element,
                $widget = this;
            var skus = this.options.configProSku;
            const todoIdList = []
            $.each(skus,function(key,val){
                todoIdList.push(val.sku)
            })
            const tem = [];
            for(var i = 0; i < todoIdList.length; i=i+3) {
                var str = ''
                for (var j = i; j < i+3; j++) {
                     str += todoIdList[j]+","
                 }
                str = str.substring(0,str.length - 1);
                tem.push(str); 
            }   
            Promise.all(
              tem.map(id => {
                return new Promise((resolve) => {
                  fetch("https://dev.adaruniforms.com/rest/V1/ProductApi?id="+id,{
                    method: 'GET',
                    headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json'
                    }
                  })
                    .then(response => {
                      return new Promise(() => {
                        response.json()
                          .then(todo => {
                            newArray = $.merge(newArray, todo);
                            console.log(newArray)
                            resolve(1000)
                          })
                      })
                    })
                })
              })
            )
        },
        _EventListener: function() {
            var $widget = this,
                options = this.options.classes,
                target;
            
        },
        
    });
    return $.mage.SwatchRenderer;
});