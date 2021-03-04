/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([    
    'jquery',
    'mage/template',   
    'Magento_Customer/js/customer-data',
    'text!Sttl_Customerorder/template/neworder-popup.html',
    'text!Sttl_Customerorder/template/neworder-lineitem.html',
    'mage/validation/validation'
], function ($, mageTemplate, customerData, thumbnailPreviewTemplate,lineitemstemp ) {
    'use strict';
     var activecat = [];
     var selector = '';

     var finalitems = [];
     var ordertotaldata = {};
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
            jsonConfig: {},

            // option's PO config
            poConfig:{},

             // option's PO config
            ConfigStyle:{},

            // Customer`s FlatDiscount From SAP`
            customersFlatDiscount: {},

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
                    " - "+ (option == 1 ? config.ShortDesc : config.ItemName) +
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
        },

         /*
        **  find all confiurable product Sky
        * @returns array|null
        */
        getConfigurableProductList: function(){          
            var dt = [],
                color = [],
                cat = [];
            var data1 = this.options.jsonConfig.map(item => item).filter(function(value, index, self) {
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
            var data2 = this.options.jsonConfig.map(item => item).filter(function(value, index, self) {
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

        /*
        **  find confiurable/Simple product list
        *  * @returns array|null
        */
        getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode';
           var falg = _.filter(this.options.jsonConfig , function (value) {
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
         
            var data = this.getConfigurableProduct(this.options.jsonConfig , 'Style');

            var base_order_id = this.options.base_order_id;  
            if(base_order_id && base_order_id != ""){
                this._collectionlogoslider();
                this.productslider();  
                this._getLineItemTable(base_order_id);
            }

            this._EventListener();

        },


        _create: function() {

            var options = this.options;
            var element = this.element;
            var $widget = this;
            
        //     this._collectionlogoslider();
        //             // this.collectionlogosclickevent();
        // this.productslider();  
        var base_order_id = this.options.base_order_id; 
            if(!base_order_id){
              element.find("#po_number").css({"opacity": ""}).attr('readOnly',false);
            }
            element.find('#po_number').on('keyup keypress', function(e){ var keyCode = e.keyCode || e.which; if (keyCode === 13) {e.preventDefault(); $('.checkPoAndInsert').trigger('click'); return false; } });
            element.find('.checkPoAndInsert').on('click', function(e){
                e.preventDefault();
               $('#po_number').validation();
               if(!$('#po_number').validation('isValid')){
                   $('#po_number').css('border','1px solid red');
                   return false;
               }else{  $('#po_number').css('border',''); }
               return $widget._determineProductData( $('#po_number').val(),$widget);
            });

            $(document).on('click', '.add-to-order-main', function(e){

                var opt_two_sku = '';
                var opt_two_qty = '';

               $('#opt_two_sku').validation();
               $('#opt_two_qty').validation();
               if(!$('#opt_two_sku').validation('isValid')){
                    $('#opt_two_qty').css('border','');
                   $('#opt_two_sku').css('border','1px solid red');
                   // console.log("Not Valid");
                   return false;
               }else if(!$('#opt_two_qty').validation('isValid')){
                    $('#opt_two_sku').css('border','');
                    $('#opt_two_qty').css('border','1px solid red');
                   // console.log("Not Valid");
                   return false;
               }else{
                    opt_two_sku = $('#opt_two_sku').val();
                    opt_two_qty = $('#opt_two_qty').val();
                    // console.log("Valid");
                    $('#opt_two_sku, #opt_two_qty').css('border','');
               }
               return $widget._optiontwoAddqty( opt_two_qty, opt_two_sku, $('#po_number').val(),$widget);

            });

            $(document).on('click',".saveData", function(e){
                  var checkinputval = $('.colorContainer').find('.checkvalue');
                  var valIsExists = false;
                  $( checkinputval ).each(function() {
                      if ($(this).val() != '')
                      {
                          valIsExists = true;
                      }
                  });
                  if(!valIsExists)
                  {
                      console.log('Please provide at least one item quantity to proceed.');
                      return false;
                  }

                  var checklink =  $(this).attr("disabled");
                  if(valIsExists)
                  {
                    var getColorCode = '';
                    if($( ".swatch-option.image.active" ).length > 0)
                    {
                      getColorCode = $( ".swatch-option.image.active" ).attr('option-color-code');
                    }
                      return $widget._optiononeAdddata();
                  }else{
                      adderror('Please provide valid data to proceed.');
                      return false;
                  }

                  if($(this).parent().siblings()){
                    $(".alignLeft.mrgT20.edit_note").hide();
                  }
              });


        },

        /**
         * Add a Data to Po using Option1 and use for AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */

          _optiononeAdddata: function () {

            var $widget = this;
            var is_savedata = 'true';
            var current_options = 1;
            var req_url = this.options.baseurl+'customerorder/customer/optiontwojs';
            $widget._renderLineitembeforeAJAX($widget, current_options);
                // $.ajax({
                //     url:req_url,
                //     type: "POST",
                //     data:$("#createorder").serialize()+"&is_savedata="+is_savedata,
                //     showLoader: false,
                //     cache: false,
                //     success: function(response){

                //         if (response.success) {
                //           console.log(response);
                //           var lineitem_temp = mageTemplate(lineitemstemp, {
                //                 finalorderrendere: $widget._getOrderDataItems($widget,response, current_options),
                //                 ordersummaryinfo: $widget._getorderSummaryinfo($widget,response),
                //                 databystylegroup : $widget._DatabyStyle()
                //             });

                //             if(response.order_id)
                //             {
                //               $("#order_id").val(response.order_id);
                //             }

                //             $(".line-item .orderListing").html(lineitem_temp);

                //             $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
                //             $(".colorContainer .showprice, .maxqtyvaldi").html('');

                //             if(lineitem_temp){
                //                 $(".cf.line-item").fadeIn(300);
                //                 $(".cf.delOrdLink").fadeIn(300);
                //             }
                //         }
                //     }
                // });


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

            $widget.element.on('input', '.dropdown', function (e) {
                return $widget._OnChangeDropdown($(this), $widget);
            });

            $widget.element.on('keydown', '.dropdown', function (e) {               
                if (e.which == 32 || e.which == 27) { return false; }                
                return $widget._OnEnterClick($(this), $widget,e);
            });

             $widget.element.on('keydown', '#opt_two_qty', function (e) {                 
                if(e.which == 9 || e.which == 13){ $('#sku_addorder').focus().trigger('click'); }                    
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) { return false; }                
                
            });

            $widget.element.on('click', '.searchFromStyle', function () {
                return $widget._OnItemChange($(this), $widget);
            });

            $widget.element.on('click', '.autocomplete-items .element', function () {
                return $widget._ItemSelect($(this), $widget);
            });

            $widget.element.on("change","#files_upload",function() {
                 return $widget._checkfileupload($(this), $widget);
            });

            $widget.element.on("click","#file_addorder",function() {
                 return $widget._uploadOptions_files($(this), $widget);
            });
             $widget.element.on("click",".newOrderStep1",function() {
                 return $widget.getConfigurableProductList();
            });

            // BS Added 16/10/2020 START
            $widget.element.on('click', '#createorder .optionTabs .nav-item', function () {
                return $widget._SwitchbetweenOptions($(this), $widget);
            });

            $widget.element.on('click', '.editOrderdItem', function (e) {
                return $widget._Editorderdata($(this), $widget);
            });

            $widget.element.on('click', '.delSingalRecords', function (e) {
                return $widget._Deleteorderdata($(this), $widget);
            });

            $widget.element.on('focusout', '.checkvalue', function(){
                $widget._checkvalueUpdate($(this), false);
            });

            $widget.element.on('keypress', '.checkvalue', function(e){
              if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                  return false;
              }
            });

            $widget.element.on('mouseover', '.renderAllHtml .swatch-option.image', function () {
                $(this).find(".bottom-tooltip-active").show();
            });

            $widget.element.on('mouseout', '.renderAllHtml .swatch-option.image', function () {
              if(!$(this).hasClass("active")){
                $(this).find(".bottom-tooltip-active").hide();
              }
            });

            $widget.element.on('click', '.catBtns .customBtns', function () {
              var cur_button = $(this).attr("product-sku");
              var active_pro = $("config_product_info").attr("id");

              if(active_pro == "config_"+cur_button){
                return false;
              }else{
                $("#show_style").val(cur_button);
                $(".searchFromStyle").trigger("click");
              }
            });

            // BS Added 16/10/2020 END
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
                this.element.find(".newOrderStep2").fadeIn('fast');
                $([document.documentElement, document.body]).animate({scrollTop: $(".newOrderStep2").offset().top-100 }, 500)
                 this._collectionlogoslider();
                    // this.collectionlogosclickevent();
                this.productslider();  
            }

        },

        /**
         * Add a Data to Po using Option1 and use for AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */

          _getLineItemTable: function (order_id) {

            var $widget = this;
            var tmp_po_number = $("#show_style").attr("value");
            var req_url = this.options.baseurl+'customerorder/customer/optiontwojs';
            var current_options = '';
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

                        if (response.success) {
                          var lineitem_temp = mageTemplate(lineitemstemp, {
                                finalorderrendere: $widget._getOrderDataItems($widget,response, current_options),
                                ordersummaryinfo: $widget._getorderSummaryinfo($widget,response),
                                databystylegroup : $widget._DatabyStyle()
                            });

                            $(".cf.line-item").fadeIn(300);
                            $(".line-item .orderListing").html(lineitem_temp);

                            if(response.order_id)
                            {
                              $("#order_id").val(response.order_id);
                            }

                            if(lineitem_temp){
                                $(".cf.delOrdLink").fadeIn(300);
                            }
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
            var data = this.options.jsonConfig;
            var temp_items = [];
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

        _getOrderDataItems: function($this, _response, _current_options) {

            // console.log(_response);

            var $widget = $this;
            var response = _response;
            var mainresponce = {};


            // var order_id = response.base64_order_id;
            var style = '';
            var submitcolor = '';
            var viewmode = ''; 

            var stylebyInventory = $widget._stylebyInventory()
            var data = this.options.jsonConfig;
            
            if($widget._DatabyStyle() && $widget._stylebyInventory()){
                var databyStyle = $widget._DatabyStyle();

                var allorderdata = '';
                if(response != ''){
                  allorderdata = response.line_item_render.allorderdata;
                }else{
                  allorderdata = $widget._generateqtyarray(_current_options, $widget);
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
                            mainresponce[current_style] = {};
                        });
                        
                        allorderdata.forEach(function(item, index){
                            for(var index_a in item_size){
                                var stylegroup = item_size[index_a]
                                if(stylegroup == item.Style){   
                                    mainresponce[current_style][stylegroup] = {};
                                }
                            }
                        });
                        allorderdata.forEach(function(item, index){
                            for(var index_a in item_size){
                                var stylegroup = item_size[index_a];
                                var colorcode = item.ColorCode;
                                if(stylegroup == item.Style){
                                    mainresponce[current_style][stylegroup][colorcode] = {};
                                }
                            }
                        });
                        var order_item_count = 0;
                        allorderdata.forEach(function(item, index){
                            for(var index_a in item_size){
                                var stylegroup = item_size[index_a];
                                var colorcode = item.ColorCode;
                                if(stylegroup == item.Style){
                                    mainresponce[current_style][stylegroup][colorcode][order_item_count] = item;
                                    order_item_count++;
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

         /**
         * Render html
         *
         * Shows the _uploadOptions_files Avaialble Info Block
         */
        _uploadOptions_files: function($this, $widget){
            $('.progress').show().find('myprogress').text('0%').animate({ 'width' : '50%'},500);
            this._checkfileupload($this, $widget);
            $('#activeoption').val('option3');
            var formData = new FormData($("#createorder")[0]);            
            $("#fileupload_text").css({"pointer-events": "none", "opacity": "0.5"});
            $('#file_addorder').html("ADDING...").attr('disabled', "disabled").css({"pointer-events": "none", "opacity": "0.5"});
            $('#submitfileuploadvalues').val(1);            
            
            $('#file_cancel_upload').show().css({"pointer-events": "all", "opacity": "1"});
            $("#file_close").css({"pointer-events": "none", "opacity": "0.5"});
            this.options.usexhr = $.ajax({
                url: this.options.baseurl+'customerorder/customer/optiontwo',
                enctype: 'multipart/form-data',
                type: "POST",
                data:formData,
                showLoader: false,
                processData:false,
                contentType: false,
                cache: true,
                success: function(response){   
                    $('#stopprogress').val(0)               
                    if(response.errors)
                    {                        
                        $('.myprogress').text('100%').css('width','100%');
                        $('.orderListing').html(response.html);
                        $widget.optionTwoError(response.message , 10000)
                    }else if(response.success){

                        $('.myprogress').text('100%').css('width','100%');                        
                        $('.orderListing').html(response.html);
                        $widget.addSuccessMessage(response.message);
                        $('#files_upload').val('');
                        $("#file_name, #file_size").html('');                        
                        $("#file_show_name,#file_name, #file_close, #file_cancel_upload").hide();
                       
                    }else{
                        $('.orderListing').html(response.html);
                       $widget.optionTwoError('Places try again');
                    }
                    if(response.orderBtnRow)                    
                        $('.line-item , .delOrdLink').show();
                    
                    if(response.order_id)
                       $("#order_id").val(response.order_id);                    

                    if(response.base64_order_id){
                        $("#base64_order_id").val(response.base64_order_id);
                        $("#base64_ncp_id").val(response.base64_ncp_id);
                        $widget.OptionChangeUrl(response.base64_order_id ,response.base64_ncp_id)
                    }
                    $("#file_cancel_upload").css({"pointer-events": "none", "opacity": "0.5"});
                    $('#customLoader').fadeOut(200);
                    $('#submitfileuploadvalues').val(0);                    
                    $('#file_addorder').html("Submit File").removeAttr('disabled').css({"pointer-events": "all", "opacity": "1"});
                    $('#files         _upload').removeAttr('disabled');
                    $("#fileupload_text").css({"pointer-events": "all", "opacity": "1"});
                    $("#file_close").css({"pointer-events": "all", "opacity": "1"}).trigger( "click" );
                }

            });


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
        _OnChangeOptTwoInfo: function ($this, $widget) {
            var result = this.getProductArray($this.val() , 2);            
            if(result.length < 1){           
                $("#qtyDetailPop").fadeOut(200);
                $("#opt_two_message").removeClass("success").addClass("error").html('Requested SKU doesn`t exists.').show();
                setTimeout(function () {$("#opt_two_message").html(''); }, 5000);
            }else{     
                $('#opt_two_qty').focus();
                var date = new Date(result.ETA);
                $("#actualunitPrice").html(result.UnitPrice);
                $("#inStock_op2").html(result.ActualQty);                
                $('#eta_op2').html(Date.parse(date) ? ((date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear()) : '');
                $("#qtyDetailPop , #opt_two_message").fadeIn(200);
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
                this.optionTwoError("Please upload file.")
                fp.focus();
                return false;
            }

            if(files.toLowerCase().lastIndexOf(".csv")==-1)
            {
                this.optionTwoError("Please upload valid CSV file.")
                fp.focus();
                return false;

            }else{
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
        optionTwoError: function (message , time = 5000)
        {
            var data = $("."+$('#newOrderTab>div.tab-pane.fade.active.show').attr('id'));
            data.removeClass("success").addClass("error").html(message);
            $("#opt_two_message").show().focus();            
            if($("."+$('#newOrderTab .fade.active.show').attr('id')).find('b').length < 1){ 
                data.prepend('<b  title="Copied!"></b> <span></span'); 
            }
            setTimeout(function(e){
               data.html('');$("#opt_two_message").hide();
            },time);
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
          if(_.isEmptys($this.val())){
                $this.css('border','1px solid red');
                return false;
            }else{
                $this.css('border','');

            }
        },


        /*
        * Section show with in time period
        */
        _OnChangeDropdown: function($this, $widget){
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
                if(!$this.parents('.option').find('span.error').length){ $this.parents('.option').append('<span class="error" Style="color:red;">Item not found</span>');}
                setTimeout(function () {  $('.error').remove(''); }, 2000);
            }
            if($('#'+$this.attr('id') +'autocomplete-list').length == 0){ $this.parent().append(a);}
            $this.parent().find('div.autocomplete-items').html(input);

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

        /**
         * Add a Qty to Po using Option and use for call AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */

        _optiontwoAddqty: function (opt_two_qty, opt_two_sku, po_number) {

            var $widget = this;



            var activeoption2 = 'true';
            var current_options = 2;


            if(!$widget._checkSkuAvailable(opt_two_sku)) {
                $widget.optionTwoError('Requested SKU doesn`t exists.');
                $("#qtyDetailPop").fadeOut(200);
            }else{
                  $widget._renderLineitembeforeAJAX($widget, current_options);
                  var current_pro = $widget._getSimpleProductInfo(opt_two_sku);
                 $("#qtyDetailPop").fadeOut(200);
                 var req_url = this.options.baseurl+'customerorder/customer/optiontwojs';
                // $.ajax({
                //     url:req_url,
                //     type: "POST",
                //     data:{
                //         opt_two_sku:opt_two_sku,
                //         opt_two_qty:opt_two_qty,
                //         po_number:po_number,
                //         activeoption:activeoption2,
                //         proinv:current_pro
                //       },
                //     showLoader: true,
                //     cache: false,
                //     success: function(response){

                //       console.log(response);


                //         if (response.success) {

                //             var lineitem_temp = mageTemplate(lineitemstemp, {
                //                 finalorderrendere: $widget._getOrderDataItems($widget,response, current_options),
                //                 ordersummaryinfo: $widget._getorderSummaryinfo($widget,response),
                //                 databystylegroup : $widget._DatabyStyle()
                //             });

                //             $(".cf.line-item").fadeIn(300);
                //             $(".line-item .orderListing").html(lineitem_temp);

                //             if(response.order_id)
                //             {
                //               $("#order_id").val(response.order_id);
                //             }

                            
                //             if(lineitem_temp){
                //                 $(".cf.delOrdLink").fadeIn(300);
                //             }

                //         }else{
                //             console.log("Somthing went Wrong");
                //         }
                //     }
                // });
            }
            return true;
        },

        /*
        * Select Style Button
        */
        _ItemSelect: function($this, $widget){

            var input = $this.parents('.box-content').find('input');
            input.val($this.find('input').val());
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
            console.log(item);
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

        /*
        * Select Style Button
        */
        _OnItemChange: function($this, $widget){

            var option = $this.attr('data-action'),
                input = $('input[data-option="' + $this.attr('data-action') + '"]'),            
                child_pro = this.getProductArray(input.val() , $this.attr('data-action'));
            var temp_parent_sku = input.val();
            
            $('#'+input.attr('id')+'autocomplete-list').remove();

            if(child_pro.length < 1){
                if(!$this.parents('.option').find('span.error-not-found').length){ $this.parents('.option').append('<span class="error-not-found" Style="color:red;">Item not found</span>');}
                setTimeout(function () {  $('span.error-not-found').remove(''); }, 2000);
            }else { 
                
                $('#'+$this.attr('id')+'autocomplete-list').animate({scrollTop: 0 });

                if(option == 1){
                    $([document.documentElement, document.body]).animate({scrollTop: $(".collection-owlslider").offset().top - 80}, 800);                    var temp_customersFlatDiscount = this.options.customersFlatDiscount,
                        magento_product =  _.filter(this.options.magento , function (value) {                            
                                                return value.sku === input.val();
                                            }),
                        temp_quick_url = magento_product.length > 0 ? this.options.baseurl+"weltpixel_quickview/catalog_product/view/id/"+magento_product[0].id : '';

                    var styleconfiguration = mageTemplate(thumbnailPreviewTemplate, {
                        'result': this._prepareDropdownContext(child_pro), 
                        data: child_pro,                       
                        quickview_url: temp_quick_url, 
                        parent_color: input.val(), 
                        customersFlatDiscount : temp_customersFlatDiscount,
                        rptswitcher: $widget._getRPTswitcherButtons(this.options.magento ,temp_parent_sku, $widget)
                    });
                    $('#option1ContStyle').html(styleconfiguration);

                }else if(option == 2){                    
                    this._OnChangeOptTwoInfo($this, $widget);

                }else if(option == 2){
                    // this._OnChangeOptTwoInfo();
                }else{
                    console.log('no option change');
                }
                $('#'+input.attr('id')+'autocomplete-list').remove();
            }
            if(input.val().length < 1){  $('.autocomplete-items').remove(500);}
        },

         /**
         * Callback which style input keyup and down.
         *
         * @param {HTMLElement} element - DOM element associated with a input keys.
         */
        _OnEnterClick:function($this, $widget , e){ 
                                       
            if(e.which == 9 || e.which == 13){
                if($this.val().length < 1){ return false }                
                $this.val($('#'+$this.attr('id')+'autocomplete-list').find('.element.autocomplete-active input').val());
                if($this.attr('data-option') == 1)
                    $('.searchFromStyle').trigger('click');
                else if($this.attr('data-option') == 2)
                    $('#opt_two_qty').focus();
                else if($this.attr('data-option') == 3)
                    console.log('third option');
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
                   
        },
        /*
        * collection slider data array.
        *
        * return json.
        */
        _slidercollection: function(){
            var data = this.getConfigurableProduct(this.options.jsonConfig , 'Style'),
                data1 = this.getConfigurableProductList(),                
                d = this.options.jsonConfig,
                collection = [];

            $.each(data,function(key,val){
                collection.push(val.Collection);
            })

            var uniquecollection = uniquevalue(collection)

            function uniquevalue(arrry){
                return arrry.filter(function(itm, i, a) {
                    return i == a.indexOf(itm);
                });
            }
            var gruname = {};
           for (var i = 0; i < uniquecollection.length; i++) {  
                var ids = [];
                $.each(data,function(key,val){
                    if(val.Collection === uniquecollection[i] && uniquecollection[i] != '' ){
                        ids.push(val.GroupName);  
                                          
                    }
                }) 
                gruname[uniquecollection[i]] = uniquevalue(ids);     
            }

            $.each(gruname,function(key,val){
                for (var i = 0; i < val.length; i++) { 
                var proids =  [];
                var proids2 = [];
                    $.each(data,function(prokey,proval){
                        if(proval.Collection == key && proval.GroupName == val[i] && val[i] != ''){
                            var obj = {};
                             obj["Style"] = proval.Style;
                             obj["ShortDesc"] = proval.ShortDesc;
                             obj["U_WImage1"] = proval.U_WImage1;
                            proids.push(obj)
                        }
                    })
                    $.each(data1,function(prokey,proval){
                        if(proval.Collection == key && proval.GroupName == val[i] && val[i] != ''){
                            var obj = {};
                             obj["Style"] = proval.Style;
                             obj["ShortDesc"] = proval.ShortDesc;
                             obj["U_WImage1"] = proval.U_WImage1;
                            proids2.push(obj);
                        }
                    })
                    var total = proids2.concat(proids);
                    total = total.filter((thing, index, self) =>
                              index === self.findIndex((t) => (
                                t.Style === thing.Style
                              ))
                            )                        
                    gruname[key][val[i]] = total                     
                }
            })

           return gruname;
        },
         _gruopnamcollection: function(){
            var data = this.getConfigurableProduct(this.options.jsonConfig , 'Style')
            function uniquevalue(arrry){
                return arrry.filter(function(itm, i, a) {
                    return i == a.indexOf(itm);
                });
            }
                var ids = [];
                $.each(data,function(key,val){
                    if(val.Collection){
                        ids.push(val.GroupName.replace(" ","_")); 
                    }
                }) 
           return uniquevalue(ids); 
        },
        /*
        * SET collection slider html.
        * owlCarousel collection logo slider.
        */
        _collectionlogoslider: function(){
            var $widget = this
            var logos = this.options.logos;          
            var html="";
            $.each(logos, function(key,logos) {
                html += "<li class='col-logo' id ="+logos.name+">"                    
                html += logos.image ? "<img src="+logos.image+" alt="+logos.name+">" : 
                    "<h1 class='collection-logo'>"+logos.name+"</h1>"                    
                html  +='</li>'
            })

            var coll = $(".Collections").owlCarousel({
                    loop:false
                    ,autoplay:false
                    ,autoplayTimeout:3000                        
                    ,margin:10
                    ,nav:false
                    ,dots:false
                    ,responsive:{
                        0:{
                            items:2
                        },
                        600:{
                            items:4
                        },
                        1000:{
                            items:5
                        }
                    },
                    onInitialized:pageload()
                        
                }); 
            coll.trigger('replace.owl.carousel', html);
            coll.trigger('refresh.owl.carousel');

            function pageload()  {

                setTimeout(function () {
                    $("li#Pro").parent().addClass("pro");
                $("li#Pro").css("width", "50px");
                 var group = $widget._gruopnamcollection()
                    $widget.collectionlogosclickevent(group);
                  }, 100);
            }

        },

        /*
        * collection logo click event.
        *
        * on click set prouct collection group name.
        */

        collectionlogosclickevent: function(group = ''){
          var $widget = this
             var gruopname = this._slidercollection();
            if(group){
                addgruopname(group)
                $(".product-group-slider").find('.group-col').first().trigger("click");
            }
             $(document).on('click', '.col-logo', function () {
                $(this).parent().toggleClass("active_collection");
                var temp = []
                $(".owl-item.active_collection.active").each(function(){
                    var collectionvalue = $(this).find(".col-logo").attr("id");
                    $.each(gruopname[collectionvalue],function(key,val){
                        temp.push(val.replace(" ","_"));
                    })
                })
                var unique = temp.filter(function(itm, i, a) {
                    return i == a.indexOf(itm);
                });
                if(unique.length > 0){
                    $widget.addgruopname(unique)
                }else{
                    $widget.addgruopname(group)
                }
                if(activecat.length){
                    $(".product-group-slider span").each(function(){
                            var gid = $(this).attr("child-id") 
                            if(_.contains(activecat, gid)){
                                // flag = true
                                $(this).trigger("click");
                            }
                        })
                }
              });
            
            
        },

        addgruopname: function(unique){
            var html = ''
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
        /*
        * Product slider html.
        * owlcarousel slider.
        */ 
        productslider: function(){
            var gruopname = this._slidercollection(),
                baseurl = this.options.baseurl;

            // product collection slider
            var owl = $('.product-slider').owlCarousel({
                     loop:true
                    ,autoplay:true
                    ,autoplayTimeout:3000
                    ,margin:27
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
                            items:4
                        }
                    }
            });
            //group collection click event show product collection slider
            $(document).on('click',".group-col", function () {
                var res = [];
                var prohtml = 'a';

                var items = 0;
                    $(this).toggleClass("active");
                if(!$("span.group-col").hasClass("active")){
                     $(".owl-controls").css("display","none")
                }
                    var vid = $(this).attr("child-id");
                if($(this).hasClass("active")){
                    activecat.push(vid)
                    activecat = uniquevalue(activecat);
                    $(this).prependTo($(this).parent());
                }else{
                    activecat = jQuery.grep(activecat, function(value) {
                      return value != vid;
                    });
                    $(this).appendTo($(this).parent());
                }
            
                if($(".Collections .owl-item").hasClass("active_collection")){
                        activeclassvalue($(".owl-item.active_collection.active"));
                    }else{
                        activeclassvalue($(".Collections .owl-item"));                
                    }   
                     function uniquevalue(arrry = ''){
                        return arrry.filter(function(itm, i, a) {
                            return i == a.indexOf(itm);
                        });
                    }
                    function activeclassvalue(active = ''){ 
                        active.each(function(){
                            var collectionvalue = $(this).find(".col-logo").attr("id");
                            var logoimage = $(this).find(".col-logo img").attr("src");
                            $(".product-group-slider span").each(function(){
                                if($(this).hasClass("active")){
                                var gid = $(this).attr("child-id").replace("_"," ")   
                                    if(gid in gruopname[collectionvalue] && gid){
                                        slidervalue(gruopname[collectionvalue][gid],logoimage,collectionvalue)  
                                    }
                                }
                            })
                        })
                        if($(".product-group-slider span.active").length < 1 && $('.Collections .owl-item .active').length < 1){
                                 slidervalue(gruopname.Addition.Tops , '','Addition');
                        }
                        $(".product-group-slider span").each(function(){
                                if($(this).hasClass("active")){
                                    var gid = $(this).attr("child-id").replace("_"," ")   
                                    if(gid in gruopname['Other']){
                                        slidervalue(gruopname['Other'][gid],"","Other")
                                    }
                            }
                        });
                    }
                    $(".product-slider .owl-stage").html('');
                    function slidervalue(res,logoimage,collectionvalue){
                        $.each(res, function(key,val) {
                            var parentid = logoimage ? "<img src="+logoimage+" class="+collectionvalue+">" : collectionvalue,
                                      ItemName = val.ShortDesc ? val.ShortDesc : '',
                                      placeholder = baseurl+'pub/media/catalog/product/placeholder/default/image.jpg';                                    
                                                                  
                            prohtml += "<div class='item product product-item' id="+val.Style+">  <a class='product-item-link'> <span class='product-image-wrapper' style='padding-bottom: 133.94495412844%; width: auto;'> <img class='' src='"+(val.U_WImage1 ? val.U_WImage1: placeholder)+"' width='218' height='292' alt="+ItemName+" /></span> </a> <div class='product details product-item-details'> <strong class='product name product-item-name'> <a class='product-item-link' title="+ItemName+" > "+ItemName+" </a> </strong> <div class='show-product-dis-box'><span>"+parentid+" collection </span></div> <div class='show-product-dis-box-more'> <span> <lable>Style No.</lable> </span> <span>"+val.Style+"</span> </div> </div> </div>";
                            items++;                            
                        });
                    }
                        if(items <= 3){
                            owl.trigger('replace.owl.carousel', prohtml,{
                              options: { loop: false,mouseDrag:false}
                            });
                            owl.trigger('refresh.owl.carousel');
                            $(".owl-controls").css("display","none")
    
                        }else{
                            owl.trigger('replace.owl.carousel', prohtml,{
                              options: {mouseDrag:true}
                            });
                            owl.trigger('refresh.owl.carousel');
                            $(".owl-controls").css("display","")
                        }
                      
                        if(!$(".product-image-sticky .product-item").children().length){
                            $(".product-image-sticky").removeClass("active")
                            $(".product-image-sticky .product-item").css("width",$(".product-slider .owl-item").width())
                            $(".product-image-sticky").html($(".product-slider .owl-stage").find(".owl-item").last().html())
                            $(".product-image-sticky .owl-item").css("display","block")
                        }
                });
        },


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

            $("#style").val($this.attr('edit-id'));
            $("#show_style").val($this.attr('edit-id'));
            $(".searchFromStyle").trigger("click");

            // $("#nav-tabContent").find(".tab-pane").removeClass("active");
            
            // $("#nav-tabContent").find("#"+edit_color+"").addClass("active")


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

        /*
        *
        **  remove the row wise data from line item`
        *  * @returns array|null
        */
        _Deleteorderdata: function($this, $widget) {
            var checkinputval = $('.colorContainer').find('.checkvalue');
            $('div.renderAllHtml').attr('data-value', 'edit');
            var valIsExists = false;
            var delete_color = $this.parent().attr('delete-color');
            var deletestyle = $this.parent().attr('delete-id');

            var current_options = '';

            // console.log(delete_color);
            // console.log(deletestyle);

            var tmp_orderdata = _.filter(finalitems , function (item) {
              if(item.Style == deletestyle && item.ColorCode == delete_color){}
              else{
                return true
              }
            });
            
            if(tmp_orderdata.length <= 0){
              $(".cf.line-item, .cf.delOrdLink").fadeOut(300);
            }else{
              finalitems = tmp_orderdata;
              $widget._renderLineitembeforeAJAX($widget, current_options);
            }
           return true;
        },


        _renderLineitembeforeAJAX: function($this, _current_options) {
            var $widget = $this;
            var orderdata = '';
            var lineitem_temp = mageTemplate(lineitemstemp, {
                finalorderrendere: $widget._getOrderDataItems($widget, orderdata, _current_options),
                ordersummaryinfo: $widget._getorderSummaryinfo($widget, orderdata),
                databystylegroup : $widget._DatabyStyle()
            });


            if(lineitem_temp){
              $(".cf.line-item").fadeIn(300);
              $(".cf.delOrdLink").fadeIn(300);

            }

            $(".line-item .orderListing").html(lineitem_temp);

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
               var magento_products =  _.filter(_magentopro , function (value) {
                    return value.sku === petiteSku;
                });

               if(magento_products.length > 0){
                  petiteSku = magento_products[0].sku;
                  if(_.contains(sapconfigskus, petiteSku)) {
                    available_skus["petite"] = petiteSku;
                  }
               }else{
                  petiteSku = '';
               }
            }


            if(tailSku != ''){
                var magento_products =  _.filter(_magentopro , function (value) {
                    return value.sku === tailSku;
                  });
                 if(magento_products.length > 0){
                    tailSku = magento_products[0].sku;
                    if(_.contains(sapconfigskus, tailSku)) {
                      available_skus["tall"] = tailSku;
                    }
                 }else{
                    tailSku = '';
                 }
            }

            if(regularSku != ''){
                var magento_products =  _.filter(_magentopro , function (value) {
                    return value.sku === regularSku;
                  });
                 if(magento_products.length > 0){
                    regularSku = magento_products[0].sku;
                    if(_.contains(sapconfigskus, regularSku)) {
                      available_skus["regular"] = regularSku;
                    }
                 }else{
                    regularSku = '';
                 }
            }
           return available_skus;
        },

        /**
         * Prepared Added Qty to PO Info
         *
         * @returns []
         * @private
         */


        _generateqtyarray: function(_current_options, $widget) {
          var data_selector = $('.colorContainer').find('.checkvalue');
          var current_options = _current_options;
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
                    Color: selectcolor
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
                  $(this).val('');
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
                Color: current_pro.Color
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

          return finalitems;

        },


        _generateordertotalarray: function($widget) {
            var data_selector = $('.colorContainer').find('.checkvalue');


            var customersFlatDiscount = $widget.options.customersFlatDiscount;
            customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;

            var sellingprice = 0;
            var discountAmount = 0; 

            var customrsbulkdiscount = $widget.options.customersBulcDiscount;

            var DocTotalQty = 0;

            finalitems.forEach(function(item, index){
              sellingprice = parseFloat(sellingprice) + parseFloat(item.TotalPrice);
              DocTotalQty = parseInt(DocTotalQty) + parseInt(item.QTYOrdered);
            });

            console.log(DocTotalQty);
            console.log(sellingprice);

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
              TotalDiscountAmount: 0
            }

            // console.log(ordertotaldata);

            return ordertotaldata;
        },
            


    });

    return $.mage.SwatchRenderer; //list
});
    
