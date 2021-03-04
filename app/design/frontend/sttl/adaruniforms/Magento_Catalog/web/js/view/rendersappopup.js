define(['jquery', 
  'mage/template',
  'text!Sttl_Customerorder/template/view_stock_pricing.html',
  'text!Sttl_Customerorder/template/view_stock_pricing_button.html',
  "text!Sttl_Customerorder/template/quickview-lineitem.html",
], function($,mageTemplate,view_stock_pricing,view_stock_pricing_button,lineitemstemp) {
    'use strict';
     var ordertotaldata = {},
         $widget = this,
         finalitems = [], 
          item_edited = true,
           remove_select_option,
         removedskus = [];

    $.widget('mage.PopupRenderer', {
        options: {
            
            parentstyledata: {},
            parent_style: {},
            poConfig:{},
            customersFlatDiscount: {},
            ConfigStyle: {}
        },

        _init: function () { 
            this._EventListener();     
            this.options.ConfigStyle = this.getConfigurableProduct(this.options.parentstyledata , 'Style'); 
                  
        },

        _create: function () {

        },
         getConfigurableProduct: function(events, key){
            var result = events.reduce(function(memo, e1){
                var matches = memo.filter(function(e2){
                  return e1.Style == e2.Style 
                                  })  
                if (matches.length == 0)
                    memo.push(e1)
                return memo;
            }, [])
            return result;
        },

        _EventListener: function (e, data) {
             var $widget = this,
                options = this.options;
                $(".buyNowBtnMain").css({"pointer-events": "all", "opacity": "1"});
                $('.loadShipping').hide()
                $('.productinebuynow').show();
                $widget._Openstockpopup($(this),$widget);

                $(document).on("click",".saveChng", function(){
                  if($('.tabactive').val() == ''){
                    return false;
                  }else{
                    var _current_options = 1;
                    $widget._optiononeAdddata(data);
                    $(".checkvalue").removeClass("valuess");
                    $(".checkvalue").each(function() {
                        $(this).val('');
                        $(this).removeClass('valuess');
                        $(this).next("span").html('');
                        var selectprice = $(this).closest('td').find('input[type=hidden]').val();
                        var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                        var selectsize = $(this).closest('td').find('input[name=selectsize]').val();
                        $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
                        $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
                    });
                  }
                })
                $(document).on('change',"#select_existing", function() {
                  var existponumberText = $('#select_existing :selected').val();
                  var existingorderText = $('#select_existing :selected').attr('order_id');
                  console.log("existingorderText-",existingorderText)
                  
                  if(existponumberText != ''){
                    $widget._updateponumberedit2($widget);
                    $widget._getLineItemTable(existingorderText);
                  }else{
                    finalitems = [];
                    $widget._getLineItemTable(existingorderText);
                  }
                })

                    $(document).on("click", ".delSelectedRecords", function(e) {
                    return $widget._DeleteSelectedorderdata($(this), $widget);
                });

                $(document).on("click", ".selectallRecord", function() {
                    var selec = $(this).parents("table.lineItemsList").find("input.deleteMultiRecord");
                    if ($(this).is(":checked")) {
                        selec.each(function() {
                            $(this).prop("checked", true);
                        });
                    } else {
                        selec.each(function() {
                            $(this).prop("checked", false);
                        });
                    }
                });

                $(document).on("click", "input.deleteMultiRecord", function() {
                    var selec = $(this).parents("table.lineItemsList").find("input.deleteMultiRecord"),
                        total_row = selec.length,
                        total_selected = 0;
                    selec.each(function() {
                        if ($(this).is(":checked")) {
                            total_selected++;
                        }
                    });
                    if (total_selected == total_row) {
                        $(this).parents("table.lineItemsList").find("input.selectallRecord").prop("checked", true);
                    } else {
                        $(this).parents("table.lineItemsList").find("input.selectallRecord").prop("checked", false);
                    }
                });

                $(document).on("click",".productview-modal-close-inside", function(){
                      finalitems = [];
                });

                 $(document).on("click", ".delSingalRecords", function(e) {
                    if (confirm("Are you sure you want to delete?")) return $widget._Deleteorderdata($(this), $widget);
                });

                $(document).on("click",".catBtns .customBtns", function(){
                      var base_url = window.location.origin;
                      $("#color-data").find(".product_options").removeClass("active");
                      var style = $(this).attr("product-sku"),
                      child_pro = $widget.getProductArray(style , 1)
                      var styleconfiguration = mageTemplate(view_stock_pricing_button, {
                                data: child_pro,                       
                                parent_color: style, 
                                customersFlatDiscount : $widget.options.customersFlatDiscount,
                                baseurl : base_url,
                            });
                    if(styleconfiguration){
                      $("#color-data").html(styleconfiguration)
                      $(".catBtns .customBtns").removeClass("activeCat");
                      $(this).addClass("activeCat");
                    }
                       // $(".swatch-color-container").find(".swatch-option.image").first().trigger("click")
                       setTimeout(function(){ $(".swatch-color-container").find(".swatch-option.image").first().trigger("click") }, 1000);
                  });


                $(document).on('input','.tabactive', function(e) {
                  if($(this).val().length <= 0)
                  {
                     $('#order_id').val('');
                     $('.editpodashboard').attr('po_number','');
                  }
              }) 

               $(document).on('focusout','.tabactive', function(e) {
                  var ponumberText = $(this).val();
                  if(ponumberText.length == 0){
                    $("#select_existing option:selected").prop("selected", false)
                    $("#select_existing").prop("disabled", true);
                     $('#sap_ponumber_id').val(null);
                     $('#order_id').val(null);
                    finalitems = [];
                              var current_options = '';
                              $widget._renderLineitembeforeAJAX($widget, current_options);
                  }
                  $('.old_po').attr('value',ponumberText)
                  if($(this).attr('readonly'))
                  {
                    return false;
                  }
                  if($.trim(ponumberText).length > 0)
                  {
                      var IsExists = false;
                      $('#select_existing option').each(function(){
                          if (this.text == ponumberText)
                          IsExists = true;
                      });
                      if(IsExists)
                      {
                        var order = $('option[value='+ponumberText+']').attr('order_id');
                        $('#sap_ponumber_id, #order_id').val(order);
                      }
                          var oldpo = $('.old_po').val();
                          var newpo = $('.editpodashboard').attr('po_number');
                          if(oldpo != newpo && newpo != '')
                          {
                            var exist = false;
                            $('#select_existing option').each(function(){
                                if (this.text == newpo || this.text == ponumberText)
                                  exist = true;
                                  var order = $('option[value='+newpo+']').attr('order_id');
                                  $('#sap_ponumber_id, #order_id').val(order);
                            });
                              if(exist)
                              {
                                  $widget._updateponumberedit($widget);
                              }
                          }
                          if(ponumberText.length < 4 || ponumberText.length == 0)
                          {   
                              finalitems = [];
                              var current_options = '';
                              $widget._renderLineitembeforeAJAX($widget, current_options);
                              $widget._adderror('PO Number must be a number or letter special character and at least 4 characters long.')
                              $('#overlay').show()
                              $('[data-toggle="collapse"]').prop('disabled',true);
                              $("#select_existing").prop("disabled", true);
                              return false;
                          }
                          else
                          {
                              $(".tabactive").attr("disabled", true);
                              $('.editpodashboard').show();
                              $('[data-toggle="collapse"]').prop('disabled',false);
                              $('#overlay').hide()
                              $("#select_existing").prop("disabled", true);
                              $("#select_existing option:selected").prop("selected", false)
                          }
                  }
                  else
                  {
                      $("#select_existing").prop("disabled", false);
                  }
              });

               $(document).on('click','.editpodashboard',function(){
                    var ponumberval = $(".tabactive").val();
                    $(".tabactive").attr("disabled", false);
                    $(".tabactive").focus();
                    $(this).attr('po_number',ponumberval);
                    $(this).hide();
                 })  

          $( document ).ajaxSuccess(function( event, xhr, settings ) {

            var browser = '';
            
            var whichbrowser = $widget._myFunction(browser);
            var successOk = 'success';
            if(whichbrowser == 'Firefox')
            {
              successOk = 'OK';
            }
            if(xhr.statusText == successOk){
              var ajaxresponce = xhr.responseJSON;

              if(typeof ajaxresponce != 'undefined'){
                var responce_length = Object.keys(ajaxresponce).length;
                if (responce_length > 0 && ajaxresponce.hasOwnProperty("is_view_stock")) {
                  if(ajaxresponce.is_view_stock == '1'){
                    console.log("XHR",xhr.responseJSON);
                    var responce = xhr.responseJSON,
                        responce_order_id = atob(responce.base64_order_id),
                        poconfig_data = $widget.options.poConfig,
                        responce_ponumber = atob(responce.base64_ncp_id);

                    var falg = _.filter(poconfig_data, function(value) {
                        return value.OrderId === parseInt(responce_order_id);
                    });

                    if(falg.length > 0){
                      console.log("Po Exist..");
                    }else{
                      var item = {
                        NumatCardPo : responce_ponumber,
                        OrderId: parseInt(responce_order_id)
                      }
                      $('#select_existing').append('<option value="'+responce_ponumber+'" order_id = "'+responce_order_id+'">'+responce_ponumber+'</option>');
                      $widget.options.poConfig.push(item);
                      console.log("Po Not Exist..");
                      console.log($widget.options.poConfig);
                    }

                  }

                }
              }
            }
          });

          
        },
         getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.parentstyledata , function (value) {
                    return value[key] === sku;
            });
           return falg;

        },

         _DeleteSelectedorderdata: function($this, $widget) {
            var somethingChecked = false;
            $("input.deleteMultiRecord:checked").each(function() {
                if ($(this).is(":checked")) {
                    somethingChecked = true;
                }
            });
            if (somethingChecked) {
                if (confirm("Are you sure you want to delete?")) {
                    var po_number = $(".tabactive").val(),
                        delete_styles = [],
                        delete_colors = [];
                    $.each($("input.deleteMultiRecord:checked"), function() {
                        var tmp_delete_styles = $(this).next().val(),
                            tmp_delete_colores = $(this).next().next().val();
                        delete_styles.push(tmp_delete_styles);
                        delete_colors.push(tmp_delete_colores);
                        var tmp_orderdata = _.filter(finalitems, function(item) {
                            if (item.Style == tmp_delete_styles && item.ColorCode == tmp_delete_colores) {} else {
                                return true;
                            }
                        });
                        var all_null = true;
                        tmp_orderdata.forEach(function(item, index) {
                            if (item.ColorCode != "") {
                                all_null = false;
                            }
                        });
                        if (all_null) {
                            tmp_orderdata = [];
                        }
                        finalitems = tmp_orderdata;
                    });
                    var is_holeorderdelete = false;
                    if (finalitems.length <= 0) {
                        var baseorder_id = $(".row.add_update_po_section .tabactive").attr('base_data');
                        $widget._deleteOrder($widget, baseorder_id);
                        is_holeorderdelete = true;
                    }
                    var current_options = "";
                    $widget._renderLineitembeforeAJAX($widget, current_options);
                    if (!is_holeorderdelete) $widget._deleteRecord(delete_styles, delete_colors, po_number);
                }
            } 
            else $widget._adderror("Select any 1 of the Order item for the Delete.");
        },
          _adderror: function(message){
          $("#posuccess-message p").html(message);
          $("#posuccess-message").modal("openModal");
          $(".modalContainer").css("pointer-events","none");
          setTimeout(function(){
            $(".modalContainer").css("pointer-events","");
            $("#posuccess-message").modal("closeModal");
          }, 3000);
          return true;
        },
        getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.parentstyledata , function (value) {
                    return value[key] === sku;
            });
           return falg;
        },
        _Openstockpopup: function($this,$widget) {
          var base_url = window.location.origin;
            var style = $this.attr("id");
            var styleconfiguration = mageTemplate(view_stock_pricing, {
                        data: this.getProductArray(style , 1),                       
                        parent_color: style, 
                        customersFlatDiscount : this.options.customersFlatDiscount,
                        rptswitcher: $widget._getRPTswitcherButtons(style, $widget),
                        ponumber: this.options.poConfig,
                        baseurl : base_url,
                    });
            $(".product-info").html(styleconfiguration)
            setTimeout(function(){ $(".swatch-color-container").find(".swatch-option.image").first().trigger("click") }, 2000);
             
            
        }, 
        _getRPTswitcherButtons: function( _sku, $widget){
            var petiteSku = '';
            var tailSku = '';
            var regularSku = '';
            var currentsku = _sku;
            var check = currentsku.substr((currentsku.length)-1, 1);
            var sapconfigdata = this.getConfigurableProduct(this.options.parentstyledata , 'Style');
            var sapconfigskus = [];
            sapconfigdata.forEach(function(item, index){
                sapconfigskus.push(item.Style);
            });
            if(check.toUpperCase() == ('P').toUpperCase() || check.toUpperCase() == ('T').toUpperCase()){
              regularSku = currentsku.substr(0, (currentsku.length - 1))
            }else{
              regularSku = _sku;
            }
            if(tailSku == '' && petiteSku == ''){
              tailSku = regularSku+'T';
              petiteSku = regularSku+'P';
            }
            var available_skus = {};
            if(petiteSku != ''){
              if(_.contains(sapconfigskus, petiteSku)) {
                available_skus["petite"] = petiteSku;
              }else{
                petiteSku = '';
              }
            }
            if(tailSku != ''){
              if(_.contains(sapconfigskus, tailSku)) {
                available_skus["tall"] = tailSku;
              }else{
                tailSku = '';
              }
            }
            if(regularSku != ''){
              if(_.contains(sapconfigskus, regularSku)) {
                available_skus["regular"] = regularSku;
              }else{
                regularSku = '';
              }
            }
           return available_skus;
        },
        _renderLineitembeforeAJAX: function($this,_current_options,styles = {}, click_event = "") {                
                var $widget = $this,
                orderdata = "",
                currency_convertedsummary = {},
                allorderitems = this._getOrderDataItems($widget, orderdata, _current_options, styles);
                $widget._getorderSummaryinfo($widget, orderdata);
                console.log("allorderitems",allorderitems)
                var DiscountPer = ordertotaldata.DiscountPer,
                TotalDiscountPer = ordertotaldata.TotalDiscountPer,
                tmp_FlatDiscount = parseFloat(DiscountPer) + parseFloat(TotalDiscountPer),
                tmp_DiscountAmount = ordertotaldata.DiscountAmount,
                TotalDiscountAmount = ordertotaldata.TotalDiscountAmount,
                tmp_DiscountAmount = parseFloat(tmp_DiscountAmount) + parseFloat(TotalDiscountAmount);
                
              currency_convertedsummary = {
                  TotalBeforeDiscount: $widget._convertcurrency(ordertotaldata.TotalBeforeDiscount.toFixed(2)),
                  DiscountAmount: $widget._convertcurrency(tmp_DiscountAmount.toFixed(2)),
                  DiscountPer: ordertotaldata.DiscountPer,
                  DocTotal: $widget._convertcurrency(ordertotaldata.DocTotal.toFixed(2)),
                  TotalDiscountPer: ordertotaldata.TotalDiscountPer,
                  TotalDiscountAmount: $widget._convertcurrency(ordertotaldata.TotalDiscountAmount),
                  TotalQtyOrdered: ordertotaldata.TotalQtyOrdered,
                  FlatDiscount: $widget._convertcurrency(tmp_FlatDiscount.toFixed(2)),
              };
              if(finalitems.length == 0){
                $widget.delete_drop();
              }
              var current_item = $(".product_options.active").attr("id");
              var lineitem_temp = mageTemplate(lineitemstemp, {
                  editid: current_item,
                  finalorderrendere: allorderitems,
                  ordersummaryinfo: currency_convertedsummary,
                  databystylegroup: $widget._DatabyStyle(),
                  currencyconvert: $.proxy(this._convertcurrency, this),
                  generateDiscountTooltip: $widget._generateDiscountTooltip(),
              });
              $('.quicklineitems').html(lineitem_temp)
            },
            delete_drop: function (argument) {
                $(document).on('change',"#select_existing", function() {
                  if($('#select_existing option').filter(function(){ return $(this).val() == remove_select_option; }).length){
                          $("#select_existing option[value='"+remove_select_option+"']").remove();
                    }
                })
            },
            _generateDiscountTooltip: function($widget) {
              var message = "",
                  customrsbulkdiscount = this.options.customersBulcDiscount,
                  DocTotalQty = ordertotaldata.TotalQtyOrdered,
                  DocTotalQty = parseInt(DocTotalQty),
                  DocTotal = ordertotaldata.DocTotal,
                  tmp_customerdata = this.options.customersFlatDiscount,
                  customerflatedis = tmp_customerdata[0].FlatDiscount,
                  message_added = false,
                  currentdiscont = ordertotaldata.DiscountPer;

              if (customrsbulkdiscount.length > 0) {
                  customrsbulkdiscount.forEach(function(item, index) {
                      if(DocTotalQty >= item.QtyFrom && DocTotalQty <= item.QtyTo){
                          customrsbulkdiscount.forEach(function(itema, indexa) {
                              if(item.QtyTo+2 > itema.QtyFrom && item.QtyTo < itema.QtyTo){
                                  var more_item = parseInt(itema.QtyFrom) - DocTotalQty,
                                      next_discount = parseFloat(customerflatedis) + parseFloat(itema.Discount);
                                  message = "DISCOUNT: Add " + more_item + " more items to qualify for " + next_discount + "% off your order";
                                  message_added = true;
                                  return message;
                              }
                          });
                      }
                  });

                if(!message_added){
                    customrsbulkdiscount.sort(function(a, b) {
                        return a.QtyFrom - b.QtyFrom;
                    });
                    var avail_to_get_dis = parseFloat(customrsbulkdiscount[customrsbulkdiscount.length-1].U_FD) + parseFloat(customrsbulkdiscount[customrsbulkdiscount.length-1].Discount);
                    if(currentdiscont < avail_to_get_dis){
                        var more_item = parseInt(customrsbulkdiscount[0].QtyFrom) - DocTotalQty,
                            next_discount = parseFloat(customerflatedis) + parseFloat(customrsbulkdiscount[0].Discount);
                        message = "DISCOUNT: Add " + more_item + " more items to qualify for " + next_discount + "% off your order";
                        return message;
                    }else{
                        message = '';
                    }
                }
            }
            return message;
        },
        _generateordertotalarray: function($widget) {
            var data_selector = $(".colorContainer").find(".checkvalue");
            var customersFlatDiscount = $widget.options.customersFlatDiscount;
            customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;
            var beforebulkdiscount = customersFlatDiscount;
              var beforebulkdiscount = customersFlatDiscount;
              var sellingprice = 0;
              var discountAmount = 0;
              var customrsbulkdiscount = $widget.options.customersBulcDiscount;
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
              var grandtotal =  $widget._convertcurrency(ordertotaldata.DocTotal.toFixed(2));
              $('a#chekout .grandtotal').html("$" + grandtotal)
              $("#hi_grandtotal").val(grandtotal);
              return ordertotaldata;
          },
          _getorderSummaryinfo: function($widget, _ordersummary) {
              var ordersummary = "";
              if (_ordersummary != "") {
                  ordersummary = _ordersummary.line_item_render.ordersummary;
              } else {
                  ordersummary = $widget._generateordertotalarray($widget);
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
                stylebyInventory = $widget._stylebyInventory(),
                data = this.options.jsonConfig;
            if ($widget._DatabyStyle() && $widget._stylebyInventory()) {
                var databyStyle = $widget._DatabyStyle();
                var allorderdata = "";
                if (response != "") {
                    allorderdata = response.line_item_render.allorderdata;
                } else {
                    allorderdata = $widget._generateqtyarray(_current_options, $widget, styles);
                }
                var tmp_distinstyle = allorderdata.map(function(item) {
                    return item.Style;
                });
                const uniqueArray = [...new Set(tmp_distinstyle)];
                var distinstyle = uniqueArray;
                var sizegrouparray = {};
                if (distinstyle) {
                    distinstyle.forEach(function(item, index) {
                        if (item in stylebyInventory) {
                            var stylesize = stylebyInventory[item].SizeGroup;
                            sizegrouparray[stylesize] = {};
                        }
                    });
                    var count = 0;
                    distinstyle.forEach(function(item, index) {
                        if (item in stylebyInventory) {
                            var stylesize = stylebyInventory[item].SizeGroup;
                            sizegrouparray[stylesize][count] = stylebyInventory[item].Style;
                            count++;
                        }
                    });
                    for (var index in sizegrouparray) {
                        var item_size = sizegrouparray[index];
                        var groupstyle = item_size;
                        var current_style = "viewtype" + index;
                        var datastyle_index = databyStyle.index;
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                mainresponce[current_style] = {};
                            }
                        });
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size[index_a];
                                    if (stylegroup == item.Style) {
                                        mainresponce[current_style][stylegroup] = {};
                                    }
                                }
                            }
                        });
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size[index_a];
                                    var colorcode = item.ColorCode;
                                    if (stylegroup == item.Style) {
                                        mainresponce[current_style][stylegroup][colorcode] = {};
                                    }
                                }
                            }
                        });
                        var order_item_count = 0;
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size[index_a];
                                    var colorcode = item.ColorCode;
                                    if (stylegroup == item.Style) {
                                        mainresponce[current_style][stylegroup][colorcode][order_item_count] = item;
                                        order_item_count++;
                                    }
                                }
                            }
                        });
                    }
                }
            }
            return mainresponce;
        },
          _getLineItemTable: function(order_id) {
            var $widget = this,
                tmp_po_number = $(".tabactive").attr("value"),
                req_url = this.options.baseurl + "customerorder/customer/optiontwojs",
                current_options = "";
                console.log("order_id",order_id)
            $.ajax({
                url: req_url,
                type: "POST",
                data: { base_order_id: order_id, po_number: tmp_po_number },
                showLoader: false,
                cache: false,
                success: function(response) {
                    if (response.session_distroy) {
                        $widget._adderror(response.message);
                        $("body").trigger("processStart");
                        window.location.reload(true);
                        return false;
                    }
                    if (response.success) {
                        $widget._updatetmpOrderData($widget, response);
                        $widget._renderLineitembeforeAJAX($widget, current_options);
                        if (response.order_id) {
                            $("#order_id").val(response.order_id);
                        }
                        if (response.base64_order_id) {
                            $("#base64_order_id").val(response.base64_order_id);
                        }
                        if (response.base64_ncp_id) {
                            $("#base64_ncp_id").val(response.base64_ncp_id);
                        }
                    }
                  if(response.length == 0){
                     $widget._renderLineitembeforeAJAX($widget, current_options);
                  } 
                },
            });
            return true;
        },
        _convertcurrencytofloat: function(price) {
            var money = price,
                firstsplit = money.split(","),
                lastinde = firstsplit[firstsplit.length - 1].split("."),
                finalmoney = "";
            for (var i = 0; i < firstsplit.length - 1; i++) {
                finalmoney = finalmoney + firstsplit[i];
            }
            finalmoney = finalmoney + lastinde[0] + "." + lastinde[1];
            return finalmoney;
        },
        _updatetmpOrderData: function($widget, _response) {
            var allorderdata = _response;
            finalitems = [];
            var orderitemsdata = allorderdata.line_item_render.allorderdata;
            var ordersummarydata = allorderdata.line_item_render.ordersummary;
            if(typeof ordersummarydata == undefined){
                 $widget.delete_drop();
            }
            orderitemsdata.forEach(function(item, index) {
                var tmpitem = {},
                    pbeforediscount = item.QTYOrdered * item.UnitPrice,
                    pbeforediscount = parseFloat(pbeforediscount),
                    product_type = "normal";
                if (item.ColorCode == "") {
                    product_type = "gift";
                }
                tmpitem = {
                    ColorCode: item.ColorCode,
                    ItemCode: item.ItemCode,
                    ColorStatus: item.ColorStatus,
                    DiscountPer: item.DiscountPer,
                    DiscountPrice: item.DiscountPrice,
                    OrderOption: item.OrderOption,
                    PriceAfterDiscount: $widget._convertcurrencytofloat(item.PriceAfterDiscount),
                    QTYOrdered: item.QTYOrdered,
                    Size: item.Size,
                    Style: item.Style,
                    StyleStatus: item.StyleStatus,
                    TotalPrice: $widget._convertcurrencytofloat(item.TotalPrice),
                    UnitPrice: item.UnitPrice,
                    PriceBeforeDiscount: "" + pbeforediscount + "",
                    Color: item.ColorName,
                    Type: product_type,
                };
                finalitems.push(tmpitem);
            });
                        console.log("tmpitem,finalitems",finalitems)

            var customersFlatDiscount = $widget.options.customersFlatDiscount;
            customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;
            var beforebulkdiscount = customersFlatDiscount;
            ordertotaldata = {
                TotalBeforeDiscount: ordersummarydata.TotalBeforeDiscount,
                DiscountAmount: ordersummarydata.DiscountAmount,
                DiscountPer: ordersummarydata.DiscountPer,
                DocTotal: ordersummarydata.DocTotal,
                TotalDiscountPer: ordersummarydata.TotalDiscountPer,
                TotalDiscountAmount: ordersummarydata.TotalDiscountAmount,
                TotalQtyOrdered: ordersummarydata.TotalQTYUnits,
                FlatDiscount: beforebulkdiscount,
            };

        },
        _getItemfromOrderList: function(sku) {
            var tml_finalitems = _.filter(finalitems, function(item) {
                return item.ItemCode == sku;
            });
            return tml_finalitems;
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
                            order_item = $widget._getItemfromOrderList(itemcode);
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
                            Style: $(".product_options.active").attr('id'),
                            StyleStatus: $('input[name="StyleStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            TotalPrice: "" + pafterdiscount + "",
                            UnitPrice: base_price,
                            PriceBeforeDiscount: "" + pbeforediscount + "",
                            Color: selectcolor,
                            Type: "normal",
                        };
                        console.log("tmpitem,tmpitem",tmpitem)
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
            console.log("finalitems",finalitems)
            return finalitems;
        },
       
        _stylebyInventory: function() {
            var data = this.options.parentstyledata,
                temp_items = [];
            data.forEach(function(item, index) {
                var style = item.Style;
                temp_items[style] = item;
            });
            return temp_items;
        },
        _DatabyStyle: function() {
            var data = this.options.parentstyledata;
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
        _Deleteorderdata: function($this, $widget) {
            var delete_styles = [];
            var delete_colors = [];
            var po_number = $(".tabactive").val();
            var checkinputval = $(".colorContainer").find(".checkvalue");
            $("div.renderAllHtml").attr("data-value", "edit");
            var valIsExists = false;
            var delete_color = $this.parent().attr("delete-color");
            var deletestyle = $this.parent().attr("delete-id");
            delete_styles.push(deletestyle);
            delete_colors.push(delete_color);
            var tmp_orderdata = _.filter(finalitems, function(item) {
                if (item.Style == deletestyle && item.ColorCode == delete_color) {} else {
                    return true;
                }
            });
            var all_null = true;
            tmp_orderdata.forEach(function(item, index) {
                if (item.ColorCode != "") {
                    all_null = false;
                }
            });
            if (all_null) {
                tmp_orderdata = [];
            }
            var is_holeorderdelete = false;
            if (tmp_orderdata.length <= 0) {
                var baseorder_id = $(".row.add_update_po_section .tabactive").attr('base_data');
                $widget._deleteOrder($widget, baseorder_id);
                is_holeorderdelete = true;
            }
            var current_options = "";
            finalitems = tmp_orderdata;
            $widget._renderLineitembeforeAJAX($widget, current_options);
            if (!is_holeorderdelete) {
                $widget._deleteRecord(delete_styles, delete_colors, po_number);
            }
            return true;
        },
         _deleteRecord: function(style, color = [], po_number, removed_markitem = false) {
            var $widget = this;
            var url = this.options.baseurl + "customerorder/customer/deletejs";
            var isorder_delete = true;
            var order_id = $("#order_id").val();
            var flatDiscount = $("#flatDiscount").val();
            var customerdata = $widget.options.customersFlatDiscount;
            var tmp_ordertotaldata = JSON.stringify(ordertotaldata);
            customerdata = JSON.stringify(customerdata);
            $.ajax({
                url: url,
                type: "POST",
                data: { flatDiscount: flatDiscount, order_id: order_id, po_number: po_number, style: style, color: color, isorder_delete: isorder_delete, customerdata: customerdata, ordersummary: tmp_ordertotaldata },
                showLoader: false,
                cache: false,
                success: function(response) {
                   remove_select_option = $('.tabactive').val();
                    if (response.session_distroy) {
                        $widget._adderror(response.message);
                        $("body").trigger("processStart");
                        window.location.reload(true);
                        return false;
                    }
                    if (response.errors) {
                        $widget._adderror(response.message);
                    } else {
                        $("#order_id").val(response.order_id);
                        if (response.errors) {
                            $widget._adderror(response.message);
                            $(".delOrdLink").fadeOut(300);
                            $(".line-item").fadeOut(300);
                        }
                        if (!removed_markitem) {
                            $widget._addsuccess(response.message);
                        }
                    }
                },
            });
        },
        _deleteOrder: function($widget, order_id) {
            var isholeorder_delete = true,
                url = this.options.baseurl + "customerorder/customer/deletejs";
            $.ajax({
                url: url,
                type: "POST",
                data: { order_id: order_id, isholeorder_delete: isholeorder_delete },
                showLoader: true,
                cache: false,
                success: function(response) {
                   remove_select_option = $('.tabactive').val();
                    if (response.session_distroy) {
                        $widget._adderror(response.message);
                        $("body").trigger("processStart");
                        window.location.reload(true);
                        return false;
                    }
                    if (response.success) {
                        $("#order_id").val(response.order_id);
                        $("#base64_order_id").val(response.base64_order_id);
                        $("#base64_ncp_id").val(response.base64_ncp_id);
                        $widget._addsuccess(response.message);
                    }
                },
            });
        },
        _addsuccess: function(message) {
            $("#msg_text").removeClass("error").addClass("success").html(message);
            $("#message").show().focus();
            setTimeout(function() {
                $("#message").fadeOut(1000);
            }, 2000);
            return true;
        },
        _optiononeAdddata: function(data) {
            var $widget = this,
                is_savedata = "true",
                current_options = 1,
                req_url = this.options.baseurl + "customerorder/customer/optiontwojs",
                customerdata = JSON.stringify($widget.options.customersFlatDiscount),
                nextstep = $("#nextstep").val();
            $widget._renderLineitembeforeAJAX($widget, current_options);
            var tmp_ordertotaldata = JSON.stringify(ordertotaldata),
                tmp_finalitems = JSON.stringify(finalitems),
                po_number = $(".tabactive").val(),
                order_id = $("#order_id").val(),
                base64_order_id = $('.tabactive').attr('base_data'),
                base64_ncp_id = $('.tabactive').attr('ncp_data')
            $(".colorContainer .checkvalue, .colorContainer .unittotal").val("");
            $(".colorContainer .showprice, .maxqtyvaldi").html("");
            if (item_edited) {
                $.ajax({
                    url: req_url,
                    type: "POST",
                    data: {
                        po_number: po_number,
                        is_savedata: is_savedata,
                        order_id: order_id,
                        base64_order_id: base64_order_id,
                        base64_ncp_id: base64_ncp_id,
                        customerdata: customerdata,
                        allorderitemdata: tmp_finalitems,
                        deletedsku: removedskus,
                        ordersummary: tmp_ordertotaldata,
                    },
                    showLoader: false,
                    cache: false,
                    success: function(response) {
                        if(response.success){
                            var sel_arr = new Array();
                            $('#select_existing option').each(function(){
                                sel_arr.push($(this).attr('order_id'));
                            });
                            if(jQuery.inArray(response.order_id, sel_arr) == -1){
                                     var po_number = $(".tabactive").val();
                                   $("#select_existing").append('<option value="'+po_number+'" order_id="'+response.order_id+'">'+po_number+'</option>');
                             }
                             
                        }
                        if (response.session_distroy) {
                            $widget._addOpt1error(response.message);
                            $("body").trigger("processStart");
                            window.location.reload(true);
                            return false;
                        }
                        if (!response.errors) {
                            if (response.order_id) $("#order_id").val(response.order_id);
                            if (response.base64_order_id) $("#base64_order_id").val(response.base64_order_id);
                            if (response.base64_ncp_id) $("#base64_ncp_id").val(response.base64_ncp_id);
                        }
                    },
                });
            } else {
                $("#is_edit_order").attr("value", "0");
                $(".colorContainer .checkvalue, .colorContainer .unittotal").val("");
                $(".colorContainer .showprice, .maxqtyvaldi").html("");
                
            }
            return true;
        },
        _Editorderdata: function($this, $widget) {
            $("#is_edit_order").attr("value", "1");
            var checkinputval = $(".colorContainer").find(".checkvalue");
            $("div.renderAllHtml").attr("data-value", "edit");
            var valIsExists = false;
            var edit_color = $this.attr("edit-color");
            var getstyle = $this.attr("edit-id");
            $(checkinputval).each(function() {
                if ($(this).val() != "") {
                    valIsExists = true;
                }
            });
            var prev_obj_id = $(".size_table_sec.newOrderStep4").find(".tab-pane.fade.active").attr("id");
            var is_qty_change = 0;
            if (typeof prev_obj_id !== "undefined") {
                is_qty_change = $("#qty_change_" + prev_obj_id.replace("/", "")).val();
            }
            var opt = { autoOpen: false };
            if (valIsExists && is_qty_change == 1) {
                $("#nextstyleserach").val(getstyle);
                $("#nextcolorserach").val(edit_color);
                $("#nextstep").val("editstyle");
                var theDialog = $("#removeUser").dialog(opt);
                theDialog.dialog("open");
                return false;
            }
           
            $("#style").val($this.attr("edit-id"));
            $("#show_style").val($this.attr("edit-id"));
            $(".searchFromStyle").trigger("click", { is_edit_order: "true" });
            $("[option-color-code='" + edit_color + "']").click();
            // $("#nav-tabContent .tab-pane").removeClass("active");
            $("#nav-tabContent")
                .find("#" + edit_color + "")
                .addClass("active");
            var style_id = $(".product_options").attr("id");
            $widget._updatepopupdata(getstyle, edit_color);
            return true;
        },
        _updatepopupdata: function(getstyle, edit_color) {
            var $widget = this;
            var style_id = $(".product_options").attr("id");
            // $("#sap_ponumber_id").val("");
            $(this).val("");
            $(this).next("span").html("");
            var tmp_orderdata = _.filter(finalitems, function(item) {
                if (item.Style === getstyle && item.ColorCode === edit_color) {
                    return true;
                }
            });
            tmp_orderdata.forEach(function(item, index) {
                var inputQty = "qty[" + item.Color + "][" + item.Size + "]";
                $("[name='" + inputQty + "']").val(item.QTYOrdered);
                $widget._checkvalueUpdate($("[name='" + inputQty + "']"), true);
            });
            $widget._showtotal();
        },
        _showtotal: function() {
            var $widget = this;
            var unittotals = $(".sizeTable").find(".unittotal");
            var gd_total = 0;
            $(unittotals).each(function() {
                if ($(this).val() != "") {
                    var total = parseFloat($(this).val());
                    gd_total = gd_total + total;
                }
            });
            var grandtotal =  $widget._convertcurrency(ordertotaldata.DocTotal.toFixed(2));
            $("#hi_grandtotal").val(ordertotaldata.DocTotal);
            $(".grandtotal").html("$" + ordertotaldata.DocTotal);
        },
        _checkvalueUpdate: function(obj, update) {
            var $widget = this;
            var qty = $(obj).val();
            var maxQty = $(obj).attr("max");
            var selectprice = $(obj).closest("td").find("input[type=hidden]").val();
            var selectcolor = $(obj).closest("td").find("input[name=selectcolor]").val();
            var selectsize = $(obj).closest("td").find("input[name=selectsize]").val();
            if (qty != "") {
                var price = qty * selectprice;
                if (parseInt(qty) > parseInt(maxQty)) {
                    var backqty = parseInt(qty) - parseInt(maxQty);
                    $(obj)
                        .next("span")
                        .html("Backorder " + backqty);
                } else {
                    $(obj).next("span").html("");
                }
                if (update == false) {
                    var colorcode = $('input[name="colorcode[' + selectcolor + "][" + selectsize + ']"').val();
                    $("#qty_change_" + colorcode.replace("/", "")).val(1);
                }
                $('input[name="inpprice[' + selectcolor + "][" + selectsize + ']"').val(price.toFixed(2));
                $('input[name="inpprice[' + selectcolor + "][" + selectsize + ']"')
                    .closest("td")
                    .find("span")
                    .html("$" + $widget._convertcurrency(price.toFixed(2)));
                var savechnagestatus = $(obj).closest("table").find(".maxqtyvaldi").text().length;
                if (savechnagestatus <= 0) {
                    $(".saveChng").attr("disabled", false);
                }
            } else {
                $(obj).val("");
                $(obj).next("span").html("");
                var selectprice = $(obj).closest("td").find("input[type=hidden]").val();
                var selectcolor = $(obj).closest("td").find("input[name=selectcolor]").val();
                var selectsize = $(obj).closest("td").find("input[name=selectsize]").val();
                $('input[name="inpprice[' + selectcolor + "][" + selectsize + ']"').val("");
                $('input[name="inpprice[' + selectcolor + "][" + selectsize + ']"')
                    .closest("td")
                    .find("span")
                    .html("");
                $(obj).next("span").html("");
            }
            $widget._showtotal();
        },
        _updateponumberedit: function($widget){
              var base_url = window.location.origin;
              var url = base_url+"/customerorder/customer/updateponumber";
              var order_id = $('#order_id').val()
              var newpo = $('.old_po').val();
                  if(newpo.length >= 4)
                  {
                       jQuery.ajax({
                          url: url,
                          type: "POST",
                          data: {order_id : order_id , new_po : newpo},
                          showLoader: false,
                          cache: false,
                          success: function(response){
                              if(response)
                              {
                                // console.log(response);
                                  var responce = response,
                                      responce_order_id = responce.order_id,
                                      poconfig_data = $widget.options.poConfig,
                                      responce_ponumber = responce.po_number;
                                  var falg = _.filter(poconfig_data, function(value) {
                                      return value.OrderId === parseInt(responce_order_id);
                                  });
                                  if(falg.length > 0){
                                    $widget.options.poConfig.forEach(function(item, index) {
                                        if (item.OrderId === falg[0].OrderId) {
                                            item.NumatCardPo = responce_ponumber;
                                        }
                                    });
                                  }
                                   var ponumber = $('.tabactive').val(); 
                                   $('option[order_id='+response.order_id+']').attr('value',response.po_number);
                                   $('.tabactive').attr('base_data',response.base64_order_id);
                                   $('.tabactive').attr('ncp_data',response.base64_ncp_id);
                                   $('option[order_id='+response.order_id+']').html(response.po_number);
                              }
                          }
                      });
                  }
                  else
                  {
                       $widget._adderror("PO Number must be a number or letter special character and at least 4 characters long.");  
                       return false;
                  }
              
          },
           _updateponumberedit2: function($widget){
              var base_url = window.location.origin;
              var url = base_url+"/customerorder/customer/updateponumber";
              var order_id = $('#order_id').val()
              var newpo = $('.tabactive').val();
                  if(newpo.length >= 4)
                  {
                       jQuery.ajax({
                          url: url,
                          type: "POST",
                          data: {order_id : order_id , new_po : newpo},
                          showLoader: false,
                          cache: false,
                          success: function(response){
                              if(response)
                              {
                                  var responce = response,
                                      responce_order_id = responce.order_id,
                                      poconfig_data = $widget.options.poConfig,
                                      responce_ponumber = responce.po_number;
                                  var falg = _.filter(poconfig_data, function(value) {
                                      return value.OrderId === parseInt(responce_order_id);
                                  });

                                  if(falg.length > 0){
                                    $widget.options.poConfig.forEach(function(item, index) {
                                        if (item.OrderId === falg[0].OrderId) {
                                            item.NumatCardPo = responce_ponumber;
                                        }
                                    });
                                  }
                                   $('.tabactive').attr('base_data',response.base64_order_id);
                                   $('.tabactive').attr('ncp_data',response.base64_ncp_id);

                              }
                             
                          }
                      });
                  }
                  else
                  {
                       $widget._adderror("PO Number must be a number or letter special character and at least 4 characters long.");  
                       return false;
                  }
              
          },
        _adderror: function(message){
          $("#posuccess-message p").html(message);
          $("#posuccess-message").modal("openModal");
          $(".modalContainer").css("pointer-events","none");
          setTimeout(function(){
            $(".modalContainer").css("pointer-events","");
            $("#posuccess-message").modal("closeModal");
          }, 3000);
          return true;
        },
        getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.parentstyledata , function (value) {
                    return value[key] === sku;
            });
           return falg;
        },

        _myFunction: function(browser) { 
            if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1 ){
                browser = 'Opera';
            }
            else if(navigator.userAgent.indexOf("Chrome") != -1 ){
                browser = 'Chrome';
            }
            else if(navigator.userAgent.indexOf("Safari") != -1){
                browser = 'Safari';
            }
            else if(navigator.userAgent.indexOf("Firefox") != -1 ){
                 browser = 'Firefox';
            }
            else if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )){
              browser = 'IE'; 
            }  
            else{
               browser = 'unknown';
            }
            return browser;
          },

          _updateponumberedit: function($widget){
              var base_url =  window.location.origin;
              var url = base_url+"/customerorder/customer/updateponumber";
              var order_id = $('#order_id').val()
              var newpo = $('.old_po').val();
                  if(newpo.length >= 4)
                  {

                       jQuery.ajax({
                          url: url,
                          type: "POST",
                          data: {order_id : order_id , new_po : newpo},
                          showLoader: false,
                          cache: false,
                          success: function(response){
                              if(response)
                              {

                                console.log(response);
                                  var responce = response,
                                      responce_order_id = responce.order_id,
                                      poconfig_data = $widget.options.poConfig,
                                      responce_ponumber = responce.po_number;

                                  console.log(poconfig_data);
                                  var falg = _.filter(poconfig_data, function(value) {
                                      return value.OrderId === parseInt(responce_order_id);
                                  });

                                  if(falg.length > 0){
                                    console.log("Po Exist..");
                                    $widget.options.poConfig.forEach(function(item, index) {
                                        if (item.OrderId === falg[0].OrderId) {
                                            item.NumatCardPo = responce_ponumber;
                                        }
                                    });

                                    console.log($widget.options.poConfig);

                                  }

                                   var ponumber = $('.tabactive').val(); 
                                   $('option[order_id='+response.order_id+']').attr('value',response.po_number);
                                   $('option[order_id='+response.order_id+']').html(response.po_number);


                              }
                             
                          }
                      });
                  }
                  else
                  {
                       $widget._adderror("PO Number must be a number or letter special character and at least 4 characters long.");  
                       return false;
                  }
              
          },

          _adderror: function(message){
          $("#posuccess-message p").html(message);
          $("#posuccess-message").modal("openModal");
          $(".modalContainer").css("pointer-events","none");
          setTimeout(function(){
            $(".modalContainer").css("pointer-events","");
            $("#posuccess-message").modal("closeModal");
          }, 3000);
          return true;
        },

         
        _Openstockpopup: function($this,$widget) {
          var base_url = window.location.origin;
            var style = this.options.parent_style;
            var styleconfiguration = mageTemplate(view_stock_pricing, {
                        data: this.getProductArray(style , 1),                       
                        parent_color: this.options.parent_style, 
                        customersFlatDiscount : this.options.customersFlatDiscount,
                        rptswitcher: $widget._getRPTswitcherButtons(style, $widget),
                        ponumber: this.options.poConfig,
                        baseurl : base_url,
                    });
            $(".product-info").html(styleconfiguration)
            setTimeout(function(){ $(".swatch-color-container").find(".swatch-option.image").first().trigger("click") }, 2000);
             
            
        }, 
        _getRPTswitcherButtons: function( _sku, $widget){
            var petiteSku = '';
            var tailSku = '';
            var regularSku = '';
            var currentsku = _sku;
            var check = currentsku.substr((currentsku.length)-1, 1);

            var sapconfigdata = this.getConfigurableProduct(this.options.parentstyledata , 'Style');
            var sapconfigskus = [];
        console.log(sapconfigdata);
            sapconfigdata.forEach(function(item, index){
                sapconfigskus.push(item.Style);
            });

            if(check.toUpperCase() == ('P').toUpperCase() || check.toUpperCase() == ('T').toUpperCase()){
              regularSku = currentsku.substr(0, (currentsku.length - 1))
            }else{
              regularSku = _sku;
            }

            if(tailSku == '' && petiteSku == ''){
              tailSku = regularSku+'T';
              petiteSku = regularSku+'P';
            }
            var available_skus = {};
            if(petiteSku != ''){
              if(_.contains(sapconfigskus, petiteSku)) {
                available_skus["petite"] = petiteSku;
              }else{
                petiteSku = '';
              }
            }


            if(tailSku != ''){
              if(_.contains(sapconfigskus, tailSku)) {
                available_skus["tall"] = tailSku;
              }else{
                tailSku = '';
              }
            }

            if(regularSku != ''){
              if(_.contains(sapconfigskus, regularSku)) {
                available_skus["regular"] = regularSku;
              }else{
                regularSku = '';
              }
            }
           return available_skus;
        },


    });

    return $.mage.PopupRenderer;
});