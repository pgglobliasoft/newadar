/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([    
    'jquery',
    'mage/template',   
    'Magento_Customer/js/customer-data',
    'text!Sttl_Customerorder/template/neworder-style.html',
    'text!Sttl_Customerorder/template/neworder-lineitem.html',
    'text!Sttl_Customerorder/template/neworder-marketing-lineitem.html',
    'text!Sttl_Customerorder/template/marketing-item.html',
    'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
    'mage/validation/validation'        
], function ($, mageTemplate, customerData, thumbnailPreviewTemplate,lineitemstemp, marketinglineitem, marketingitem , inventory) {
    'use strict';
     var activecat = [],
          activecat_material = [],
         activecol = [],
         finalitems = [],
         removedskus = [],
         ordertotaldata = {},
         selector = '',
         owlproduct = '',
         owlmaterial = '',
         item_edited = false,
         message_timeout = '',
         json1Config = {},
         mark_invdata =  {},
         mark_invdata_ral= [],
         config_total_data = new Array;
    /**
     * Render neworder controls
     * Required two json:
     *  - jsonConfig (magento's sap product config)
     *  - poConfig (Po number config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */     
    $.widget('mage.SwatchRenderer', {
        options: {
            // option's json config
            jsonConfig: inventory,

            // option's PO config
            poConfig:{},

             // option's PO config
            ConfigStyle:{},

            // Customer`s FlatDiscount From SAP`
            customersFlatDiscount: {},

            conditionjson: {},

            // BaseURl
            baseurl: {},

            // Simple SKU(s)
            SimpleStyle:{},

            // swatch's json config
            jsonSwatchConfig: {},

            // Use ajax to get image data
            useAjax: false,

            //Magento product listing page
            magento :{},

            //Get order id from URL
            base_order_id: {},

            //Return the Bulk discount of current customer
            customersBulcDiscount: {},

            // slider collection array
            slidercollections: {},

            marketData_real : {},
            //Ajax 
            usexhr: false
        },

        /**
         * Div Input for Item.
         * This control shouldn have "type=hidden" :)
         *
         * @param {Object} config
         * @private
         */
         _RenderAutoItemDivLi: function (config , i, option) {
            var classActive = i < 1 ? 'autocomplete-active' : '';
            return '<div class="element '+classActive +' " '+
                    'data-index="' + i +'" >' +
                    '<span>'+ (option == 1 ? config.Style : config.ItemCode)  +
                    " - "+ config.ShortDesc +
                    '<input class="super-attribute-select" ' +
                    'name="super_attribute"' +
                    'type="hidden"' +
                    'value="'+(option == 1 ? config.Style : config.ItemCode) +'"'+
                    'aria-invalid="false" />'+
                    '</span></div>';
        },

        /*
        **  find all confiurable product Sky
        *  * @returns array|null
        */
        getConfigurableProduct: function(events, key){
            if(events.length > 0){
              var result = events.reduce(function(memo, e1){
                  var matches = memo.filter(function(e2){
                    return e1.Style == e2.Style 
                                    })
                  if (matches.length == 0)
                      memo.push(e1)
                  return memo;
              }, [])
              this.options.ConfigStyle = result;
              return result;
          }else{
            return {};
          }
        },

        getRandomList: function(){ 
            
            var $widget = this,
                data1 = new Array(),     
                data = $widget.getConfigurableProduct(this.options.jsonConfig,'Style')   
            data.forEach(function(item, index){
              if(item.Collection !== ''){
                var result = $widget.getColorProductArray(item.Style,1)             
                if(result)
                  data1.push(result);              
              }
            });        
            return data1;
            
        },

        /*
        **  find confiurable/Simple product list
        *  * @returns array|null
        */
        getColorProductArray: function(sku ){
          
          var child_pro = this.getProductArray(sku, 1),
              final_vale_array = [],
              main_swatch_color_array = [];

          _.each(child_pro, function(value, index) {
              if(value.Color != '' && value.ColorSwatch != '' ) {
                if(!_.contains(main_swatch_color_array, value.ColorCode)) {
                  main_swatch_color_array.push(value.ColorCode);
                  final_vale_array.push(value);
                }
              }
          });
          if(final_vale_array.length < 0){
            final_vale_array = child_pro;
          }
          var rendom =  final_vale_array.length > 0 ? Math.floor(Math.random() * final_vale_array.length) : 0;
          rendom = rendom >= 0  && rendom < final_vale_array.length  ? rendom : 0;          
          return final_vale_array[rendom];         
          
        },



        /*
        **  find confiurable/Simple product list
        *  * @returns array|null
        */
        getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.jsonConfig , function (value) {
                    return value[key] === sku;
            });
           return falg;

        },
        getProductbysku: function(sku){
           var falg = _.filter(this.options.ConfigStyle , function (value) {
                    return value['Style'] === sku;
            });
           return falg;
        },

        _init: function () {                
            var $widget = this;
            $widget._slidercollection();                        
            $widget._collectionlogoslider();
            $widget.productslider();
            
            // $widget._mark_slidercollection(mark_invdata); 
            $widget.mark_materialslider();
            $widget._mark_materialcategory();
            
            $widget._EventListener();
            $("#collectionTabs .slider-loader").hide();
            const datad =  this.options.marketData
            this.options.marketData_real = this.options.marketData
            mark_invdata = this.options.marketData;

            mark_invdata_ral = JSON.parse(JSON.stringify(mark_invdata));;
            // mark_invdata.forEach(function(val,index){
            //   mark_invdata_ral[index] = val;
            // });


            // console.log(mark_invdata_ral);

            if(this.options.base_order_id && this.options.base_order_id != ''){this._getLineItemTable(this.options.base_order_id); }
            $(".cf.newOrderStep1").find(".showposec").fadeOut(300);
            $("#po_number").focus();
            var data = '<div id="tip-second" class="tip-content hidden"> <h2>My second tip</h2> <p>This is my second tip content</p> </div>';           
            // console.log('datad ==>',datad)
            // console.log('datad ==>',datad)
            

        },


        _create: function() {          
            // console.log(this.options.poConfig);
            var options = this.options,
                element = this.element,
                $widget = this,  
                base_order_id = this.options.base_order_id; 
            $('#po_number').focus();
            if(!base_order_id)
               element.find("#po_number").css({"opacity": ""}).attr('readOnly',false);
            
            // setTimeout(function(e){$widget._collectionlogoslider(); $widget.productslider(); },2000);

            element.find('.checkPoAndInsert').on('click', function(e){
                e.preventDefault();
               $('#po_number').validation();
               if(!$('#po_number').validation('isValid')){
                   $('#po_number').css('border','1px solid red');
                   return false;
               }else{  $('#po_number').css('border',''); }
                
               return $widget._determineProductData( $('#po_number').val(),$widget);
            });

            $(document).on('click',".saveData", function(e, data){
                  var edited_added_item_count = 0;
                  var checkinputval = $('.colorContainer').find('.checkvalue'),
                      valIsExists = false,
                      isEdit = $("#is_edit_order").val();

                  $( checkinputval ).each(function() {
                      if ($(this).val() != '' && $(this).val() > 0){
                          valIsExists = true;
                          if($widget._isNormalInteger($(this).val())){
                            edited_added_item_count = parseInt(edited_added_item_count) + parseInt($(this).val());
                          }
                      }
                  });

                  // console.log(edited_added_item_count);

                  if(!valIsExists && isEdit == 0)
                  {
                      $widget._addOpt1error('Please provide at least one item quantity to proceed.')
                      return false;
                  }else{
                    $("#message").fadeOut(300);
                    if($(this).parents(".cf").find(".edit_note").is(":visible"))
                      $(this).parents(".cf").find(".edit_note").fadeOut(300);                    
                  }

                  var checklink =  $(this).attr("disabled");
                  if(valIsExists || isEdit == 1)
                  {
                    var getColorCode = '';
                    if($( ".swatch-option.image.active" ).length > 0)
                      getColorCode = $( ".swatch-option.image.active" ).attr('option-color-code');
                    
                    $(".success-tooltip span").html(edited_added_item_count+" items added to your P.O ");
                    if(!$(" .success-tooltip").is(":visible")){
                      $(".success-tooltip").fadeIn(300);
                      setTimeout(function(){
                        $(".success-tooltip").fadeOut(300, function(){
                          $(".success-tooltip span").html('');
                        });
                      },2000);
                    }
                    return $widget._optiononeAdddata(data);

                  }else{
                      $widget._addOpt1error('Please provide valid data to proceed.');
                      return false;
                  }
              });


        },

        /**
         * Add a Data to Po using Option1 and use for AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */

        _optiononeAdddata: function (data) {
            
            var $widget = this,
                is_savedata = 'true',
                current_options = 1,
                req_url = this.options.baseurl+'customerorder/customer/optiontwojs',
                customerdata = JSON.stringify($widget.options.customersFlatDiscount),
                nextstep = $('#nextstep').val();
                // $(".cf.loaderAdd").children().slideDown(300);
            $widget._renderLineitembeforeAJAX($widget, current_options);
              // $(".cf.loaderAdd").children().slideUp(300);

            var tmp_ordertotaldata = JSON.stringify(ordertotaldata),
                tmp_finalitems = JSON.stringify(finalitems),
                po_number = $("#po_number").val(),
                order_id = $("#order_id").val(),
                base64_order_id = $("#base64_order_id").val(),
                base64_ncp_id = $("#base64_ncp_id").val();
                $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
                $(".colorContainer .showprice, .maxqtyvaldi").html('');
                // $('html, body').animate({scrollTop: $("#nav-tabContent").offset().top - 80, }, 700, 'linear')
                $widget._ScrollAnimate($("#nav-tabContent"), 700)
              if(item_edited){
                $.ajax({
                    url:req_url,
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
                    success: function(response){
                        if(response.session_distroy){
                          $widget._addOpt1error(response.message);
                          $('body').trigger('processStart');
                          window.location.reload(true);
                          return false;
                        }

                        if(!response.errors){
                          if(response.order_id)
                          $("#order_id").val(response.order_id);
                        
                          if(response.base64_order_id)
                            $("#base64_order_id").val(response.base64_order_id);
                          
                          if(response.base64_ncp_id)
                            $("#base64_ncp_id").val(response.base64_ncp_id);
                        }
                        if(response.errors){

                            $widget._updatetmpOrderData($widget,response);
                            var lineitem_temp = $widget._renderLineitemAfterAJAX($widget,response, current_options);
                            $(".cf.line-item").fadeIn(300);
                            $(".line-item .orderListing").html(lineitem_temp);

                            if(response.message)
                              $widget._addOpt1error(response.message);
                            
                            $widget._changeurl(response.base64_order_id, response.base64_ncp_id);

                            if(lineitem_temp)
                                $(".cf.delOrdLink").fadeIn(300);

                            $("#is_edit_order").attr("value","0");
                        }

                        if (response.success) {

                            // if(response.message)
                              // $widget._addsuccess(response.message);
                            if(response.base64_order_id && response.base64_ncp_id)
                              $widget._changeurl(response.base64_order_id, response.base64_ncp_id);
                            $("#is_edit_order").attr("value","0");
                        }
                        if(nextstep == 'continuetopayment')
                        {
                          $('#nextstep').val('');
                          $widget._Contopayment();
                        }
                        if(nextstep == 'saveasdraft')
                        {
                          $('#nextstep').val('');
                          $widget._SaveasDraft();
                        }

                    }
                });
              }else{
                $("#is_edit_order").attr("value","0");
                $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
                $(".colorContainer .showprice, .maxqtyvaldi").html('');
                if(nextstep == 'continuetopayment')
                {
                  $('#nextstep').val('');
                  $widget._Contopayment();
                }
                if(nextstep == 'saveasdraft')
                {
                  $('#nextstep').val('');
                  $widget._SaveasDraft();
                }
              }
            return true;
        },

        _addMarketingItem: function () {
            
            var $widget = this,
                is_savedata = 'true',
                current_options = 1,
                req_url = this.options.baseurl+'customerorder/customer/optiontwojs',
                customerdata = JSON.stringify($widget.options.customersFlatDiscount),
                nextstep = $('#nextstep').val();
                // $(".cf.loaderAdd").children().slideDown(300);
            // $widget._renderLineitembeforeAJAX($widget, current_options);
              // $(".cf.loaderAdd").children().slideUp(300);
            var tmp_ordertotaldata = JSON.stringify(ordertotaldata),
                tmp_finalitems = JSON.stringify(finalitems),
                po_number = $("#po_number").val(),
                order_id = $("#order_id").val(),
                base64_order_id = $("#base64_order_id").val(),
                base64_ncp_id = $("#base64_ncp_id").val();
                $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
                $(".colorContainer .showprice, .maxqtyvaldi").html('');
                // $('html, body').animate({scrollTop: $("#nav-tabContent").offset().top - 80, }, 700, 'linear')
                $.ajax({
                    url:req_url,
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
                    success: function(response){
                        if(response.session_distroy){
                          $widget._addOpt1error(response.message);
                          $('body').trigger('processStart');
                          window.location.reload(true);
                          return false;
                        }

                        if(!response.errors){
                          if(response.order_id)
                          $("#order_id").val(response.order_id);
                        
                          if(response.base64_order_id)
                            $("#base64_order_id").val(response.base64_order_id);
                          
                          if(response.base64_ncp_id)
                            $("#base64_ncp_id").val(response.base64_ncp_id);
                        }
                        if(response.errors){

                            $widget._updatetmpOrderData($widget,response);
                            var lineitem_temp = $widget._renderLineitemAfterAJAX($widget,response, current_options);
                            $(".cf.line-item").fadeIn(300);
                            $(".line-item .orderListing").html(lineitem_temp);

                            if(response.message)
                              $widget._addOpt1error(response.message);
                            
                            $widget._changeurl(response.base64_order_id, response.base64_ncp_id);

                            if(lineitem_temp)
                                $(".cf.delOrdLink").fadeIn(300);

                            $("#is_edit_order").attr("value","0");
                        }

                        if (response.success) {
                            var update_slider = true;
                            $widget._checkcondition($widget,update_slider);
                            if(response.base64_order_id && response.base64_ncp_id)
                              $widget._changeurl(response.base64_order_id, response.base64_ncp_id);
                            $("#is_edit_order").attr("value","0");
                        }
                        if(nextstep == 'continuetopayment')
                        {
                          $('#nextstep').val('');
                          $widget._Contopayment();
                        }
                        if(nextstep == 'saveasdraft')
                        {
                          $('#nextstep').val('');
                          $widget._SaveasDraft();
                        }

                    }
                });
            return true;
        },


        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var $widget = this,
                options = this.options.classes,
                target;              

           
            $widget.element.on('click', '.num-in span', function (e) {
                return $widget._inremenot($widget, $(this));
            });

            $widget.element.on('input', '.dropdown', function (e) {
                return $widget._OnChangeDropdown($(this), $widget);
            });
            
            $widget.element.on('click','.num-in .plus', function(e){
              var qty = parseInt($(this).parents('.num-in').find('.qty_add_num').val());
                if(!$(this).attr('data-index'))
                    $(this).attr('data-index',1)
                else
                    $(this).attr('data-index', parseInt($(this).attr('data-index'))+ 1)

                       if(!$(this).attr('max-plus'))
                       {
                               $(this).attr('max-plus',5);
                       }
                       var maxplus = parseInt($(this).attr('max-plus'));

                  var parantclass= $(this).parent().attr('class'),
                      productid = $(this).parents().closest('.product-item').attr('id'),
                      configproduct= $('#'+productid+ ' .num-in input[name="mark_item_sku"]').val(),
                      qty_ordered_tmp = $(this).siblings('.qty_add_num').val(),
                      unitprice_tmp = $(this).siblings('input[name="mark_item_price"]').attr('value'),
                      co = 5;

                let itemdata = {
                  item_sku: productid,
                  qty_ordered: qty_ordered_tmp,
                  unitprice: unitprice_tmp
                }
                  $widget._renderLineitembeforeAJAX($widget,co, itemdata, 'plus');  
                  $(this).addClass("disable");
                  $widget._addMarketingItem();
                // if(parseInt($(this).attr('data-index')) < maxplus && qty < maxplus ){
                //   $(this).parents('.num-in').find('span.minus').removeClass('disable');
                // } else{
                //   $(this).addClass('disable').attr('disabled','disabled');
                // }           
            })

        
            $widget.element.on('click','span.minus', function(e){

                 var qty = $(this).parents('.num-in').find('.qty_add_num').val();
                   if(!$(this).attr('data-index'))
                    $(this).attr('data-index',1)
                else
                    $(this).parents('.num-in').find('span.plus').attr('data-index', parseInt($(this).attr('data-index'))- 1)
                    $(this).attr('data-index', parseInt($(this).attr('data-index'))- 1)    
                  var  parantclass= $(this).parent().attr('class'),
                    productid = $(this).parents().closest('.product-item').attr('id'),
                    configproduct= $('#'+productid+' .num-in input[name="mark_item_sku"]').val(),
                    qty_ordered_tmp = $(this).siblings('.qty_add_num').val(),
                    unitprice_tmp = $(this).siblings('input[name="mark_item_price"]').attr('value'),
                    co = 5;
                    // $widget._checkcondition($widget);
                  let itemdata = {
                    item_sku: productid,
                    qty_ordered: qty_ordered_tmp,
                    unitprice: unitprice_tmp
                  }
                  $widget._renderLineitembeforeAJAX($widget,co, itemdata, 'minus');
                  $(this).addClass("disable");
                  $widget._addMarketingItem();
                if(qty > 0)
                {
                  $(this).parents('.num-in').find('span.plus').removeClass('disable');
                }
                else
                {
                  var orderitem = $widget._getFinalItemsbySku(productid)
                  if(typeof orderitem != 'undefined'){
                    var deletesku = [productid],
                        po_number = $('#po_number').val(),
                        removed_markitem = true;
                    $widget._deleteRecord(deletesku,[''],po_number,removed_markitem);
                  }
                  $(this).addClass('disable').attr('disabled','disabled');
                }



            })

            $widget.element.on('click', '.col-logo', function (e) {
                $widget.collectionlogosclickevent($(this));
            });
            
            $(document).on("click",function() {
                 jQuery('div.autocomplete-items').remove(); 

            });

            $(document).on("click",'.selectallRecord',function() {
              var selec = $(this).parents("table.lineItemsList").find("input.deleteMultiRecord");
              if($(this).is(":checked")){
                selec.each(function(){
                  $(this).prop('checked', true);
                });
              }else{
                selec.each(function(){
                  $(this).prop('checked', false);
                });
              }
            });

            $(document).on("click",'input.deleteMultiRecord',function() {
              var selec = $(this).parents('table.lineItemsList').find("input.deleteMultiRecord"),
                  total_row = selec.length,
                  total_selected = 0;
              selec.each(function(){
                if($(this).is(":checked")){
                  total_selected++;
                }
              });

              if(total_selected == total_row){
                $(this).parents('table.lineItemsList').find("input.selectallRecord").prop('checked', true);
              }else{
                $(this).parents('table.lineItemsList').find("input.selectallRecord").prop('checked', false);
              }

            });



            $(document).delegate('#option1', 'click', function(e) {
                setTimeout(function(e) {                  
                    return $widget.owlres();
                }, 2000)
            });

            // $(document).delegate('.collectionTabs.nav.nav-tabs', 'click', function(e) {               
            //     setTimeout(function(e) {                  
            //         return $widget.owlres('.material-slider');
            //     }, 2000)
            // })

            $widget.element.on('click',".product-group-slider .group-col", function () {
                if($('.product-slider .owl-item.active.sticky').length === 1){
                   $('.product-slider-sticky').remove();
                }
                $widget.categoryclick($(this));  
                
            });

            $widget.element.on('click',".material-group-slider .group-col", function () {
                $widget.mark_material_categoryclick($(this));  
                
            });

            $widget.element.on('keydown', '.dropdown', function (e) {               
                if (e.which == 32 || e.which == 27) { return false; }                
                return $widget._OnEnterClick($(this), $widget,e);
            });

            $(document).on('keydown', '#opt_two_qty', function (e) {
                var keyval = e.keyCode || e.which;
                if (keyval == 96 && $(this).val() < 1 ) { return false; }
                if ($('#opt_two_qty').val().length < 1 && keyval == 9) {
                  $("#opt_two_sku").focus();
                  $widget.optionTwoError("Please provide Quantity.");
                  return false;
                }
                if($('#opt_two_sku').val().length >= 0 && (keyval == 9 || keyval == 13)){
                  if(!$widget._checkSkuAvailable($('#opt_two_sku').val())){
                    $widget.optionTwoError("Please provide Valid SKU.");
                    return false;
                  }
                }
                if(keyval == 9 || keyval == 13){ $('.add-to-order-main').trigger('click'); return false;}
            });

            $(document).on('click', '.add-to-order-main', function(e){

               
                var opt_two_sku = $('#opt_two_sku').val(),
                    opt_two_qty = $('#opt_two_qty').val();                
               
                if(opt_two_sku != '' && opt_two_qty != '')
                  return $widget._optiontwoAddqty( opt_two_qty, opt_two_sku, $('#po_number').val(),$widget);              

            });           

            $(document).on('keydown', '#po_number', function (e) {
                var keyCode = e.keyCode || e.which, enterevent = 13;

                if($.browser.safari !== undefined){
                  if($.browser.safari){
                    enterevent = 65;
                  }
                }                

                if(keyCode === 9 || keyCode == enterevent) { 
                  $('#po_number').validation();
                  if(!$('#po_number').validation('isValid')){
                     $('#po_number').css('border','1px solid red');
                     return false;
                  }else{  $('#po_number').css('border',''); }
                  e.preventDefault(); 
                  $widget._determineProductData( $('#po_number').val(),$widget);
                  return false; 
                }
            });

            $widget.element.on('focusout', '#opt_two_qty, #opt_two_sku', function (e) {
              $("#qtyDetailPop").fadeOut(300);
            });

            $widget.element.on('focusin', '#opt_two_qty', function (e) {
               jQuery('div.autocomplete-items').remove();
              if($("#opt_two_sku").val().length > 0){
                $("#qtyDetailPop").fadeIn(300);
              }
            });

            $widget.element.on('click', '.searchFromStyle', function (event, data) {
                return $widget._OnItemChange($(this), $widget, data);
            });

            $widget.element.on('click', '.autocomplete-items .element', function () {
                return $widget._ItemSelect($(this), $widget);
            });

            $widget.element.on("change","#files_upload",function() {
                 return $widget._checkfileupload($(this), $widget);
            });
            
            // BS 16/10/2020 START
            $widget.element.on('click', '#createorder .optionTabs .nav-item', function () {
                return $widget._SwitchbetweenOptions($(this), $widget);
            });

            $widget.element.on('click', '.editOrderdItem', function (e) {
                return $widget._Editorderdata($(this), $widget);
            });

            $widget.element.on('click', '.delSingalRecords', function (e) {
              if (confirm('Are you sure you want to delete?'))
                return $widget._Deleteorderdata($(this), $widget);              
            });

            $widget.element.on('focusout', '.checkvalue', function(){
                $widget._checkvalueUpdate($(this), false);
            });

            $widget.element.on('keypress', '.checkvalue', function(e){
              if (e.which == 13) {
                  $(this).focusout();
                  $(".saveData").trigger('click',{enter_trigger:true});
              }
              if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) 
                  return false;
              
            });

            // $(window).resize(function(){                
            //     $('.product-slider,.Collections').css('width',jQuery('#user-gird-conatiner').width())
            //     $('.product-slider').trigger('refresh.owl.carousel');                
            //     $(".Collections").trigger('refresh.owl.carousel');        
            // });

            $widget.element.on('mouseover', '.renderAllHtml .swatch-option.image', function () {
                $(this).find(".bottom-tooltip-active").show();
            });

            $widget.element.on('mouseout', '.renderAllHtml .swatch-option.image', function () {
              if(!$(this).hasClass("active")){
                $(this).find(".bottom-tooltip-active").hide();
              }
            });

            $widget.element.on('click', '.catBtns .customBtns', function () {
              var cur_button = $(this).attr("product-sku"),
                  active_pro = $("config_product_info").attr("id");

              if(active_pro == "config_"+cur_button)
                return false;
              else{
                $("#show_style").val(cur_button);
                $(".searchFromStyle").trigger("click");
              }
            });
           
            $widget.element.on('click', '.delSelectedRecords', function (e) {
              return $widget._DeleteSelectedorderdata($(this), $widget);
            });

            $widget.element.on('click', '.deleteorder', function (e) {

              if (confirm('Are you sure you want to delete order?')) {
                var baseorder_id = $("#base64_order_id").val(),           
                    nexturl = $widget.options.baseurl+'customerorder/customer/deletejs/id/'+baseorder_id+'/';
                $('body').trigger('processStart');
                window.location.href = nexturl;
              }
            });

            $widget.element.on('click', '#file_addorder', function () {
              $widget._optionthreeupload($(this), $widget);
            });

            $widget.element.on('click', '.contopayment', function (e) {
              e.preventDefault();
              $widget._Contopayment($(this), $widget);
            });

            $widget.element.on("click","#file_close",function() {

                var fp = $("#files_upload"),
                    items = fp[0].files,
                    lg = fp[0].files.length;
                $("#file_show_name").fadeOut(300);
                $("#file_name").fadeOut(300);
                $("#file_close").fadeOut(300);
                $("#file_addorder").fadeOut(100);
                setTimeout(function(){$('#files_upload').val(''); $("#file_name").html(''); $("#file_size").html(''); },400);
            });

            $widget.element.on('click', '.saveasdraft', function (e) {
              return $widget._SaveasDraft($(this), $widget);
            });

            // BS 21/10/2020 END

            // BS 23/10/2020 Start
            $(document).on("click","#goback",function() {
                $("#contopaymentredirect").val('');
                $("#clickOnSaveAndDraft").val('');
                $('#nextstep').val('');
                $('#removeUser').dialog('close');
            });

            $(document).on("click","#savecontinue",function() {
                $('#removeUser').dialog('close');
                $widget._optiononeAdddata();
            });

            $(document).on( "click", "#removeUser .mfp-close-inside", function( event, ui ) {
                $('#nextstep').val('');
                $('#removeUser').dialog('close');
            });

            $(document).on("mouseover", ".product_options.createOrder",function(e){
              var imgs = $('.option-thumbnail span');
              imgs.each(function (index, value) {
                var img = $('<img />', {
                    'class': 'popupImage',
                    'src': $(this).attr('data-href'),
                    'alt': $(this).attr('alt')
                });
                $(this).replaceWith(img);
              });
            });

            // BS 23/10/2020 END

            
        },
        owlResize: function(name){

           var $carousel = $(name);            
            var carousel = $carousel.data();           
            carousel.owlCarousel.options.autoplay = false;
            carousel.owlCarousel.options.loop = false;
            $('.owl-carousel').trigger('refresh.owl.carousel');
             setTimeout(function(E){       
                carousel.owlCarousel._invalidated.width = true;     
                carousel.owlCarousel.options.loop = true;             
                carousel.owlCarousel.options.autoplay = true;
                $('.owl-carousel').trigger('refresh.owl.carousel');


            },100)     

        },
        owlres :function(name = '.product-slider' ) {              
          
            var $carousel = $(name);            
            var carousel = $carousel.data();           
            carousel.owlCarousel.options.autoplay = false;
            carousel.owlCarousel.options.loop = false;
            $('.owl-carousel').trigger('refresh.owl.carousel');

              

                // if instance is existing
              if(carousel != null)
                
                setTimeout(function(E){       
                carousel.owlCarousel._invalidated.width = true;     
                carousel.owlCarousel.options.loop = true;             
                carousel.owlCarousel.options.autoplay = true;
                $('.owl-carousel').trigger('refresh.owl.carousel');


            })                      
              
            $('.owl-carousel').owlCarousel('invalidate', 'all').owlCarousel('refresh');
        },

        _getMSimpleProductInfo: function(sku){
          var arr = mark_invdata;
            var flag = 0;
            var single_product = [];
            arr.forEach(function(item, index){
                if(item.Style === sku){
                    single_product = item;
                }
            });
            return single_product;

        },

        _SaveasDraft: function(){

          var checkinputval = $('.product_options.createOrder').find('.checkvalue'),
              valIsExists = false;

          $( checkinputval ).each(function() {
              if ($(this).val() != '') valIsExists = true;              
          });

          var is_qty_change = 0,
              prev_obj_id = $(".swatch-attribute-options.clearfix").find('.tab-pane.fade.active').attr("id");

          if (typeof prev_obj_id !== "undefined") {is_qty_change = $("#qty_change_"+prev_obj_id.replace("/", "")).val(); }
          var opt = {autoOpen: false };
        
          if(valIsExists == 1 && is_qty_change  == 1){
                var getColorCode = '';
                if($( ".swatch-option.image.active" ).length > 0)
                  getColorCode = $( ".swatch-option.image.active" ).attr('option-color-code');
                $('#nextstep').val('saveasdraft');
                var theDialog = $("#removeUser").dialog(opt);
                $("#clickOnSaveAndDraft").val('1');
                theDialog.dialog("open");
          }else{
            $('body').trigger('processStart');
            $.ajax({
              url: this.options.baseurl+'customerorder/customer/shippingaddress',
              type: "POST",
              data: {'order_number' : $('#order_id').val()},
              showLoader: true,
              cache: false,
              success: function(response){
                if(response.error == true)
                {
                  // console.log('No shipping adrress is Founds');
                }
              }
            });

            var nexturl = this.options.baseurl+'customerorder/customer/index?q=d';
            setTimeout(function(){ window.location.href = nexturl; }, 300);

          }
        },

        _Contopayment: function() {

          var checkinputval = $('.swatch-attribute-options.clearfix').find('.checkvalue'),
              valIsExists = false;

          $( checkinputval ).each(function() {  if($(this).val() != '') { valIsExists = true; } });

          var is_qty_change = 0,
              prev_obj_id = $(".swatch-attribute-options.clearfix").find('.tab-pane.fade.active').attr("id");

          if (typeof prev_obj_id !== "undefined") {is_qty_change = $("#qty_change_"+prev_obj_id.replace("/", "")).val(); }

          var opt = {autoOpen: false };

          if(valIsExists == 1 && is_qty_change == 1){
            var getColorCode = '';
            if($( ".swatch-option.image.active" ).length > 0)
              getColorCode = $( ".swatch-option.image.active" ).attr('option-color-code');
            $('#nextstep').val('continuetopayment');
            var theDialog = $("#removeUser").dialog(opt);
            $("#contopaymentredirect").val('1');
            theDialog.dialog("open");
          }else{
            var contopayment = $(".delSelectedRecords").html();
            if(contopayment != '' && typeof contopayment !== "undefined"){
              $('body').trigger('processStart');
              $('#createorder').submit();
              return false;
            }
          }
        },

        _DeleteSelectedorderdata: function($this, $widget){

            var somethingChecked = false;
            $("input.deleteMultiRecord:checked").each(function() {if($(this).is(':checked')) {somethingChecked = true; } });

            if(somethingChecked){

              if (confirm('Are you sure you want to delete?')) {
                var po_number = $("#po_number").val(),
                    delete_styles = [],
                    delete_colors = [];

                $.each($("input.deleteMultiRecord:checked"), function(){
                  var tmp_delete_styles = $(this).next().val(),
                      tmp_delete_colores = $(this).next().next().val();

                  delete_styles.push(tmp_delete_styles);
                  delete_colors.push(tmp_delete_colores);
                  var tmp_orderdata = _.filter(finalitems , function (item) {
                    if(item.Style == tmp_delete_styles && item.ColorCode == tmp_delete_colores){
                    }else{
                      return true; 
                    }
                  });
                  finalitems = tmp_orderdata;  
                });

                var is_holeorderdelete = false;
                if(finalitems.length <= 0){
                  var baseorder_id = $("#base64_order_id").val();
                  $widget._deleteOrder($widget, baseorder_id);
                  is_holeorderdelete = true;
                }

                var current_options = '';
                $widget._renderLineitembeforeAJAX($widget, current_options);
                
                if(!is_holeorderdelete)
                  $widget._deleteRecord(delete_styles,delete_colors,po_number);                
              }

            }else
              $widget._adderror("Select any 1 of the Order item for the Delete.");
            
        },

        _deleteOrder: function($widget, order_id){

            var isholeorder_delete = true,
                url = this.options.baseurl+'customerorder/customer/deletejs';

            $.ajax({
              url: url,
              type: "POST",
              data: {
                order_id:order_id,
                isholeorder_delete: isholeorder_delete,
              },
              showLoader: true,
              cache: false,
              success: function(response){
                if(response.session_distroy){
                  $widget._adderror(response.message);
                  $('body').trigger('processStart');
                  window.location.reload(true);
                  return false;
                }
                if(response.success){
                  $("#order_id").val(response.order_id);
                  $("#base64_order_id").val(response.base64_order_id);
                  $("#base64_ncp_id").val(response.base64_ncp_id);
                  $widget._changeemptyurl();
                  $widget._addsuccess(response.message);
                }
              }
            });
        },

        _changeemptyurl: function(){

            if (typeof (history.pushState) != "undefined") {
                var nexturl = this.options.baseurl+'customerorder/customer/neworder',
                    obj = { Title: 'orderpage', Url: nexturl };
                    history.pushState(obj, obj.Title, obj.Url);
            } else 
                alert("Browser does not support HTML5.");
              
        },


        /**
         * Determine product id and related data
         *
         * @returns {{productId: *, isInProductView: bool}}
         * @private
         */
        _determineProductData: function (PoVal) {
            // this.options.poConfig
            var falg = _.filter(this.options.poConfig, function (value) {
                    return value['NumatCardPo'] === PoVal;
            });
            if(falg.length > 0 ) {
                 this.element.find('.po-exist').addClass("message-error error").html('PO Number already exists.');
            }else if(!/^[^-\s][a-zA-Z0-9!%*@#$&()\\-`.+,\-\s=\"]{3,}$/.test(PoVal)){
                this.element.find('.po-exist').addClass("message-error error").html('PO Number must be at least 4 characters long and cannot start with a space');
            }else{
                this.element.find('.po-exist').html("");
                $(".checkPoAndInsert").css({"pointer-events": "none", "opacity": "0.5"});
                this.element.find("#po_number").css({"opacity": "0.5"}).attr('readOnly',true);
                this.element.find(".newOrderStep2").css('pointer-events' , '').animate({'opacity' : '1',},500);
                $([document.documentElement, document.body]).animate({scrollTop: $(".newOrderStep2").offset().top-100 }, 500)
                $("#show_style").focus();
            }

        },

        /**
         * Add a Data to Po using Option1 and use for AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */

          _getLineItemTable: function (order_id) {

              var $widget = this,
                  tmp_po_number = $("#po_number").attr("value"),
                  req_url = this.options.baseurl+'customerorder/customer/optiontwojs',
                  current_options = '';

              $.ajax({
                  url:req_url,
                  type: "POST",
                  data:{
                    base_order_id:order_id,
                    po_number:tmp_po_number
                  },
                  showLoader: false,
                  cache: false,
                  success: function(response){
                      if(response.session_distroy){
                          $widget._adderror(response.message);
                          $('body').trigger('processStart');
                          window.location.reload(true);
                          return false;
                        }
                      if (response.success) {
                        $widget._updatetmpOrderData($widget,response);

                        $widget._renderLineitembeforeAJAX($widget, current_options);

                        if(response.order_id) { $("#order_id").val(response.order_id); }

                        if(response.base64_order_id) { $("#base64_order_id").val(response.base64_order_id); }

                        if(response.base64_ncp_id) { $("#base64_ncp_id").val(response.base64_ncp_id); }

                      }
                  }
              });
              
              return true;
        },

        /**
         * Prepared all inventory data by style
         *
         * @returns []
         * @private
         */

        _stylebyInventory: function() {
            var data = this.options.jsonConfig,
                temp_items = [];

            data.forEach(function(item, index){
                var style = item.Style;
                temp_items[style] = item;
            });

            return temp_items;
        },

        /**
         * Prepared Line item by Distinct Style
         *
         * @returns []
         * @private
         */

        _getOrderDataItems: function($this, _response, _current_options, styles={}) {

            var $widget = $this,
                response = _response,
                mainresponce = {},
                style = '',
                submitcolor = '',
                viewmode = '', 
                stylebyInventory = $widget._stylebyInventory(),
                data = this.options.jsonConfig;


            // var order_id = response.base64_order_id;
            
            
            if($widget._DatabyStyle() && $widget._stylebyInventory()){
                var databyStyle = $widget._DatabyStyle();

                var allorderdata = '';
                if(response != ''){
                  allorderdata = response.line_item_render.allorderdata;
                }else{
                  allorderdata = $widget._generateqtyarray(_current_options, $widget, styles);
                }
                
                var tmp_distinstyle = allorderdata.map(function(item){
                    return item.Style;
                });

                const uniqueArray = [...new Set(tmp_distinstyle)];

                var distinstyle = uniqueArray;
                var sizegrouparray = {};
                if(distinstyle){
                    distinstyle.forEach(function(item, index){
                        if(item in stylebyInventory){
                            var stylesize = stylebyInventory[item].SizeGroup;
                            sizegrouparray[stylesize] = {};
                        }
                    });
                    var count = 0;
                    distinstyle.forEach(function(item, index){
                        if(item in stylebyInventory){
                            var stylesize = stylebyInventory[item].SizeGroup;
                            sizegrouparray[stylesize][count] = stylebyInventory[item].Style;
                            count++;
                        }
                    });
                    for(var index in sizegrouparray){
                        
                        var item_size = sizegrouparray[index];
                        var groupstyle = item_size;
                        var current_style = 'viewtype'+index;
                        var datastyle_index = databyStyle.index;
                        allorderdata.forEach(function(item, index){
                          if(item.Type != 'gift'){
                              mainresponce[current_style] = {};
                          }
                        });
                        
                        allorderdata.forEach(function(item, index){
                          if(item.Type != 'gift'){
                              for(var index_a in item_size){
                                  var stylegroup = item_size[index_a]
                                  if(stylegroup == item.Style){   
                                      mainresponce[current_style][stylegroup] = {};
                                  }
                              }
                          }
                        });
                        allorderdata.forEach(function(item, index){
                          if(item.Type != 'gift'){
                              for(var index_a in item_size){
                                  var stylegroup = item_size[index_a];
                                  var colorcode = item.ColorCode;
                                  if(stylegroup == item.Style){
                                      mainresponce[current_style][stylegroup][colorcode] = {};
                                  }
                              }
                          }
                        });
                        var order_item_count = 0;
                        allorderdata.forEach(function(item, index){
                          if(item.Type != 'gift'){
                              for(var index_a in item_size){
                                  var stylegroup = item_size[index_a];
                                  var colorcode = item.ColorCode;
                                  if(stylegroup == item.Style){
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

        /**
         * Render html
         *
         * Get Final order Summary
         * return {array|[]}
         */

        _getorderSummaryinfo: function ($widget, _ordersummary) {
            var ordersummary = '';
            if(_ordersummary != ''){
              ordersummary = _ordersummary.line_item_render.ordersummary;
            }else{
              ordersummary = $widget._generateordertotalarray($widget);
            }
            return ordersummary;
        },

        /**
         * Prepared all inventory data by group
         *
         * @returns []
         * @private
         */

        _DatabyStyle: function() {

            var data = this.options.jsonConfig;
            var temp_databyStyle = {};
            
            data.forEach(function(item, index){
                var sizegroup = item.SizeGroup;
                temp_databyStyle[sizegroup] = {};
            });

            data.forEach(function(item, index){
                var sizegroup = item.SizeGroup;
                var sizeorder = item.SizeOrder;
                var size = item.Size;
                temp_databyStyle[sizegroup][sizeorder] = size;
            });

            return temp_databyStyle;
        },
       

       OptionChangeUrl:function(base64_order_id,base64_ncp_id)
        {
            if (typeof (history.pushState) != "undefined") {
                var nexturl = this.options.baseurl+'customerorder/customer/neworder/id/'+base64_order_id+'/ncp/'+base64_ncp_id,
                obj = { Title: 'orderpage', Url: nexturl };
                history.pushState(obj, obj.Title, obj.Url);
            }  

        },

        /**
         * Render html
         *
         * Shows the Option2 Avaialble Info Block
         */
        _OnChangeOptTwoInfo: function ($this, $widget, keyup = 0) {
            var result = this.getProductArray($this.val() , 2);  
            if(keyup = 1){ result =  this.getProductArray($('#opt_tow .element.autocomplete-active').find('input').val(),2); }
            if(result.length > 0){           
              $widget.inputvaleValidation($this);               
                var date = new Date(result[0].ETA);
                $("#unitPrice_op2").html('$'+result[0].UnitPrice);
                $("#inStock_op2").html(result[0].ActualQty);                
                $('#eta_op2').html(Date.parse(date) ? ((date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear()) : '');
                if(!$('#qtyDetailPop').is(":visible")){
                  $("#qtyDetailPop , #opt_two_message").fadeIn(200);
                }
            }
        },

        /*
        * option3 option 
        */
        _checkfileupload: function($this, $widget)
        {            
            // console.log('test');
            var fp = $("#files_upload"),
                files = fp.val(),
                items = fp[0].files;

            if(files == ''){
                this.optionThreeError("Please upload file.")
                fp.focus();
                return false;
            }

            if(files.toLowerCase().lastIndexOf(".csv")==-1)
            {
                this.optionThreeError("Please upload valid CSV file.")
                fp.focus();
                return false;
            }else{
                $("#file_addorder").fadeIn(100);
                $('.option3Cont').html('')
                $("#file_name").html(items[0].name);
                $("#file_size").html('('+items[0].size+'K)');
                $("#file_show_name").show();
                $("#file_name").show();
                $("#file_close").show();
            }
            return true;
        },

        /**
         * Render controls
         *
         * @private
         */
        optionThreeError: function (message , time = 5000)
        {
            var data = $("."+$('#newOrderTab>div.tab-pane.fade.active.show').attr('id'));
            data.removeClass("success").addClass("error").html(message);
            $("#opt_two_message").show().focus();            
            if($("."+$('#newOrderTab .fade.active.show').attr('id')).find('b').length < 1){ 
                data.prepend('<b  title="Copied!"></b> <span></span'); 
            }
        },


        optionTwoError: function (message , time = 5000)
        {
            var data = $("."+$('#newOrderTab>div.tab-pane.fade.active.show').attr('id'));
            data.removeClass("success").addClass("error").html(message);
            $("#opt_two_message").show().focus();
            if(time != 0){
              setTimeout(function(e){
                 data.html('');$("#opt_two_message").hide();
              },time);
            }
        },

        /**
         * success message controls
         *
         * @private
         */
        addSuccessMessage : function (message)
        {
            var data = $("."+$('#newOrderTab>div.tab-pane.fade.active.show').attr('id'));
            data.removeClass("error").addClass("success").html(message);
            $("#opt_two_message").show().focus();
            setTimeout(function(e){
                data.html('');$("#opt_two_message").hide();
            },5000);
            return true;
        },


        /**
         * Input validation controls
         *
         * @private
         */
        inputvaleValidation: function ($this)
        { 

          if($this.val().length <= 0){
                $this.parent().find(".require-field-error").remove();
                $this.css('border','1px solid red');
                var para = document.createElement("span");
                para.classList.add("require-field-error");
                var node = document.createTextNode("This is Required Field.");
                para.appendChild(node);
                var current_element = $this.parent();
                current_element.append(para);
                return false;
            }else{
                $this.parent().find(".require-field-error").remove();
                $this.css('border','');
                return true
            }
        },


        /*
        * Section show with in time period
        */
        _OnChangeDropdown: function($this, $widget){
            // $widget.inputvaleValidation($this);
            $("#opt_two_message").hide();
            var a,i,j = 0, val = $this.val(),input='',res = [];
            var arr = $this.attr('data-option') == 1 ? this.options.ConfigStyle : this.options.jsonConfig;
            a = '<div id="'+ $this.attr('id') +'autocomplete-list" class= "autocomplete-items" ><div>';
            for (i = 0; i < arr.length; i++) {
                var key =$this.attr('data-option') == 1 ? 'Style' : 'ItemCode';
                if(arr[i][key].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                    res.push(arr[i]);
                    input += this._RenderAutoItemDivLi(arr[i] , j ,$this.attr('data-option'));
                    j++;
                }else if(j >10){return false;
                }else if(val.length <1 ){  $this.parent().find('div.autocomplete-items').remove(); return false; }
            }
            if(res.length < 1){
                $("#qtyDetailPop").fadeOut(300);
                if($this.val().length > 0){
                  if($this.attr('data-option') != 1){
                    $widget.optionTwoError('Item not found.',0);
                  }else{
                    if(!$this.parents('.option').find('span.error-not-found').is(":visible")){
                      $this.parents('.option').append('<span class="error-not-found" Style="color:red;">Item not found</span>');
                      setTimeout(function () {  $('span.error-not-found').remove(''); }, 2000);
                    }
                  }
                }else{
                  $("#opt_two_message").hide();
                }
            
            }
            if($('#'+$this.attr('id') +'autocomplete-list').length == 0){ $this.parent().append(a);}                     
            $this.parent().find('div.autocomplete-items').html(input);
            if($this.attr('data-option') == 2){  setTimeout(function(e){ $widget._OnChangeOptTwoInfo($this, $widget,1)},200)}

        },

        /**
         * Check SKU is available or Not
         *
         * @returns {true|false}
         * @private
         */

        _checkSkuAvailable: function (sku) {
            var arr = this.options.jsonConfig;
            var flag = 0;
            arr.forEach(function(item, index){
                if(item.ItemCode === sku){
                    flag = 1;
                }
            });
            if(flag <= 0){
                return false;
            }else{
                return true;
            }
        },

        /**
         * Render html
         *
         * Get Simple Product Info by SKu
         * return {array|[]}
         */

        _getSimpleProductInfo: function (sku) {
            var arr = this.options.jsonConfig;
            var flag = 0;
            var single_product = [];
            arr.forEach(function(item, index){
                if(item.ItemCode === sku){
                    single_product = item;
                }
            });
            return single_product;
        },

        _getMarkItemProductInfo: function (sku) {
            var arr = mark_invdata_ral;
            var flag = 0;
            var single_product = [];
            arr.forEach(function(item, index){
                if(item.item_code === sku){
                    single_product = item;
                }
            });
            return single_product;
        },

        _getFinalItemsbySku: function (sku) {
            var arr = finalitems;
            var flag = 0;
            var single_product = [];
            arr.forEach(function(item, index){
                if(item.ItemCode === sku){
                    single_product = item;
                }
            });
            return single_product;
        },



        _getselectesproduct: function(){

        },
        /**
         * Add a Qty to Po using Option and use for call AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */

        _optiontwoAddqty: function (opt_two_qty, opt_two_sku, po_number) {

            var $widget = this;
            // console.log("TYest")          


            var activeoption2 = 'true';
            var current_options = 2;

            if(!$widget._checkSkuAvailable(opt_two_sku) && opt_two_sku !== '') {
                $widget.optionTwoError('Requested SKU doesn`t exists.',8000);
                $("#qtyDetailPop").fadeOut(200);
                $("#opt_two_sku").val("");
            }else{
                  $widget._renderLineitembeforeAJAX($widget, current_options);
                  var current_pro = $widget._getSimpleProductInfo(opt_two_sku);
                 $("#qtyDetailPop").fadeOut(200);
                 var req_url = this.options.baseurl+'customerorder/customer/optiontwojs';
                 var customerdata = $widget.options.customersFlatDiscount;
                  customerdata = JSON.stringify(customerdata);
                  var tmp_ordertotaldata = JSON.stringify(ordertotaldata);
                  var tmp_finalitems = JSON.stringify(finalitems);
                  var po_number = $("#po_number").val();
                  var order_id = $("#order_id").val();
                  var base64_order_id = $("#base64_order_id").val();
                  var base64_ncp_id = $("#base64_ncp_id").val();
                  $("#opt_two_sku, #opt_two_qty").val('');
                   $("#opt_two_sku").focus();
                $.ajax({
                    url:req_url,
                    type: "POST",
                    data: {
                        po_number: po_number,
                        order_id: order_id,
                        base64_order_id: base64_order_id,
                        base64_ncp_id: base64_ncp_id,
                        customerdata: customerdata,
                        allorderitemdata: tmp_finalitems,
                        deletedsku: removedskus,
                        ordersummary: tmp_ordertotaldata,
                        activeoption:activeoption2,
                    },
                    showLoader: false,
                    cache: false,
                    success: function(response){
                      if(response.session_distroy){
                          $widget._adderror(response.message);
                          $('body').trigger('processStart');
                          window.location.reload(true);
                          return false;
                        }

                        if (response.success) {

                            $(".cf.line-item").fadeIn(300);

                            if(response.order_id)
                            {
                              $("#order_id").val(response.order_id);
                            }

                            if(response.base64_order_id)
                            {
                              $("#base64_order_id").val(response.base64_order_id);
                            }

                            if(response.base64_ncp_id)
                            {
                              $("#base64_ncp_id").val(response.base64_ncp_id);
                            }
                            if(response.message)
                            {
                              var backorder_qty = current_pro.ActualQty - opt_two_qty;
                              
                              if(backorder_qty < 0){
                                backorder_qty = Math.abs(backorder_qty);

                                var currentdate = new Date(),
                                    backorder_date = '',
                                    eta_date = new Date(current_pro.ETA);
                                
                                if(eta_date > currentdate){
                                  backorder_date = (eta_date.getMonth()+1)+'/'+(eta_date.getDate())+'/'+(eta_date.getFullYear().toString().substr(-2));
                                  var backorder_message = response.message+" On backorder: "+backorder_qty+" for "+  backorder_date;
                                  $widget.optionTwoSuccess(backorder_message);
                                }else{
                                  var backorder_message = response.message+" On backorder: "+backorder_qty;
                                  $widget.optionTwoSuccess(backorder_message);
                                }
                              }
                              // else{
                              //   $widget.optionTwoSuccess(response.message);
                              // }

                            }

                            if(response.base64_order_id && response.base64_ncp_id)
                            {
                              $widget._changeurl(response.base64_order_id, response.base64_ncp_id);
                            }
                            // $("#opt_two_sku, #opt_two_qty").val('');
                            $(".box-content.rltv #qtyDetailPop").fadeOut(300);
                            $(".cf.delOrdLink").fadeIn(300);
                        }else{
                            // console.log("Somthing went Wrong");
                        }
                    }
                });
            }
            return true;
        },

        /*
        * Select Style Button
        */
        _ItemSelect: function($this, $widget){
            var input = $this.parents('.box-content').find('input.dropdown');
            // console.log(input.addClass('opopop'));
            input.val($this.find('input').val());
            if(input.attr('data-option') == 2  ) { $('#opt_two_qty').focus(); } //RB
            this.element.find('[data-action="'+input.attr('data-option')+'"]').trigger('click');             
        },

        /**
         * Extend basic context with additional data (search results, search term)
         * @param {Object} context
         * @return {Object}
         * @private
         */
        _prepareDropdownContext: function (_items) {
            // var context = this._superApply(arguments);
            return $.extend( {
                items: _items,
                optionData: function (item) {
                    return 'test';
                },
                itemSelected: $.proxy(this._isItemSelected, this),
                noRecordsText: $.mage.__('No records found.')
            });
        },

        /**
         * @param {Object} item
         * @return {Boolean}
         * @private
         */
        _isItemSelected: function (item) {
            // console.log(item);
            return item.id;
        },

        /**
         * @param {Object} item
         * @return {Boolean}
         * @private
         */
        _isItemOverFlowList: function (item,index) {
            var totalWidth = 0, scrollIndex = 0;            
            var menuWidth = item.innerHeight();           

            item.find('div').each(function () {
                totalWidth += $(this).innerHeight();
                if (totalWidth > menuWidth) {                                             
                    scrollIndex = $(this).attr('data-index');   
                    return false;                     
                }

            });            
            return scrollIndex-1;
        },
        getProductbysku: function(sku){
           var falg = _.filter(this.options.ConfigStyle , function (value) {
                    return value['Style'] === sku;
            });
           return falg;
        },
        /*
        * Select Style Button
        */
        _OnItemChange: function($this, $widget, data){

            var option = $this.attr('data-action'),
                input = $('input[data-option="' + $this.attr('data-action') + '"]'),            
                child_pro = this.getProductArray(input.val() , $this.attr('data-action')),
                temp_parent_sku = input.val();
          
            $('#'+input.attr('id')+'autocomplete-list').remove();

            if(child_pro.length < 1){
                if(!$this.parents('.option').find('span.error-not-found').length){ 
                    if(option == 1){
                      $this.parents('.option').append('<span class="error-not-found" Style="color:red;">Item not found</span>');
                      setTimeout(function () {  $('span.error-not-found').remove(''); }, 2000);
                    }else{
                      // message_timeout = $widget.optionTwoError('Item not found.',1000);
                    }
                }
            }else { 
                
                // $('#'+$this.attr('id')+'autocomplete-list').animate({scrollTop: 0 });

                if(option == 1){
                    $(".edit_note").hide();
                    // $([document.documentElement, document.body]).animate({scrollTop: $(".collection-owlslider").offset().top - 80}, 800);                    
                    var temp_customersFlatDiscount = this.options.customersFlatDiscount,
                        magento_product =  _.filter(this.options.magento , function (value) {                            
                                                return value.sku === input.val();
                                            }),

                        // console.log(magento_product);

                        temp_quick_url = magento_product.length > 0 ? this.options.baseurl+"weltpixel_quickview/catalog_product/view/id/"+magento_product[0].id : '';
                        // console.log(temp_quick_url);
                    var styleconfiguration = mageTemplate(thumbnailPreviewTemplate, {
                        'result': this._prepareDropdownContext(child_pro), 
                        data: child_pro,                       
                        quickview_url: temp_quick_url, 
                        parent_color: input.val(), 
                        customersFlatDiscount : temp_customersFlatDiscount,
                        rptswitcher: $widget._getRPTswitcherButtons(this.options.magento ,temp_parent_sku, $widget)
                    });

                    if($('#marketing_materials').is(":visible")){
                      $('#browse_collection_tab').click();
                    }

                    $('#option1ContStyle').html(styleconfiguration);
                    // setTimeout(function() {  var x = window.scrollX, y = window.scrollY; jQuery('#nav-tabContent div.show.active .qtyTd').first().find('input.checkvalue').focus();  window.scrollTo(x, y);}, 200); 

                    if($(".product_options").find(".core-color-section").length > 0){
                      $(".customorder-color.core-color-section .swatch-option.image").first().trigger('click');
                    }else{
                      $(".customorder-color.fashion-color-section .swatch-option.image").first().trigger('click');
                    }

                    var current_product = $widget.getConfigurableProduct(this.options.jsonConfig , 'Style');

                    var tml_finalitems = _.filter(current_product, function(item) {
                        return item.Style == temp_parent_sku;
                    });

                    var category_collection = tml_finalitems[0].Collection;
                    if(!$('.Collections #'+category_collection).hasClass('.active_collection') && !$('.product-slider #'+temp_parent_sku).length){
                      category_collection = category_collection == "Other" ? "All" : category_collection;  
                      // console.log(category_collection)
                      $('.Collections #'+category_collection  ).trigger('click');
                    }
                    
                    var category_name = tml_finalitems[0].GroupName.replace(" ","_");

                    if(!$("[child-id='"+category_name+"']").hasClass("active") && !$('.product-slider #'+temp_parent_sku).length){
                      $("[child-id='"+category_name+"']").trigger('click');
                    }

                    if(data !== undefined){
                      if(data.is_edit_order){
                        $(".edit_note").fadeIn(300);
                         $widget._ScrollAnimate($(".product_options.createOrder"), 800)
                      }
                    }else{
                      $widget._ScrollAnimate($(".collectionTabs"), 800)
                    }

                    $(".product-slider").find(".owl-item").removeClass("sticky");
                    $(".product-slider").find(".owl-item .product-item").removeClass("pro_active");
                    $(".product-slider").find("#"+temp_parent_sku+"").addClass("pro_active");
               


                    var active = temp_parent_sku;       

                    jQuery('.product-slider .owl-stage div.product[id="'+active+'"]').addClass("pro_active").parent().addClass('sticky');
                      if(jQuery('.product-slider .owl-stage div.product[id="'+active+'"]').length > 0){       
                           $('.product-slider-sticky').css({'right':'unset','left':'0'});
                          if($('.product-slider-sticky').length < 1){           
                              var content = $('.owl-item.sticky').html();
                              var newdiv = $("<div class='product-slider-sticky'>");
                              newdiv.css( {'width':jQuery('.owl-item.sticky').width() ,'right':'unset','left':'0'}).html(content);
                              $('.product-slider .owl-stage').after(newdiv);
                          }else{
                             $('.product-slider-sticky').remove();
                             var content = $('.owl-item.sticky').html();
                              var newdiv = $("<div class='product-slider-sticky'>");
                              newdiv.css( {'width':jQuery('.owl-item.sticky').width() ,'right':'unset','left':'0'}).html(content);
                              $('.product-slider .owl-stage').after(newdiv);
                          }

                    }                   

                    if($('.owl-item.sticky.active').length > 0)
                      $('.product-slider-sticky').remove();

                }else if(option == 2){                    
                    this._OnChangeOptTwoInfo($this, $widget, 1);

                }else if(option == 2){
                    // this._OnChangeOptTwoInfo();
                }else{
                    // console.log('no option change');
                }
                $('#'+input.attr('id')+'autocomplete-list').remove();
            }
            if(input.val().length < 1){  $('.autocomplete-items').remove(500);}
        },

        _ScrollAnimate: function($this, time){
          var scroll_top = 0;
          if($this.attr("class").includes("collectionTabs")){
              scroll_top = $this.position().top + 50;
          }else{
              scroll_top = $(".collectionTabs").position().top + $('#user-gird-conatiner').height() + 140;
          }
          console.log(scroll_top);
          $('.column.main').animate({
             scrollTop: scroll_top,
           },
           time )
        },

         /**
         * Callback which style input keyup and down.
         *
         * @param {HTMLElement} element - DOM element associated with a input keys.
         */
        _OnEnterClick:function($this, $widget , e){ 
            // $widget.inputvaleValidation($this);
            if(e.which == 9 || e.which == 13){
                if($this.val().length < 1){ return false }                
                


                if($(".autocomplete-items").length > 0){
                  $this.val($('#'+$this.attr('id')+'autocomplete-list').find('.element.autocomplete-active input').val());
                }

                if($this.attr('data-option') == 1)
                    $('.searchFromStyle').trigger('click');
                else if($this.attr('data-option') == 2){
                  if(!$widget._checkSkuAvailable($this.val())){ return false }
                 $('#opt_two_qty').focus();     
                 this._OnChangeOptTwoInfo($this, $widget);
                }else if($this.attr('data-option') == 3)
                    // console.log('third option');
                $('#'+$this.attr('id')+'autocomplete-list').remove();
                return false;
            }else if(e.which == 40 || e.which == 38){
                /* Key down and key up active set*/
                if($this.val().length < 1){ return false }                
                var list = $('#'+$this.attr('id')+'autocomplete-list'),                   
                    totalWidth = 0,
                    active = parseInt(list.find('.element.autocomplete-active').attr('data-index')),         
                    last = parseInt(list.find('.element:last-of-type').attr('data-index') - 1),                
                    index =  e.which == 40 && active > last || e.which == 38 && active < 1  ?  0 : (e.which == 40 ? active + 1 : active - 1 ),                              
                    scroller = this._isItemOverFlowList(list);
                if(active >= scroller && e.which == 40){  

                    var totalscroller = 0;
                    list.find('div').each(function () {
                        var index1 = $(this).attr('data-index');
                        if(index1 >= scroller && index1 <= index) 
                          totalscroller += $(this).innerHeight();
                    })                                    
                }else if(e.which == 38){                        

                    var total = list.find('div').length,
                        last = total - scroller,
                        totalscroller = 0;

                    list.find('div').each(function () {
                        var index1 = $(this).attr('data-index');
                        if(index1 < index && index1 <= last) 
                          totalscroller += $(this).innerHeight();
                    });                    
                      
                }   

                list.animate({scrollTop: totalscroller });            
                list.find('.element').removeClass('autocomplete-active');           
                $('div.element[data-index="'+(index)+'"]').addClass('autocomplete-active');
               
            }
            if($this.attr('data-option') == 2){
                 this._OnChangeOptTwoInfo($this, $widget , 1);
              }
        },

// <--------------------------------------------------AB Collction slider [Start] ------------------------------------------------->
        _slidercollection: function(){
            var data = this.getRandomList(),              
                d = this.options.jsonConfig,
                collection = []
            var $widget = this
            $.each(data,function(key,val){
                collection.push(val.Collection);
            })

            var uniquecollection = $widget.uniquevalue(collection)
            var gruname = {};
           for (var i = 0; i < uniquecollection.length; i++) {  
                var ids = [];
                $.each(data,function(key,val){
                    if(val.Collection === uniquecollection[i] && uniquecollection[i] != '' ){
                        ids.push(val.GroupName);  
                                          
                    }
                }) 
                gruname[uniquecollection[i]] = $widget.uniquevalue(ids);     
            }

            $.each(gruname,function(key,val){
                for (var i = 0; i < val.length; i++) { 
                var proids =  [];
                    $.each(data,function(prokey,proval){
                        if((proval.Collection == key)  && proval.GroupName == val[i] && val[i] != ''){
                            var obj = {};
                             obj["Style"] = proval.Style;
                             obj["ShortDesc"] = proval.ShortDesc;
                             obj["U_WImage1"] = proval.U_WImage1;
                             obj["Collection"] = proval.Collection;
                             obj["GroupName"] = proval.GroupName;
                             obj["DisPercent"] = proval.DisPercent;
                             obj["DisPrice"] = proval.DisPrice;
                             obj["UnitPrice"] = proval.UnitPrice;
                            proids.push(obj)
                        }
                    })
                               
                    gruname[key][val[i]] = proids                     
                }
            })
            this.options.slidercollections = gruname;
            // console.log(gruname)
           return gruname;
        },
         _gruopnamcollection: function(){
            var data = this.getRandomList()
            var logos = this.options.logos; 
            var collection = []
            $.each(logos, function(key,logos) {
              collection.push(logos.name)
            });
                var ids = [];
                $.each(data,function(key,val){
                    if(val.Collection && _.contains(collection,val.Collection)){
                        ids.push(val.GroupName.replace(" ","_")); 
                    }
                }) 
           return this.uniquevalue(ids); 
        },
        market_gruopnamcollection: function(){
            var data = this.mark_getRandomList();
           return this.uniquevalue(data); 
        },
        getcollectionlogo: function(){
          var $widget = this
          var logos = this.options.logos; 
          var result = []
          $.each(logos, function(key,logos) {
              result.push(logos.name)
          })
          return result;
        },
        /*
        * SET collection slider html.
        * owlCarousel collection logo slider.
        */
        _collectionlogoslider: function(){
          $(".Collections").css('width',jQuery('#user-gird-conatiner').width());
            $("#user-gird-conatiner .title").css("display","Block")
            var $widget = this
            var logos = this.options.logos; 
            var html="";
            html  += '<li class="col-logo" id="All"><h1 class="collection-logo">All Collections</h1></li>'
            $.each(logos, function(key,logos) {
                html += "<li class='col-logo' id ="+logos.name+">"                    
                html += logos.image ? "<img src="+logos.image+" alt="+logos.name+">" : 
                    "<h1 class='collection-logo'>"+logos.name+"</h1>"                    
                html  +='</li>'
            })
            

            var coll = $(".Collections").owlCarousel({
                    loop:false
                    ,autoplay:false
                    ,autoplayTimeout:30000                        
                    ,margin:10
                    ,nav:false
                    ,dots:false
                    ,responsive:{
                        0:{
                            items:2
                        },
                        600:{
                            items:6
                        },
                        1000:{
                            items:6
                        }
                    },  
                }); 
            coll.trigger('replace.owl.carousel', html);
            coll.trigger('refresh.owl.carousel');

            setTimeout(function () {
                $("li#Pro").parent().addClass("pro");
                $("li#All").parent().addClass("pro");
                $("li#Sivvan").parent().addClass("pro");
                $("li#Pro").css("width", "50px");                
                $widget.addgruopname($widget._gruopnamcollection())
                 if(!$(".owl-item.pro").hasClass("active_collection")){
                  $("li#All").parent().addClass("active_collection");
                }
            }, 150 );
            setTimeout(function(e){ $(".product-group-slider").find('.group-col').first().trigger("click"); },160);
           
        },

        /*
        * collection logo click event.
        *
        * on click set prouct collection group name.
        */

        collectionlogosclickevent: function($this){
          var $widget = this
          var gruopname = this.options.slidercollections;
          if($("li#All").parent().hasClass("active_collection")){
            activecol = [];
          }
            $this.parent().toggleClass("active_collection");
            var vid = $this.attr("id");
            if(vid != "All"){
              $("li#All").parent().removeClass("active_collection");
            }else if(vid == "All" && $("li#All").parent().hasClass("active_collection")){
              $(".owl-item").removeClass("active_collection");
              $("li#All").parent().addClass("active_collection");
              var logos = this.getcollectionlogo();
              logos.push("Other")
              activecol = logos.reverse();
            }
            if(vid != "All" && $this.parent().hasClass("active_collection")){
                activecol.push(vid)
                activecol = $widget.uniquevalue(activecol);     
            }else{
                activecol = $.grep(activecol, function(value) {
                  return value != vid;
                });
            }
            var temp = []
                $(".owl-item.active_collection.active").each(function(){
                    var collectionvalue = $(this).find(".col-logo").attr("id");
                    $.each(gruopname[collectionvalue],function(key,val){
                        temp.push(val.replace(" ","_"));
                    })
                })
                var unique = $widget.uniquevalue(temp);
                if(unique.length > 0){
                    $widget.addgruopname(unique)
                }else{
                  var group = $widget._gruopnamcollection()
                    $widget.addgruopname(group)
                }
                if(activecat.length){
                $(".product-group-slider span").each(function(){
                        var gid = $(this).attr("child-id") 
                        if(_.contains(activecat, gid)){
                            $(this).trigger("click");
                        }
                    })
                }
         
                if(!$(".product-group-slider span").hasClass("active")){
                  $("span.group-col[child-id = 'All']").trigger("click")
                }
                if(!$(".product-group-slider span.group-col").hasClass("active")){
                  var result = $widget.getslidervalueusingcollection();
                  var datavalue = $widget.slidervalue(result)
                  $widget.resetslidervalue(datavalue["html"],datavalue["items"]) 
                 }else{
                    var result = $widget.getslidervaluewithactivecat();
                    var datavalue = $widget.slidervalue(result)
                  $widget.resetslidervalue(datavalue["html"],datavalue["items"])
                 }
        },
        addgruopname: function(unique){
                var html = "<span class='group-col' child-id='All'><h1>All</h1></span>"
                for (var i = 0; i < unique.length; i++) {
                      html += "<span class='group-col' child-id="+unique[i]+"><h1>"+unique[i].replace("_"," ")+"</h1></span>" 
                    }
                     $(".product-group-slider").html(html);
                     this.changeOrder($(".product-group-slider"));
        },  
        changeOrder: function(lbl) {
            var wrapper = lbl;
            wrapper.find('.group-col').sort(function(a, b) {
                //return +a.dataset.name - +b.dataset.name;
                if ($(a).attr('child-id') > $(b).attr('child-id')) {
                    return 1;
                } else {
                    return -1;
                }
            }).appendTo(wrapper);
        },

        uniquevalue: function(arrry = ''){
            return arrry.filter(function(itm, i, a) {
                return i == a.indexOf(itm);
            });
        },
        /*
        * Product slider html.
        * owlcarousel slider.
        */ 
        productslider: function(){
            // product collection slider
            $('.product-slider').css('width',jQuery('#user-gird-conatiner').width())
            owlproduct = $('.product-slider').owlCarousel({
                     loop:true
                    ,autoplay:true
                    ,autoplayTimeout:3000
                    ,margin:0
                    // ,lazyLoad:true
                    ,rewind: true
                    ,nav:true
                    ,dots:false
                    ,navText:['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>'
                    ,'<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>'],
                    responsive:{
                        0:{
                            items:1
                        },
                        600:{
                            items:2
                        },
                        800:{
                            items:4
                        },
                        1000:{
                            items:6
                        },
                        1200:{
                            items:7
                        }
                    },    
                    onInitialized: fixOwl,
                    onRefreshed: fixOwl,   
                    onChanged: callBack
            });      
            var fixOwl = function(){
                var $stage = $('.product-slider'),
                    stageW = $stage.width(),
                    $el = $('.owl-item'),
                    elW = 0;
                $el.each(function() {
                    elW += $(this).width()+ +($(this).css("margin-right").slice(0, -2))
                });
                if ( elW > stageW ) {
                    $stage.width( elW );
                };
                // console.log('fixOwl',fixOwl);

            }      
            function callBack(event) {
                if($('.product-slider .owl-item.active.sticky').length > 0){
                      $('.product-slider-sticky').remove();
                }

                if($('.product-slider').attr('data-event') !== 'prev'){
                    var owl = $('.product-slider').data('owlCarousel');                 
                    var i = 0;    
                    if(!$('.product-slider .owl-item.active').next().hasClass('sticky')){ i = 1; 
                    }else{
                        i = 0;$('.product-slider-sticky').remove() }
                    if(i >= 1){
                        if($('.product-slider-sticky').length < 1){
                            var content = $('.owl-item.sticky').html();
                            var newdiv = $("<div class='product-slider-sticky'>");
                                newdiv.css( {'width':jQuery('.owl-item.sticky').width() ,'right':'unset','left':'0'}).html(content);
                            $('.product-slider .owl-stage').after(newdiv);
                        }

                    }else{
                        $('.product-slider-sticky').remove();
                    }
                }else{
                  if($('.product-slider .owl-item.active').prev('.sticky').length === 1){ $('.product-slider-sticky').remove(); }
                        $('.product-slider').attr('data-event', '')
                }

            }

            
        },
        //group collection click event show product collection slider
        categoryclick: function($this){
            var $widget = this
            var vid = $this.attr("child-id");
            if(vid == "All" && !$("span.group-col[child-id = 'All']").hasClass("active")){
              activecat = []
              $(".product-group-slider span").removeClass("active");
            }
            $this.toggleClass("active");
            if(vid != "All" && $this.hasClass("active")){
              $("span.group-col[child-id = 'All']").removeClass("active")
                activecat.push(vid)
                activecat = $widget.uniquevalue(activecat);
            }else{
                activecat = jQuery.grep(activecat, function(value) {
                  return value != vid;
                });
            }
            if(vid == "All"){
                var value = $widget.allcategoryfilter();
                var datavalue = $widget.slidervalue(value)
                $widget.resetslidervalue(datavalue["html"],datavalue["items"])  
            }else{
                var value = $widget.activeclassvalue();
                var datavalue = $widget.slidervalue(value);

                $widget.resetslidervalue(datavalue["html"],datavalue["items"])
            }
        },
        resetslidervalue: function(prohtml,items){
                if(prohtml){
                    $(".product-slider .owl-stage").html('');
                    if(items < 7){
                        owlproduct.trigger('replace.owl.carousel', prohtml,{
                          options: { loop: false,mouseDrag:false}
                        });
                        owlproduct.trigger('refresh.owl.carousel');
                        // $(".owl-controls").css("display","none")

                    }else{
                        owlproduct.trigger('replace.owl.carousel', prohtml,{
                          options: {mouseDrag:true,loop:true}
                        });
                        owlproduct.trigger('refresh.owl.carousel');
                        owlproduct.trigger('to.owl.carousel', 0)
                        $('.pro-slider .owl-stage').addClass("stop")
                        setTimeout(function(){
                          $('.pro-slider .owl-stage').removeClass("stop")
                        },250)
                        // $('.pro-slider .owl-stage').css({ 'transition': '', 'transform': ''})
                        // $(".owl-controls").css("display","")
                    }
                }
        },
        allcategoryfilter: function(){ 
          var gruopname = this._slidercollection(),
              result = new Array();
               var gvalue = activecat.reverse();
               var cvalue = activecol.reverse();
             if(cvalue.length <= 0){
              var logos = this.options.logos; 
                var collection = []
                $.each(logos, function(key,logos) {
                  collection.push(logos.name)
                });
                collection.push("Other")
                cvalue = collection;
             }
              $.each(cvalue,function(ckey,cvalue){
                var collectionvalue = cvalue;
                $(".product-group-slider span").each(function(key,value){
                    var gid = $(this).attr("child-id");
                    if(_.contains(gruopname[collectionvalue], gid)){
                       var array = gruopname[collectionvalue][gid]
                         result.push(...array);
                    } 
                })
            })
            return result.length > 0 ? result : this.getallproductcollectionwise()
        },
        activeclassvalue: function(){ 
          var gruopname = this._slidercollection(),
              result = new Array();
              var gvalue = []
              var cvalue = []
              gvalue = activecat.reverse();
              cvalue = activecol;
             if(cvalue.length <= 0){
              var logos = this.options.logos; 
                var collection = []
                $.each(logos, function(key,logos) {
                  collection.push(logos.name)
                });
                collection.push("Other")
                cvalue = collection;
             }
             if(!$(".product-group-slider span").hasClass("active")){
                  gvalue = []
                  $(".product-group-slider span").each(function(){
                    var id = $(this).attr("child-id");
                     gvalue.push(id);
                  })
             }
             // console.log("c",cvalue);
            $.each(gvalue,function(key,value){
              var gid = value.replace("_"," ")   
              $.each(cvalue,function(ckey,cvalue){
                var collectionvalue = cvalue;
                    if(_.contains(gruopname[collectionvalue], gid)){
                       var array = gruopname[collectionvalue][gid]
                         result.push(...array);
                    } 
                })
            })
            return result
        },
        getslidervalueusingcollection: function(){ 
            var data = this.getRandomList(),
                gcol = activecol.reverse(),
                result = [];
            if(gcol.length > 0){
              $.each(gcol,function(key,colvalue){  
                _.filter(data, function (value) {
                    if(value['Collection'] == colvalue){
                        result.push(value)      
                    }
                });
              })
            }      
            return  result
         },
        getslidervaluewithactivecat: function(){ 
            var gruopname = this._slidercollection(),
              result = new Array();
               var final_array = [];
               var gvalue = activecat.reverse();
               var cvalue = activecol.reverse();
              $.each(cvalue,function(ckey,cvalue){
                var collectionvalue = cvalue;
                $.each(gvalue,function(key,value){
                    var gid = value.replace("_"," ")   
                    if(_.contains(gruopname[collectionvalue], gid)){
                       var array = gruopname[collectionvalue][gid]
                         result.push(...array);
                    } 
                })
            })
            return result
        },
         shortothercolllection: function(res){
          var temp = []
          $.each(res,function(key,value){
            if(value.Collection != "Other") {
              temp.push(value)
            }
          })
          $.each(res,function(key,value){
            if(value.Collection == "Other") {
              temp.push(value)
            }
          })
          return temp
        },
        getallproductcollectionwise: function(){
          var cvalue = this.getcollectionlogo();
          // console.log("cvalue",cvalue);
              cvalue.push("Other");
          var result = new Array();
          var gruopname = this._slidercollection();
          var gvalue = this._gruopnamcollection();
            $.each(cvalue,function(ckey,cvalue){
                var collectionvalue = cvalue;
                $.each(gvalue,function(key,value){
                    var gid = value.replace("_"," ")   
                    if(_.contains(gruopname[collectionvalue], gid)){
                       var array = gruopname[collectionvalue][gid]
                         result.push(...array);
                    } 
                })
            })
            return result
        },
        getCollectionlogoUrl: function(collectionname){
          var imageurl = ""
          _.filter(this.options.logos , function (value) {
                    if(value['name'] == collectionname){
                      imageurl = value['image']
                    }
            });
           return imageurl;
        },
        slidervalue: function(res){ 

          // console.log(res);

           res = res.filter((thing, index, self) =>
                  index === self.findIndex((t) => (
                    t.Style === thing.Style
                  ))
                )   
          var sliderhtml = ''
          var obj = {}
          var $widget = this
          var baseurl = this.options.baseurl;
            $.each(res, function(key,val) {

              var logoimage = $widget.getCollectionlogoUrl(val.Collection)
                var parentid = logoimage ? "<img src="+logoimage+" class="+val.Collection+">" : val.Collection+" collection",
                          ItemName = val.ShortDesc ? val.ShortDesc : '',
                          placeholder = baseurl+'pub/media/catalog/product/placeholder/default/image.jpg';    
                      var DisPercent = val.DisPercent;
                      var imagurltag= baseurl+'pub/media/Sttl_Customerorder/discount.png';
                      if(DisPercent > 0){ var dtag= "<img class='disctag' src='"+imagurltag+"'>"}else{dtag=""};                                  
                      $('.disctag').css({'width':'1px !important','height':'1px !important'});
                                                      
                sliderhtml += "<div class='item product product-item' gname="+val.GroupName+" id="+val.Style+">  <a class='product-item-link'> <span class='product-image-wrapper' style='padding-bottom: 133.94495412844%; width: auto;'> <img class='img-responsive owl-lazys' src='"+(val.U_WImage1 ? val.U_WImage1: placeholder)+"' width='218' height='292' alt="+ItemName+" /></span> </a> <div class='product details product-item-details'> <strong class='product name product-item-name'> <a class='product-item-link' title="+ItemName+" > "+ItemName+" </a> </strong> <div class='show-product-dis-box'><span>"+parentid+"</span>"+dtag+"</div><div class='show-product-dis-box-more'> <span> <lable>Style No.</lable> </span> <span>"+val.Style+"</span> </div> </div> </div>";                        
            });
            // $('img').error(function(){
            //   $(this).attr
            // })
            obj["html"] = sliderhtml
            obj["items"] = res.length
            return obj;
        },
// <---------------------------------------- AB collection slider [Done] ----------------------------------------------------------->
        
        // BS Added Script 16/10/2020 START

        /*
        * Perform Action While Switching bitween Options`
        */
        _SwitchbetweenOptions: function($this, $widget){
            if($this.find("a").attr("id") != 'option1' ){
              $("#activeoption").val($this.find("a").attr("id"));
            }    
            var current_option = $this.find("a").attr("id");
            if(current_option != "option1"){
                $(".cf.catalog-product-view").fadeOut(200);
            }else if(current_option == "option1"){
                $(".cf.catalog-product-view").fadeIn(200);
            }
        },

        /*
        **  Edit the Line item data
        *  * @returns true
        */
        _Editorderdata: function($this , $widget){
            $("#is_edit_order").attr("value","1");
            var checkinputval = $('.colorContainer').find('.checkvalue');
            $('div.renderAllHtml').attr('data-value', 'edit');
                var valIsExists = false;
                var edit_color = $this.attr('edit-color');
                var getstyle = $this.attr('edit-id');
                $( checkinputval ).each(function() {
                    if ($(this).val() != '')
                    {
                        valIsExists = true;
                    }
                });
            var prev_obj_id = $(".swatch-attribute-options.clearfix").find('.tab-pane.fade.active').attr("id");
            var is_qty_change = 0;
            if (typeof prev_obj_id !== "undefined") {
              is_qty_change = $("#qty_change_"+prev_obj_id.replace("/", "")).val();
            }
            var opt = {autoOpen: false };
            if(valIsExists && is_qty_change == 1)
            {
              $('#nextstyleserach').val(getstyle);
              $('#nextcolorserach').val(edit_color);
              $('#nextstep').val('editstyle');
              var theDialog = $("#removeUser").dialog(opt);
              theDialog.dialog("open");
              return false;
            }
            var activetab = $('#activeoption').val();
            if(activetab == 'option2' || activetab == 'option3')
            {
              $('#option1').click();
            }

            if($('#marketing_materials').is(":visible")){
              $('#browse_collection_tab').click();
            }

            $("#style").val($this.attr('edit-id'));
            $("#show_style").val($this.attr('edit-id'));
            $(".searchFromStyle").trigger("click",{is_edit_order:'true'});
            $("[option-color-code='"+edit_color+"']").click();

            $("#nav-tabContent .tab-pane").removeClass("active");
            
            $("#nav-tabContent").find("#"+edit_color+"").addClass("active");


            var style_id = $(".product_options").attr("id");

            $widget._updatepopupdata(getstyle,edit_color)

           return true;

        },

        /*
        *
        **  Update qty in Product`s size table`
        *  * @returns array|null
        */
        _updatepopupdata: function(getstyle,edit_color){
          var $widget = this;
           var style_id = $(".product_options").attr("id");

            $('#sap_ponumber_id').val('');
            $(this).val('')
            $(this).next("span").html('');

            var tmp_orderdata = _.filter(finalitems , function (item) {
              if(item.Style === getstyle && item.ColorCode === edit_color){
                return true
              }
            });

            tmp_orderdata.forEach(function(item, index){
              var inputQty = "qty["+item.Color+"]["+item.Size+"]";
              $("[name='"+inputQty+"']").val(item.QTYOrdered);
              $widget._checkvalueUpdate($("[name='"+inputQty+"']"), true);
            });
            $widget._showtotal();
        },

        _checkvalueUpdate: function(obj, update){
          var $widget = this;
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
              var backqty = parseInt(qty) - parseInt(maxQty);
              $(obj).next("span").html('Backorder '+ backqty);
            }
            else
            {
                $(obj).next("span").html('');
            }
              if(update == false)
              {
                var colorcode = $('input[name="colorcode['+selectcolor+']['+selectsize+']"').val();
                $("#qty_change_"+colorcode.replace("/", "")).val(1);
              }

              $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val(price.toFixed(2));
              $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('$'+$widget._convertcurrency(price.toFixed(2)));
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
              $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
              $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
              $(obj).next("span").html('');
            }
            $widget._showtotal();
        },

        _showtotal: function(){
          var $widget = this;
            var unittotals = $('.sizeTable').find('.unittotal');
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

        _convertcurrencytofloat: function(price){
            var money = price,
            firstsplit = money.split(","),
            lastinde = firstsplit[firstsplit.length - 1 ].split("."),
            finalmoney = '';
            for(var i = 0; i< firstsplit.length - 1; i++){
              finalmoney = finalmoney+firstsplit[i]
            }
            finalmoney = finalmoney+lastinde[0]+'.'+lastinde[1];
            // finalmoney = parseFloat(finalmoney);
            return finalmoney;
        },

        _convertqtytoint: function(qty){
            var money = qty,
            firstsplit = money.split(","),
            finalqty = '';
            for(var i = 0; i< firstsplit.length; i++){
              finalqty = finalqty+firstsplit[i]
            }
            // console.log(finalqty);
            return finalqty;
        },

        /*
        *
        **  remove the row wise data from line item`
        *  * @returns array|null
        */
        _Deleteorderdata: function($this, $widget) {
            var delete_styles = [];
            var delete_colors = [];
            var po_number = $("#po_number").val();

            var checkinputval = $('.colorContainer').find('.checkvalue');
            $('div.renderAllHtml').attr('data-value', 'edit');
            var valIsExists = false;
            var delete_color = $this.parent().attr('delete-color');
            var deletestyle = $this.parent().attr('delete-id');
            delete_styles.push(deletestyle);
            delete_colors.push(delete_color);
            var tmp_orderdata = _.filter(finalitems , function (item) {
              if(item.Style == deletestyle && item.ColorCode == delete_color){}
              else{
                return true
              }
            });

            var is_holeorderdelete = false;
            if(tmp_orderdata.length <= 0){
              var baseorder_id = $("#base64_order_id").val();
              $widget._deleteOrder($widget, baseorder_id)
              is_holeorderdelete = true;
            }

            var current_options = '';
            finalitems = tmp_orderdata;
            $widget._renderLineitembeforeAJAX($widget, current_options);

            if(!is_holeorderdelete){
              $widget._deleteRecord(delete_styles,delete_colors,po_number);
            }
           return true;
        },

        _deleteRecord: function(style,color = [],po_number, removed_markitem = false){
          // console.log(style);
          var $widget = this;
          var url = this.options.baseurl+'customerorder/customer/deletejs';
          var isorder_delete = true;
          var order_id = $("#order_id").val();
          var flatDiscount = $("#flatDiscount").val();
          var customerdata = $widget.options.customersFlatDiscount;
          var tmp_ordertotaldata = JSON.stringify(ordertotaldata);
          customerdata = JSON.stringify(customerdata);
          $.ajax({
            url: url,
            type: "POST",
            data: {
              flatDiscount:flatDiscount,
              order_id:order_id,
              po_number:po_number,
              style: style,
              color: color,
              isorder_delete:isorder_delete,
              customerdata: customerdata,
              ordersummary: tmp_ordertotaldata,
            },
            showLoader: false,
            cache: false,
            success: function(response){
              if(response.session_distroy){
                $widget._adderror(response.message);
                $('body').trigger('processStart');
                window.location.reload(true);
                return false;
              }
              if(response.errors) {
                $widget._adderror(response.message);
              }
              else{
                $("#order_id").val(response.order_id);
                if(response.errors){
                  $widget._adderror(response.message);
                  $(".delOrdLink").fadeOut(300);
                  $(".line-item").fadeOut(300);
                }
                if(!removed_markitem){
                  $widget._addsuccess(response.message)
                }
              }
              var grandtotal = $("#grandtotal").val();
              if(grandtotal == "undefined" && grandtotal == ""){
                $(".delOrdLink").hide();
              }
            }
          });
        },
        _conditioninfoDescripion:function($widget,itemcode){
            var message = '';
            var item = $widget._getMarkItemProductInfo(itemcode)
            // message += '<b>A minimum total order of '+item.minimum_order_amt+' is required.</b>';

            message += "<div class='marketing-item-notes-tooltip'> <div><span>Minimum total order of $"+ $widget._convertcurrency(parseInt(item.minimum_order_amt))+" required to purchase.</span></div>"+
                        "<div> <span>1 FREE pack with every purchase of $"+$widget._convertcurrency(parseInt(item.free_after))+"</span></div> </div>"
            return message;

        },
     
         _checkcondition:function($widget, update_slider=false, click_event = ''){
          var po_number = $("#po_number").val(),
              deletesku_check = Array();
           var count=false;
                  var slider_update = false;
                  var TotalBeforeDiscount = ordertotaldata.TotalBeforeDiscount;

                  var current_active_skus = Array(),
                      slider_item_selector = $(".mark-slider .owl-stage .owl-item").find(".product-item");

                  slider_item_selector.each(function(){
                    current_active_skus.push($(this).attr('id'))
                  });
                  var needtoupdate = Array();


                  $.each(mark_invdata, function(index,value){
                      var data = value,
                          itemcode = data.item_code,
                          minimum_price = parseInt(data.minimum_order_amt),
                          currentitem_inorder = $widget._getFinalItemsbySku(itemcode),
                          currentitem_qty = currentitem_inorder.QTYOrdered;

                      $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").val(currentitem_qty);
                      if(currentitem_qty > 0){
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").removeClass('disable');
                      }
                      var qty= $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('value');
                      if(typeof qty == 'undefined'){
                        qty = 0;
                      }
                      if(parseInt($('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").attr('max-plus')) <= qty){
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").addClass('disable').attr('disabled','disabled');
                      }else{
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").parents('.num-in').find('span.minus').removeClass('disable');
                      }  
                      if(qty < 1) {
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").addClass('disable').attr('disabled','disabled');
                      }else{
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").removeClass('disable').attr('disabled','disabled');
                      }
                      if(TotalBeforeDiscount >= minimum_price) {
                          if(parseInt($('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").attr('max-plus')) <= qty){
                            $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").addClass('disable').attr('disabled','disabled');
                          } else{
                            $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").parents('.num-in').find('span.minus').removeClass('disable');
                            $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").removeClass("disable");
                          }
                          if(qty < 1) {
                            $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").addClass('disable').attr('disabled','disabled');
                          }else{
                            $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").removeClass('disable').attr('disabled','disabled');
                          }
                          var addeditem = currentitem_inorder,
                              addeditem_qyu = addeditem.QTYOrdered;
                          
                          if(typeof addeditem_qyu == 'undefined'){
                            addeditem_qyu = 0;
                          }

                          var input = $('.mark-slider .owl-item').find("#"+itemcode+" .qty_add_num"),
                              original_price = mark_invdata_ral[index].price,
                              max_item = parseFloat(data.maximum_pre_order),
                              original_price = $widget._convertcurrencytofloat(original_price),
                              free_after = $widget._convertcurrencytofloat(data.free_after),
                              addeditem_total = free_after*max_item;

                        var new_freeafter_total = parseFloat(free_after) * 5;
                              // original_price = $('.mark-slider .owl-item').find("#"+itemcode+" .num-in input[name='mark_item_price']").attr('value'),
                        if(TotalBeforeDiscount >= parseFloat(free_after))
                        {
                          if(new_freeafter_total <= TotalBeforeDiscount){
                            mark_invdata[index].price = "0.00";
                            $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html('$0.00');
                            $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','true');
                          }else{
                            // var item_count = (parseInt(addeditem_qyu)+1) * parseFloat(free_after);
                            // if((TotalBeforeDiscount > parseFloat(free_after) && parseInt(addeditem_qyu) <= 1) && (parseFloat(free_after) * 2) > TotalBeforeDiscount ){
                            //   mark_invdata[index].price = "0.00";
                            //   var tmp_not_changed_price = mark_invdata_ral[index].price;
                            //   $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','true');
                            //   $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html('$0.00');
                            // }else{
                            //   var tmp_not_changed_price = mark_invdata_ral[index].price,
                            //       orginal_price = $widget._convertcurrency(tmp_not_changed_price);TotalBeforeDiscount
                            //   mark_invdata[index].price = tmp_not_changed_price;
                            //   $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','false');
                            //   $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html("$"+orginal_price);
                            // }


                            if(TotalBeforeDiscount > parseFloat(free_after) && parseInt(addeditem_qyu + 1) <= 1){
                              mark_invdata[index].price = "0.00";
                              var tmp_not_changed_price = mark_invdata_ral[index].price;
                              $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','true');
                              $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html('$0.00');
                            }else{
                              var tmp_not_changed_price = mark_invdata_ral[index].price,
                                  orginal_price = $widget._convertcurrency(tmp_not_changed_price);TotalBeforeDiscount
                              mark_invdata[index].price = tmp_not_changed_price;
                              $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','false');
                              $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html("$"+orginal_price);  
                            }


                          }
                        }else{
                          if(typeof currentitem_inorder.ItemCode != 'undefined' && currentitem_inorder.ItemCode == itemcode && !update_slider){
                                  if(!_.contains(needtoupdate,itemcode)){
                                    needtoupdate.push(itemcode);
                                  }
                                  var tmp_not_changed_price = mark_invdata_ral[index].price;
                                  finalitems.forEach(function(item, index){
                                    if(item.ItemCode === itemcode && item.QTYOrdered >= 1){
                                        var ordertotal = parseFloat(item.QTYOrdered) * parseFloat(tmp_not_changed_price);
                                            ordertotal = ordertotal.toFixed(2)
                                        item.TotalPrice = $widget._convertcurrency(ordertotal);
                                        item.PriceAfterDiscount = $widget._convertcurrency(ordertotal);
                                        item.PriceBeforeDiscount = $widget._convertcurrency(ordertotal);
                                        item.UnitPrice = $widget._convertcurrency(tmp_not_changed_price);
                                     }
                                  });
                                }
                          var orginal_price = $widget._convertcurrency(mark_invdata_ral[index].price);
                          mark_invdata[index].price = mark_invdata_ral[index].price;
                          $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','false');
                          $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html("$"+orginal_price);
                        }
                    }else{
                      if(!$('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").hasClass("disable")){
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .plus").addClass("disable");
                      }
                      if(!$('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").hasClass("disable")){
                        $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").addClass("disable");
                      }
                      var mark_original_price = mark_invdata_ral[index].price,
                          mark_original_price_tmp = $widget._convertcurrency(mark_original_price);
                      mark_invdata[index].price = mark_original_price;
                      $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").val(0);
                      $('.mark-slider .owl-item').find("#"+itemcode+" .description .product-item-price").html("$"+mark_original_price_tmp);
                      $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .qty_add_num").attr('is_free','false');
                      var tml_finalitems = _.filter(finalitems, function(item) {
                         return item.ItemCode !== itemcode;
                      });
                      
                      var order_item = $widget._getFinalItemsbySku(itemcode);
                      // debugger
                      if(!_.contains(deletesku_check,itemcode) && typeof order_item.ItemCode != 'undefined'){
                        deletesku_check.push(itemcode);
                      }
                      finalitems = tml_finalitems;
                    }

                    if(typeof currentitem_inorder.ItemCode != 'undefined' && currentitem_inorder.ItemCode == itemcode){
                      var available_qty = 0,
                          tmp_calculated_item_total = 0,
                          tmp_calculated_free_item = (TotalBeforeDiscount/parseFloat(free_after)),
                          tmp_calculated_free_item = parseInt(tmp_calculated_free_item);
                          // tmp_item_total = parseInt(addeditem_qyu) * parseFloat(mark_invdata_ral[index].price),
                          // tmp_calculated_item_total = tmp_item_total - (tmp_calculated_free_item * parseFloat(mark_invdata_ral[index].price)),
                          // tmp_calculated_item_total = parseFloat(tmp_calculated_item_total),
                          // tmp_calculated_item_total = tmp_calculated_item_total.toFixed(2);

                      var new_freeafter_total = parseFloat(free_after) * 5,
                          new_added_able_to_free = 0;
                          if(TotalBeforeDiscount > parseFloat(free_after)){
                            if(new_freeafter_total <= TotalBeforeDiscount){
                              new_added_able_to_free = addeditem_qyu;
                            }else{
                              if(TotalBeforeDiscount > parseFloat(free_after)){
                                new_added_able_to_free = 1;
                              }
                            }
                          }else{
                            new_added_able_to_free = 0;
                          }


                      var tmp_item_total = parseInt(addeditem_qyu) * parseFloat(mark_invdata_ral[index].price),
                          tmp_calculated_item_total = tmp_item_total - (new_added_able_to_free * parseFloat(mark_invdata_ral[index].price)),
                          tmp_calculated_item_total = parseFloat(tmp_calculated_item_total),
                          tmp_calculated_item_total = tmp_calculated_item_total.toFixed(2);






                          if(tmp_calculated_item_total <= 0){
                            tmp_calculated_item_total = 0.00;
                          }

                          if(parseFloat(currentitem_inorder.TotalPrice) != tmp_calculated_item_total){
                            if(!_.contains(needtoupdate,itemcode)){
                                needtoupdate.push(itemcode);
                              }
                            finalitems.forEach(function(item, index){
                              if(item.ItemCode === itemcode &&item.QTYOrdered >= 1){
                                  var ordertotal = tmp_calculated_item_total;
                                  item.TotalPrice = $widget._convertcurrency(ordertotal);
                                  item.PriceAfterDiscount = $widget._convertcurrency(ordertotal);
                                  item.PriceBeforeDiscount = $widget._convertcurrency(ordertotal);
                               }
                            });
                            if(currentitem_inorder.QTYOrdered > 0){
                              $('.mark-slider .owl-item').find("#"+itemcode+" .num-in .minus").removeClass("disable");
                            }

                          }
                    }
               });
                
                if(needtoupdate.length > 0 && !update_slider){
                  $widget._addMarketingItem();
                }
                if(deletesku_check.length > 0){
                  var removed_markitem = true;
                  var style_array = Array();
                  for (var i = 0; i <= deletesku_check.length -1; i++) {
                    style_array.push('');
                  }
                  $widget._deleteRecord(deletesku_check,style_array,po_number,removed_markitem);
                }

        },



        _renderLineitembeforeAJAX: function($this, _current_options, styles={}, click_event='') {
            
            var $widget = $this,
                orderdata = '',
                currency_convertedsummary = {},
                allorderitems = $widget._getOrderDataItems($widget, orderdata, _current_options, styles);
            
            $widget._getorderSummaryinfo($widget, orderdata);

            var DiscountPer = ordertotaldata.DiscountPer,
                TotalDiscountPer = ordertotaldata.TotalDiscountPer,
                tmp_FlatDiscount = parseFloat(DiscountPer) + parseFloat(TotalDiscountPer),
                tmp_DiscountAmount = ordertotaldata.DiscountAmount,
                TotalDiscountAmount = ordertotaldata.TotalDiscountAmount,
                tmp_DiscountAmount = parseFloat(tmp_DiscountAmount) + parseFloat(TotalDiscountAmount);

            currency_convertedsummary = {
              TotalBeforeDiscount: $widget._convertcurrency((ordertotaldata.TotalBeforeDiscount).toFixed(2)),
              DiscountAmount: $widget._convertcurrency(tmp_DiscountAmount.toFixed(2)),
              DiscountPer: ordertotaldata.DiscountPer,
              DocTotal: $widget._convertcurrency((ordertotaldata.DocTotal).toFixed(2)),
              TotalDiscountPer: ordertotaldata.TotalDiscountPer,
              TotalDiscountAmount: $widget._convertcurrency(ordertotaldata.TotalDiscountAmount),
              TotalQtyOrdered: ordertotaldata.TotalQtyOrdered,
              FlatDiscount: $widget._convertcurrency(tmp_FlatDiscount.toFixed(2)),
            };
            var lineitem_temp = mageTemplate(lineitemstemp, {
                finalorderrendere: allorderitems,
                ordersummaryinfo: currency_convertedsummary,

                databystylegroup : $widget._DatabyStyle(),
                currencyconvert: $.proxy(this._convertcurrency, this),
                generateDiscountTooltip: $widget._generateDiscountTooltip()
            });

            $widget._checkcondition($widget);

            if(lineitem_temp && finalitems.length > 0){
              $(".cf.line-item, .cf.delOrdLink").fadeIn(300);
            }else{
              $(".cf.line-item, .cf.delOrdLink").fadeOut(300);
            }

            $(".line-item .orderListing").html(lineitem_temp);

            // console.log(finalitems);

            var result = _.filter(finalitems , function (in_item) {
                return in_item.Type === 'gift'
            });

            if(result.length > 0){
              var marketing_lineitem = mageTemplate(marketinglineitem, {
                  marketingitems: result,
                  current_item: $.proxy(this._getMarkItemProductInfo, this)
              });
            $(".orderList.lineItemsList_mark").html(marketing_lineitem);
              $(".orderList.lineItemsList_mark").fadeIn(300);
            }else{
              $(".orderList.lineItemsList_mark").fadeOut(300);
            }

            finalitems.forEach(function(item, index){
                var total_qty_tmp = 0;
                var total_price_tmp = 0;
                var tmp_orderdata = _.filter(finalitems , function (in_item) {
                  if(in_item.Style === item.Style){
                    total_qty_tmp += parseInt(in_item.QTYOrdered);
                    total_price_tmp += parseFloat(in_item.TotalPrice);
                  }
                });
                config_total_data[item.Style] = {
                  total_qty: total_qty_tmp,
                  total_price: total_price_tmp
                };
            });

        },

        _renderLineitemAfterAJAX: function($widget,response, current_options){

            var allorderitems = $widget._getOrderDataItems($widget,response, current_options);
            $widget._getorderSummaryinfo($widget,response);

            var DiscountPer = ordertotaldata.DiscountPer,
                TotalDiscountPer = ordertotaldata.TotalDiscountPer,
                tmp_FlatDiscount = parseFloat(DiscountPer) + parseFloat(TotalDiscountPer);

            var tmp_DiscountAmount = ordertotaldata.DiscountAmount,
                TotalDiscountAmount = ordertotaldata.TotalDiscountAmount,
                tmp_DiscountAmount = parseFloat(tmp_DiscountAmount) + parseFloat(TotalDiscountAmount);

            var currency_convertedsummary = {
              TotalBeforeDiscount: $widget._convertcurrency(parseFloat(ordertotaldata.TotalBeforeDiscount).toFixed(2)),
              DiscountAmount: $widget._convertcurrency(tmp_DiscountAmount.toFixed(2)),
              DiscountPer: ordertotaldata.DiscountPer,
              DocTotal: $widget._convertcurrency(parseFloat(ordertotaldata.DocTotal).toFixed(2)),
              TotalDiscountPer: ordertotaldata.TotalDiscountPer,
              TotalDiscountAmount: $widget._convertcurrency(ordertotaldata.TotalDiscountAmount),
              TotalQtyOrdered: ordertotaldata.TotalQtyOrdered,
              FlatDiscount: $widget._convertcurrency(tmp_FlatDiscount.toFixed(2)),
            };
            // $widget._conditiocheck($widget);


            // console.log(currency_convertedsummary);

          var lineitem_temp = mageTemplate(lineitemstemp, {
              finalorderrendere: allorderitems,
              ordersummaryinfo: currency_convertedsummary,
              databystylegroup : $widget._DatabyStyle(),
              currencyconvert: $.proxy(this._convertcurrency, this),
              generateDiscountTooltip: $widget._generateDiscountTooltip($widget)
          });

          return lineitem_temp;
        },

        _generateDiscountTooltip: function($widget){
          var message = '',
              customrsbulkdiscount = this.options.customersBulcDiscount,
              DocTotalQty = ordertotaldata.TotalQtyOrdered,
              DocTotal = ordertotaldata.DocTotal,
              tmp_customerdata = this.options.customersFlatDiscount,
              customerflatedis = tmp_customerdata[0].FlatDiscount,
              message_added = false;
            if(customrsbulkdiscount.length > 0){
              var array_length = customrsbulkdiscount.length,
                  last_item = customrsbulkdiscount[array_length - 1],
                  next_discount = parseFloat(customerflatedis)+parseFloat(last_item.Discount);
              if(DocTotalQty < last_item.QtyFrom){
                var more_item = parseInt(last_item.QtyFrom) - DocTotalQty;
                if(more_item > 0 ){
                  message = "DISCOUNT: Add "+more_item+" more items to qualify for "+next_discount+"% off your order";
                }
              }
            }
          return message;
        },

        /*
        **  Set Regualar, Petite or Tail buttons in Searched Style
        *  * @returns array|null
        */
        _getRPTswitcherButtons: function(_magentopro, _sku, $widget){

            var petiteSku = '';
            var tailSku = '';
            var regularSku = '';
            var currentsku = _sku;
            var check = currentsku.substr((currentsku.length)-1, 1);

            var sapconfigdata = this.options.ConfigStyle;
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

        _getItemfromOrderList: function(sku){
          var tml_finalitems = _.filter(finalitems, function(item) {
               return item.ItemCode == sku;
          });
          return tml_finalitems;
        },

        /**
         * Prepared Added Qty to PO Info
         *
         * @returns []
         * @private
         */


        _generateqtyarray: function(_current_options, $widget, styles={}) {
          removedskus = [];
          var data_selector = $('.colorContainer').find('.checkvalue');
          var current_options = _current_options;
          item_edited = false;
          if(current_options == 1 && current_options != 0 && current_options != ''){
            $(data_selector).each(function(){
              var count = 0;
              if ($(this).val() != '')
              {
                  var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                  var selectsize = $(this).closest('td').find('input[name=selectsize]').val();

                  var base_price = $('input[name="mainprice['+selectcolor+']['+selectsize+']"').val();
                  var disprice = $('input[name="DiscountPrice['+selectcolor+']['+selectsize+']"').val();
                  var added_qty = $(this).val();
                  

                  var itemcode = $('input[name="itemscode['+selectcolor+']['+selectsize+']"').val(),
                      order_item = $widget._getItemfromOrderList(itemcode);

                  if(order_item.length > 0){
                    if(order_item[0].QTYOrdered !== added_qty){
                      item_edited = true;
                    }
                  }else{
                    item_edited = true;
                  }


                  var pafterdiscount = parseFloat();
                  var pbeforediscount = added_qty * base_price;
                  pbeforediscount = parseFloat(pbeforediscount);

                  if(disprice < base_price){
                    pafterdiscount = added_qty * disprice;
                  }else{
                    pafterdiscount = added_qty * base_price;
                  }

                  var tmpitem = {};

                  var current_itemcode = $('input[name="itemscode['+selectcolor+']['+selectsize+']"').val();

                  tmpitem = {
                    ColorCode: $('input[name="colorcode['+selectcolor+']['+selectsize+']"').val(),
                    ItemCode: $('input[name="itemscode['+selectcolor+']['+selectsize+']"').val(),
                    ColorStatus: $('input[name="ColorStatus['+selectcolor+']['+selectsize+']"').val(),
                    DiscountPer: $('input[name="DiscountPer['+selectcolor+']['+selectsize+']"').val(),
                    DiscountPrice: $('input[name="DiscountPrice['+selectcolor+']['+selectsize+']"').val(),
                    OrderOption: "1",
                    PriceAfterDiscount: ''+pafterdiscount+'',
                    QTYOrdered: added_qty,
                    Size: selectsize,
                    Style: $(".product_options.createOrder").attr("id"),
                    StyleStatus: $('input[name="StyleStatus['+selectcolor+']['+selectsize+']"').val(),
                    TotalPrice: ''+pafterdiscount+'',
                    UnitPrice: base_price,
                    PriceBeforeDiscount: ''+pbeforediscount+'',
                    Color: selectcolor,
                    Type:"normal"

                  };

                  var itemcodeexistinarray = false;
                  var array_index = -1;

                  finalitems.forEach(function(item, index){
                    if(item.ItemCode == current_itemcode && itemcodeexistinarray == false){
                      itemcodeexistinarray = true;
                      array_index = index;
                    }
                  });

                  if(itemcodeexistinarray){
                    if(added_qty > 0){
                      finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                      finalitems[array_index].QTYOrdered = added_qty;
                      finalitems[array_index].TotalPrice = pafterdiscount;
                    }else{
                      var tml_finalitems = _.filter(finalitems, function(item) {
                          if(item.ItemCode == current_itemcode){
                            removedskus.push(current_itemcode);
                          }
                           return item.ItemCode !== current_itemcode
                      });
                      finalitems = tml_finalitems;
                    }
                  }else{
                    if(added_qty > 0){
                      finalitems.push(tmpitem);
                    } 
                  }
                  $widget._checkvalueUpdate($(this),true);

              }
            });
          }

          if(current_options == 2 && current_options != 0 && current_options != ''){
              var opt_two_sku = $('#opt_two_sku').val();
              var opt_two_qty = $('#opt_two_qty').val();
              var current_pro = $widget._getSimpleProductInfo(opt_two_sku);


              var base_price = current_pro.UnitPrice;
              var disprice = current_pro.DisPrice;
              var added_qty = opt_two_qty;

              var pbeforediscount = added_qty * base_price;
              pbeforediscount = parseFloat(pbeforediscount);

              var pafterdiscount = parseFloat();
              if(disprice < base_price){
                pafterdiscount = added_qty * disprice;
              }else{
                pafterdiscount = added_qty * base_price;
              }

              var tmpitem = {};

              var current_itemcode = current_pro.ItemCode;


              tmpitem = {
                ColorCode: current_pro.ColorCode,
                ItemCode: current_pro.ItemCode,
                ColorStatus: current_pro.ColorStatus,
                DiscountPer: current_pro.DisPercent,
                DiscountPrice: current_pro.DisPrice,
                OrderOption: "2",
                PriceAfterDiscount: ''+pafterdiscount+'',
                QTYOrdered: added_qty,
                Size: current_pro.Size,
                Style: current_pro.Style,
                StyleStatus: current_pro.StyleStatus,
                TotalPrice: ''+pafterdiscount+'',
                UnitPrice: base_price,
                PriceBeforeDiscount: ''+pbeforediscount+'',
                Color: current_pro.Color,
                Type:"normal"
              };

              var itemcodeexistinarray = false;
              var array_index = -1;

              finalitems.forEach(function(item, index){
                if(item.ItemCode == current_itemcode && itemcodeexistinarray == false){
                  itemcodeexistinarray = true;
                  array_index = index;
                }
              });

              if(itemcodeexistinarray){
                if(added_qty > 0){
                  finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                  finalitems[array_index].QTYOrdered = added_qty;
                  finalitems[array_index].TotalPrice = pafterdiscount;
                }else{
                  var tml_finalitems = _.filter(finalitems, function(item) {
                     return item.ItemCode !== current_itemcode
                  });
                  finalitems = tml_finalitems;
                }
              }else{
                if(added_qty > 0){
                  finalitems.push(tmpitem);
                }
              }

          }


          if(current_options == 5 && current_options != ''){
              var itemvalue = styles,
                  ordertotal = added_qty * base_price,
                  tmpitem = {},
                  current_itemcode = itemvalue.item_sku,
                  qyu_ord = itemvalue.qty_ordered,
                  item_unitprice = itemvalue.unitprice,
                  tmp_item_unitprice = $widget._convertcurrencytofloat(item_unitprice),
                  current_item = $widget._getMarkItemProductInfo(current_itemcode),
                  past_added_item = $widget._getFinalItemsbySku(current_itemcode),
                  is_item_free = $('.mark-slider .owl-item').find("#"+current_itemcode+" .num-in .qty_add_num").attr('is_free'),
                  is_item_free = (is_item_free === 'true') ? true : false;


              var product_existin_po = false;
              if(typeof past_added_item.ItemCode != 'undefined'){
                product_existin_po = true
              }

              if(!is_item_free){
                  if(product_existin_po){
                    if(parseInt(qyu_ord) > parseInt(past_added_item.QTYOrdered)){
                      ordertotal = parseFloat(past_added_item.TotalPrice) + parseFloat(current_item.price);
                    }else{
                      var free_after = $widget._convertcurrencytofloat(current_item.free_after),
                          item_count = (parseInt(qyu_ord)+1) * parseFloat(free_after),
                          TotalBeforeDiscount = ordertotaldata.TotalBeforeDiscount;
                      if(item_count <= TotalBeforeDiscount && parseInt(qyu_ord) >= 0){
                        // console.log("True");
                        ordertotal = 0;
                      }else{
                        ordertotal = parseFloat(past_added_item.TotalPrice) - parseFloat(current_item.price);
                      }
                    }
                  }else{
                    ordertotal = parseFloat(qyu_ord) * parseFloat(current_item.price);
                  }
              }else{
                  ordertotal = parseFloat(qyu_ord) * parseFloat('0.00');
              }

              ordertotal = ordertotal.toFixed(2);


              tmpitem = {
                ColorCode: "",
                ItemCode: current_itemcode,
                ItemName: current_item.item_name,
                ColorStatus: "",
                DiscountPer: "0.00",
                DiscountPrice: $widget._convertcurrencytofloat(item_unitprice),
                OrderOption: "5",
                PriceAfterDiscount: ordertotal.toString(),
                QTYOrdered: qyu_ord,
                Size: "",
                Style: current_itemcode,
                StyleStatus: "",
                TotalPrice: ordertotal.toString(),
                UnitPrice: $widget._convertcurrencytofloat(item_unitprice),
                PriceBeforeDiscount: ordertotal.toString(),
                Color: "",
                Type:"gift"
              };
            

              var itemcodeexistinarray = false;
              var array_index = -1;

              finalitems.forEach(function(item, index){
                if(item.ItemCode == current_itemcode && itemcodeexistinarray == false){
                  itemcodeexistinarray = true;
                  array_index = index;
                }
              });

              if(itemcodeexistinarray){
                if(qyu_ord > 0){
                  if(!is_item_free){
                    finalitems[array_index].PriceAfterDiscount = ordertotal.toString();
                    finalitems[array_index].PriceBeforeDiscount = ordertotal.toString();
                    finalitems[array_index].QTYOrdered = qyu_ord;
                    finalitems[array_index].TotalPrice = ordertotal.toString();
                  }else{
                    finalitems[array_index].QTYOrdered = qyu_ord;
                  }
                }else{
                  var tml_finalitems = _.filter(finalitems, function(item) {
                     return item.ItemCode !== current_itemcode
                  });
                  finalitems = tml_finalitems;
                }
              }else{
                if(qyu_ord > 0){
                  finalitems.push(tmpitem);
                }
              }

          }

          // console.log(finalitems);

          return finalitems;

        },


        _generateordertotalarray: function($widget) {
            var data_selector = $('.colorContainer').find('.checkvalue');


            var customersFlatDiscount = $widget.options.customersFlatDiscount;
            customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;
            var beforebulkdiscount = customersFlatDiscount;
            var sellingprice = 0;
            var discountAmount = 0; 

            var customrsbulkdiscount = $widget.options.customersBulcDiscount;

            var DocTotalQty = 0;

            finalitems.forEach(function(item, index){
              sellingprice = parseFloat(sellingprice) + parseFloat(item.TotalPrice);
              DocTotalQty = parseInt(DocTotalQty) + parseInt(item.QTYOrdered);
            });

            // console.log(DocTotalQty);
            // console.log(sellingprice);

            DocTotalQty = parseFloat(DocTotalQty);


            var bulkdiscount = 0;

            if(customrsbulkdiscount.length > 0){
              customrsbulkdiscount.forEach(function(item, index){
                var bulkqtyfrom = item.QtyFrom;
                var bulkqtyto = item.QtyTo;
                if(DocTotalQty >= bulkqtyfrom && DocTotalQty <= bulkqtyto){
                  bulkdiscount = item.Discount;
                }
              });
            }

            var totalpriceordered = sellingprice;
            customersFlatDiscount = parseFloat(customersFlatDiscount) + parseFloat(bulkdiscount);
            if(customersFlatDiscount > 0){
              sellingprice = sellingprice - (sellingprice * (customersFlatDiscount/100));
              discountAmount = totalpriceordered * (customersFlatDiscount/100);
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

            // console.log(ordertotaldata);

            return ordertotaldata;
        },

        optionTwoSuccess: function (message)
        {
            var data = $("."+$('#newOrderTab>div.tab-pane.fade.active.show').attr('id'));
            data.removeClass("error").addClass("success").html(message);
            $("#opt_two_message").show().focus();
            setTimeout(function () {
              $("#opt_two_message").fadeOut(1000);
            }, 5000);
            return true;
        },

        _updatetmpOrderData: function($widget ,_response){
          var allorderdata = _response;
          finalitems = [];

          if(allorderdata.line_item_render.allorderdata.length <= 0){
            $widget._changeemptyurl();
            window.location.reload(true);
            return false;
          }

          var orderitemsdata = allorderdata.line_item_render.allorderdata;
          var ordersummarydata = allorderdata.line_item_render.ordersummary;

          orderitemsdata.forEach(function(item, index){
            var tmpitem = {},
                pbeforediscount = item.QTYOrdered * item.UnitPrice,
                pbeforediscount = parseFloat(pbeforediscount),
                product_type = 'normal';

            if(item.ColorCode == ''){
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
                PriceBeforeDiscount: ''+pbeforediscount+'',
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

        _adderror: function(message){
          
          $("#msg_text").removeClass("success").addClass("error").html(message);
          $("#message").show().focus();
          setTimeout(function(){$("#message").fadeOut(1000); },2000); 
          return true;
        },

        _addsuccess: function(message){
            $("#msg_text").removeClass("error").addClass("success").html(message);
            $("#message").show().focus();
            setTimeout(function(){$("#message").fadeOut(1000); },2000); 
            return true;
        },

        _addOpt1error: function(message){
          $("#option1_msg_text").removeClass("success").addClass("error").html(message);
          $("#option1_message").show().focus();
          setTimeout(function(){$("#option1_message").fadeOut(1000); },2000); 
          return true;
        },

        _addOpt1success: function(message){
            $("#option1_msg_text").removeClass("error").addClass("success").html(message);
            $("#option1_message").show().focus();
            setTimeout(function(){$("#option1_message").fadeOut(1000); },2000); 
            return true;
        },

        _changeurl: function(base64_order_id,base64_ncp_id){
            var order_id = $('#order_id').val();
            if(order_id != '')
            {
              if (typeof (history.pushState) != "undefined") {
                  var nexturl = this.options.baseurl+'customerorder/customer/neworder/id/'+base64_order_id+'/ncp/'+base64_ncp_id,
                      obj = { Title: 'orderpage', Url: nexturl };
                    history.pushState(obj, obj.Title, obj.Url);
                } else 
                    alert("Browser does not support HTML5.");              
            }
        },

        _optionthreeupload: function($this, $widget){
          var $widget = this,
              filevalus = [],
              fileUpload = $("#files_upload"),
              files = fileUpload.val(),
              items = fileUpload[0].files;
          // $widget._checkfileupload($this, $widget);
          if(files == ''){
            $widget.optionThreeError("Please upload file.")
            fileUpload.focus();
            return false;
          }

          if (files.toLowerCase().lastIndexOf(".csv") !== -1) {
              if (typeof (FileReader) != "undefined") {
                  var reader = new FileReader();
                  reader.onload = function (e) {
                    var filevalus = [];
                      var rows = e.target.result.split("\n");
                      for (var i = 1; i < rows.length - 1; i++) {
                          var cells =   rows[i].split(","),
                              obj = {}
                          obj["sku"] = cells[0];
                          var tmp_qty = "",
                              is_valid = true;
                          for (var m = 1; m < cells.length; m++) {
                            if(cells[m] == ''){
                                tmp_qty += cells[m];
                            }else{
                              if($widget._isNormalInteger(cells[m]) && is_valid){
                                if(/\s/.test(cells[m])){
                                  is_valid = false;
                                }else{
                                  tmp_qty += cells[m];
                                }
                              }else{
                                is_valid = false;
                                tmp_qty = '';
                              }
                            }
                          }
                          obj["qty"] = tmp_qty;
                          filevalus.push(obj);
                      }
                      $widget._readCSV(filevalus, $widget);
                  }
                  reader.readAsText(fileUpload[0].files[0]);
              } else 
                  alert("This browser does not support HTML5.");
              
          } else {
              $widget.optionThreeError("Please upload valid CSV file.")
              fileUpload.focus();
              return false;
          }
            
            
        },
        dublicateskuqty: function(filevalus, $widget){
          var result = []
          var sku = []
          var flag = []
          // console.log(filevalus);
          $.each(filevalus,function(key,value){
            var opt_two_sku = value.sku,
                opt_two_qty = value.qty;
            if(sku.indexOf(value.sku) > -1 && opt_two_sku != '' && opt_two_qty != ''){
                  var qty = value.qty;
                  if(qty == '' && !$widget._isNormalInteger(qty)){
                    qty = 0;
                  }
                  flag = _.filter(result , function (kvalue) {
                    if(kvalue["sku"] == value.sku){
                      kvalue["qty"] = (parseInt(kvalue["qty"]) + parseInt(qty)).toString();
                    }
                    return kvalue
                  });
            }else{
              sku.push(value.sku)
              flag.push(value);
            }
          })
          return flag;
        },
        _readCSV: function(filedata, $widget){
            var final_filedata = $widget.dublicateskuqty(filedata, $widget);
            // var csvdata = filedata;
            var itemnotavailablearray = [],
                backorderarray = [],
                qtynotvalid = [],
                linenumber = 2,
                readytoadd = false;
            final_filedata.forEach(function(item, index){
              
              var opt_two_sku = item.sku,
                  opt_two_qty = item.qty,
                  regexAll = /^([a-zA-Z0-9\!\@\#\&\*\(\)\-\_\+\/\\\:\;\.\,\>\<\=\|\}\{\]\[ ]+)$/;

                opt_two_sku = opt_two_sku.replace(/\s+/g,'');

                if(opt_two_sku != '' && regexAll.test(opt_two_sku)){
                  if($widget._isNormalInteger(opt_two_qty) && opt_two_qty!= ''){
                    if($widget._checkSkuAvailable(opt_two_sku)){
                      var current_pro = $widget._getSimpleProductInfo(opt_two_sku),
                         base_price = current_pro.UnitPrice,
                         disprice = current_pro.DisPrice,
                         added_qty = opt_two_qty,
                         pbeforediscount = added_qty * base_price,
                        pbeforediscount = parseFloat(pbeforediscount),
                         pafterdiscount = parseFloat();
                      
                      if(disprice < base_price)
                        pafterdiscount = added_qty * disprice;
                      else
                        pafterdiscount = added_qty * base_price;                    

                      var tmpitem = {},
                          current_itemcode = current_pro.ItemCode,
                          backorder_qty = current_pro.ActualQty - opt_two_qty;

                      if(backorder_qty < 0){
                        backorder_qty = Math.abs(backorder_qty);

                        var currentdate = new Date(),
                            backorder_date = '',
                            eta_date = new Date(current_pro.ETA);
                        
                        if(eta_date > currentdate){
                          backorder_date = (eta_date.getMonth()+1)+'/'+(eta_date.getDate())+'/'+(eta_date.getFullYear().toString().substr(-2));
                          var tmp_arra = {
                            sku: opt_two_sku,
                            linenumber: linenumber,
                            backorderqty: backorder_qty,
                            backordereta: backorder_date,
                          }
                          backorderarray.push(tmp_arra);
                        }
                      }

                      tmpitem = {
                        ColorCode: current_pro.ColorCode,
                        ItemCode: current_pro.ItemCode,
                        ColorStatus: current_pro.ColorStatus,
                        DiscountPer: current_pro.DisPercent,
                        DiscountPrice: current_pro.DisPrice,
                        OrderOption: "3",
                        PriceAfterDiscount: ''+pafterdiscount+'',
                        QTYOrdered: added_qty,
                        Size: current_pro.Size,
                        Style: current_pro.Style,
                        StyleStatus: current_pro.StyleStatus,
                        TotalPrice: ''+pafterdiscount+'',
                        UnitPrice: base_price,
                        PriceBeforeDiscount: ''+pbeforediscount+'',
                        Color: current_pro.Color
                      };

                      var itemcodeexistinarray = false, array_index = -1;

                      finalitems.forEach(function(item, index){
                        if(item.ItemCode == current_itemcode && itemcodeexistinarray == false){
                          itemcodeexistinarray = true;
                          array_index = index;
                        }
                      });

                      if(itemcodeexistinarray){
                        if(added_qty > 0){
                          finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                          finalitems[array_index].QTYOrdered = added_qty;
                          finalitems[array_index].TotalPrice = pafterdiscount;
                          readytoadd = true;
                        }else{
                          var tml_finalitems = _.filter(finalitems, function(item) {
                             return item.ItemCode !== current_itemcode
                          });
                          finalitems = tml_finalitems;
                        }
                      }else{
                        if(added_qty > 0){
                          readytoadd = true;
                          finalitems.push(tmpitem);
                        }
                      }
                    }else{
                      var tmp_arra = {
                        sku: opt_two_sku,
                        linenumber: linenumber,
                      }
                      itemnotavailablearray.push(tmp_arra);
                    }
                    linenumber++;
                  }else{
                    if(opt_two_qty === '' || regexAll.test(opt_two_qty)){
                      var tmp_arra = {
                        sku: opt_two_sku,
                        linenumber: linenumber,
                        message: "Qty is not valid"
                      }
                      qtynotvalid.push(tmp_arra);
                    }
                    linenumber++;
                  }
                }else{
                   if((opt_two_sku === '' && opt_two_qty != '') || regexAll.test(opt_two_sku)){
                      var tmp_arra = {
                          sku: opt_two_sku,
                          linenumber: linenumber,
                          message: "SKU is not valid"
                        }
                        qtynotvalid.push(tmp_arra);
                    }
                    linenumber++;
                }

            });

            var errorhtml = '';
            if(itemnotavailablearray.length > 0){
              errorhtml += '<li>The following lines have errors </li>';
              itemnotavailablearray.forEach(function(item, index){
                errorhtml += '<li>line '+item.linenumber+' SKU: '+item.sku+', Item not found</li>';
              });
            }

            if(backorderarray.length > 0){
              backorderarray.forEach(function(item, index){
                errorhtml += '<li>line '+item.linenumber+' Item added successfully. SKU: '+item.sku+', On backorder:'+item.backorderqty+' for '+item.backordereta+'</li>';
              });
            }

            if(qtynotvalid.length > 0){
              qtynotvalid.forEach(function(item, index){
                errorhtml += '<li>line '+item.linenumber+' SKU: '+item.sku+', '+item.message+'</li>';
              });
            }

            if(itemnotavailablearray.length > 0 || backorderarray.length > 0 || qtynotvalid.length > 0){
              var tmp_errorhtml = "<ul>"+errorhtml+"</ul>";
              $widget.optionThreeError(tmp_errorhtml);
            }

            var current_options = '';
            $widget._renderLineitembeforeAJAX($widget, current_options);
            
            if(readytoadd){
              $widget._saveCSVdata();
            }


            $("#file_show_name").fadeOut(300);
            $("#file_name").fadeOut(300);
            $("#file_addorder").fadeOut(100);
            $("#file_close").fadeOut(300);

            setTimeout(function(){$('#files_upload').val(''); $("#file_name").html(''); $("#file_size").html(''); },400);
        },

        _isNormalInteger: function(str){
          try {
              str = str.replace(/\s+/g,'');
              if(str != ''){
                str = str.toString();
                str = str.trim();
                if (!str) {
                    return false;
                }
                str = str.replace(/^0+/, "") || "0";
                var n = Math.floor(Number(str));
                return String(n) === str && n >= 0;
              }else{
                return false;
              }
          }catch(err) {
            return false
          }
        },

        _adderroropttwo: function(message){
          var data = $("."+$('#newOrderTab .fade.active.show').attr('id'));
          data.removeClass("success");
          data.addClass("error");
          data.html(message);

          if($("."+$('#newOrderTab .fade.active.show').attr('id')).find('b').length < 1){ data.prepend('<b  title="Copied!"></b> <span></span'); }
          $("#opt_two_message").show();
          $("#opt_two_message").focus();

          return true;
        },

        _saveCSVdata: function(){
            
            var $widget = this,
                is_savedata = 'true',
                req_url = this.options.baseurl+'customerorder/customer/optiontwojs',
                customerdata = $widget.options.customersFlatDiscount,
                current_options = '';

                customerdata = JSON.stringify(customerdata);
              var tmp_ordertotaldata = JSON.stringify(ordertotaldata),
                  tmp_finalitems = JSON.stringify(finalitems),
                  po_number = $("#po_number").val(),
                  order_id = $("#order_id").val(),
                  base64_order_id = $("#base64_order_id").val(),
                  base64_ncp_id = $("#base64_ncp_id").val();

                $.ajax({
                    url:req_url,
                    type: "POST",
                    data: {
                        po_number: po_number,
                        is_savedata: is_savedata,
                        order_id: order_id,
                        base64_order_id: base64_order_id,
                        base64_ncp_id: base64_ncp_id,
                        customerdata: customerdata,
                        allorderitemdata: tmp_finalitems,
                        ordersummary: tmp_ordertotaldata,
                    },
                    showLoader: true,
                    cache: false,
                    success: function(response){
                      if(response.session_distroy){
                          $widget._adderror(response.message);
                          $('body').trigger('processStart');
                          window.location.reload(true);
                          return false;
                        }
                      var fp = $("#files_upload"),
                          items = fp[0].files,
                          lg = fp[0].files.length;

                      $("#file_show_name").fadeOut(300);
                      $("#file_name").fadeOut(300);
                      $("#file_addorder").fadeOut(100);
                      $("#file_close").fadeOut(300);

                      setTimeout(function(){$('#files_upload').val(''); $("#file_name").html(''); $("#file_size").html(''); },400);

                        if(response.errors){
                          
                            $widget._updatetmpOrderData($widget,response);

                            var lineitem_temp = $widget._renderLineitemAfterAJAX($widget,response, current_options);


                            $(".cf.line-item").fadeIn(300);
                            $(".line-item .orderListing").html(lineitem_temp);

                            if(response.order_id)
                              $("#order_id").val(response.order_id);
                            
                            if(response.base64_order_id)                            
                              $("#base64_order_id").val(response.base64_order_id);
                            
                            if(response.base64_ncp_id)
                              $("#base64_ncp_id").val(response.base64_ncp_id);
                            
                            if(response.message)
                              $widget._adderror(response.message);
                            
                            $widget._changeurl(response.base64_order_id, response.base64_ncp_id);

                            if(lineitem_temp)
                              $(".cf.delOrdLink").fadeIn(300);
                            
                        }

                        if (response.success) {
                          
                            if(response.order_id)
                              $("#order_id").val(response.order_id);
                            
                            if(response.base64_order_id)
                              $("#base64_order_id").val(response.base64_order_id);
                            
                            if(response.base64_ncp_id)
                              $("#base64_ncp_id").val(response.base64_ncp_id);

                            if(response.message)
                              $widget._addsuccess(response.message);
                            
                            $widget._changeurl(response.base64_order_id, response.base64_ncp_id);

                            $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
                            $(".colorContainer .showprice, .maxqtyvaldi").html('');
                        }
                    }
                });
                return true;
        },

// <-------------------------------------------------------- Marketing Material START BS ------------------------------------------------->

        mark_materialslider: function(){
          $('.material-slider').css('width',jQuery('#user-gird-conatiner').width());
          owlmaterial = $('.material-slider').owlCarousel({
                     loop:true
                    ,autoplay:false
                    ,autoplayTimeout:3000
                    ,margin:0
                    ,rewind: true
                    ,nav:true
                    ,dots:false
                    ,autoHeight: false
                    ,navText:['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>'
                    ,'<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>'],
                    responsive:{
                        0:{
                            items:1
                        },
                        600:{
                            items:2
                        },
                        800:{
                            items:4
                        },
                        1000:{
                            items:6
                        },
                        1200:{
                            items:7
                        }
                    },    
                    onInitialized: fixOwl,
                    onRefreshed: fixOwl,
                    onChanged: fixOwl
            });      
            var fixOwl = function(){
             
                var $stage = $('.material-slider'),
                    stageW = $stage.width(),
                    $el = $('.material-slider .owl-item'),
                    elW = 0;
                $el.each(function() {
                    elW += $(this).width()+ +($(this).css("margin-right").slice(0, -2))
                });
                // if ( elW > stageW ) {
                    $stage.width( elW );
                // };
            }
        },

        _mark_materialcategory: function(){
          var $widget = this;
            $widget.addmaterialcategory($widget.market_gruopnamcollection());
            setTimeout(function(){
              $(".material-group-slider").find('.group-col').first().trigger("click");
            },160)
        },

        addmaterialcategory: function(unique){
          var html = "<span class='group-col' child-id='All'><h1>All</h1></span>"
          for (var i = 0; i < unique.length; i++) {
            html += "<span class='group-col' child-id="+unique[i].replace(" ","_")+"><h1>"+unique[i].replace("_"," ")+"</h1></span>" 
          }
          $(".material-group-slider").html(html);
        }, 

        mark_getRandomList: function(inv_data={}){ 
            var $widget = this,
                data1 = new Array(),     
                data = $widget.getConfigurableProduct(inv_data,'id');  
           var marketData = this.options.marketData;   

            marketData.forEach(function(item, index){
              if(item.category !== ''){
                  data1.push(item.category);              
              }
            });      
            return data1;
        },

        _mark_slidercollection: function(inv_data={}){
          var data = inv_data,
                collection = [],
                collection2 = []
            var $widget = this
            var marketData = $widget.options.marketData;

             $.each(marketData,function(key,val){
                collection2.push(val.category);
            })
            var uniquecollection = $widget.uniquevalue(collection2)
            
                for (var i = 0; i < uniquecollection.length; i++) { 
                var proids =  [];
                    $.each(marketData,function(prokey,proval){
                            var obj = {};
                             obj["item_name"] = proval.item_name;
                             obj["item_url"] = proval.item_url;
                             obj["file_url"] = proval.file_url;
                             obj["category"] = proval.category;
                             obj["price"] = proval.price;
                             obj["item_code"] = proval.item_code;
                            proids.push(obj)
                    })
                }
           return proids;
        },

        _mark_allcategoryfilter:function(data={}){
          var gruopname = this._mark_slidercollection(data),
              result = new Array();
               var gvalue = activecat_material.reverse();
               var cvalue = activecol.reverse();
               // console.log("gp",gruopname)
             if(cvalue.length <= 0){
              var logos = this.options.logos; 
                var collection = []
                $.each(logos, function(key,logos) {
                  collection.push(logos.name)
                });
                collection.push("Other")
                cvalue = collection;
             }
              $.each(cvalue,function(ckey,cvalue){
                var collectionvalue = cvalue;
                $(".product-group-slider span").each(function(key,value){
                    var gid = $(this).attr("child-id");
                    if(_.contains(gruopname[collectionvalue], gid)){
                       var array = gruopname[collectionvalue][gid]
                         result.push(...array);
                    } 
                })
            })
            return result.length > 0 ? result : this.getallproductcollectionwise()
        },

        _mark_activeclassvalue:function(data={}){
           var gruopname = this._mark_slidercollection(data),
              result = new Array();
              var array = activecat_material.map(function (el) {
                  return el.replace("_"," ");
                });
             activecat_material = this.uniquevalue(array);
              // console.log("A",activecat)
             var dd = gruopname.filter(item => activecat_material.includes(item.category));
             // console.log("DD",dd)
             return dd;
        },

        mark_material_categoryclick: function($this){
          var $widget = this
            var vid = $this.attr("child-id");
            if(vid == "All" && !$(".material-group-slider span.group-col[child-id = 'All']").hasClass("active")){
              activecat_material = []
              $(".material-group-slider span").removeClass("active");
            }
            $this.toggleClass("active");
            if(vid != "All" && $this.hasClass("active")){
              // $(".material-group-slider span.group-col[child-id = 'All']").removeClass("active")
              $(".material-group-slider span.group-col").removeClass("active")
                $this.addClass("active")
                activecat_material = [];
                activecat_material.push(vid)
                activecat_material = $widget.uniquevalue(activecat_material);
            }else{
                activecat_material = jQuery.grep($widget.uniquevalue(activecat_material), function(value) {
                  
                  return value != vid.replace("_"," ");
                });
            }
            
            if(vid == "All" || activecat_material == ''){
                // var marketData = this.options.marketData;
                var value = $widget._mark_allcategoryfilter(mark_invdata);
                var datavalue = $widget.mark_material_slidervalue(mark_invdata);
                $widget.mark_material_resetslidervalue(datavalue["html"],datavalue["items"])
            }else{
                // var marketData = this.options.marketData; 
                var value = $widget._mark_activeclassvalue(mark_invdata);
                var datavalue = $widget.mark_material_slidervalue(value);
                $widget.mark_material_resetslidervalue(datavalue["html"],datavalue["items"])
            }
            $widget._checkcondition($widget);
        },

        mark_material_slidervalue: function(res){
         var sliderhtml = ''
          var obj = {}
          var $widget = this;
          var baseurl = this.options.baseurl;
          // description = this._conditioninfoDescripion();
          // console.log(description);
            $.each(res, function(key,val) {
                  var current_item = val.item_code;
                  var mark_item = mageTemplate(marketingitem, {
                      data: val,
                      baseurl: baseurl,  
                      description:$widget._conditioninfoDescripion($widget,current_item),
                      currencyconvert: $.proxy($widget._convertcurrency, this)
                  });
                  sliderhtml += mark_item;
                // sliderhtml += "<div class='item product product-item' gname="+val.GroupName+" id="+val.Style+">  <a class='product-item-link'> <span class='product-image-wrapper' style='padding-bottom: 133.94495412844%; width: auto;'> <img class='img-responsive owl-lazys' src='"+(val.U_WImage1 ? val.U_WImage1: placeholder)+"' width='218' height='292' alt="+ItemName_trimmed+" /></span> </a> <div class='product details product-item-details'> <strong class='product name product-item-name'> <a class='product-item-link' title="+ItemName+" > "+ItemName+" </a> </strong> <span class='product price product-item-price'>$"+itemPrice+"</span> <div class='num-in'> <span class='minus dis'></span> <input type='number' placeholder='Qty' inputmode='numeric' class='qty_add_num' value='0' min='0'> <input name='addstyleqty' id='addstyleqty' type='hidden' value=''> <input name='currenstylesku' id='currenstylesku' type='hidden' value=''> <span class='plus'></span> </div> </div> </div>";
            });
            obj["html"] = sliderhtml
            obj["items"] = res.length
            return obj;
        },

        mark_material_resetslidervalue: function(materialhtml,items){
                if(materialhtml){
                    $(".material-slider .owl-stage").html('');
                    if(items <= 4){
                        owlmaterial.trigger('replace.owl.carousel', materialhtml,{
                          options: { loop: false,mouseDrag:false}
                        });
                        owlmaterial.trigger('refresh.owl.carousel');
                        // $(".owl-controls").css("display","none")
                    }else{
                        owlmaterial.trigger('replace.owl.carousel', materialhtml,{
                          options: {mouseDrag:true,loop:true}
                        });
                        owlmaterial.trigger('refresh.owl.carousel');
                        owlmaterial.trigger('to.owl.carousel', 0)
                        $('.mark-slider .owl-stage').addClass("stop")
                        setTimeout(function(){
                          $('.mark-slider .owl-stage').removeClass("stop")
                        },250)
                        // $('.pro-slider .owl-stage').css({ 'transition': '', 'transform': ''})
                        // $(".material-slider .owl-controls").css("display","")
                    }
                }
        },

        _inremenot: function($widget, $this){
              var $input =  $this.siblings('input.qty_add_num');
              if($this.hasClass('minus')) {
                var count = parseFloat($input.val()) - 1;
                count = count < 1 ? 0 : count;
                if (count < 2) {
                  $this.addClass('dis');
                }else {
                  $this.removeClass('dis');
                }
                // $("input.qty_add_num").attr("value",count);
                $input.val(count);
              }else {
                if($input.val() == ''){
                  $input.val(0);
                }
                count = parseFloat($input.val()) + 1
                // $("input.qty_add_num").attr("value",count);
                $input.val(count);
                if (count > 1) {
                  $this.parents('.num-block').find(('.minus')).removeClass('dis');
                }
              }
            // $input.change();
            return false;
        },

// <-------------------------------------------------------- Marketing Material END BS ------------------------------------------------->


    });

    return $.mage.SwatchRenderer; //list
});
    
