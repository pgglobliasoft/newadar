define([    
    'jquery',
    'mage/template',   
    'Magento_Ui/js/modal/modal',
    "mage/requirejs/json!Sttl_Customerorder/template/Inventory.json",
    'text!Magento_Catalog/template/view_stock_pricing.html',
], function ($, mageTemplate, modal, inventory,view_stock_pricing) {
    'use strict';
    var  $widget = this,
    skusdata = {},
    inventorydata_all = [],
    poConfig_val = [],
    finalitems = [],
    removedskus = [],
    ordertotaldata = {},
     item_edited = false,
    baseurl;
    $.widget('mage.SwatchRenderer', {
        options: {
            jsonConfig: inventory,
            poConfig: {},
        },

        _init: function () {       
            this._EventListener();
            console.log("inventory",this.options.poConfig)
            poConfig_val = this.options.poConfig
            inventorydata_all = this.options.jsonConfig;
            baseurl = this.options.baseurl
            this.posuccesspopup();
        },

        _create: function() {
          var $widget = this;
          $widget.setTable();
          console.log("draft_po_list",this.options.poConfig)
        },

        posuccesspopup: function () {
            var options = {
              type: 'popup',
              responsive: true,
              innerScroll: true,
              modalClass: "po-success-popup",
              modalCloseBtn: true,
            };
            var popup = modal(options, $('#posuccess-message'));
          },
        _EventListener: function () {
             var $widget = this;
              $widget.qtyaddpopup();

             $(document).on('click','.view_summary_sec',function(){
                $(".loading-mask").css("display","block");
            });

            $(document).on('click', '.view-buyBtnContainer-section strong.viewSection', function (e) {
              // $('.search_stock_price_data').css
                $(this).toggleClass('togActive')
                $(this).parent().next().slideToggle( 1000, function(){
                  if ($(this).is(":visible")) {
                    $('html,body').animate({
                      scrollTop: $(this).parent("div").offset().top
                    }, 1000)
                  }
                })   
            })   

            var tool_timeout;
            $(document).on("click", ".eta-tooltip",function(e){
              if($(".eta-date").is(":visible")){
                $(".eta-date").fadeOut();
                clearTimeout(tool_timeout);
              }

              var left = ( $(this).outerWidth() / 2 ) - 30 ;
              $(this).find(".eta-date").css('left',left+'px')
              $(this).find(".eta-date").fadeIn();
              tool_timeout =  setTimeout(function(){ $(".eta-date").fadeOut(); }, 5000);
              e.stopPropagation();
            });

             $(document).on('input', '#po_number', function (e) {

              if ($("#po_number").val().length >= 4) {
                $(".left_icon").hide();
                $(".checkPoAndInsert").show();
              } else {
                $('.left_icon.left_icon1').find('i').removeClass('fa-edit').addClass('fa-caret-down');
                $(".left_icon").show();
                $(".checkPoAndInsert").hide();
              }
              $('#po_numberautocomplete-list').fadeIn(500);
              return $widget._OnChangeDropdownPO($(this), $widget);
            });


             $(document).on('keypress','#po_number',function(e){
              var keycode = (e.keyCode ? e.keyCode : e.which);

              if(keycode == '13'){
                e.preventDefault();
                var po_number = $("#po_number").val();
                var checkexesting = false;
                var exesing_orderid = '';
                var selected_po = '';

                if($("#po_numberautocomplete-list.autocomplete-items").children(".view-po.active.element").length == 1){
                  var autoselected_po = $("#po_numberautocomplete-list.autocomplete-items").find(".view-po.active.element input").val();
                  $("#po_number").attr("value",autoselected_po);
                  // console.log(autoselected_style)
                  po_number = autoselected_po;
                }
                var res_polist  = $widget.options.poConfig
                for(var keys in res_polist){

                  if(checkexesting == false){
                   var currentpo_val = res_polist[keys];
                   selected_po = currentpo_val.NumatCardPo;
                   var lowercase_po = selected_po.toLowerCase();
                   if(po_number.toLowerCase() == lowercase_po){
                     checkexesting = true;
                     exesing_orderid = currentpo_val.OrderId;
                   }
                 }
                }

                if(checkexesting){
                  $widget._selectpo(exesing_orderid,selected_po);
                  $('#po_numberautocomplete-list .view-po').trigger('click');

                  // return false;
                }else{
                  $(".autocomplete-items").hide();
                  $(this).blur();
                  $( ".checkPoAndInsert" ).trigger( "click" );
                }
                return false;
              }
            });

            $(document).on('click', '.swatch-attribute .swatch-option',function(){
                $('.tab-pane.fade').removeClass("active");

              var table_id = $(this).attr('option-label');
              $(".selected_color_size").html(table_id);
              var actual_active_color = $(".swatch-option.image.selected").attr("aria-describedby");

              var color_type = "";
              if(actual_active_color == "option-label-color-93"){
                var color_type = "Solid";
              }else if(actual_active_color == "option-label-seasonalcolors-152"){
                var color_type = "Seasonal";
              }else if(actual_active_color == "option-label-heather_colors-171"){
                var color_type = "Heather";
              }

              $(".selected_color_size").html(color_type);
              var size_table_id = table_id.replace(/\s+/g, '');
              $('#product_color_'+size_table_id).addClass("active");
            })

            $(document).on('focusout', '#po_number', function (e) {
              // $('.checkPoAndInsert').trigger('click');
              $('#po_numberautocomplete-list').fadeOut(500);
              $(".checkPoAndInsert").hide();
              $(".left_icon").show();
            });

            // $(document).on('focus', '#po_number', function (e) {

            //   $('#po_numberautocomplete-list').fadeIn(500);
            // });

            $(document).on('click', '#po_numberautocomplete-list .view-po.element,#po_numberautocomplete-list .view-po.element ', function (e) {
              return $widget._OnClickPoList($(this), $widget);
            });

            $(document).on('click', '.left_icon.left_icon1.editsection', function (e) {
              $('#po_number').css('opacity', '1').attr('readonly', false).focus();
              $("#po_number").attr("readonly", false);
              $("#po_number").focus();
              $("#po_number").css("opacity", "1");
              $(".left_icon").hide();
              // $(".checkPoAndInsert").show();
            })
            $(document).on('click', '.left_icon.left_icon1:not(.editsection)', function (e) {
                $('.checkPoAndInsert').hide();
                $(this).toggleClass('open');
                if ($(this).hasClass('open') && $(".autocomplete-items").children().length > 0) {
                  $(this).find('i').attr('class', 'fa fa-caret-up');
                  $('#po_numberautocomplete-list').addClass('active');
                  $('#po_numberautocomplete-list').slideDown();
                } else {
                  $(this).find('i').attr('class', 'fa fa-caret-down');
                  $('#po_numberautocomplete-list').removeClass('active');
                  $('#po_numberautocomplete-list').slideUp();
                }
                if ($("#po_number").val().length <= 0 && $("#po_number").is('[readonly]') == false && $("#po_numberautocomplete-list").length > 0) {
                  $(this).find(".fa-caret-down").toggleClass("fa-caret-up");
                  $(this).parent().find(".autocomplete-items.fullpo").slideToggle();
                  var a = $(this);
                  if ($(a).find('i').hasClass('fa-caret-down')) {
                    $(a).find('i').removeClass('fa-caret-down');
                    $(a).find('i').addClass('fa-caret-up');
                  } else {
                    $(a).find('i').addClass('fa-caret-down');
                    $(a).find('i').removeClass('fa-caret-up');
                  }
                }

            });
            $(document).on('click','#po_numberautocomplete-list .view-po',function(e){
              // var $widget = this;
                  $(this).addClass("clickeffect");
                  setTimeout(function(){
                    $(this).removeClass("clickeffect");
                  },1500)
                  var po_id = $(this).find('.super-attribute-select').attr("dyorderid"),
                      po_number = $(this).find('.super-attribute-select').val();
                      $('#order_id').attr('value',po_id)
                   // var po_number = $(this).children('input').attr('value');
                   // var po_id = $(this).children('input').attr('id');
                    if(po_id != '' || po_number != '' || po_number != 'undefined'){
                       var nexturl = baseurl+'customerorder/customer/neworder/id/'+window.btoa(po_id)+"/ncp/"+window.btoa(po_number)+"#block-title";
                       // window.location.href = nexturl;
                       // return false;
                       var order_id = po_id;
                       $widget._selectpo(order_id,po_number);
                       return false;
                    }
                    return $widget._OnClickPoList($(this), $widget);
              });

            $(document).on("click", ".scroll-to-po",function(e){
                  $("#po_number").css({"border":"1px solid red","box-shadow":"0px 1px 10px 4px rgba(11, 66, 105, 0.48)"});
                  $("#po_number").focus();
                  setTimeout(function(){
                      $("#po_number").css({"border":"","box-shadow":""});
                  },500);
            });

            $(document).on('click', ".saveChng", function (e, data) {
              return $widget._optiononeAdddata($widget, 1);
            });

          $(document).on('click','.checkPoAndInsert',function(e){
            var po_number = $("#po_number").val();
             if(po_number == "" || po_number == 'undefined'){
               $(".po-exist").addClass("message-error error");
               $(".po-exist").html("Please enter PO number.");
               return false;
             }else{
              $(".po-exist").removeClass("message-error error");
               $(".po-exist").html(" ");
             }
             $widget.checkPOnumber();
             return $widget._determineProductData($('#po_number').val());
           });


          $(document).on("click", function(event){
           if(!$(event.target).closest(".d-flex.po-section .box-content, footer .block.mobile-block-collapsible-nav").length){
             if($("#po_number").val().length > 0 && $("#po_number").is('[readonly]') == false){
               event.preventDefault();
               var po_number = $("#po_number").val();
               var checkexesting = false;
               var exesing_orderid = '';
               var selected_po = '';
               if($("#po_numberautocomplete-list.autocomplete-items").children(".view-po.active.element").length == 1){
                 var autoselected_po = $("#po_numberautocomplete-list.autocomplete-items").find(".view-po.active.element input").val();
                 $("#po_number").attr("value",autoselected_po);
                 // console.log(autoselected_style)
                 po_number = autoselected_po;
               }

               var res_polist  = $widget.options.poConfig
                for(var keys in res_polist){

                  if(checkexesting == false){
                   var currentpo_val = res_polist[keys];
                   selected_po = currentpo_val.NumatCardPo;
                   var lowercase_po = selected_po.toLowerCase();
                   if(po_number.toLowerCase() == lowercase_po){
                     checkexesting = true;
                     exesing_orderid = currentpo_val.OrderId;
                   }
                 }
                }


               if(checkexesting){
                 $widget._selectpo(exesing_orderid,selected_po);
               }else{
                 $(".autocomplete-items").hide();
                 $(this).blur();
                 $( ".checkPoAndInsert" ).trigger( "click" );
               }
               return false;
             }
           }
         });


        },
        _determineProductData: function (PoVal, option = 1) { // this.options.poConfig


          if (!/^[^-\s][a-zA-Z0-9!%*@#$&()\\-`.+,\-\s=\"]{3,}$/.test(PoVal) && option == 1) {
            var $tmp_widget = this.element;
              console.log("tmp_widget",$tmp_widget)

            $tmp_widget.find('.po-exist').addClass("message-error error").html('PO Number must be at least 4 characters long and cannot start with a space');
            setTimeout(function () {
              $tmp_widget.find('.po-exist').fadeOut(2000, function () {
                $tmp_widget.find('.po-exist').html('');
              });
            }, 2000)
          } else {
              console.log("colorSwatches")

            this.element.find('.po-exist').html("");
            $('.or-txt,.checkPoAndInsert').fadeOut(200);
            $('#show_style').focus();
            $('#po_number').css('opacity', '0.5');
            $("#po_number").attr('readOnly', true);
            $('.left_icon.left_icon1:not(.draft)').addClass('editsection').find('i').removeClass('fa-caret-down').addClass('fa-edit').parent().fadeIn(200);
            var po_number = $("#po_number").val();
            if (!$('.colorSwatches').is(':empty') && po_number) {
              console.log("colorSwatches")
              $(".scroll-to-po").addClass("saveData");
              jQuery(".scroll-to-po").addClass("saveChng");
              var value_exist = false,
                value_selector = jQuery("[name^='qty[']");
              value_selector.each(function () {
                if (jQuery(this).val().length && value_exist == false)
                  value_exist = true;
              });
              if (value_exist == false) {
                 console.log("colorSwatches____")
                jQuery(".scroll-to-po").addClass("disabled", 1000, "easeOutBounce");
              } else {
                 console.log("colorSwatches....")
                jQuery(".scroll-to-po").removeClass("disabled", 1000, "easeOutBounce");
              }
              $(".saveData").html("");
              $(".saveData").html("ADD/UPDATE P.O.");
              if ($(".saveData").hasClass("scroll-to-po")) {
                $(".saveData").removeClass("scroll-to-po");
              }
              $('html, body').animate({
                scrollTop: $($(".Collections .owl-stage-outer")).offset().top - 5,
              }, 1000, 'linear')

            }
          }

        },
        _getItemfromOrderList: function (sku) {
          var tml_finalitems = _.filter(finalitems, function (item) {
            return item.ItemCode == sku;
          });
          return tml_finalitems;
        },
        _generateqtyarray: function (_current_options, $widget, styles = {}) {
            removedskus = [];
            var data_selector = $('.colorContainer').find('.checkvalue');
            var current_options = _current_options;
            item_edited = false;
            if (current_options == 1 && current_options != 0 && current_options != '') {
              $(data_selector).each(function () {
                var count = 0;
                if ($(this).val() != '') {
                  var itemcode = $(this).attr("itemscode"),
                    orderdata = $widget.getProductArray(itemcode, 0),
                    selectcolor = orderdata[0].Color,
                    selectsize = orderdata[0].Size,
                    base_price = '',
                    disprice = '',
                    added_qty = $(this).val(),
                    order_item = $widget._getItemfromOrderList(itemcode),
                    customersFlatDiscount = $widget.options.customersFlatDiscount,
                    cstomer_price_group = customersFlatDiscount[0].PriceList;

                  if (cstomer_price_group == 'Institutional Price List') {
                    base_price = orderdata[0].InsPrice;
                    disprice = orderdata[0].InsPrice;
                  } else {
                    base_price = orderdata[0].UnitPrice;
                    disprice = orderdata[0].DisPrice;
                  }
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

                  var current_itemcode = $(this).attr("itemscode");

                  tmpitem = {
                    ColorCode: orderdata[0].ColorCode,
                    ItemCode: itemcode,
                    ColorStatus: orderdata[0].ColorStatus,
                    DiscountPer: orderdata[0].DisPercent,
                    DiscountPrice: orderdata[0].DisPrice,
                    OrderOption: "1",
                    PriceAfterDiscount: '' + pafterdiscount + '',
                    QTYOrdered: added_qty,
                    Size: selectsize,
                    Style: orderdata[0].Style,
                    StyleStatus: orderdata[0].StyleStatus,
                    TotalPrice: '' + pafterdiscount + '',
                    UnitPrice: base_price,
                    PriceBeforeDiscount: '' + pbeforediscount + '',
                    Color: selectcolor,
                    Type: "normal"

                  };

                  var itemcodeexistinarray = false;
                  var array_index = -1;

                  finalitems.forEach(function (item, index) {
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
                      var tml_finalitems = _.filter(finalitems, function (item) {
                        if (item.ItemCode == current_itemcode) {
                          removedskus.push(current_itemcode);
                        }
                        return item.ItemCode !== current_itemcode
                      });
                      finalitems = tml_finalitems;
                    }
                  } else {
                    if (added_qty > 0) {
                      finalitems.push(tmpitem);
                    }
                  }
                  $widget._checkvalueUpdate($(this), true);

                }
              });
            }
            if (current_options == 5 && current_options != 0 && current_options != '') {
              $.each(styles, function (key, value) {
                var count = 0;
                if (value.qty != '') {
                  var itemcode = value.itemscode,
                    orderdata = $widget.getProductArray(itemcode, 0),
                    selectcolor = orderdata[0].Color,
                    selectsize = orderdata[0].Size,
                    base_price = '',
                    disprice = orderdata[0].DisPrice,
                    added_qty = value.qty,
                    order_item = $widget._getItemfromOrderList(itemcode),
                    customersFlatDiscount = $widget.options.customersFlatDiscount,
                    cstomer_price_group = customersFlatDiscount[0].PriceList;

                  if (cstomer_price_group == 'Institutional Price List') {
                    base_price = orderdata[0].InsPrice;
                  } else {
                    base_price = orderdata[0].UnitPrice;
                  }
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

                  var current_itemcode = value.itemscode;

                  tmpitem = {
                    ColorCode: orderdata[0].ColorCode,
                    ItemCode: itemcode,
                    ColorStatus: orderdata[0].ColorStatus,
                    DiscountPer: orderdata[0].DisPercent,
                    DiscountPrice: orderdata[0].DisPrice,
                    OrderOption: "1",
                    PriceAfterDiscount: '' + pafterdiscount + '',
                    QTYOrdered: added_qty,
                    Size: selectsize,
                    Style: orderdata[0].Style,
                    StyleStatus: orderdata[0].StyleStatus,
                    TotalPrice: '' + pafterdiscount + '',
                    UnitPrice: base_price,
                    PriceBeforeDiscount: '' + pbeforediscount + '',
                    Color: selectcolor,
                    Type: "normal"

                  };

                  var itemcodeexistinarray = false;
                  var array_index = -1;

                  finalitems.forEach(function (item, index) {
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
                      var tml_finalitems = _.filter(finalitems, function (item) {
                        if (item.ItemCode == current_itemcode) {
                          removedskus.push(current_itemcode);
                        }
                        return item.ItemCode !== current_itemcode
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
          _checkvalueUpdate: function (obj, update, $widget) {
            var $widget = this
            var qty = $(obj).val();
            var maxQty = $(obj).attr("max");
            var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
            var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
            var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();

            if (qty != '' && qty != 0) {

              var price = qty * selectprice;
              if (parseInt(qty) > parseInt(maxQty)) {
                var backqty = parseInt(qty) - parseInt(maxQty);
                $(obj).next("span").html('Backorder ' + backqty);
              } else {
                $(obj).next("span").html('');
              }
              if (update == false) {
                var colorcode = $('input[name="colorcode[' + selectcolor + '][' + selectsize + ']"').val();
                $("#qty_change_" + colorcode.replace("/", "")).val(1);
              }

              $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').val(price.toFixed(2));
              $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').closest('td').find('span').html('$' + $widget._convertcurrency(price.toFixed(2)));

              var savechnagestatus = $(obj).closest('table').find('.maxqtyvaldi').text().length;
              if (savechnagestatus <= 0) {
                $('.saveChng').attr('disabled', false);
              }
            } else {
              $(obj).val('')
              $(obj).next("span").html('');
              var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
              var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
              var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();
              $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').val('');
              $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').closest('td').find('span').html('');
              $(obj).next("span").html('');
            }
            $widget._showtotal($widget);
          },
        _DatabyStyle: function () {

          var data = this.options.jsonConfig;
          var temp_databyStyle = {};

          data.forEach(function (item, index) {
            var sizegroup = item.SizeGroup;
            temp_databyStyle[sizegroup] = {};
          });

          data.forEach(function (item, index) {
            var sizegroup = item.SizeGroup;
            var sizeorder = item.SizeOrder;
            var size = item.Size;
            temp_databyStyle[sizegroup][sizeorder] = size;
          });

          return temp_databyStyle;
        },
        _stylebyInventory: function () {
          var data = this.options.jsonConfig,
            temp_items = [];

          data.forEach(function (item, index) {
            var style = item.Style;
            temp_items[style] = item;
          });

          return temp_items;
        },
        _getOrderDataItems: function ($this, _response, _current_options, styles = {}) {

          var $widget = $this,
            response = _response,
            mainresponce = {},
            style = '',
            submitcolor = '',
            viewmode = '',
            stylebyInventory = $widget._stylebyInventory(),
            data = this.options.jsonConfig;


          // var order_id = response.base64_order_id;
          if ($widget._DatabyStyle() && $widget._stylebyInventory()) {
            var databyStyle = $widget._DatabyStyle();

            var allorderdata = '';
            if (response != '') {
              allorderdata = response.line_item_render.allorderdata;
            } else {
              allorderdata = $widget._generateqtyarray(_current_options, $widget, styles);
            }

            var tmp_distinstyle = allorderdata.map(function (item) {
              return item.Style;
            });

            const uniqueArray = [...new Set(tmp_distinstyle)];

            var distinstyle = uniqueArray;
            var sizegrouparray = {};
            if (distinstyle) {
              distinstyle.forEach(function (item, index) {
                if (item in stylebyInventory) {
                  var stylesize = stylebyInventory[item].SizeGroup;
                  sizegrouparray[stylesize] = {};
                }
              });
              var count = 0;
              distinstyle.forEach(function (item, index) {
                if (item in stylebyInventory) {
                  var stylesize = stylebyInventory[item].SizeGroup;
                  sizegrouparray[stylesize][count] = stylebyInventory[item].Style;
                  count++;
                }
              });
              for (var index in sizegrouparray) {

                var item_size = sizegrouparray[index];
                var groupstyle = item_size;
                var current_style = 'viewtype' + index;
                var datastyle_index = databyStyle.index;
                allorderdata.forEach(function (item, index) {
                  if (item.Type != 'gift') {
                    mainresponce[current_style] = {};
                  }
                });

                allorderdata.forEach(function (item, index) {
                  if (item.Type != 'gift') {
                    for (var index_a in item_size) {
                      var stylegroup = item_size[index_a]
                      if (stylegroup == item.Style) {
                        mainresponce[current_style][stylegroup] = {};
                      }
                    }
                  }
                });
                allorderdata.forEach(function (item, index) {
                  if (item.Type != 'gift') {
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
                allorderdata.forEach(function (item, index) {
                  if (item.Type != 'gift') {
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

          // console.log(mainresponce);

          return mainresponce;
        },
        _getorderSummaryinfo: function ($widget, _ordersummary) {
          var $widget = this;
            var ordersummary = '';
            if (_ordersummary != '') {
              ordersummary = _ordersummary.line_item_render.ordersummary;
            } else {
              ordersummary = $widget._generateordertotalarray($widget);
            }
            return ordersummary;
        },
        showsuccesspopup: function (message) {
          $("#posuccess-message p").html(message);
          $("#posuccess-message").modal("openModal");
        },
        closeposuccesspopup: function () {
          $("#posuccess-message").modal("closeModal");
        },
        _generateordertotalarray: function ($widget) {
            var data_selector = $('.colorContainer').find('.checkvalue');


            var customersFlatDiscount = $widget.options.customersFlatDiscount;
            customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;
            var beforebulkdiscount = customersFlatDiscount;
            var sellingprice = 0;
            var discountAmount = 0;

            var customrsbulkdiscount = $widget.options.customersBulcDiscount;

            var DocTotalQty = 0;

            finalitems.forEach(function (item, index) {
              sellingprice = parseFloat(sellingprice) + parseFloat(item.TotalPrice);
              DocTotalQty = parseInt(DocTotalQty) + parseInt(item.QTYOrdered);
            });


            var bulkdiscount = 0;

            if (customrsbulkdiscount.length > 0) {
              customrsbulkdiscount.forEach(function (item, index) {
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
              sellingprice = sellingprice - (sellingprice * (customersFlatDiscount / 100));
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
            }


            return ordertotaldata;
          },
         _renderLineitembeforeAJAX: function ($this, _current_options, styles = {}, click_event = '') {
            var $widget = $this,
              orderdata = '',
              allorderitems = $widget._getOrderDataItems($widget, orderdata, _current_options, styles);
              $widget._getorderSummaryinfo($widget, orderdata)
          },
        _optiononeAdddata: function ($widget, current_options, Styles = {}) {
          var $widget = this
          var is_savedata = 'true',
            req_url = baseurl + 'customerorder/customer/optiontwojs',
            customerdata = JSON.stringify($widget.options.customersFlatDiscount),
            nextstep = $('#nextstep').val();
          // $(".cf.loaderAdd").children().slideDown(300);
          $widget.showsuccesspopup("Item Successfully Added To PO");
          setTimeout(function () {
            $widget.closeposuccesspopup();
          }, 2000);

          $widget._renderLineitembeforeAJAX($widget, current_options, Styles);

          // $(".cf.loaderAdd").children().slideUp(300);
          var tmp_ordertotaldata = JSON.stringify(ordertotaldata),
            tmp_finalitems = JSON.stringify(finalitems),
            po_number = $("#po_number").val(),
            order_id = $("#order_id").val(),
            base64_order_id = window.btoa(order_id),
            base64_ncp_id = window.btoa(po_number) 
          $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
          $(".colorContainer .showprice, .maxqtyvaldi").html('');
          $(".saveData").addClass("disabled", 1000, "easeOutBounce");
          // $('html, body').animate({scrollTop: $("#nav-tabContent").offset().top - 80, }, 700, 'linear')
          // $widget._ScrollAnimate($("#nav-tabContent"), 700)
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
            success: function (response) {
              if (response.session_distroy) {
                $('body').trigger('processStart');
                window.location.reload(true);
                return false;
              }

              if (!response.errors) {
                $(".view_summary_btn").show();
                var nexturl = baseurl+'customerorder/customer/neworder/id/'+response.base64_order_id+'/ncp/'+response.base64_ncp_id;
                $(".view_summary_btn").attr("href",nexturl);
                $(".left_icon.left_icon1").fadeOut();
                if (response.order_id)
                  $("#order_id").val(response.order_id);
                  $(".saveChng").addClass("disabled",1000,"easeOutBounce");

                if (response.base64_order_id)
                  $("#base64_order_id").val(response.base64_order_id);

                if (response.base64_ncp_id)
                  $("#base64_ncp_id").val(response.base64_ncp_id);
              }
              if (response.errors) {

                $widget._updatetmpOrderData($widget, response);
                var lineitem_temp = $widget._renderLineitemAfterAJAX($widget, response, current_options);
                $(".cf.line-item").fadeIn(300);
                $(".line-item .orderListing").html(lineitem_temp);

                if (response.message)
                  $widget.adderror(response.message)
                $widget._changeurl(response.base64_order_id, response.base64_ncp_id);

                if (lineitem_temp)
                  $(".cf.delOrdLink").fadeIn(300);

                $("#is_edit_order").attr("value", "0");
              }

            }
          });

          return true;
        },
         _getLineItemTable: function (order_id, po_number = '') {

            var $widget = this,
              tmp_po_number = $("#po_number").attr("value"),
              req_url = this.options.baseurl + 'customerorder/customer/optiontwojs',
              current_options = '';

            $.ajax({
              url: req_url,
              type: "POST",
              data: {
                base_order_id: order_id,
                po_number: tmp_po_number
              },
              showLoader: false,
              cache: false,
              success: function (response) {
                if (response.session_distroy) {
                  $widget.adderror(response.message);
                  $('body').trigger('processStart');
                  window.location.reload(true);
                  return false;
                }
                if (response.success) {
                  $widget._updatetmpOrderData($widget, response);

                  $widget._renderLineitembeforeAJAX($widget, current_options);
                  $('#order_id').val(parseInt(order_id));
                  if (po_number != '' && order_id != '') {
                    // $widget._changeurl(btoa(order_id), btoa(po_number));
                  }

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
              }
            });

            return true;
          },
          _convertcurrencytofloat: function (price) {
            var money = price,
              firstsplit = money.split(","),
              lastinde = firstsplit[firstsplit.length - 1].split("."),
              finalmoney = '';
            for (var i = 0; i < firstsplit.length - 1; i++) {
              finalmoney = finalmoney + firstsplit[i]
            }
            finalmoney = finalmoney + lastinde[0] + '.' + lastinde[1];
            // finalmoney = parseFloat(finalmoney);
            return finalmoney;
          },
        _updatetmpOrderData: function ($widget, _response) {
            var allorderdata = _response;
            finalitems = [];

            if (allorderdata.line_item_render.allorderdata.length <= 0) {
              // $widget._changeemptyurl();
              window.location.reload(true);
              return false;
            }

            var orderitemsdata = allorderdata.line_item_render.allorderdata;
            var ordersummarydata = allorderdata.line_item_render.ordersummary;
console.log("ordersummarydata",ordersummarydata)
            orderitemsdata.forEach(function (item, index) {
              var tmpitem = {},
                pbeforediscount = item.QTYOrdered * item.UnitPrice,
                pbeforediscount = parseFloat(pbeforediscount),
                product_type = 'normal';

              if (item.ColorCode == '') {
                product_type = 'gift';

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
                PriceBeforeDiscount: '' + pbeforediscount + '',
                Color: item.ColorName,
                Type: product_type
              };

              finalitems.push(tmpitem);
            });

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
            }
          },
        _selectpo: function(order_id,po_number){
          var $widget = this
              var url = baseurl+'customerorder/customer/selectpo';

              var existponumberText = po_number;

              if(existponumberText == ''){
                $('.btn-primary.saveChng').hide();
                $('.view_summary_btn').hide();
              }

              var base64_order_id = btoa(order_id);
              var base64_ncp_id = btoa(existponumberText);

              if(existponumberText !='')
              {
                $(".tabactive").val(existponumberText);
                $(".tabactive").prop("readonly", true);
                var nexturl = baseurl+'customerorder/customer/neworder/id/'+base64_order_id+'/ncp/'+base64_ncp_id;
                $(".view_summary_btn").attr("href",nexturl);
              }
              $widget._updatepopupdata(order_id);
              $("#po_numberautocomplete-list").slideUp();

        },
        _updatepopupdata: function(existponumberText){
          var $widget = this
            if(existponumberText != '')
              {
                $('[data-toggle="collapse"]').prop('disabled',false);
                $('#overlay').hide()
                $("#msg_text").html("");
                $('.discardChng').show();
                $('#sap_ponumber_id').val(existponumberText);
                var url = baseurl+'adaruniforms/cart/update';
                var style_id = $("#style").val();
                $widget._showtotal();

                $.ajax({
                  url: url,
                  type: "POST",
                  data: {'po_number' : existponumberText, 'style_id' : style_id},
                  showLoader: true,
                  cache: false,
                  success: function(response){
                    // console.log(response);
                      $("#message").html(response.message);
                      setTimeout(function(){
                        $("#message").html("");
                      },2000);

                    if(response.success) {
                      var data = response.data;


                      if (data != null) {

                        $('.btn-primary.saveChng').show();
                        $('.view_summary_btn').show();
                        $("#po_number").css("opacity","0.5");
                        $("#po_number").attr("readonly",true);
                        $(".left_icon.left_icon1").fadeOut();
                        if(existponumberText){
                          $(".add-to-cart-button-sec a").addClass("mobile-button");
                          $(".add-to-cart-button-sec a").removeClass("mobile-button-click");
                           $(".scroll-to-po").addClass("saveChng");
                           var value_exist = false;
                          var value_selector = $("[name^='qty[']");
                          value_selector.each(function(){
                            if($(this).val().length && value_exist == false){
                              value_exist = true;
                            }
                          });
                          if(value_exist == false){
                            $(".scroll-to-po").addClass("disabled",1000,"easeOutBounce");
                          }else{
                            $(".scroll-to-po").removeClass("disabled",1000,"easeOutBounce");
                          }
                           $(".saveChng").html("");
                           $(".saveChng").html("ADD/UPDATE P.O.");
                           if($(".saveChng").hasClass("scroll-to-po")){
                              $(".saveChng").removeClass("scroll-to-po");
                           }
                           // $('html, body').animate({
                           //       scrollTop: $($(".swatch-attribute.color")).offset().top - 30,
                           //     },
                           //     1500,
                           //     'linear'
                           //   )
                        }
                      }
                    }
                  }
                });
              }else{
                $(".tabactive").prop("readonly", false);
                $(".tabactive").val('');
                $('#sap_ponumber_id').val('');
                //$('.discardChng').hide();
                $('.collapse').removeClass("show");
                $("#cart-form a[data-toggle='collapse']").addClass("collapsed").attr("aria-expanded", false);
                $(".checkvalue").each(function() {
                  $(this).val('')
                  $(this).next("span").html('');
                  var selectprice = $(this).closest('td').find('input[type=hidden]').val();
                  var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                  var selectsize = $(this).closest('td').find('input[name=selectsize]').val();
                  $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
                  $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
                });
                  $widget._showtotal();
              }
        },
        _showtotal: function(){
          var $widget = this
          var unittotals = $('.product_options').find('.unittotal');
          var gd_total = 0;
          $(unittotals).each(function() {
            if($(this).val() != '')
            {
              var total = parseFloat($(this).val());
              gd_total = gd_total + total;
            }

          });
          var totalprice = $widget._convertcurrency(parseFloat(gd_total).toFixed(2));
          $('#hi_grandtotal').val(parseFloat(gd_total).toFixed(2));
          $('.grandtotal').html('');
          $('.grandtotal').html('$'+ totalprice);
        },
        _convertcurrency: function(price){
          var x=price;

          x=x.toString();
          var afterPoint = '';
          if(x.indexOf('.') > 0)
          afterPoint = x.substring(x.indexOf('.'),x.length);
          x = Math.floor(x);
          x=x.toString();
          var lastThree = x.substring(x.length-3);
          var otherNumbers = x.substring(0,x.length-3);
          if(otherNumbers != '')
          lastThree = ',' + lastThree;
          return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;

        },
        adderror: function(message)
        {
          if($("#message").children().length <= 0){
            $("#message").html("<div id='msg_text'></div>");
          }
          $("#msg_text").removeClass("success");
          $("#msg_text").addClass("error");
          $("#msg_text").html(message);
          $("#message").show();
          $("#message").focus();
          return true;
        },
        addsuccess: function(message)
        {
          if($("#message").children().length <= 0){
            $("#message").html("<div id='msg_text'></div>");
          }
          $("#msg_text").removeClass("error");
          $("#msg_text").addClass("success");
          $("#msg_text").html(message);
          $("#message").show()
          $("#message").focus();
          return true;
        },
        submitform: function(){
          var $widget = this;
          $("#msg_text").html("");

          var url = baseurl+'adaruniforms/cart/add';
          $.ajax({
            url: url,
            type: "POST",
            data: $("#cart-form").serialize(),
            showLoader: true,
            cache: false,
            success: function(response){
              if(response.enty_id)
              {
                $('#sap_ponumber_id').val(response.enty_id);
                $('.discardChng').show();
              }
              if(response.success)
              {
                var qty_selector = $("[name^='qty[']");
                qty_selector.each(function(){
                    $(this).val("");
                    $(this).siblings("span").html("");
                    $(this).parent().next().find("input").val("");
                    $(this).parent().next().find("span").html("");
                });
                $(".saveChng").addClass("disabled",1000,"easeOutBounce");
                $(".view_summary_btn").show();
                var nexturl = baseurl+'customerorder/customer/neworder/id/'+response.base64_enty_id+'/ncp/'+response.base64_po_number;
                console.log("nexturl",nexturl)
                $(".view_summary_btn").attr("href",nexturl);
                $(".left_icon.left_icon1").fadeOut();
                // $(".themeBtn.saveChng").html("Item Successfully added to P.O.");
                $widget.showposuccesspopup();
                setTimeout(function(){
                  $widget.closeposuccesspopup();
                },2000);
              }
              else
              {
                $widget.adderror(response.messages);
              }
              var closepopup = $("#closepopup").val();
              if(closepopup == 1)
              {
                $('.productview-modal-close-inside').trigger( "click" );

              }
              var valuescheckout = $("#chekouthidden").val();
              if(valuescheckout == 1 && response.base64_enty_id && response.base64_po_number)
              {
                var nexturl = baseurl+'customerorder/customer/neworder/id/'+response.base64_enty_id+"/ncp/"+response.base64_po_number;
                top.location = nexturl;
              }
            }
          });
        },
        
        checkPOnumber: function(){
            var url = baseurl+'customerorder/customer/createorder';
            var po_number = $("#po_number").val();
            var is_chcheckpo = true;
            $.ajax({
              url: url,
              type: "POST",
              data: {po_number: po_number,is_chcheckpo: is_chcheckpo},
              showLoader: true, 
              cache: false,
              success: function(response){
                if(response.errors) {
                  $(".po-exist").addClass("message-error error");
                  $(".po-exist").html(response.message);
                  setTimeout(function(){
                      $(".po-exist").html("");
                  },2000);
                }else if(response.session_distroy){
                  $(".po-exist").addClass("message-error error");
                  $(".po-exist").html(response.message);
                  $(".po-exist").focus;
                  window.location.href = BASE_URL;
                }else{
                   $(".checkPoAndInsert, #po_numberautocomplete-list").hide();
                   $("#po_number").css({"opacity": "0.5"});
                   $("#po_number").attr('readOnly',true);
                   $(".btn-primary.saveChng").show();
                   $(".left_icon.left_icon1").find("i").hide();
                   $(".left_icon.left_icon1").find("i").addClass("fa-edit");
                   $(".left_icon.left_icon1").find("i").fadeIn();
                   if(po_number){
                      $(".scroll-to-po").addClass("saveChng");
                      var value_exist = false;
                     var value_selector = $("[name^='qty[']");
                     value_selector.each(function(){
                       if($(this).val().length && value_exist == false){
                         value_exist = true;
                       }
                     });
                     if(value_exist == false){
                       $(".scroll-to-po").addClass("disabled",1000,"easeOutBounce");
                     }else{
                       $(".scroll-to-po").removeClass("disabled",1000,"easeOutBounce");
                     }
                      $(".saveChng").html("");
                      $(".saveChng").html("ADD/UPDATE P.O.");
                      if($(".saveChng").hasClass("scroll-to-po")){
                         $(".saveChng").removeClass("scroll-to-po");
                      }
                   }
                   $('.newOrderStep1').addClass('done');
                   $(".left_icon").show();
                   $(".left_icon").find("i").removeClass("fa-caret-up");
                   $(".left_icon").find("i").removeClass("fa-caret-down");
                }
              }
            });
          },
        _OnClickPoList: function ($this, $widget) {

          var OrderId = $this.find('.super-attribute-select').attr("dyorderid"),
            PoNumber = $this.find('.super-attribute-select').val(),
            result = '';
console.log("$this",$this)
console.log("o_",OrderId,PoNumber)
            if (OrderId != '' && PoNumber != '') {
                result = this._getLineItemTable(OrderId, PoNumber);
              }

          $('.left_icon.left_icon1').remove();
          $('#po_number').val($this.find('.super-attribute-select').val())
          $('#po_numberautocomplete-list').fadeOut(300);
          $('#po_numberautocomplete-list').removeClass('active');
           $('.view_summary_btn').show();
          // $widget._determineProductData($('#po_number').val());

        },
        _determineProductData: function (PoVal, option = 1) { // this.options.poConfig

          if (!/^[^-\s][a-zA-Z0-9!%*@#$&()\\-`.+,\-\s=\"]{3,}$/.test(PoVal) && option == 1) {
            var $tmp_widget = this.element;
            $tmp_widget.find('.po-exist').addClass("message-error error").html('PO Number must be at least 4 characters long and cannot start with a space');
            setTimeout(function () {  
              $tmp_widget.find('.po-exist').fadeOut(2000, function () {
                $tmp_widget.find('.po-exist').html('');
              });
            }, 2000)
          } else {
            this.element.find('.po-exist').html("");
            $('.or-txt,.checkPoAndInsert').fadeOut(200);
            $('#show_style').focus();
            $('#po_number').css('opacity', '0.5');
            $("#po_number").attr('readOnly', true);
            $('.left_icon.left_icon1:not(.draft)').addClass('editsection').find('i').removeClass('fa-caret-down').addClass('fa-edit').parent().fadeIn(200);
            var po_number = $("#po_number").val();
            if (!$('.colorSwatches').is(':empty') && po_number) {
              $(".scroll-to-po").addClass("saveData");
              $(".scroll-to-po").addClass("saveChng");
              var value_exist = false,
                value_selector = $("[name^='qty[']");
              value_selector.each(function () {
                if ($(this).val().length && value_exist == false)
                  value_exist = true;
              });
              if (value_exist == false) {
                $(".scroll-to-po").addClass("disabled", 1000, "easeOutBounce");
              } else {
                $(".scroll-to-po").removeClass("disabled", 1000, "easeOutBounce");
              }
              $(".saveData").html("");
              $(".saveData").html("ADD/UPDATE P.O.");
              if ($(".saveData").hasClass("scroll-to-po")) {
                $(".saveData").removeClass("scroll-to-po");
              }
              

            }
          }

        },
        _RenderAutoItemDivPO: function (config, i) {
          // var classActive = i < 1 ? 'active' : '';
          return '<div class="view-po element " ' +
            'data-index="' + i + '" >' +
            '<span>' + config.NumatCardPo +
            '<input class="super-attribute-select" ' +
            'name="super_attribute"' +
            'type="hidden"' +
            'dyorderid="' + config.OrderId + '"' +
            'value="' + config.NumatCardPo + '"' +
            'aria-invalid="false" />' +
            '</span></div>';
        },
        _OnChangeDropdownPO: function ($this, $widget, inputs = "") {
          $("#opt_two_message").hide();
          var a, i, j = 0,
            val = $this.val(),
            input = '',
            res = [];
          var arr = this.options.poConfig;
          a = '<div id="po_numberautocomplete-list" class= "autocomplete-items" ><div>';
          for (i = 0; i < arr.length; i++) {
            var key = 'NumatCardPo';
            if (inputs != 'dropdwon') {
              if (arr[i][key].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10) {
                res.push(arr[i]);
                input += this._RenderAutoItemDivPO(arr[i], j);
                j++;
              } else if (j > 10) {
                return false;
              } else if (val.length < 1) {
                input = ''
                return false;
              }
            } else {
              res.push(arr[i]);
              input += this._RenderAutoItemDivPO(arr[i], j);
              j++;
            }
          }
          if ($('#po_numberautocomplete-list').length == 0) {
            $this.parent().append(a);
          }
          if (inputs == 'dropdwon')($('#po_numberautocomplete-list').slideUp());
          $this.parent().find('div.autocomplete-items').html(input);
        },
        

        qtyaddpopup: function() {
           var options = {
             type: 'popup',
             responsive: true,
             innerScroll: true,
             modalClass: "qty-add-popup",
             modalCloseBtn: true,

              buttons: [{
                 text: 'Done',
                 click: function (event) {
                  $("body").removeClass("prevent_scrolling");
                  this.closeModal(event);
                  setInitialPositions();
                  $("body").removeClass("overflow_hidden");
                 },
                 class: 'style-qty-edit',
               }],

               };



               var popup = modal(options, $('#qty-add-popup-modal'));
               var table_style_sku = [];
               var table_style_qty_name = [];
               var table_style_qty_name_data = {};
               var entered_by_keyboard = false;

                $(document).on("keypress", ".qty_add_num", function (e) {
                 var keycode = (e.keyCode ? e.keyCode : e.which);
                 if(keycode >= 48 && keycode <= 57){
                   entered_by_keyboard = true;
                 }
                });

                $(document).on("click", function(event){
                    if(!$(event.target).closest(".qty_add_num, .size_navigation").length){
                        entered_by_keyboard = false;
                    }
                });

                $(document).on('click','.checkvalue', function() {
                   var ar_key = $(this).parent().find("[name^='itemscode']").val();
                   var data = $(this).parents("tbody").children(":not(:first-child)");
                   $("#currenstylesku").attr('value',ar_key);
                   table_style_sku = [];
                   table_style_qty_name = [];
                   table_style_qty_name_data = {};
                   data.each(function(){
                     table_style_sku.push($(this).children(":nth-child(4)").find("[name^='itemscode']").val());
                     table_style_qty_name.push($(this).children(":nth-child(4)").find("[name^='qty']").attr("name"));
                     table_style_qty_name_data[$(this).children(":nth-child(4)").find("[name^='qty']").attr("name")] = $(this).children(":nth-child(4)").find("[name^='qty']").val();
                   });
                   $("#addstyleqty").attr('value',$(this).attr("name"));
                   updateqtypopupdata();
                   setInitialPositions();
                   $("#qty-add-popup-modal").modal("openModal");
                   $("body").addClass("prevent_scrolling");
                });

                $(document).on('click','.size_navigation span', function() {
                   if(entered_by_keyboard){
                     $(".qty_add_num").focus();
                   }
                   table_style_qty_name_data[$("#addstyleqty").attr("value")] = $(".qty_add_num").attr("value");
                   var ar_key = $("#currenstylesku").attr("value");
                   var curent_element_index = $.inArray( ar_key, table_style_sku );
                   if($(this).hasClass('previous_size')){
                     curent_element_index = curent_element_index - 1;
                     if((curent_element_index+1) > 0){
                       $("#currenstylesku").attr('value',table_style_sku[curent_element_index]);
                       $("#addstyleqty").attr('value',table_style_qty_name[curent_element_index]);
                       updateqtypopupdata();
                     }
                   }else if($(this).hasClass('next_size')){
                     var last_style_index = table_style_sku.length;
                     curent_element_index = curent_element_index+1;
                     if(curent_element_index < last_style_index){
                       $("#currenstylesku").attr('value',table_style_sku[curent_element_index]);
                       $("#addstyleqty").attr('value',table_style_qty_name[curent_element_index]);
                       updateqtypopupdata();
                     }
                   }
                });
                   var box = document.getElementsByClassName('qty-add-popup'),
                      main_select = box[0].getElementsByClassName('modal-inner-wrap'),
                      draggable = main_select[0],
                      yl = $(".block.mobile-block-collapsible-nav").height(),
                      dd = $(".modal-inner-wrap").width(),
                      isMouseDown, initX, initY, height = draggable.offsetHeight,
                      width = draggable.offsetWidth;
                    draggable.style.position = 'absolute';
                    draggable.style.left = (window.innerWidth - dd) / 2 + 'px';

                    draggable.addEventListener('touchstart', function (e) {
                      isMouseDown = true;
                      var touchobj = e.changedTouches[0];
                      initX = touchobj.clientX - this.offsetLeft;
                      initY = touchobj.clientY - this.offsetTop;
                    });

                    draggable.addEventListener('touchmove', function (e) {
                      var touchobj = e.changedTouches[0];
                      if (isMouseDown) {
                        var cx = touchobj.clientX - initX,
                          cy = touchobj.clientY - initY;
                        if (cx < 0) {
                          cx = 0;
                        }
                        if (cy < 0) {
                          cy = 0;
                        }
                        if (window.innerWidth - touchobj.clientX + initX < width) {
                          cx = window.innerWidth - width;
                        }
                        if (touchobj.clientY > window.innerHeight - height - yl + initY) {
                          cy = window.innerHeight - height - yl;
                        }
                        draggable.style.left = cx + 'px';
                        draggable.style.top = cy + 'px';
                      }

                    });

                    draggable.addEventListener('touchend', function () {
                      isMouseDown = false;
                      document.body.classList.remove('modal-inner-wrap');
                    });

                function skudatas(){
                  var inventorydata = inventorydata_all;
                  $.each(inventorydata, function (key, value) {
                    skusdata[value.ItemCode] = value
                  })
                }

               function updateqtypopupdata(){
                   var ar_key = $("#currenstylesku").attr("value");
                    skudatas();
                   var skudata = skusdata[ar_key];
                   $(".qty_for_size").html("Qty for "+skudata.Size);

                   if(skudata.ActualQty !=  '' && skudata.ActualQty != null)
                     {
                       var Qty = parseFloat(skudata.ActualQty);
                       $(".available_qty_popup").html(Qty);
                     }

                   var currenstyleqty = $("#addstyleqty").attr("value");
                   // console.log(table_style_qty_name_data);
                   if(table_style_qty_name_data[currenstyleqty] > 0 && table_style_qty_name_data[currenstyleqty] != ''){
                     $(".qty_add_num").attr("value",table_style_qty_name_data[currenstyleqty]);
                   }else{
                     $(".qty_add_num").attr("value",'');
                     $(".qty_add_num").val('');
                   }
                   // var date_condition = new Date(skudata.ETA);
                   var dateString = skudata.ETA;
                   var date = new Date(dateString.replace(/-/g, '/'));
                   if(skudata.ETA != '' && skudata.ETA != null){
                     if (!isNaN(date)) {
                       if(!$('.eta_info').is(":visible")){
                         $('.eta_info').fadeIn();
                       }
                        $('.eta_info_detailed').html( (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear());
                     } else {
                        $('.eta_info').fadeOut();/*Not Available*/
                     }
                   }

                   var currentskuindex = $.inArray( ar_key, table_style_sku );
                   var lastindex = (table_style_sku.length) - 1;
                   if(currentskuindex == 0){
                     if((table_style_sku.length) <= 1){
                         // console.log("Condition Satisfied..");
                         $(".previous_size , .next_size").hide();
                     }else{
                       $(".previous_size").hide();
                       $(".next_size").show();
                     }
                   }else if(currentskuindex == lastindex){
                     $(".next_size").hide();
                     $(".previous_size").show();
                   }else{
                     $(".previous_size, .next_size").show();
                   }
                }

                $(document).ready(function() {
                   $('.num-in span').click(function () {
                     entered_by_keyboard = false;
                       var $input = $(this).siblings('input.qty_add_num');
         
                     if($(this).hasClass('minus')) {
                       var count = parseFloat($input.val()) - 1;

                       count = count < 1 ? '' : count;

                       if (count < 2) {
                         $(this).addClass('dis');
                       }
                       else {
                         $(this).removeClass('dis');
                       }
                       $("input.qty_add_num").attr("value",count);
                     }
                     else {
                       if($input.val() == ''){
                         $input.val(0);
                       }
                       count = parseFloat($input.val()) + 1
                       $("input.qty_add_num").attr("value",count);
                       if (count > 1) {
                         $(this).parents('.num-block').find(('.minus')).removeClass('dis');
                       }
                     }

                     $input.change();
                     return false;
                   });
                });

                $(document).on('click','.modal-footer button.style-qty-edit', function() {
                   var style = $("#addstyleqty").val();
                   var qty = $(".qty_add_num").val();
                   table_style_qty_name_data[style] = qty;
                   setqtyintable(table_style_qty_name_data);

                });

                function setqtyintable(style_data) {
                  var valueexist = false;
                  for(var key in style_data){
                   var currentstyle_val = style_data[key];
                      var qty_selector = '[name="'+key+'"]';
                      // table_data += "<span>"+key +" = "+style_data[key]+"</span><br>";
                      if(currentstyle_val > 0){
                        valueexist = true;
                        $(qty_selector).val(currentstyle_val);
                      }else{
                        $(qty_selector).val('');
                      }
                      checkvalueUpdate(qty_selector, true);
                  }
                  if(valueexist){
                    $(".saveChng").removeClass("disabled",1000,"easeInOutBounce");
                  }else{
                    $(".saveChng").addClass("disabled",1000,"easeOutBounce");
                  }
                }

                function setInitialPositions() {
                  
                  const popup_init_pos = {
                    top: (window.innerHeight / 2) - parseInt($('.qty-add-popup').find('.modal-inner-wrap').height()) / 2,
                    left: (window.innerWidth / 2) - parseInt($('.qty-add-popup').find('.modal-inner-wrap').width()) / 2
                  }
                    document.getElementsByClassName('qty-add-popup')[0].getElementsByClassName('modal-inner-wrap')[0].style.top = popup_init_pos.top + "px";
                    document.getElementsByClassName('qty-add-popup')[0].getElementsByClassName('modal-inner-wrap')[0].style.left = popup_init_pos.left + "px";
                }

                function checkvalueUpdate(obj, update)
                  {

                  var qty = $(obj).val();
                  var maxQty = $(obj).attr("max");
                  var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
                  var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
                  var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();

                  if(qty != '')
                  {

                    var price = qty * selectprice;
                    if(parseInt(qty) > parseInt(maxQty))
                    {
                      //$(this).after('<span class="maxqtyvaldi">pzl enter '+maxQty+' value </span>');
                      var backqty = parseInt(qty) - parseInt(maxQty);
                      $(obj).next("span").html('Backorder '+ backqty);

                      /**$('.saveChng').attr('disabled', true);
                        $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');**/
                      }
                      else
                      {
                        //$(this).after().remove('.maxqtyvaldi');
                        $(obj).next("span").html('');
                      }
                      if(update == false)
                      {
                        var colorcode = $('input[name="colorcode['+selectcolor+']['+selectsize+']"]').val();
                        $("#qty_change_"+colorcode.replace("/", "")).val(1);
                      }

                      $('input[name="inpprice['+selectcolor+']['+selectsize+']"]').val(price.toFixed(2));
                      $('input[name="inpprice['+selectcolor+']['+selectsize+']"]').closest('td').find('span').html('$'+convertcurrency(price.toFixed(2)));
                      var savechnagestatus = $(obj).closest('table').find('.maxqtyvaldi').text().length;
                      if(savechnagestatus <= 0)
                      {
                        $('.saveChng').attr('disabled', false);
                      }
                    }
                    else
                    {
                      $(obj).val('')
                      $(obj).next("span").html('');
                      var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
                      var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
                      var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();
                      $('input[name="inpprice['+selectcolor+']['+selectsize+']"]').val('');
                      $('input[name="inpprice['+selectcolor+']['+selectsize+']"]').closest('td').find('span').html('');
                      $(obj).next("span").html('');
                    }
                    showtotal();

                  }

                    function showtotal()
                    {
                      var unittotals = $('.product_options').find('.unittotal');
                      var gd_total = 0;
                      $(unittotals).each(function() {
                        if($(this).val() != '')
                        {
                          var total = parseFloat($(this).val());
                          gd_total = gd_total + total;
                        }

                      });
                      var totalprice = convertcurrency(parseFloat(gd_total).toFixed(2));
                      $('#hi_grandtotal').val(parseFloat(gd_total).toFixed(2));
                      $('.grandtotal').html('');
                      $('.grandtotal').html('$'+ totalprice);
                    }

                      function convertcurrency(price)
                      {
                        var x=price;

                        x=x.toString();
                        var afterPoint = '';
                        if(x.indexOf('.') > 0)
                        afterPoint = x.substring(x.indexOf('.'),x.length);
                        x = Math.floor(x);
                        x=x.toString();
                        var lastThree = x.substring(x.length-3);
                        var otherNumbers = x.substring(0,x.length-3);
                        if(otherNumbers != '')
                        lastThree = ',' + lastThree;
                        return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;

                      }

                $(document).on("click", ".qty_add_num", function () {
                  var isiDevice = /ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase());
                  if(isiDevice){
                    this.setSelectionRange(0, $(this).val().length);
                  }else{
                    $(this).select();
                  }
                  entered_by_keyboard = true;
                });

                $('.qty_add_num').on('input', function (e) {
                 // console.log(this.value);
                 this.value = this.value.replace(/[^0-9]/g, '');
                 if(this.value == 0){
                   // console.log("aaaaaa");
                   this.value = '';
                 }

                });

           },
        skudatas: function () {
          var inventorydata = this.options.jsonConfig;
          $.each(inventorydata, function (key, value) {
            skusdata[value.ItemCode] = value
          })
        },
        getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.jsonConfig , function (value) {
                    return value[key] === sku;
            });
           return falg;
        },
        updateqtypopupdata: function (table_style_qty_name_data, table_style_sku) {
          var $widget = this
          var ar_key = $("#currenstylesku").attr("value");
          $widget.skudatas();
          var skudata = skusdata[ar_key];
          $(".qty_for_size").html("Qty for " + skudata.Size);
          if (skudata.ActualQty != '' && skudata.ActualQty != null) {
            var Qty = parseFloat(skudata.ActualQty);
            $(".available_qty_popup").html(Qty);
          }

          var currenstyleqty = $("#addstyleqty").attr("value");
          // console.log(table_style_qty_name_data);


          if (table_style_qty_name_data[currenstyleqty] > 0 && table_style_qty_name_data[currenstyleqty] != '') {
            $(".qty_add_num").attr("value", table_style_qty_name_data[currenstyleqty]);
          } else {
            $(".qty_add_num").attr("value", '');
            $(".qty_add_num").val('');
          }


          // var date_condition = new Date(skudata.ETA);
          var dateString = skudata.ETA;
          var date = new Date(dateString.replace(/-/g, '/'));
          if (skudata.ETA != '' && skudata.ETA != null) {
            // var date = new Date(skudata.ETA);
            // console.log(date);
            if (!isNaN(date)) {
              if (!$('.eta_info').is(":visible")) {
                $('.eta_info').fadeIn();
              }
              $('.eta_info_detailed').html((date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear());
            } else {
              $('.eta_info').fadeOut(); /*Not Available*/
              $(".modal-popup.qty-add-popup.modal-slide .modal-content").css("height", "170px");
            }
          }

          var currentskuindex = $.inArray(ar_key, table_style_sku);
          var lastindex = (table_style_sku.length) - 1;
          if (currentskuindex == 0) {
            if ((table_style_sku.length) <= 1) {
              // console.log("Condition Satisfied..");
              $(".previous_size , .next_size").fadeOut("fast");
            } else {
              $(".previous_size").fadeOut("fast");
              $(".next_size").fadeIn("fast");
            }
          } else if (currentskuindex == lastindex) {
            $(".next_size").fadeOut("fast");
            $(".previous_size").fadeIn("fast");
          } else {
            $(".previous_size, .next_size").fadeIn("fast");
          }
        },

        setTable : function(){
          var $widget = this;
            // $('.search_stock_price_data').css('display','block')
            var styleconfiguration = mageTemplate(view_stock_pricing, {
                data: this.getProductArray(this.options.style , 1),                       
                parent_color: this.options.style, 
                customersFlatDiscount: this.options.customersFlatDiscount,
                baseurl: baseurl
            });
            $('.search_stock_price_data').html(styleconfiguration);

            var actual_active_swatch = $(".swatch-option.image.selected").attr("option-label");
            var actual_active_color = $(".swatch-option.image.selected").attr("aria-describedby");
             if($(".swatch-option.image").is(":visible")){
                 actual_active_swatch = actual_active_swatch.replace(/\s+/g, '');
              }

              var color_type = "";
              if(actual_active_color == "option-label-color-93"){
                var color_type = "Solid";
              }else if(actual_active_color == "option-label-seasonalcolors-152"){
                var color_type = "Seasonal";
              }else if(actual_active_color == "option-label-heather_colors-171"){
                var color_type = "Heather";
              }

             
              setTimeout(function(){  
              $(".selected_color_size").html(color_type); 
              $widget._OnChangeDropdownPO($('span.left_icon.left_icon1'), $widget, 'dropdwon');
               }, 2000);
        }

    });

    return $.mage.SwatchRenderer; //list
});
    
