define(['jquery', 
  'mage/template',
  'text!WeltPixel_Quickview/template/quickviewpopup.html',
  'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
  'mage/requirejs/json!Sttl_Customerorder/template/featurepro.json',
  "text!Sttl_Customerorder/template/quickview-lineitem.html",
], function($,mageTemplate,quickviewpopup,inventory,featurepro,lineitemstemp) {
    'use strict';
    var $widget = this,
        simpleproduct = [],
        configurationproduct = [],
        sizeoption = [],
        proskuss = [],
        login = 1,
        ordertotaldata = {},
        finalitems = [],
        item_edited = true,
        removedskus = [],
        remove_select_option,
        orderDevelopquick1 = false;
    $.widget('mage.PopupRenderer', {
        options: {
            viewfileurl:{},
            parentstyledata: inventory,
            baseurl:{},
            action: {},
            login : {},
            baseurl: {},
            ConfigStyle: {},
            customersFlatDiscount: {},
            customersBulcDiscount: {},
        },
        _init: function () { 
            console.log("finalitemsfinalitems",finalitems)
        },
        _create: function () {
            var $widget = this
            login = $widget.options.login
            if($widget.options.action === "dashboard-index-index"){
                $widget.dashboardpagequickview();
            }
            $widget._EventListener(); 
            if($widget.options.action === "catalog-category-view" || $widget.options.action === "catalogsearch-result-index"){
                var skus = $widget.getproductskus();
                $widget.feachsiteattrvalue();
                $widget.catalogpagequickview(skus);
            }

        },
        feachsiteattrvalue: async function(){
            const response = await fetch("https://dev.adaruniforms.com/rest/V1/attribute/options?id=size")
            var res = await response.json()
            sizeoption = res[0].option;
            login = res[0].customerdata;            
        },
        getproductskus: function(){
            var selector = $('.products.list.items').find(".product-item").children();
            proskuss = [];
            selector.each(function(){
                const item_sku = $(this).attr('data-config-item');
                if(!_.contains(proskuss, item_sku)){
                    proskuss.push(item_sku);
                }
            })
            return proskuss
        },
        dashboardpagequickview: function(){
            var $widget = this
            var url = $widget.options.baseurl+"customerorder/customer/quickview";
            // $.get(url, function(data, status){
            //     if(data){
            //         var produs = data;
            //     } 
            //   });    
            // console.log(featurepro);
                    simpleproduct = featurepro[0]['simapleproduct'];
                    sizeoption = featurepro[0]['sizeoption'];
                    configurationproduct = featurepro[0]['configurationproduct'];
                    $(".quickviewpopup1").css("pointer-events","")
                    $(".grid-item.grid-sizer.forhovereffact").removeClass("blure");
                    $widget.setfeaturedpro($widget);
        },
        catalogpagequickview: function(todoIdList){
            const tem = [];
            for(var i = 0; i < todoIdList.length; i=i+3) {
                var str = ''
                for (var j = i; j < i+3; j++) {
                    if(todoIdList[j]){
                     str += todoIdList[j]+",";
                    }
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
                            simpleproduct = $.merge(simpleproduct, todo[0].simapleproduct);
                            configurationproduct = $.merge(configurationproduct, todo[0].configurationproduct);
                            // console.log(simpleproduct)
                            // console.log(configurationproduct)
                            orderDevelopquick1 = true;
                            $.each(todo[0].configurationproduct,function(key,value){
                                $(".quickviewpopup1#"+value.id).parent().removeClass();
                                $(".quickdots#"+value.id).remove();
                                $(".orderDevelopquick").removeClass("orderDevelopquick");
                                $(".loadDots123").removeClass("loadDots123");
                                $('.customquickviewpopup.quickviewpopup1').attr("href", "#quickviewpopup1");
                                $('.customquickviewpopup.quickviewpopup1').attr("data-target", "#quickviewpopup1");
                            })
                            resolve(0)
                          })
                      })
                    })
                })
              })
            )
        },
        _EventListener: function (e,data) {
             var $widget = this,
                options = this.options;   
                $('.customquickviewpopup').on('click', function(e){
                    e.preventDefault();
                    if($(this).find('.quickdots').length <= 1 && $(this).find('.quickdots').is(":visible")){
                        return false;
                    }
                })
            $(document).keyup(function(e){
                 if (e.keyCode === 27) {
                     $('.QuickViewContenthtml .modalContainer.quickViewCont button.close').trigger('click');
                     $('button.mfp-close.close-image-chart-popup').trigger('click');
                 }
                 if (e.keyCode === 13) {
                    if($(".checkvalue").val()){
                        $(".saveChng").trigger("click")
                    }
                 }
             });

             $(document).on("click",".saveChng", function(){
                  if($('.tabactive').val() == ''){
                    return false;
                  }else{
                    var checkinputval = $(".product_options").find('.checkvalue'),
                        valIsExists = false,
                        _current_options = 1;
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
            
            $(document).on("click",".productview-modal-close-inside", function(){
                  finalitems = [];
            });             

             $(document).on('focusout','.tabactive', function(e) {
                  var ponumberText = $(this).val();
                  if(ponumberText.length == 0){
                    $("#select_existing option:selected").prop("selected", false)
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
                                  $widget._updateponumberedit2($widget);
                              }
                          }
                          if(ponumberText.length < 4 || ponumberText.length == 0)
                          {   
                              finalitems = [];
                              var current_options = '';
                              $widget._renderLineitembeforeAJAX($widget, current_options);
                              // $widget._adderror('PO Number must be a number or letter special character and at least 4 characters long.')
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

            $(document).on("click", ".delSingalRecords", function(e) {
                    if (confirm("Are you sure you want to delete?")) return $widget._Deleteorderdata($(this), $widget);
            });
           
            $(document).on('change',"#select_existing", function() {
                var existponumberText = $('#select_existing :selected').val(),
                    existingorderText = $('#select_existing :selected').attr('order_id');
                  if(existponumberText != ''){
                    $widget._getLineItemTable(existingorderText);
                  }else{
                    finalitems = [];
                    $widget._getLineItemTable(existingorderText);
                  }
            })

            $(document).on("click", ".editOrderdItem", function(e) {
                return $widget._Editorderdata($(this), $widget);
            });

            $(document).on('keypress',".tabactive", function(e) {
                var inputVal = this.value + String.fromCharCode(e.keyCode);
                var regexAlphanumeric = /^([a-zA-Z0-9\!\@\#\&\*\(\)\-\_\+\/\\\:\;\.\,\>\<\=\|\}\{\]\[]+)$/;
                var keycode = (e.keyCode ? e.keyCode : e.which);
                if (regexAlphanumeric.test(inputVal) == false && keycode != '13') {
                    $(this).next("span").html("Please provide valid PO Number.").show().fadeOut(2500);
                    return false;
                }
                // $widget._updateponumberedit2($widget);
                if(keycode == '13'){
                    e.preventDefault();
                    $(".tab-pane.show.active").find(".checkvalue").first().focus();
                }
            });

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

            $(document).on("click", ".catBtns .customBtns", function() {
                var current_options = 1;
                $widget._renderLineitembeforeAJAX($widget, current_options);
                
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

            document.addEventListener("newproduct_rendered", function (e) {
                let skuss = [];
                var selector = $('.products.list.items').find(".product-item").children();
                selector.each(function(){
                    const item_sku = $(this).attr('data-config-item');
                    if(!_.contains(skuss, item_sku)){
                        skuss.push(item_sku);
                    }
                }) 
                let temp = [];       
                $.each(skuss,function(key,val){
                    if(!_.contains(proskuss, val)){
                        proskuss.push(val);
                        temp.push(val);
                    }
                })
                $widget.catalogpagequickview(temp);
            });

            $("#quickviewpopup1 .modal-content").draggable({
                handle: ".container.bg-primary.p-2",
                containment: ".modal-backdrop"
            });

            $(document).on("click",".quickviewpopup1",function(){
                var id = $(this).attr('id');
                $widget.quickviewpopuphtml($widget,id) ;

            })

            $( ".product-item-info" ).mouseover(function() {
              if(orderDevelopquick1 == true){
                $('.orderDevelopquick').removeClass("orderDevelopquick");
                    $("div").remove(".quickdots");
                }else{
                    $('.customquickviewpopup.quickviewpopup1').unbind("click");
                    $('.orderDevelopquick').css("display", "block");
                }
            });
            $(document).on("click",".QuickViewContenthtml .swatch-option.image",function(){
                    $(".QuickViewContenthtml .swatch-option.image").removeClass("selected");
                    $(this).toggleClass("selected");
                var productsku = $(this).attr("product-id"),
                    prodata = $widget.getProductArray(simpleproduct,productsku),
                    colortype = $(this).attr("color-type"),
                    colorname = $(this).attr("aria-label"),
                    parentsku = $(this).attr("parent-id");
                    if(colortype != "size"){
                        $("span.swatch-attribute-selected-option.color").html("");
                        $("span.swatch-attribute-selected-option.heather").html("");
                        $("span.swatch-attribute-selected-option.seasonal").html("");
                    }
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html(colorname);
                $widget._onclickchangeproduct($widget,prodata,colorname,colortype,parentsku);
            })

            $(document).on("mouseover",".QuickViewContenthtml .swatch-option.image",function(){
                var  colorname = $(this).attr("aria-label");
                var  colortype = $(this).attr("color-type");
                $(this).addClass("selected2")
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html("");
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html(colorname);

            })

            $(document).on("mouseout",".QuickViewContenthtml  .swatch-attribute .swatch-option.image",function(){
                
                var  colortype = $(this).attr("color-type");
                var  colorname = $('[color-type='+colortype+'].selected').attr("aria-label");  
                $(this).removeClass("selected2")
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html("");
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html(colorname);
            })
           
            $(document).on("click",".QuickViewContenthtml .swatch-option.text.swatch-option-size",function(){
                var size = $(this).attr('option-tooltip-value');
                $('span.swatch-attribute-color-selected-option').html(size);
                $(".QuickViewContenthtml .swatch-option.text.swatch-option-size").removeClass("selected");
                    $(this).toggleClass("selected");
            })
           
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
            $("#hi_grandtotal").val(grandtotal);
            $(".grandtotal").html("$" + grandtotal);
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
                                   $('.tabactive').attr('order_id',response.order_id);

                              }
                             
                          }
                      });
                  }
                  else
                  {
                       // $widget._adderror("PO Number must be a number or letter special character and at least 4 characters long.");  
                       return false;
                  }
              
          },
        _updatetmpOrderData: function($widget, _response) {
            var allorderdata = _response;
            finalitems = [];
            var orderitemsdata = allorderdata.line_item_render.allorderdata;
            var ordersummarydata = allorderdata.line_item_render.ordersummary;
            console.log("ordersummarydata",ordersummarydata)
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
        _getLineItemTable: function(order_id) {
            var $widget = this,
                tmp_po_number = $(".tabactive").attr("value"),
                req_url = this.options.baseurl + "customerorder/customer/optiontwojs",
                current_options = "";
            $.ajax({
                url: req_url,
                type: "POST",
                data: { base_order_id: order_id, po_number: tmp_po_number },
                showLoader: false,
                cache: false,
                success: function(response) {
                    console.log("res",response);
                    if (response.session_distroy) {
                        $widget._adderror(response.message);
                        $("body").trigger("processStart");
                        window.location.reload(true);
                        return false;
                    }
                    if (response.success) {
                        $widget._updatetmpOrderData($widget, response);
                        $widget._renderLineitembeforeAJAX($widget, current_options);
                        $('.tabactive').attr('base_data',response.base64_order_id);
                        $('.tabactive').attr('ncp_data',response.base64_ncp_id);
                        $('.tabactive').attr('order_id',response.order_id);
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
        _optiononeAdddata: function(data) {
            var $widget = this,
                is_savedata = "true",
                current_options = 1,
                req_url = this.options.baseurl + "customerorder/customer/optiontwojs",
                customerdata = JSON.stringify($widget.options.customersFlatDiscount),
                nextstep = $("#nextstep").val();
            $widget._renderLineitembeforeAJAX($widget, current_options);
            console.log("finalitems",finalitems)
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
                            // $widget._addOpt1error(response.message);
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
            // console.log("sasa",$('.tabactive').attr('order_id'))
            // console.log("sasa ty",typeof $('.tabactive').attr('order_id'))
            // if(typeof $('.tabactive').attr('order_id') == 'undefined'){
            //       finalitems = [];
            // }
          
            return true;
        },
        _renderLineitembeforeAJAX: function($this,_current_options,styles = {}, click_event = "") {            
                var $widget = $this,
                orderdata = "",
                currency_convertedsummary = {},
                allorderitems = this._getOrderDataItems($widget, orderdata, _current_options, styles);
                console.log("allorderitems",allorderitems)
                $widget._getorderSummaryinfo($widget, orderdata);
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


              var current_item = $(".product_options.active").attr("id");
              if(finalitems.length == 0){
                $widget.delete_drop();
              }
              var lineitem_temp = mageTemplate(lineitemstemp, {
                  editid: current_item,
                  finalorderrendere: allorderitems,
                  ordersummaryinfo: currency_convertedsummary,
                  databystylegroup: $widget._DatabyStyle(),
                  currencyconvert: $.proxy(this._convertcurrency, this),
                  generateDiscountTooltip: $widget._generateDiscountTooltip(),
              });
              console.log("lineitem_temp",lineitem_temp)
              $('.quicklineitems').html(lineitem_temp)
                // if(typeof $('.tabactive').attr('order_id') == 'undefined'){
                //       finalitems = [];
                // }
            },
        delete_drop: function (argument) {
            $(document).on('change',"#select_existing", function() {
              if($('#select_existing option').filter(function(){ return $(this).val() == remove_select_option; }).length){
                      $("#select_existing option[value='"+remove_select_option+"']").remove();
                }
            
            })
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
              $('#hi_grandtotal').val(grandtotal)
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
        _stylebyInventory: function() {
            var data = this.options.parentstyledata,
                temp_items = [];
            data.forEach(function(item, index) {
                var style = item.Style;
                temp_items[style] = item;
            });
            return temp_items;
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
        setfeaturedpro: function($widget){
            var html = ''
            $.each(configurationproduct,function(key,value){
               html +=  '<div class="grid-item grid-sizer forhovereffact"> <a href="#quickviewpopup1" data-target="#quickviewpopup1" class="quickviewpopup1" id ="'+value.id+'"  data-toggle="modal"><div class="featureborder"> <img src="'+$widget.options.baseurl+"pub/media/catalog/product"+value.productimgurl+'"></div></a></div>'
            })
            $(".featured-row .orderItem-loader").hide();
            $(".featured-pro").append(html);
        },
        _onclickchangeproduct: function($widget,productdata,colorname,colortype,parentsku){
            var data = productdata
            var featuhtml = ''
            $.each(data[0].feature,function(index,value){ 
                featuhtml+= "<li class='value'><img src="+$widget.options.baseurl+'pub/media/'+value.small_image+">"+value.name+"</li>"
            })
            $(".productQuality.product.feature").html(featuhtml);
            var fabric_chaturl = data[0].fabric_chat == '' ? this.options.baseurl+"pub/media/fabricurl/placeholder/fabric_placeholder_text.png" : data[0].fabric_chat;
            $("img.fabric_pop_img").attr("src",fabric_chaturl)
            var itemno = $("#big .owl-item.active").find(".item").attr("itemno");
            
            $("div#sizechartPopupModal .size-image img").attr("src",data.size_chat)
            
            var imagesliderhtml = $widget.slidervlaue(data[0].image);
                if(data[0].image.length == 1){
                    $("#big").html(imagesliderhtml);
                    $("#thumbs").trigger('replace.owl.carousel', imagesliderhtml);
                    $("#thumbs").trigger('refresh.owl.carousel');
                }

            if(data[0].image.length > 1){
                $("#big").trigger('replace.owl.carousel', imagesliderhtml);
                $("#thumbs").trigger('replace.owl.carousel', imagesliderhtml);
                $("#big").trigger('refresh.owl.carousel');
                $("#thumbs").trigger('refresh.owl.carousel');
                $("#big").data("owlCarousel").to(itemno,1, true);
                
                var current = itemno
                var thumbs = $("#thumbs"); 
                  thumbs.find(".owl-item").removeClass("current").eq(current).addClass("current"); 
                var onscreen = thumbs.find(".owl-item.active").length - 1; 
                var start = thumbs.find(".owl-item.active").first().index();
                var end = thumbs.find(".owl-item.active").last().index();
                if (current+1 > end) {
                  thumbs.data("owlCarousel").to(current, 100, true);
                }
                if (current < start) {
                  thumbs.data("owlCarousel").to(current - onscreen, 100, true);
                }
              
            }
            var simp = $widget.getProductArray(simpleproduct,parentsku,'parent_sku'),
                 sizess =  $widget.sizefilter(simp,colorname,colortype),
                 sizehtml = '';

            $.each(sizess, function(key,value){
                var sizelable = $widget.sizevalue(value.size)

                sizehtml += '<div class="swatch-option text swatch-option-size image" sort="'+sizelable.index+'" color-type="size" aria-label="'+sizelable.label+'" role="option">'+sizelable.label+'</div>'
            })
            $("#size-attr").html(sizehtml);

            $widget.changeSizeOrderr();
            // $("#big").data("owlCarousel").to(itemno, 0, true);
        //     setTimeout(function(){
        //         $("#thumbs").data("owlCarousel").to(itemno, 0, true);
        // },100)

        },
        sizevalue: function(size){
            var obj = {}
            $.map(sizeoption,function(index,val){
                if(index.value == size){
                    obj['label'] = index.label;
                    obj['index'] = val;
                }
            })
            return obj
        },
        slidervlaue: function(res){
            var html = '';
            for (var i = 0; i < res.length; i++) {
                      html += "<div class='item' itemno="+i+">"
                      
                      html += "<img src="+res[i]+">"
                      
                      html += "</div>"
            }
            return html
        },      
        quickviewpopuphtml: function($widget,psku){
            var simp = $widget.getProductArray(simpleproduct,psku,'parent_sku'),
                config = $widget.getProductArray(configurationproduct,psku),
                fabric_chaturl = config[0].fabric_chat == '' ? this.options.baseurl+"pub/media/fabricurl/placeholder/fabric_placeholder_text.png" : config[0].fabric_chat;
            var images = simp[0].image;
            if(images.length <= 0){
                $.each(simp,function(key,value){
                    if(value.image.length > 0){
                        images = value.image
                    }
                })
            }
            var brandurl ='';
            if(config[0].productBrandUrl){
                
                if(config[0].productBrandUrl.length > 0){

                    // var brandUrl = config[0].productBrandUrl[0];
                    // var demo = jQuery.inArray('brand_url',brandUrl);
                    // if(demo >= 0)
                    // {
                        brandurl = config[0].productBrandUrl[0].brand_url;
                    // }
                }
                else
                {
                    brandurl ='#';
                }
            }           

            var configid = $('.quickviewpopup1').attr('id');
            var products = this.getProductArrayforviewstock(configid , 1);
            var hasviewstockdata = true;
            // console.log(products);
            if(products.length <= 0)
            {
                hasviewstockdata = false;
            }

            // console.log(config);
            $('.modal-content.ui-draggable').css('left','0px');
            $('.modal-content.ui-draggable').css('top','0px');
                var styleconfiguration = mageTemplate(quickviewpopup, {
                        sku: config[0].id,                       
                        name: config[0].name,  
                        producturl: config[0].producturl,                     
                        collection: config[0].collection,                       
                        color: $widget.filterbyattr(simp,'color',1),                       
                        heathercolor: $widget.filterbyattr(simp,'heathercolor',1),                       
                        seasonalcolor: $widget.filterbyattr(simp,'seasonalcolor',1),                       
                        size: $widget.filterbyattr(simp,'size'),
                        featureddata:simp[0].feature, 
                        productimage: config[0].productimages,
                        baseurl: this.options.baseurl,  
                        sizeoption: sizeoption,
                        sizevaluee: $.proxy(this.sizevalue, this),
                        productbaseurl : brandurl,
                        logindata: login,
                        hasviewstockdata : hasviewstockdata
                    });

            
            $("#featuredproductpopupdata").html(styleconfiguration);
            $widget.imageslider();
            $widget.removedublicatecolor($widget,psku);
            
            setTimeout(function(e){
             // $(".QuickViewContenthtml .swatch-option.image").first().trigger("click");
             $widget.changeorder();
            },1000);

        },
        getProductArrayforviewstock: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.parentstyledata , function (value) {
                    return value[key] === sku;
            });
           return falg;

        },
        removedublicatecolor: function($widget,psku){
            var simp = $widget.getProductArray(simpleproduct,psku,'parent_sku'),
                color = $widget.filterbyattr(simp,'color',1),                       
                heathercolor = $widget.filterbyattr(simp,'heathercolor',1),                       
                seasonalcolor = $widget.filterbyattr(simp,'seasonalcolor',1);
                $.each(heathercolor,function(key,value){
                    $('[color-type="color"]').each(function(){
                       var color = $(this).attr("aria-label")
                        if(value.heathercolor == color){
                            $(this).remove();
                        }
                    });
                });
        },
        changeorder: function(){

                if ($('.swatch-option.image').is(':visible')) { //if the container is visible on the page
                    changeOrder($('[aria-labelledby=option-label-color-93]')); //Adds a grid to the html
                    changeOrder($('[aria-labelledby=option-label-seasonalcolors-152]'));
                    changeOrder($('[aria-labelledby=option-label-heather_colors-227]'));
                    changeSizeOrder($('[aria-labelledby=option-label-size-145]'));

                } 
            function changeOrder(lbl) {
                var $wrapper = lbl;
                $wrapper.find('.swatch-option').sort(function(a, b) {
                    if ($(a).attr('aria-label') > $(b).attr('aria-label')) {
                        return 1;
                    } else {
                        return -1;
                    }
                }).appendTo($wrapper);
            }
            function changeSizeOrder(lbl) {
                var $wrapper = lbl;
                $wrapper.find('.swatch-option').sort(function(a, b) {
                    if (parseInt($(a).attr('sort')) > parseInt($(b).attr('sort'))) {
                        return 1;
                    } else {
                        return -1;
                    }
                }).appendTo($wrapper);
            }
        },
        changeSizeOrderr: function(){
            var $wrapper = $('[aria-labelledby=option-label-size-145]');
            $wrapper.find('.swatch-option').sort(function(a, b) {
                if (parseInt($(a).attr('sort')) > parseInt($(b).attr('sort'))) {
                    return 1;
                } else {
                    return -1;
                }
            }).appendTo($wrapper);
        },
        imageslider: function(){
            var bigimage = $("#big");
              var thumbs = $("#thumbs");
              var syncedSecondary = true;
              bigimage
                .owlCarousel({
                items: 1,
                slideSpeed: 2000,
                lazyLoad: true,
                nav: true,
                autoplay: false,
                dots: false,
                loop: ($(".owl-carousel .owl-item").length >= 1) ? true : false,
                dots: false,
                responsiveRefreshRate: 200,
                navText: [
                  '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                  '<i class="fa fa-angle-right" aria-hidden="true"></i>'
                ]
              })
                .on("changed.owl.carousel", syncPosition);

              thumbs
                .on("initialized.owl.carousel", function() {
                thumbs
                  .find(".owl-item")
                  .eq(0)
                  .addClass("current");
              })
                .owlCarousel({
                items: 4,
                dots: true,
                margin:10,
                nav: true,
                dots: false,
                navText: [
                  '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                  '<i class="fa fa-angle-right" aria-hidden="true"></i>'
                ],
                smartSpeed: 200,
                slideSpeed: 500,
                slideBy: 4,
                responsiveRefreshRate: 100
              })
                // .on("changed.owl.carousel", syncPosition2);

              function syncPosition(el) {
                //if loop is set to false, then you have to uncomment the next line
                //var current = el.item.index;

                //to disable loop, comment this block
                var count = el.item.count - 1;
                var current = Math.round(el.item.index - el.item.count / 2 - 0.5);

                if (current < 0) {
                  current = count;
                }
                if (current > count) {
                  current = 0;
                }
                if(current == 5){
                 current = 0;
                }
                //to this
                thumbs.find(".owl-item").removeClass("current").eq(current).addClass("current"); 
                var onscreen = thumbs.find(".owl-item.active").length - 1; 
                var start = thumbs.find(".owl-item.active").first().index();
                var end = thumbs.find(".owl-item.active").last().index();
   
                if (current+1 > end) {
                  thumbs.data("owlCarousel").to(current, 100, true);
                }
                if (current < start) {
                  thumbs.data("owlCarousel").to(current - onscreen, 100, true);
                }
              }

              function syncPosition2(el) {
                if (syncedSecondary) {
                  var number = el.item.index;
                  $("#big").data("owlCarousel").to(number, 100, true);
                }
              }

              thumbs.on("click", ".owl-item", function(e) {
                e.preventDefault();
                var number = $(this).index();
                $("#big").data("owlCarousel").to(number, 300, true);
              });
                  
        },
        getProductArray: function(arr,sku ,option=''){
           var key = option == 'parent_sku' ? 'parent_sku' : 'id',
               falg = _.filter(arr , function (value) {
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
                    console.log("del_response",response)
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
        sizefilter: function(arr,color,type){
            var $widget = this, key = '', ukey = '';
                switch (type) { 
                case 'color': 
                    key = 'color'  
                    ukey = 'colorurl'   
                    break;
                case 'heather': 
                    key = 'heathercolor'    
                    ukey = 'heathercolorurl'   
                    break;
                case 'seasonal': 
                    key = 'seasonalcolor'    
                    ukey = 'seasonalcolorurl'
                    break;
                }
            
                var dataaa = [];
               $.each(arr,function(vkey,value){
                    if(!_.contains(dataaa, value.size)){
                        if(value[key] === color){
                            dataaa.push(value);
                        }
                    }
               })

           return dataaa;
        },
        filterbyattr: function(simpleproduct,attr,atype = 0){
            var $widget = this, key = '', ukey = '';
            switch (attr) { 
                case 'color': 
                    key = 'color'  
                    ukey = 'colorurl'   
                    break;
                case 'heathercolor': 
                    key = 'heathercolor'    
                    ukey = 'heathercolorurl'   
                    break;
                case 'seasonalcolor': 
                    key = 'seasonalcolor'    
                    ukey = 'seasonalcolorurl'
                    break;
                case 'size': 
                    key = 'size'    
                    break;
            }
            var attr = [],
                temp = []
           $.each(simpleproduct,function(vkey,value){
                if(value.image){
                if(!_.contains(temp, value[key])){
                    temp.push(value[key])
                    var obj = {}
                    if(atype == 1){
                        if(value[key] != "No Color" && value[key] != null && value[ukey] != null ) {
                            attr.push(value)
                        }
                    }else{
                        
                        attr.push(attr.push(value))
                    }
                }
            }
           })
           return attr;
        },
    });

    return $.mage.PopupRenderer;
});