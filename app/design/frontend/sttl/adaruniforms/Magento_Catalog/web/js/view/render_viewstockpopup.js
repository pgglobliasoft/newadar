define(['jquery', 
  'mage/template',
  'text!Sttl_Customerorder/template/view_stock_pricing.html',
  'text!Sttl_Customerorder/template/view_stock_pricing_button.html',
  'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
  "text!Sttl_Customerorder/template/quickview-lineitem.html",
  "text!Globalia_Quickcheckout/template/checkoutlinetable.html",
], function($,mageTemplate,view_stock_pricing,view_stock_pricing_button,inventory,lineitemstemp,checkoutlinetable) {
    'use strict';
    var ordertotaldata = {},
        $widget = this,
        finalitems = [],
        item_edited = true,
        remove_select_option,
        removedskus = [],
        ajaxrequested = 0,
        is_edit_order_line = false,
        mapprice = 0,
        hideprice = 1;
    $.widget('mage.PopupRenderer', {
        options: {
            parentstyledata: inventory,
            poConfig:{}, 
            poCheck:{},
            baseurl: {},
            ConfigStyle: {},
            customersFlatDiscount: {},
        },
        _init: function () { 
          $(window).resize(function() {
            var fid;
                if ($(".swatch-option.image.active").length > 0) fid = $(".swatch-option.image.active").attr("href");
                if(fid){
                    var itemId = fid.substring(1, fid.length);
                    if($('#'+itemId+' .colorContainer table.table.table-bordered.table-responsive tbody').width() < $('#'+itemId+' .colorContainer table.table.table-bordered.table-responsive tbody tr').width()){
                       $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','move')
                    }else{
                        $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','default')
                    }
                }else{
                    if($('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').width() < $('.colorContainer table.table.table-bordered.table-responsive tbody tr').width()){
                       $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','move')
                    }else{
                        $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','default')
                    }
                }
          });         
          
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
          var base_url = window.location.origin;
             var $widget = this,
                options = this.options;
                $(".buyNowBtnMain").css({"pointer-events": "all", "opacity": "1"});
                $('.loadShipping').hide()
                $('.productinebuynow').show();

                document.addEventListener("ordersubmited", function (e) {
                    const flag = _.filter($widget.options.poConfig, function(value) {
                                  return value["OrderId"] !== parseInt(e.detail);
                              });
                              $widget.options.poConfig = flag;
                              
                });           
                document.addEventListener("changepoconfig",function(e){
                     const flag = _.filter($widget.options.poConfig, function(value) {
                        return value["OrderId"] !== parseInt(e.detail);
                     });
                     $widget.options.poConfig = flag;
                })

                $(document).on("scroll","#popupModal .scroll-content",function() {
                  $(".tooltip-img").css({
                        "display":"none"
                    })
                });

                $(document).on("mouseover",".swatch-option.image",function() {
                    var content = $(this).find(".box-hover").html();
                    $(".tooltip-img").html(content)
                    var position = $(this).position()
                    var boxheght = $(this).height();
                    var neardivwidth = $('.option_thumnail_detail').width();
                    $(".tooltip-img").css({
                        "display":"block",
                        "top": position.top - boxheght - 60,
                        "left": position.left + neardivwidth - $(".pro_image_detail .option_thumnail_detail").width() - 25
                    })
                });
                $(document).on("mouseout",".swatch-option.image",function() {
                    var position = $(this).position()
                    $(".tooltip-img").css({
                        "display":"none"
                    })
                });


                 $(document).on('click', '.price-check', function (e) {
                var seleter = $("#nav-tabContent .tab-pane.fade table");
                var mainselter = $("#nav-tabContent .tab-pane.fade")

                if($(this).prop('checked') == true){
                    if($(this).val() == "map_price"){
                        seleter.find(".mapprice").removeClass("hide");
                        seleter.find(".price-tr").removeClass("hide");      
                        mainselter.find("#map_price").prop('checked', true);
                        mapprice = 1       
                    }else if($(this).val() == "hide_price"){
                        mainselter.find("#hide_price").prop('checked', true);
                        seleter.find(".price").addClass("hide");
                        hideprice = 0
                        if(mapprice == 0 && hideprice == 0){
                           seleter.find(".price-tr").addClass("hide");        
                        }
                    }
                }else{
                    if($(this).val() == "map_price"){
                        mainselter.find("#map_price").prop('checked', false);
                        seleter.find(".mapprice").addClass("hide"); 
                        seleter.find(".price-tr").removeClass("hide");    
                        mapprice = 0
                        if(mapprice == 0 && hideprice == 0){
                           seleter.find(".price-tr").addClass("hide");        
                        }
                    }else if($(this).val() == "hide_price"){
                        mainselter.find("#hide_price").prop('checked', false);
                        seleter.find(".price").removeClass("hide");
                        seleter.find(".price-tr").removeClass("hide");   
                        hideprice = 1
                    }
                }
            })
                $(document).keyup(function(e) {
                    if (e.which === 27) {

                      if($('#customer-edit-address').hasClass('show') || $('#customer-add-payment').hasClass('show')){
                        if($('#customer-edit-address').hasClass('show'))
                        {
                          $('#customer-edit-address button.close.mfp-close-inside').trigger('click');
                        }
                        if($('#customer-add-payment').hasClass('show'))
                        {
                          $('div#customer-add-payment .quickViewCont button.close.mfp-close-inside').trigger('click');
                        }
                      }
                      else if($('.imgquickViewCont').hasClass('_show'))
                      {
                        $('button.mfp-close.close-image-chart-popup').trigger('click');
                      }  
                      else if($('#popupModal').hasClass('show') && !$('.imgquickViewCont').hasClass('_show') && !$('#customer-edit-address').hasClass('show') && !$('#customer-add-payment').hasClass('show'))
                      {
                        $(".productview-modal-close-inside").trigger("click");
                      }
                      else{
                          $('#quickviewpopup1 .modalContainer.quickViewCont button.close').trigger('click');
                      } 



                     

                      // else
                      // {
                      //     if($('.imgquickViewCont').hasClass('_show') || $('#quickcheckout').hasClass('show')){
                      //       if(!$('#customer-edit-address').hasClass('show')){
                      //           // $('#quickcheckout .modalContainer.quickViewCont button.close').trigger('click');
                      //         $(".productview-modal-close-inside").trigger("click");

                      //       }
                      //         $('button.mfp-close.close-image-chart-popup').trigger('click');
                      //     }else if($('#popupModal').hasClass('show') && !$('.imgquickViewCont').hasClass('_show') && !$('#customer-edit-address').hasClass('show') && !$('#customer-add-payment').hasClass('show')){
                      //         $(".productview-modal-close-inside").trigger("click");
                      //     }else{
                      //         $('#quickviewpopup1 .modalContainer.quickViewCont button.close').trigger('click');
                      //     }
                      //     if($('.imgquickViewCont').hasClass('_show'))
                      //     {
                      //       $(".productview-modal-close-inside").trigger("click");
                      //     }
                      //     else if($('#popupModal').hasClass('show') && !$('.imgquickViewCont').hasClass('_show'))
                      //     {
                              

                      //     }
                      // }

                    }
                });

                $(document).on("click",".saveChng", function(){
                  if($('.tabactive').val() == '' || $('.tabactive').val() < 4){
                    $widget.addsuccess('PO Number must be a number or letter special character and at least 4 characters long.');
                    return false;
                  }else{
                      var _current_options = 1;

                      var existponumberText = $('#select_existing :selected').val()
                      var ponumberText = $('.tabactive').val();
                      if (existponumberText != '' || jQuery.trim(ponumberText).length > 0) {
                        var checkinputval = $('.product_options').find('.checkvalue');
                        var valIsExists = false;
                        $(checkinputval).each(function () {
                          if ($(this).val() != '' && $(this).val() != 0) {
                            valIsExists = true;
                          }
                        });
                        // var isEdit = $("#is_edit_order_line").val();
                          if (!valIsExists &&  is_edit_order_line == false) {
                            if(!ajaxrequested){
                              $widget.addsuccess('Please provide at least one item quantity to proceed.');
                            }
                            return false;
                          }
                          else
                          {
                            ajaxrequested = 1;
                             var checkinputval = $(".product_options").find('.checkvalue');
                                        var edited_added_item_count = 0;
                            var valIsExists = false;
                            $( checkinputval ).each(function() {
                               if ($(this).val() != "" && $(this).val() > 0) {
                                  valIsExists = true;
                                  if ($widget._isNormalInteger($(this).val())) {
                                      edited_added_item_count = parseInt(edited_added_item_count) + parseInt($(this).val());
                                  }
                              }
                            });

                          if (edited_added_item_count > 0) {
                              $(".success-quick-tooltip span").html(edited_added_item_count + " items added to your P.O ");
                              if (!$(".success-quick-tooltip").is(":visible")) {
                                  $(".success-quick-tooltip").fadeIn(300);
                                  setTimeout(function() {
                                      $(".success-quick-tooltip").fadeOut(300, function() {
                                          $(".success-quick-tooltip span").html("");
                                      });
                                  }, 2000);
                              }
                          }
                            $widget._optiononeAdddata(data);
                          }                          
                        
                      
                      } 
                      else {
                        adderror('Please select a PO or Create a New PO before entering Qty\'s');
                      }
                      
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

                $(document).on("click", ".delSingalRecords", function(e) {
                    if (confirm("Are you sure you want to delete?")) return $widget._Deleteorderdata($(this), $widget);
                });
           
                $(document).on('change',"#select_existing", function() {
                  if(!$('#sap_ponumber_id').val()) {
                    $(".tabactive").attr("disabled", false);
                    $('#popupModal').find('.tabactive').focus();
                    $('.editpodashboard').hide();
                  }

                   var authors = $("div#nav-tab .swatch-option.image");
                    authors.each(function() {
                        if ($(this).hasClass("active")){
                           setTimeout(function() {  $('#nav-tabContent div.show.active .qtyTd').first().find('input.checkvalue').focus(); },500);
                        }
                    });
                    
                  var existponumberText = $('#select_existing :selected').val();
                  var existingorderText = $('#select_existing :selected').attr('order_id');
                  $('#enable_input_id').val(existponumberText)
                  var d1 = btoa(existponumberText)
                  var d2 = btoa(existingorderText)
                  if(existponumberText != ''){
                    $('.tabactive').attr('base_data',d2);
                    $('.tabactive').attr('ncp_data',d1);
                    $widget._getLineItemTable(existingorderText);
                  }else{
                    finalitems = [];
                    $widget._getLineItemTable(existingorderText);
                  }
                })

                $(document).on("click", ".editOrderdItem", function(e) {
                    return $widget._Editorderdata($(this), $widget);
                });


                $(document).on("click","div#nav-tab .swatch-option.image",function(){
                  setTimeout(function() {  $('#nav-tabContent div.show.active .qtyTd').first().find('input.checkvalue').focus(); },500);
                })

                $(document).on('keypress',".tabactive", function(e) {
                    var inputVal = this.value + String.fromCharCode(e.keyCode);
                    var regexAlphanumeric = /^([a-zA-Z0-9\!\@\#\&\*\(\)\-\_\+\/\\\:\;\.\,\>\<\=\|\}\{\]\[]+)$/;
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if (regexAlphanumeric.test(inputVal) == false && keycode != '13') {
                        $(this).next("span").html("Please provide valid PO Number.").show().fadeOut(2500);
                        return false;
                    }

                    if(keycode == '13'){
                        e.preventDefault();
                          var ponumberText = $('.tabactive').val();
                          var falg = _.filter($widget.options.poCheck, function(value) {
                                  return value["NumatCardPo"].toLowerCase() === ponumberText.toLowerCase();
                          });
                          var editpodashboard = $('.editpodashboard').attr('po_number');
                             if (falg.length > 0 && editpodashboard != ponumberText) {
                            $('#overlay').show();
                             $(".ponum-exist").addClass("message-error error").html("PO Number already exists.");
                             setTimeout(function(){
                                $(".ponum-exist").removeClass("message-error")
                                $(".ponum-exist").html("");  
                             },2000);
                             if($(".tabactive").val().length <= 0)
                             {
                                $(".tabactive").attr("disabled", false);
                             }
                              
                              $('.editpodashboard').hide();
                               $(".tab-pane.show.active").find(".checkvalue").first().blur();
                                $('#popupModal .product-info .bottomBtn a.btn-primary').css('pointer-events','none')
                          } else{
                            $(".ponum-exist").removeClass("message-error")
                            $(".ponum-exist").html("");
                            $('#overlay').hide();
                            $('#popupModal .product-info .bottomBtn a.btn-primary').css('pointer-events','')
                              if(inputVal.length > 4){
                                
                                var authors = $("div#nav-tab .swatch-option.image");
                                authors.each(function() {
                                    if ($(this).hasClass("active")){
                                       $(".tab-pane.show.active").find(".checkvalue").first().focus();
                                    }
                                });

                                $('.tabactive').focusout();
                              }else{
                                $widget._adderror('PO Number must be a number or letter special character and at least 4 characters long.')
                              }
                          }   
                       
                    }
                });


                $(document).on("click",".buyNowBtnMain", function(){
                  $widget._Openstockpopup($(this),$widget);
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

                $(document).on("click",".catBtns .customBtns", function(){
                    var current_options = 1;
                    $widget._renderLineitembeforeAJAX($widget, current_options);

                      var base_url = window.location.origin;
                      $("#color-data").find(".product_options").removeClass("active");
                      var style = $(this).attr("product-sku"),
                      child_pro = $widget.getProductArray(style , 1)
                      var styleconfiguration = mageTemplate(view_stock_pricing_button, {
                                data: child_pro,                       
                                parent_color: style, 
                                customersFlatDiscount : $widget.options.customersFlatDiscount,
                                rptswitcher: $widget._getRPTswitcherButtons(style, $widget),
                                baseurl : base_url,
                                mapprice: mapprice,
                                hideprice: hideprice 
                            });
                    if(styleconfiguration){
                      $("#color-data").html(styleconfiguration)
                      // $(".catBtns .customBtns").removeClass("activeCat");
                      $(this).addClass("activeCat");
                      var htmlcontent =  '<div class="show-product-dis-box"> <span>'+child_pro[0].Collection+' by adar </span><br> <span>Style: <span id="current_active_style_head">'+child_pro[0].Style+'</span></span><br> <span>Status: <span>'+child_pro[0].StyleStatus+'</span></span></div>'
                      $(".add_update_po_section .show-product-dis-box").html(htmlcontent)
                        setTimeout(function(){
                            if($("#popupModal .modal-content").height() >= window.innerHeight){
                                    $("#popupModal .scroll-content").css({"height":window.innerHeight-300, "overflow":"auto"})
                                  } 
                    },200)  

                    setTimeout(function(){
                        var fid;
                        if ($(".swatch-option.image.active").length > 0) fid = $(".swatch-option.image.active").attr("href");                        
                        if(fid){
                          var itemId = fid.substring(1, fid.length);
                            if($('#'+itemId+' .colorContainer table.table.table-bordered.table-responsive tbody').width() < $('#'+itemId+' .colorContainer table.table.table-bordered.table-responsive tbody tr').width()){
                               $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','move')
                            }else{
                                $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','default')
                            }
                        }else{
                            if($('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').width() < $('.colorContainer table.table.table-bordered.table-responsive tbody tr').width()){
                               $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','move')
                            }else{
                                $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','default')
                            }
                        }
                    },200)   
                    
                    if(mapprice == 1){
                        $(".price-checkbox #map_price").prop('checked', true);
                    }
                    if(hideprice == 0){
                        $(".price-checkbox #hide_price").prop('checked', true);
                    }

                    var modelSelector = document.getElementById('popupModal'),
                    scrollSelector = modelSelector.getElementsByClassName('scroll-content')[0];
                    scrollSelector.addEventListener("scroll", function(){
                       $(".tooltip-img").css({
                            "display":"none"
                        })
                    })


                    } 

                 

                    $widget.mousedrag()
                       setTimeout(function(){ 
                        // $(".swatch-color-container").find(".swatch-option.image").first().trigger("click")  
                        if($(".tabactive").val().length <= 0)
                             {
                                $(".tabactive").attr("disabled", false);
                             }                      }, 1000);

                        var current_options = 1;
                    $widget._renderLineitembeforeAJAX($widget, current_options);
                });

                $(document).on("click",".productview-modal-close-inside", function(){
                      finalitems = [];
                });

                $(document).on("click","#popupModal .col-md-2.col-sm-2.close_popup", function(){
                      $('#popupModal .modal-content').css('left','0px');
                      $('#popupModal.modal.show .modal-dialog').removeClass('drag-popup');
                      finalitems = [];
                });
                
                $(document).on('focusout','.tabactive', function(e) {
                  var ponumberText = $(this).val();
                  if(ponumberText.length == 0){
                    $('#overlay').hide();
                    $(".ponum-exist").html("");
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
                            var falg = _.filter($widget.options.poCheck, function(value) {
                                return value["NumatCardPo"].toLowerCase() === ponumberText.toLowerCase();
                            });
                            var editpodashboard = $('.editpodashboard').attr('po_number');
                             if (falg.length > 0 && editpodashboard != ponumberText) {
                              $('#order_id').val(null);
                               $(".ponum-exist").addClass("message-error error").html("PO Number already exists.");
                               setTimeout(function(){
                                $(".ponum-exist").removeClass("message-error")
                              $(".ponum-exist").html("");  
                               },2000);
                                if($(".tabactive").val().length <= 0)
                                 {
                                    $(".tabactive").attr("disabled", false);
                                 }
                                 $('#overlay').show();
                                $('.editpodashboard').hide();
                              return false;
                            } else{
                              $(".ponum-exist").removeClass("message-error")
                              $(".ponum-exist").html("");
                              $(".tabactive").attr("disabled", true);
                              $('.editpodashboard').show();
                              $('[data-toggle="collapse"]').prop('disabled',false);
                              $('#overlay').hide()
                              // if($("#sap_ponumber_id").val() == ""){
                              //  $("#select_existing").prop("disabled", true);
                               $("#select_existing option:selected").prop("selected", false) 
                              // }
                              if(exist)
                              {
                                  $widget._updateponumberedit($widget);
                              }
                            }
                          }
                  }
                  else
                  {
                      $("#select_existing").prop("disabled", false);
                  }
              });

          $( document ).ajaxSuccess(function( event, xhr, settings ) {
            if(xhr.statusText == 'success'){
              var ajaxresponce = xhr.responseJSON;
              
              if(typeof ajaxresponce != 'undefined'){
                var responce_length = Object.keys(ajaxresponce).length;
                if (responce_length > 0 && ajaxresponce.hasOwnProperty("is_view_stock")) {
                  if(ajaxresponce.is_view_stock == '1'){
                    var responce = xhr.responseJSON,
                        responce_order_id = atob(responce.base64_order_id),
                        poconfig_data = $widget.options.poConfig,
                        responce_ponumber = atob(responce.base64_ncp_id);

                    var falg = _.filter(poconfig_data, function(value) {
                        return value.OrderId === parseInt(responce_order_id);
                    });
                    if(falg.length > 0){
                    }else{
                      var item = {
                        NumatCardPo : responce_ponumber,
                        OrderId: parseInt(responce_order_id)
                      }
                      $('#select_existing').append('<option value="'+responce_ponumber+'" order_id = "'+responce_order_id+'">'+responce_ponumber+'</option>');
                      $widget.options.poConfig.push(item);
                    }
                  }

                }
              }
            }
          });

        },
        // _determineProductData: function(PoVal) {

        //     var falg = _.filter(this.options.poCheck, function(value) {
        //         return value["NumatCardPo"] === PoVal;
        //     });

        //     if (falg.length > 0) {
        //       alert("already exist")
        //        $(".ponum-exist").addClass("message-error error").html("PO Number already exists.");
        //     } 
        // },



       mousedrag: function(){
         const sizeTable = document.querySelectorAll('.colorContainer table.table.table-bordered.table-responsive tbody');
            let isDown = false;
            let startX;
            let scrollLeft;
            sizeTable.forEach((item) => {
              item.addEventListener('mousedown', (e) => {
                isDown = true;
                item.classList.add('active');
                startX = e.pageX - item.offsetLeft;
                scrollLeft = item.scrollLeft;
              });
              item.addEventListener('mouseleave', () => {
                isDown = false;
                item.classList.remove('active');
              });
              item.addEventListener('mouseup', () => {
                isDown = false;
                item.classList.remove('active');
              });
              item.addEventListener('mousemove', (e) => {
                if(!isDown) return;
                e.preventDefault();
                const x = e.pageX - item.offsetLeft;
                const walk = (x - startX) * 1;
                item.scrollLeft = scrollLeft - walk;
              });
            })
        },

        _oneclickcheckutlinetable: function($widget){
          var allorderitems = this._getOrderDataItems($widget,'', 1, {});
          
          var linetablehtml = mageTemplate(checkoutlinetable, {
                            ordersummaryinfo: ordertotaldata,
                            finalorderrendere: allorderitems,
                            databystylegroup: $widget._DatabyStyle(),
                            convertcurrency: $.proxy($widget._convertcurrency),
                      });
          $('#quickcheckoutcont .orderSummary-data').html(linetablehtml);
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
                  console.log("res",response)
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
                        if (response.success) {
                          $widget._oneclickcheckutlinetable($widget);   
                          $(".success-quick-tooltip span").html(response.message);
                              if (!$(".success-quick-tooltip").is(":visible")) {
                                  $(".success-quick-tooltip").fadeIn(300);
                                  setTimeout(function() {
                                      $(".success-quick-tooltip").fadeOut(300, function() {
                                          $(".success-quick-tooltip span").html("");
                                      });
                                  }, 2000);
                              }
                        }
                    }
                },
            });
        },
        _deleteOrder: function($widget, order_id) {
          var $widget = this;
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
                        $widget._oneclickcheckutlinetable($widget);
                        
                        $("#order_id").val(response.order_id);
                        $("#base64_order_id").val(response.base64_order_id);
                        $("#base64_ncp_id").val(response.base64_ncp_id);
                        if($('#select_existing option').filter(function(){ return $(this).attr('order_id') == response.delete_order_id; }).length){
                          $("#select_existing option[order_id='"+response.delete_order_id+"']").remove();
                        }
                        var item = {
                          NumatCardPo : $(".tabactive").val(),
                          OrderId: parseInt(response.delete_order_id)
                        }
                         $widget.options.poCheck.pop(item);
                         $widget.options.poConfig.pop(item);
                              $(".success-quick-tooltip span").html(response.message);
                              if (!$(".success-quick-tooltip").is(":visible")) {
                                  $(".success-quick-tooltip").fadeIn(300);
                                  setTimeout(function() {
                                      $(".success-quick-tooltip").fadeOut(300, function() {
                                          $(".success-quick-tooltip span").html("");
                                      });
                                  }, 2000);
                              }
                    }
                },
            });
        },
        addsuccess: function(message,timeOut = 3000) {
            jQuery("#posuccess-message p").html(message);
            if(!$('.modal-popup.po-success-popup').hasClass('_show')){
              jQuery("#posuccess-message").modal("openModal");
              $('div#popupModal .modalContainer').addClass('custombackshadow');
            }
            // $("#popupModal > .modal-dialog .modal-content").addClass("custombackshadow");
            jQuery(".modalContainer").css("pointer-events", "none");
            if(timeOut != 0){
              setTimeout(function() {
                  jQuery(".modalContainer").css("pointer-events", "");
                  jQuery("#posuccess-message").modal("closeModal");
                  $('div#popupModal .modalContainer').removeClass('custombackshadow');
              }, timeOut);
            }
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
                        ajaxrequested = 0;

                        
                        if (response.errors) {
                          if (response.orderStatus != 'Draft') {
                              const orderId = response.orderId;
                               const flag = _.filter($widget.options.poConfig, function(value) {
                                  return value["OrderId"] !== parseInt(orderId);
                              });
                              $widget.options.poConfig = flag;
                              const warningHTML = $widget._renderOrderSubmittedWarning(response.message);
                              if($("#popupModal .modalContainer").find('.product-info').length){
                                $("#popupModal .modalContainer").find('.product-info').append(warningHTML).focus();
                              }
                              setTimeout(function(){
                                $(".scaleAnimation").removeClass("scaleAnimation");
                              },1600)
                              return false;
                          }   
                        }
                        if(response.success){
                            var sel_arr = new Array();
                            $('#select_existing option').each(function(){
                                sel_arr.push($(this).attr('order_id'));
                            });

                            // custom event for the 1 click checkout
                            if(po_number !== '' && order_id ==  ''){
                              var obj = {"orderod":response.order_id , "ponumber" : $(".tabactive").val()}
                              let event = new CustomEvent("newordercheckout",{detail: obj});
                              document.dispatchEvent(event);
                            }else{

                            $widget._oneclickcheckutlinetable($widget);
                            }


                            var check_id = response.order_id.toString()
                            if(jQuery.inArray(check_id, sel_arr) == -1){
                              var po_number = $(".tabactive").val();
                              var item = {
                                NumatCardPo : po_number,
                                OrderId: parseInt(response.order_id)
                              }
                              $widget.options.poCheck.push(item);
                              $widget.options.poConfig.push(item);

                                $("#select_existing").append('<option value="'+po_number+'" order_id="'+response.order_id+'">'+po_number+'</option>');
                                $('.tabactive').attr('base_data',response.base64_order_id);

                                     
                              is_edit_order_line = false;
                                   
                             } 
                             
                        }
                        if (response.session_distroy) {
                            $widget._adderror(response.message);
                            $("body").trigger("processStart");
                            window.location.reload(true);
                            return false;
                        }
                        if (!response.errors) {
                            is_edit_order_line = false
                            if (response.order_id) $("#order_id").val(response.order_id);
                            if (response.base64_order_id) $("#base64_order_id").val(response.base64_order_id);
                            if (response.base64_ncp_id) $("#base64_ncp_id").val(response.base64_ncp_id);
                        }
                    },
                });
            } else {
              is_edit_order_line = false
                // $("#is_edit_order_line").attr("value", "0");
                $(".colorContainer .checkvalue, .colorContainer .unittotal").val("");
                $(".colorContainer .showprice, .maxqtyvaldi").html("");
                
            }
            return true;
        },

        _renderOrderSubmittedWarning(message){
          return '<div id="orderWarningWrapper" class="orderWarning-wrapper">'+
                  '<div class="orderWarning-content scaleAnimation">'+
                  '<p class="orderWarning-message">'+
                  '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" version="1.1" width="20" height="20" x="0" y="0" viewBox="0 0 512.001 512.001" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g> <g xmlns="http://www.w3.org/2000/svg"> <g> <path d="M503.839,395.379l-195.7-338.962C297.257,37.569,277.766,26.315,256,26.315c-21.765,0-41.257,11.254-52.139,30.102    L8.162,395.378c-10.883,18.85-10.883,41.356,0,60.205c10.883,18.849,30.373,30.102,52.139,30.102h391.398    c21.765,0,41.256-11.254,52.14-30.101C514.722,436.734,514.722,414.228,503.839,395.379z M477.861,440.586    c-5.461,9.458-15.241,15.104-26.162,15.104H60.301c-10.922,0-20.702-5.646-26.162-15.104c-5.46-9.458-5.46-20.75,0-30.208    L229.84,71.416c5.46-9.458,15.24-15.104,26.161-15.104c10.92,0,20.701,5.646,26.161,15.104l195.7,338.962    C483.321,419.836,483.321,431.128,477.861,440.586z" fill="#000000" data-original="#000000" style="" class=""></path> </g> </g> <g xmlns="http://www.w3.org/2000/svg"> <g> <rect x="241.001" y="176.01" width="29.996" height="149.982" fill="#000000" data-original="#000000" style="" class=""></rect> </g> </g> <g xmlns="http://www.w3.org/2000/svg"> <g> <path d="M256,355.99c-11.027,0-19.998,8.971-19.998,19.998s8.971,19.998,19.998,19.998c11.026,0,19.998-8.971,19.998-19.998    S267.027,355.99,256,355.99z" fill="#000000" data-original="#000000" style="" class=""></path> </g> </g> </g></svg>'+
                  message+
                  '</p>'+
                  '</div>'+
                  '</div>';
        },

         _isNormalInteger: function(str) {
            try {
                str = str.replace(/\s+/g, "");
                if (str != "") {
                    str = str.toString();
                    str = str.trim();
                    if (!str) {
                        return false;
                    }
                    str = str.replace(/^0+/, "") || "0";
                    var n = Math.floor(Number(str));
                    return String(n) === str && n >= 0;
                } else {
                    return false;
                }
            } catch (err) {
                return false;
            }
        },
        _Editorderdata: function($this, $widget) {
          is_edit_order_line = true
            // $("#is_edit_order_line").attr("value", "1");
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
            // $(".searchFromStyle").trigger("click", { is_edit_order_line: "true" });
            var ponumber = $('.tabactive').val();

            $widget._Openstockpopup($(this)==null,$widget,getstyle,ponumber);

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
                                  var responce = response,
                                      responce_order_id = responce.order_id,
                                      poconfig_data = $widget.options.poConfig,
                                      responce_ponumber = responce.po_number;
                                  var falg = _.filter(poconfig_data, function(value) {
                                      return value.OrderId === parseInt(responce_order_id);
                                  });
                                  

                                  if(response.po_number !== '' && response.order_id !==  ''){
                                    var obj = {"orderod":response.order_id , "ponumber" : response.po_number}
                                    let event = new CustomEvent("afterupdatepocheckout",{detail: obj});
                                    document.dispatchEvent(event);
                                  }


                                  if(falg.length > 0){
                                    $widget.options.poConfig.forEach(function(item, index) {
                                        if (item.OrderId === falg[0].OrderId) {
                                            item.NumatCardPo = responce_ponumber;
                                        }
                                    });
                                    $widget.options.poCheck.forEach(function(item, index) {
                                        if (item.OrderId === falg[0].OrderId) {
                                            item.NumatCardPo = responce_ponumber;
                                        }
                                    });
                                  }
                                   var ponumber = $('.tabactive').val(); 
                                      $('option[order_id='+response.order_id+']').attr('value',response.po_number);
                                      $('option[order_id='+response.order_id+']').html(response.po_number);
                                      $('.tabactive').val(response.po_number)
                                   
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
        _Openstockpopup: function($this,$widget,styleid,ponumber) {
          var base_url = window.location.origin;
            var style = $this ? $this.attr("id") : styleid;
              var styleconfiguration = mageTemplate(view_stock_pricing, {
                data: this.getProductArray(style , 1),                       
                parent_color: style, 
                customersFlatDiscount : this.options.customersFlatDiscount,
                rptswitcher: $widget._getRPTswitcherButtons(style, $widget),
                ponumber: this.options.poConfig,
                baseurl : base_url,
                mapprice: mapprice,
                hideprice: hideprice
            });
            $(".product-info").html(styleconfiguration)

             setTimeout(function(){
                if($('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').width() < $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody tr').width()){
                     $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','move')
                }else{
                    $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','default')
                }
            },200) 
             
              setTimeout(function(){
                if($("#popupModal .modal-content").height() >= window.innerHeight){
                        $("#popupModal .scroll-content").css({"height":window.innerHeight-300, "overflow":"auto"})
                      } 
              },200)                     
              if(mapprice == 1){
                  $(".price-checkbox #map_price").prop('checked', true);
              }
              if(hideprice == 0){
                  $(".price-checkbox #hide_price").prop('checked', true);
              }
              if(styleconfiguration){
                
                var modelSelector = document.getElementById('popupModal'),
                        scrollSelector = modelSelector.getElementsByClassName('scroll-content')[0];
                  scrollSelector.addEventListener("scroll", function(){
                     $(".tooltip-img").css({
                          "display":"none"
                      })
                  })
              }
            $widget.mousedrag();
            $(".tabactive").val(ponumber)
            $('#select_existing').val(ponumber);
            var existponumberText = $('#select_existing :selected').val();
            var existingorderText = $('#select_existing :selected').attr('order_id');
            if(typeof existingorderText != 'undefined'){
              var d1 = btoa(existponumberText)
              var d2 = btoa(existingorderText)
              $('.tabactive').attr('ncp_data',d1);
              $('.tabactive').attr('base_data',d2);
              $('#order_id').val(existingorderText);
            }  
            if(finalitems.length != 0){
              var current_options = 1;
              $widget._renderLineitembeforeAJAX($widget, current_options);
            }
            setTimeout(function(){ 
              if($('.tabactive').val().length <= 0)
              {
                $(".tabactive").attr("disabled", false);
              }else{
                $('.editpodashboard').show();
              }
            }, 2000);
             
            
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
              var lineitem_temp = mageTemplate(lineitemstemp, {
                  editid: current_item,
                  finalorderrendere: allorderitems,
                  ordersummaryinfo: currency_convertedsummary,
                  databystylegroup: $widget._DatabyStyle(),
                  currencyconvert: $.proxy(this._convertcurrency, this),
                  generateDiscountTooltip: $widget._generateDiscountTooltip(),
              });

              $('.quicklineitems').html(lineitem_temp);

                console.log("len",finalitems.length)

                if(finalitems.length == 0){
                  $('#quickcheckoutelink').addClass("opacity-down");
                  $('.container.bg-light.p-2.bottomBtn a#chekout').addClass("opacity-down");
                }else{
                  $('#quickcheckoutelink').removeClass("opacity-down");
                  $('.container.bg-light.p-2.bottomBtn a#chekout').removeClass("opacity-down");
                }
                
              if($("#popupModal .modal-content").height() >= window.innerHeight){
                $("#popupModal .scroll-content").css({"height":window.innerHeight-300, "overflow":"auto"})
              }
              $('#popupModal .modal-content').css('top','0px')
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
            // if(typeof ordersummarydata == undefined){
            //      $widget.delete_drop();
            // }
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

    });
    return $.mage.PopupRenderer;
});