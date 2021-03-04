define([
'jquery',
"mage/template",
"mage/requirejs/json!Sttl_Customerorder/template/Inventory.json",
"text!Sttl_Customerorder/template/quick_lineitem.html"
],
    function($,mageTemplate,inventory,lineitemstemp) {
        'use strict';
        var $widget1 = this
        var removedskus = [],
        ordertotaldata = {},
         finalitems = [];
        return { 
        	options: {
	            jsonConfig: inventory,
				customersBulcDiscount: {},
	            customersFlatDiscount: {},
	        },
        	lineitem: function($this,_current_options,styles = {}, click_event = "") {                
                var $widget = $this,
                orderdata = "",
                currency_convertedsummary = {},
                allorderitems = this._getOrderDataItems($widget, orderdata, _current_options, styles);
                console.log("allorderitems",allorderitems)

                this._getorderSummaryinfo($widget, orderdata);
                 
                var DiscountPer = ordertotaldata.DiscountPer,
                TotalDiscountPer = ordertotaldata.TotalDiscountPer,
                tmp_FlatDiscount = parseFloat(DiscountPer) + parseFloat(TotalDiscountPer),
                tmp_DiscountAmount = ordertotaldata.DiscountAmount,
                TotalDiscountAmount = ordertotaldata.TotalDiscountAmount,
                tmp_DiscountAmount = parseFloat(tmp_DiscountAmount) + parseFloat(TotalDiscountAmount);
            console.log("order",ordertotaldata)

            var lineitem_temp = mageTemplate(lineitemstemp, {
                finalorderrendere: allorderitems
                // ordersummaryinfo: currency_convertedsummary,
                // databystylegroup: $widget._DatabyStyle(),
                // currencyconvert: $.proxy(this._convertcurrency, this),
                // generateDiscountTooltip: $widget._generateDiscountTooltip(),
            });

            },
             _generateordertotalarray: function($widget) {
	            var data_selector = $(".colorContainer").find(".checkvalue");
	            var customersFlatDiscount = this.options.customersFlatDiscount;
	            console.log("customersFlatDiscount",customersFlatDiscount);
	            customersFlatDiscount = customersFlatDiscount;
	            var beforebulkdiscount = customersFlatDiscount;
	            var sellingprice = 0;
	            var discountAmount = 0;
	            var customrsbulkdiscount = this.options.customersBulcDiscount;
	            var DocTotalQty = 0;
	            finalitems.forEach(function(item, index) {
	                sellingprice = parseFloat(sellingprice) + parseFloat(item.TotalPrice);
	                DocTotalQty = parseInt(DocTotalQty) + parseInt(item.QTYOrdered);
	            });
	            DocTotalQty = parseFloat(DocTotalQty);
	            var bulkdiscount = 0;
	            if (customrsbulkdiscount.length > 0) {
	                customrsbulkdiscount.forEach(function(item, index) {
	                    var bulkqtyfrom = item.QtyFrom;
	                    var bulkqtyto = item.QtyTo;
	                    if (DocTotalQty >= bulkqtyfrom && DocTotalQty <= bulkqtyto) {
	                        bulkdiscount = item.Discount;
	                    }
	                });
	            }
	            var totalpriceordered = sellingprice;
	            customersFlatDiscount = parseFloat(customersFlatDiscount) + parseFloat(bulkdiscount);
	            if (customersFlatDiscount > 0) {
	                sellingprice = sellingprice - sellingprice * (customersFlatDiscount / 100);
	                discountAmount = totalpriceordered * (customersFlatDiscount / 100);
	            }
	            ordertotaldata = {
	                TotalBeforeDiscount: totalpriceordered,
	                DiscountAmount: discountAmount,
	                DiscountPer: customersFlatDiscount,
	                DocTotal: sellingprice,
	                TotalDiscountPer: 0,
	                TotalDiscountAmount: 0,
	                TotalQtyOrdered: DocTotalQty,
	                FlatDiscount: beforebulkdiscount,
	            };
	            return ordertotaldata;
	        },
	        _getorderSummaryinfo: function($widget, _ordersummary) {
	            var ordersummary = "";
	            if (_ordersummary != "") {
	                ordersummary = _ordersummary.line_item_render.ordersummary;
	            } else {
	                ordersummary = this._generateordertotalarray($widget);
	            }
	            return ordersummary;
	        },
            _convertcurrency: function(price) {
            var x = price;
            x = x.toString();
            var afterPoint = "";
            if (x.indexOf(".") > 0) afterPoint = x.substring(x.indexOf("."), x.length);
            x = Math.floor(x);
            x = x.toString();
            var lastThree = x.substring(x.length - 3);
            var otherNumbers = x.substring(0, x.length - 3);
            if (otherNumbers != "") lastThree = "," + lastThree;
            return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;
        },
             _getOrderDataItems: function($this, _response, _current_options, styles = {}) {
            var $widget = $this,
                response = _response,
                mainresponce = {},
                style = "",
                submitcolor = "",
                viewmode = "",
                stylebyInventory = this._stylebyInventory(),
                data = this.options.jsonConfig;
            if (this._DatabyStyle() && this._stylebyInventory()) {
                var databyStyle = this._DatabyStyle();
                var allorderdata = "";
                if (response != "") {
                    allorderdata = response.line_item_render.allorderdata;
                } else {
                    allorderdata = this._generateqtyarray(_current_options, $widget, styles);
                }
                var tmp_distinstyle = allorderdata.map(function(item) {
                    return item.Style;
                });
                var id = $(".customquickviewpopup.quickviewpopup1").attr('id');
                var sizegrouparray = id;
                if (sizegrouparray) {

                        var item_size = sizegrouparray;
                        var groupstyle = item_size;
                        var current_style = "viewtype";
                        var datastyle_index = databyStyle.index;

                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                mainresponce[current_style] = {};
                            }
                        });
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size;
                                    if (stylegroup == item_size) {
                                        mainresponce[current_style][stylegroup] = {};
                                    }
                                }
                            }
                        });
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size;
                                    var colorcode = item.ColorCode;
                                    if (stylegroup == item_size) {
                                        mainresponce[current_style][stylegroup][colorcode] = {};
                                    }
                                }
                            }
                        });
                        var order_item_count = 0;
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size;
                                    var colorcode = item.ColorCode;
                                    if (stylegroup == item_size) {
                                        mainresponce[current_style][stylegroup][colorcode][order_item_count] = item;
                                        order_item_count++;
                                    }
                                }
                            }
                        });
                }
            }
            return mainresponce;
        },
        _stylebyInventory: function() {
            var data = this.options.jsonConfig,
                temp_items = [];
            data.forEach(function(item, index) {
                var style = item.Style;
                temp_items[style] = item;
            });
            return temp_items;
        },
         _DatabyStyle: function() {
            var data = this.options.jsonConfig;
            var temp_databyStyle = {};
            data.forEach(function(item, index) {
                var sizegroup = item.SizeGroup;
                temp_databyStyle[sizegroup] = {};
            });
            data.forEach(function(item, index) {
                var sizegroup = item.SizeGroup;
                var sizeorder = item.SizeOrder;
                var size = item.Size;
                temp_databyStyle[sizegroup][sizeorder] = size;
            });
            return temp_databyStyle;
        },
         _generateqtyarray: function(_current_options, $widget, styles = {}) {
            removedskus = [];
            var data_selector = $(".colorContainer").find(".checkvalue");
            var current_options = _current_options;
            item_edited = false;
            if (current_options == 1 && current_options != 0 && current_options != "") {
                $(data_selector).each(function() {
                    var count = 0;
                    if ($(this).val() != "") {
                        var selectcolor = $(this).closest("td").find("input[name=selectcolor]").val();
                        var selectsize = $(this).closest("td").find("input[name=selectsize]").val();
                        var base_price = $('input[name="mainprice[' + selectcolor + "][" + selectsize + ']"').val();
                        var disprice = $('input[name="DiscountPrice[' + selectcolor + "][" + selectsize + ']"').val();
                        var added_qty = $(this).val();
                        var itemcode = $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val(),
                            order_item = this._getItemfromOrderList(itemcode);
                        if (order_item.length > 0) {
                            if (order_item[0].QTYOrdered !== added_qty) {
                                item_edited = true;
                            }
                        } else {
                            item_edited = true;
                        }
                        var pafterdiscount = parseFloat();
                        var pbeforediscount = added_qty * base_price;
                        pbeforediscount = parseFloat(pbeforediscount);
                        if (disprice < base_price) {
                            pafterdiscount = added_qty * disprice;
                        } else {
                            pafterdiscount = added_qty * base_price;
                        }
                        var tmpitem = {};
                        var current_itemcode = $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val();
                        tmpitem = {
                            ColorCode: $('input[name="colorcode[' + selectcolor + "][" + selectsize + ']"').val(),
                            ItemCode: $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val(),
                            ColorStatus: $('input[name="ColorStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            DiscountPer: $('input[name="DiscountPer[' + selectcolor + "][" + selectsize + ']"').val(),
                            DiscountPrice: $('input[name="DiscountPrice[' + selectcolor + "][" + selectsize + ']"').val(),
                            OrderOption: "1",
                            PriceAfterDiscount: "" + pafterdiscount + "",
                            QTYOrdered: added_qty,
                            Size: selectsize,
                            Style: $(".customquickviewpopup.quickviewpopup1").attr('id'),
                            StyleStatus: $('input[name="StyleStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            TotalPrice: "" + pafterdiscount + "",
                            UnitPrice: base_price,
                            PriceBeforeDiscount: "" + pbeforediscount + "",
                            Color: selectcolor,
                            Type: "normal",
                        };
                        var itemcodeexistinarray = false;
                        var array_index = -1;
                        finalitems.forEach(function(item, index) {
                            if (item.ItemCode == current_itemcode && itemcodeexistinarray == false) {
                                itemcodeexistinarray = true;
                                array_index = index;
                            }
                        });
                        if (itemcodeexistinarray) {
                            if (added_qty > 0) {
                                finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                                finalitems[array_index].QTYOrdered = added_qty;
                                finalitems[array_index].TotalPrice = pafterdiscount;
                            } else {
                                var tml_finalitems = _.filter(finalitems, function(item) {
                                    if (item.ItemCode == current_itemcode) {
                                        removedskus.push(current_itemcode);
                                    }
                                    return item.ItemCode !== current_itemcode;
                                });
                                finalitems = tml_finalitems;
                            }
                        } else {
                            if (added_qty > 0) {
                                finalitems.push(tmpitem);
                            }
                        }
                    }
                });
            }
            return finalitems;
        },
        _generateqtyarray: function(_current_options, $widget, styles = {}) {
            removedskus = [];
            var data_selector = $(".colorContainer").find(".checkvalue");
            var current_options = _current_options;
            if (current_options == 1 && current_options != 0 && current_options != "") {
                $(data_selector).each(function() {
                    var count = 0;
                    if ($(this).val() != "") {
                        var selectcolor = $(this).closest("td").find("input[name=selectcolor]").val();
                        var selectsize = $(this).closest("td").find("input[name=selectsize]").val();
                        var base_price = $('input[name="mainprice[' + selectcolor + "][" + selectsize + ']"').val();
                        var disprice = $('input[name="DiscountPrice[' + selectcolor + "][" + selectsize + ']"').val();
                        var added_qty = $(this).val();
                        var itemcode = $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val();
                        
                        var pafterdiscount = parseFloat();
                        var pbeforediscount = added_qty * base_price;
                        pbeforediscount = parseFloat(pbeforediscount);
                        if (disprice < base_price) {
                            pafterdiscount = added_qty * disprice;
                        } else {
                            pafterdiscount = added_qty * base_price;
                        }
                        var tmpitem = {};
                        var current_itemcode = $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val();
                        tmpitem = {
                            ColorCode: $('input[name="colorcode[' + selectcolor + "][" + selectsize + ']"').val(),
                            ItemCode: $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val(),
                            ColorStatus: $('input[name="ColorStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            DiscountPer: $('input[name="DiscountPer[' + selectcolor + "][" + selectsize + ']"').val(),
                            DiscountPrice: $('input[name="DiscountPrice[' + selectcolor + "][" + selectsize + ']"').val(),
                            OrderOption: "1",
                            PriceAfterDiscount: "" + pafterdiscount + "",
                            QTYOrdered: added_qty,
                            Size: selectsize,
                            Style: $(".customquickviewpopup.quickviewpopup1").attr('id'),
                            StyleStatus: $('input[name="StyleStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            TotalPrice: "" + pafterdiscount + "",
                            UnitPrice: base_price,
                            PriceBeforeDiscount: "" + pbeforediscount + "",
                            Color: selectcolor,
                            Type: "normal",
                        };
                        var itemcodeexistinarray = false;
                        var array_index = -1;
                        finalitems.forEach(function(item, index) {
                            if (item.ItemCode == current_itemcode && itemcodeexistinarray == false) {
                                itemcodeexistinarray = true;
                                array_index = index;
                            }
                        });
                        if (itemcodeexistinarray) {
                            if (added_qty > 0) {
                                finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                                finalitems[array_index].QTYOrdered = added_qty;
                                finalitems[array_index].TotalPrice = pafterdiscount;
                            } else {
                                var tml_finalitems = _.filter(finalitems, function(item) {
                                    if (item.ItemCode == current_itemcode) {
                                        removedskus.push(current_itemcode);
                                    }
                                    return item.ItemCode !== current_itemcode;
                                });
                                finalitems = tml_finalitems;
                            }
                        } else {
                            if (added_qty > 0) {
                                finalitems.push(tmpitem);
                            }
                        }
                    }
                });
            }
            return finalitems;
        },
       

        }

    });
