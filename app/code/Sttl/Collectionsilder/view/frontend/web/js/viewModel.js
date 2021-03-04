define([
    'jquery',
    'uiComponent',
    'ko'
], function($,Component, ko) {
    return Component.extend({
        clock: ko.observableArray([
                { name: "Bungle", type: "Bear" },
                { name: "George", type: "Hippo" },
                { name: "Zippy", type: "Unknown" }
            ]),
        items: ko.observableArray(),
        collection: ko.observableArray([]),
        category: ko.observableArray([]),
        allItems: ko.observableArray([]),



        initialize: function (config) {

            var collection = new Array();
            var test = this.getConfigurableProductList(config.jsonConfig);
            this._super();
            this.allItems(this.getConfigurableProductList(test));

            $.each( test ,function(key,val){collection.push(val.Collection); })
            this.collection(this.unique(collection));
            
            console.log(collection);

            
        },

        unique:function (array){
            return array.filter(function(el, index, arr) {
                return index === arr.indexOf(el);
            });
        },

         getConfigurableProductList: function(jsonConfig){          
            var dt = [],
                color = [],
                cat = [];
            var data1 = jsonConfig.map(item => item).filter(function(value, index, self) {
                    if(dt.indexOf(value.Style) === -1){
                        if(color.indexOf(value.ColorCode)  === -1 || value.ColorCode == ''){
                                color.push(value.ColorCode);
                                dt.push(value.Style);
                                return value;
                        }else{
                            if(cat.indexOf(value.Style) === -1){cat.push(value.Style); }
                        }
                }
            });
            color = dt = [];
            var data2 = jsonConfig.map(item => item).filter(function(value, index, self) {
                if(cat.indexOf(value.Style) > 0 &&  dt.indexOf(value.Style) === -1 ){
                    if(color.indexOf(value.ColorCode)  === -1 || value.ColorCode == ''){
                        color.push(value.ColorCode);
                        dt.push(value.Style);
                        return value;
                    }
                }
            });
            return data1.concat(data2);
        },

        reloadTime: function () {
            /* Setting new time to our clock variable. DOM manipulation will happen automatically */
            this.clock(Date());
        },
        getClock: function () {
            return this.clock;
        }
    });
});