/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 
  'mage/template',
  'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
  'text!Sttl_Customerorder/template/view_stock_pricing.html',
  'text!Sttl_Customerorder/template/view_stock_pricing_button.html',
  "ionrange",
], function ($,mageTemplate,inventory,view_stock_pricing,view_stock_pricing_button,ionRangeSlider) {
    'use strict';
     var activecat = [],
         owlproduct= '',
         recentsearch = '',
         timeout = '',
         activecol = [],
         fromprice = 0,
        toprice = 0,
        selecedcheckbox = [],
        max = 0;
     var product_Active  = null;
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

            // BaseURl
            baseurl: {},

            ConfigStyle: {},

            poConfig:{},

            customersBulcDiscount:{},

            customersFlatDiscount:{}
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
            return result;
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
        getProductArray: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.jsonConfig , function (value) {
                    return value[key] === sku;
            });
           return falg;

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
        _init: function () {       
             this.options.ConfigStyle = this.getConfigurableProduct(this.options.jsonConfig , 'Style');
                this._collectionlogoslider();
                this.productslider();
              this._EventListener();
              var array = this.options.jsonConfig;
              var min = 0;
              max = Math.max.apply(null,
                  Object.keys(array).map(function(e) {
                      return array[e]['UnitPrice'];
                  }));
              var random = Math.random() * (max - min + 1) + min
              fromprice = 0;
              toprice = max;
              $(".js-range-slider").ionRangeSlider({
                  type: "double",
                  grid: true,
                  min: min,
                  max: parseInt(max)+1,
                  from: 0,
                  to: parseInt(max)+1,
                  step: 1,
                  prefix: "$"
              });
        },

         /*
        **  Set Regualar, Petite or Tail buttons in Searched Style
        *  * @returns array|null
        */
        _getRPTswitcherButtons: function( _sku, $widget){

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
 
        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
          var $widget = this;
          var $baseurl =this.options.baseurl;
          if ($('.orderItem-loader').css('display') == 'none')
          {
             $('.product-group .filter-popup').css('display','block');
             $('.product-group .filter-popup').css('display','block');
          }

          $( window ).resize(function() {
            setTimeout(function(){

              $('.product-slider').css('width', jQuery('.featured-pro').width()  - 50)
              var owl = $('.product-slider').data("owlCarousel");
              owl.onResize();
            },300)
            });

          $(document).on('click', function(event) {
                if (!$(event.target).closest('div#user-gird-conatiner .product-group .filter-popup, .popup').length) {
                    $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-active",1000);
                    $("div#user-gird-conatiner .product-group .popup").css("display", "none",700);
                    $("div#user-gird-conatiner .product-group .more-filter").css("border-radius", "50px");
                var condition = selecedcheckbox.length  
                if(fromprice > 0 || toprice < max){
                    condition = condition + 1;
                }
                if(selecedcheckbox.includes("All")){
                    condition = condition - 1;
                }
                    if (condition != 0) {
                        $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").addClass("filter-option");
                    } else {
                        $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-option");
                    }
                }
            });
            $(document).on("click", ".more-filter", function(e) {

                var condition = selecedcheckbox.length
                 if(fromprice > 0 || toprice < max){
                    condition = condition + 1;
                }
                if(selecedcheckbox.includes("All")){
                    condition = condition - 1;
                }
               
                if (condition != 0) {
                    $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").addClass("filter-option");
                } else {
                    $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-option");
                }
                if ($('.more-filter').hasClass('filter-active') == true) {
                    $("div#user-gird-conatiner .product-group .more-filter").css("border-radius", "50px");
                } else {
                    $("div#user-gird-conatiner .product-group .more-filter").css("border-radius", "0px");
                    $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-option");
                }
                if ($(".product-group .popup").is(":visible")) {
                    $(this).removeClass("filter-active",1000);
                } else {
                    $(this).addClass("filter-active",700);
                }
                $(".product-group .popup").fadeToggle();
            });
            $(document).on("click", ".product-group .popup #more-filter", function(e) {
                fromprice = $('span.irs-from').text();
                toprice = $('span.irs-to').text();
                fromprice = fromprice.substring(1);
                toprice = toprice.substring(1);
                $("div#user-gird-conatiner .product-group .more-filter").css("border-radius", "50px");
                $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-active");
                $("div#user-gird-conatiner .product-group .popup").css("display", "none");
                var condition = $("ul.gender-filter :checkbox:checked").length
                if(fromprice > 0 || toprice < max){
                    condition = condition + 1;
                }
                var selecedcheckbox1 = [];
                $('.gender-filter :checkbox:checked').each(function() {
                    selecedcheckbox1.push(this.value);
                    console.log("chcekbox",selecedcheckbox1)
                });
                if(selecedcheckbox1.includes("All")){
                    condition = condition - 1;
                }
                selecedcheckbox = selecedcheckbox1;

                if(condition >= 1){
                    $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").addClass("filter-option");
                    $("div#user-gird-conatiner .product-group .filter-popup .more-filter span").html("More Filters: " + condition);
                }else{
                    $("div#user-gird-conatiner .product-group .filter-popup .more-filter span").html("More Filters");
                    $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-option");
                }
                $widget.categoryclick();
                $widget.collectionlogosclickevent();
            });
            $(document).on("click", ".product-group .popup ul.gender-filter input.checkbox.selectAll", function(e) {
                $(".gender-filter input[type=checkbox]").prop('checked', $(this).prop('checked'));
            });
            $(document).on("click", ".customLable input[type=checkbox]", function(e) {
                $('input.checkbox.selectAll').prop("checked", false)
                var checkbox = $("[type='checkbox']:checked").length;
                var totalCheckboxes = $('input:checkbox').length - 1;
                if(checkbox == totalCheckboxes){
                         $('input.checkbox.selectAll').prop("checked", true)
                }
            });
            $(document).on("click", ".product-group .popup #reset-filter", function(e) {
              selecedcheckbox = [];
                $("div#user-gird-conatiner .product-group .more-filter").css("border-radius", "50px");
                $("div#user-gird-conatiner .product-group .filter-popup").find(".more-filter").removeClass("filter-active");
                $("div#user-gird-conatiner .product-group .popup").css("display", "none");
                $("div#user-gird-conatiner .product-group .filter-popup .more-filter span").html("More Filters");
                $('.product-group .popup input:checkbox').removeAttr('checked');
                $widget._resetfilter();
            });

                $(".product-group .popup #reset-filter").mouseenter(function(){
                    $('div#user-gird-conatiner .product-group .popup .row.filter.btn').find("#more-filter").css("border","2px solid #fff")
                    $(this).css("border","2px solid #00416B");
                });
                $(".product-group .popup #reset-filter").mouseleave(function(){
                    $('div#user-gird-conatiner .product-group .popup .row.filter.btn').find("#more-filter").css("border","2px solid #00416B")
                    $(this).css("border","2px solid #fff");
                });

          if (!$(".owl-item").hasClass("active_collection")) {
              $("li#All").parent().addClass("active_collection");
          }

         $('body.dashboard-index-index').find('#popupModal .modal-dialog #cart-form .modalContainer .bg-primary .col-md-2.col-sm-2').on('click', function() {
            $('.product-slider .owl-stage').find('.product-item').removeClass('pro_active');
          });
         


              $(document).on('input','.tabactive', function(e) {
                  if($(this).val().length <= 0)
                  {
                     $('#order_id').val('');
                     $('.editpodashboard').attr('po_number','');
                  }
              }) 

             
        

            $('.showslider').css({"pointer-events": "", "opacity": ""})
            $widget.element.on('click', '.col-logo', function (e) {
                $widget.collectionlogosclickevent($(this));
            });

            $(document).on('input', '#searchstyle', function (e) {
              this.value = this.value.replace(/[^a-zA-Z0-9 _]/g, '');
              if ($("#user-gird-conatiner").css('display') != 'block') {
                  $("#user-gird-conatiner").show();
                  $('.showslider').html("CLOSE VIEW")
              }
              var result = $widget._OnSearchSliderItem($(this), $widget);
              // clearTimeout(timeout);
               // timeout = setTimeout(function(){
                  if(result.length){
                      $.each(result,function(key,val){
                          recentsearch = val
                      })
                  }
                    // },2000);
            });

             
            $(".showslider").mouseover(function(){
              $(this).css("background","#0e4169");
              $(this).css("color","#fff");
            }); 
            $(".showslider").mouseout(function(){
              $(this).css("background","#fff");
              $(this).css("color","#0e4169");
            });  
            $(document).on('click', '.showslider', function (e) {
                  if ($("#user-gird-conatiner").css('display') == 'block') {
                     if (!$(".owl-item").hasClass("active_collection")) {
                        $("li#All").parent().addClass("active_collection");
                     }
                    $("#user-gird-conatiner").slideUp(1000);
                   $('.showslider').html("View All")
                   
                  }else{
                    var top = $("#user-gird-conatiner").height() + $("body").height();
                    $("#user-gird-conatiner").slideDown(1000);
                     $('.column.main').animate({
                        scrollTop: top,
                    },
                    1000)
                      $('.showslider').html("CLOSE VIEW")
                  }
                });
            $widget.element.on('click',".group-col", function () {
                $widget.categoryclick($(this));  
            });

      $(document).on('click', '.product-slider .owl-prev' ,function(e){
            var i = 0;
        $('.product-slider').attr('data-event', 'prev');
        
        if(!$('.product-slider .owl-item.active').hasClass('sticky')){ i = 1; }else{
          i = 0; $('.product-slider-sticky').remove()     
        }
        if(jQuery('.product-slider .sticky').prevAll('.owl-item.active').length >= 3 && jQuery('.product-slider .sticky.active').length === 0  ) {      
          i = 1;
        }
        if(jQuery('.product-slider .sticky.active').next('.owl-item:not(.active)').length == 1) {  i = 1; }
        
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

         $('.product-slider .owl-next').show();
          if(jQuery('.owl-item.active').prevAll('.owl-item:not(.active)').length < 1){
           $('.product-slider .owl-prev').hide();
          }


      })

      $(document).on('click', '.product-slider .owl-next' ,function(e){
        
          $('.product-slider .owl-prev').show();
          if(jQuery('.owl-item.active').nextAll('.owl-item:not(.active)').length < 1){
           $('.product-slider .owl-next').hide();
          }
      });

      $(document).on('click','.group-col',function(e){
            /*.product-slider.owl-carousel.owl-theme.owl-loaded*/

            var active = (jQuery('.product-slider-sticky').find('.product-item').attr('id')) || product_Active;       
            if($('#show_style').val()){
              active = $('#show_style').val();
            }

            jQuery('.product-slider .owl-stage div.product[id="'+active+'"]').addClass("pro_active").parent().addClass('sticky');
            if(jQuery('.product-slider .owl-stage div.product[id="'+active+'"]').length > 0){       
              $('.product-slider-sticky').css({'right':'unset','left':'0'});
              if($('.product-slider-sticky').length < 1){           
              var content = $('.owl-item.sticky').html();
              var newdiv = $("<div class='product-slider-sticky'>");
              newdiv.css( {'width':jQuery('.owl-item.sticky').width() ,'right':'unset','left':'0'}).html(content);
              $('.product-slider .owl-stage').after(newdiv);
            }

            }
            else
              $('.product-slider-sticky').remove();

            if($('.owl-item.sticky.active').length > 0)
              $('.product-slider-sticky').remove();
            
         })
          $(document).on('click', '.searchFromStyle', function (event, data) {
                return $widget._OnItemChange($(this), $widget, data);
            });

          // $( document ).ajaxSuccess(function( event, xhr, settings ) {
          //   if(xhr.statusText == 'success'){
          //     var ajaxresponce = xhr.responseJSON;
          //     if(typeof ajaxresponce != 'undefined'){
          //       var responce_length = Object.keys(ajaxresponce).length;
          //       if (responce_length > 0 && ajaxresponce.hasOwnProperty("is_view_stock")) {
          //         if(ajaxresponce.is_view_stock == '1'){
                   
          //           var responce = xhr.responseJSON,
          //               responce_order_id = atob(responce.base64_order_id),
          //               poconfig_data = $widget.options.poConfig,
          //               responce_ponumber = atob(responce.base64_ncp_id);

          //           var falg = _.filter(poconfig_data, function(value) {
          //               return value.OrderId === parseInt(responce_order_id);
          //           });

          //           if(falg.length > 0){
          //             // console.log("Po Exist..");
          //           }else{
          //             var item = {
          //               NumatCardPo : responce_ponumber,
          //               OrderId: parseInt(responce_order_id)
          //             }
          //             $('#select_existing').append('<option value="'+responce_ponumber+'" order_id = "'+responce_order_id+'">'+responce_ponumber+'</option>');
          //             $widget.options.poConfig.push(item);
          //             // console.log("Po Not Exist..");
          //           }

          //         }

          //       }
          //     }
          //   }
          // });

        },


        // _updateponumberedit: function($widget){
        //       var base_url = this.options.baseurl;
        //       var url = base_url+"/customerorder/customer/updateponumber";
        //       var order_id = $('#order_id').val()
        //       var newpo = $('.old_po').val();
        //           if(newpo.length >= 4)
        //           {

        //                jQuery.ajax({
        //                   url: url,
        //                   type: "POST",
        //                   data: {order_id : order_id , new_po : newpo},
        //                   showLoader: false,
        //                   cache: false,
        //                   success: function(response){
        //                       if(response)
        //                       {
        //                           var responce = response,
        //                               responce_order_id = responce.order_id,
        //                               poconfig_data = $widget.options.poConfig,
        //                               responce_ponumber = responce.po_number;

        //                           // console.log(poconfig_data);
        //                           var falg = _.filter(poconfig_data, function(value) {
        //                               return value.OrderId === parseInt(responce_order_id);
        //                           });

        //                           if(falg.length > 0){
        //                             // console.log("Po Exist..");
        //                             $widget.options.poConfig.forEach(function(item, index) {
        //                                 if (item.OrderId === falg[0].OrderId) {
        //                                     item.NumatCardPo = responce_ponumber;
        //                                 }
        //                             });

        //                             // console.log($widget.options.poConfig);

        //                           }

        //                            var ponumber = $('.tabactive').val(); 
        //                            $('option[order_id='+response.order_id+']').attr('value',response.po_number);
        //                            $('option[order_id='+response.order_id+']').html(response.po_number);


        //                       }
                             
        //                   }
        //               });
        //           }
        //           else
        //           {
        //                // $widget._adderror("PO Number must be a number or letter special character and at least 4 characters long.");  
        //                return false;
        //           }
              
        //   },

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


        // <--------------------------------------------------AB Collction slider [Start] ------------------------------------------------->
         /**
         * Add a Data to Po using Option1 and use for AJAX
         *
         * @returns {{order_id: text, : bool}}
         * @private
         */
         _OnSearchSliderItem: function($this,$widget){
            var val = $this.val(),
                res = [],
                temp = [];
           if(recentsearch){
              res.push(recentsearch)
           }         

            var arr = this.getRandomList();
            for (var i = 0; i < arr.length; i++) {
                if(arr[i]['Style'].substr(0, val.length).toUpperCase() == val.toUpperCase()){
                    res.push(arr[i]);
                }
            }
            if(res.length > 0){
              var datavalue = $widget.slidervalue(res)
                  $widget.resetslidervalue(datavalue["html"],datavalue["items"]);
                  // if($('.product-slider-sticky').length < 1){           
                  //   var content = '<div class="pro_active" style="/* height: -webkit-fill-available; */">   <div class="product details product-item-details"> <strong class="product name product-item-name"> <span class="product-item-link" title="Women\'s" bomber="" zipped="" jacket="" style="font-size: 11pt; color: gray; ">Displaying Search Result</span> </strong> </div> </div>';
                  //   var newdiv = $("<div class='product-slider-sticky'>");
                  //   newdiv.css( {'width': '29%','right':'unset','left':'0', 'display':'flex','justify-content':'center','align-items':'center'}).html(content);
                  //   $('.product-slider .owl-stage').after(newdiv);
                  // }
            }else{
              // jQuery('.product-slider .owl-stage').prepend('<div class="owl-item active" style="width:'+jQuery('.pro-slider .owl-item.active').width()+'px" >'+jQuery('.product-slider-sticky').html()+'<div>');
              // $('.product-slider-sticky').remove();
              var html = "<div class='item product product-item'>Item not Found.</div>"
              $widget.resetslidervalue(html,1)               
            }

            if($this.val().length <= 0){
              // jQuery('.product-slider .owl-stage').prepend('<div class="owl-item active" style="width:'+jQuery('.pro-slider .owl-item.active').width()+'px" >'+jQuery('.product-slider-sticky').html()+'<div>');
              // $('.product-slider-sticky').remove();
            }



            if(val != ''){
               temp = $widget.getProductbysku(val.toUpperCase());      
               if(temp.length > 0){
                 $(".column.main .product-item#"+val.toUpperCase()).parent().removeClass("sticky")
                 $(".column.main .product-item#"+val.toUpperCase()).removeClass("pro_active")
                 $(".column.main .product-item#"+val.toUpperCase()).parent().addClass('sticky');
                 $(".column.main .product-item#"+val.toUpperCase()).addClass("pro_active");

               }
            }
            return temp;
         },
          getProductbysku: function(sku){
            var current_product = this.getConfigurableProduct(this.options.jsonConfig , 'Style');
             var falg = _.filter(current_product , function (value) {
                      return value['Style'] === sku;
              });
             return falg;
          },


          _OnItemChange: function($this, $widget, data){

             var input = $('input#show_style'),            
                child_pro = this.getProductArray(input.val(),1),
                temp_parent_sku = input.val();
             if(child_pro.length > 0){               
                /* sikcy product view*/
                var current_product = $widget.getConfigurableProduct(this.options.jsonConfig , 'Style'),
                    tml_finalitems = _.filter(current_product, function(item) {
                        return item.Style == temp_parent_sku;
                    });

                var tempcollection = [];
                $.each(this.options.logos,function(key,val){tempcollection.push(val.name) })
                var category_collection = tml_finalitems[0].Collection;
                if(!$('.Collections #'+category_collection).hasClass('.active_collection') && !$('.product-slider #'+temp_parent_sku).length){
                    category_collection = category_collection == "Other" ? "All" : category_collection;                      
                    $('.Collections #'+category_collection  ).trigger('click');
                  }
                  var category_name = tml_finalitems[0].GroupName.replace(" ","_");
                if(tempcollection.indexOf(category_collection) >= 0){
                    if(!$("[child-id='"+category_name+"']").hasClass("active") && !$('.product-slider #'+temp_parent_sku).length){
                      $("[child-id='"+category_name+"']").trigger('click');
                    }
                }else{
                  $(".pro-slider").attr("sticky","false");
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

                    }else{
                      $('.product-slider-sticky').remove();
                    }                   

                    if($('.owl-item.sticky.active').length > 0)
                      $('.product-slider-sticky').remove();
              }
        },
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
                             obj["UnitPrice"] = proval.UnitPrice;
                             obj["Gender"] = proval.Gender;
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
            $("#user-gird-conatiner .title").css("display","Block")
            var $widget = this
            var logos = this.options.logos; 
            var baseurl = this.options.baseurl;
            var html="";
            // html  += '<li class="col-logo" id="All"><h1 class="collection-logo">All</h1></li>'

            html  += '<li class="col-logo" id="All"><h1 class="collection-logo">All Collections</h1></li>'
            $.each(logos, function(key,logos) {
                html += "<li class='col-logo' id ="+logos.name+">"                    
                html += logos.image ? "<img src="+logos.image+" alt="+logos.name+">" : 
                    "<h1 class='collection-logo'>"+logos.name+"</h1>"
                if(logos.name =="Universal_STRETCH"){
                  html += "<img src='"+baseurl+"/pub/media/collection/image/U/n/Universal_Stretch_Logo_Product11.png' class='active-us' Style='display:none'>" 
                }                    
                html  +='</li>'
            })

            var coll = $(".Collections").owlCarousel({
                    loop:false
                    ,autoplay:false
                    ,autoplayTimeout:30000                        
                    ,margin:10
                    ,nav:false
                    ,dots:false
                    ,responsive: { 0: { items: 2 }, 600: { items: 6 }, 1000: { items: 6 } }
                }); 
            coll.trigger('replace.owl.carousel', html);
            coll.trigger('refresh.owl.carousel');

            setTimeout(function () {
                // $("li#Pro").parent().addClass("pro");
                // $("li#All").parent().addClass("pro");
                // $("li#Sivvan").parent().addClass("pro");
                // $("li#Pro").css("width", "50px");                
                $widget.addgruopname($widget._gruopnamcollection())

            }, 150 );
            setTimeout(function(e){ $(".product-group-slider").find('.group-col').first().trigger("click"); },160);
            $(".searchstyledata").css("display","")
           
        },



        /*
        * collection logo click event.
        *
        * on click set prouct collection group name.
        */

        collectionlogosclickevent: function($this) {
            var $widget = this
            var gruopname = this.options.slidercollections;
            if ($("li#All").parent().hasClass("active_collection")) {
                activecol = [];
            }
            $this ? $this.parent().toggleClass("active_collection") : null;
            var vid = $this ? $this.attr("id") : null;
            if (vid != "All") {
                $("li#All").parent().removeClass("active_collection");
            } else if (vid == "All" && $("li#All").parent().hasClass("active_collection")) {
                $(".owl-item").removeClass("active_collection");
                $("li#All").parent().addClass("active_collection");
                var logos = this.getcollectionlogo();
                logos.push("Other")
                activecol = logos.reverse();
            }
            if (vid != "All" && $this ? $this.parent().hasClass("active_collection") : null) {
                activecol.push(vid)
                activecol = $widget.uniquevalue(activecol);
            } else {
                activecol = $.grep(activecol, function(value) {
                    return value != vid;
                });
            }
            var temp = []
            $(".owl-item.active_collection.active").each(function() {
                var collectionvalue = $(this).find(".col-logo").attr("id");
                $.each(gruopname[collectionvalue], function(key, val) {
                    temp.push(val.replace(" ", "_"));
                })
            })
            var unique = $widget.uniquevalue(temp);
            if (unique.length > 0) {
                $widget.addgruopname(unique)
            } else {
                var group = $widget._gruopnamcollection()
                $widget.addgruopname(group)
            }
            if (activecat.length) {
                $(".product-group-slider span").each(function() {
                    var gid = $(this).attr("child-id")
                    if (_.contains(activecat, gid)) {
                        $(this).trigger("click");
                    }
                })
            }

            if (!$(".product-group-slider span").hasClass("active")) {
                $("span.group-col[child-id = 'All']").trigger("click")
            }
            if (!$(".product-group-slider span.group-col").hasClass("active")) {
                var result = $widget.getslidervalueusingcollection();
                var datavalue = $widget.slidervalue(result)
                $widget.resetslidervalue(datavalue["html"], datavalue["items"])
            } else {
                var result = $widget.getslidervaluewithactivecat();
                var datavalue = $widget.slidervalue(result)
                $widget.resetslidervalue(datavalue["html"], datavalue["items"])
            }
        },
        // addgruopname: function(unique){
        //         var html = ""
        //         var html = "<span class='group-col' child-id='All'><h1>All</h1></span>"
        //         for (var i = 0; i < unique.length; i++) {
        //               html += "<span class='group-col' style='width: fit-content !important' child-id="+unique[i]+"><h1>"+unique[i].replace("_"," ")+"</h1></span>" 
        //             }
                     
        //         var catslider = $(".product-group-slider").owlCarousel({
        //             loop:false
        //             ,autoplay:false
        //             ,autoplayTimeout:30000                        
        //             ,margin:10
        //             ,nav:true
        //             ,dots:false
        //             ,autoWidth:true
        //             ,navText:['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>'
        //             ,'<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>']
        //         });
        //         catslider.trigger('replace.owl.carousel', html); 

        //         var $stage = $(".product-group-slider .owl-stage"),
        //             stageW = $stage.width(),
        //             $el = $('.product-group-slider .owl-item'),
        //             elW = 15;

        //         $el.each(function() {
        //             elW += $(this).width()+ +($(this).css("margin-right").slice(0, -2))
        //         });

               
        //         if ( stageW < $(window).width()) {                
        //             $stage.css('min-width',elW);
        //             console.log('asdasdasdas');
        //         };


        //         catslider.trigger('refresh.owl.carousel'); 

        //         // this.changeOrder($(".product-group-slider"));
        // },  


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
            $('.product-slider').css('width',jQuery('.featured-pro').width() - 50)
            owlproduct = $('.product-slider').owlCarousel({
                     loop:true
                    ,autoplay:true
                    ,autoplayTimeout:3000
                    ,margin:0
                    ,rewind: true
                    ,nav:true
                    ,dots:false
                    ,navText:['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>'
                    ,'<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>'],
                    responsive:{
                        0:{
                            items:1
                        },
                        375:{
                            items:2
                        },
                        425:{
                            items:3
                        },
                        768:{
                            items:4
                        },
                        800:{
                            items:4
                        },
                        1000:{
                            items:5
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
        categoryclick: function($this) {
            var $widget = this
            var vid = $this ? $this.attr("child-id") : null;
            if (vid == "All" && !$("span.group-col[child-id = 'All']").hasClass("active")) {
                activecat = []
                $(".product-group-slider span").removeClass("active");
            }
            $this ? $this.toggleClass("active") : null;
            if (vid != "All" && $this ? $this.hasClass("active") : null) {
                $("span.group-col[child-id = 'All']").removeClass("active")
                activecat.push(vid)
                activecat = $widget.uniquevalue(activecat);
            } else {
                activecat = jQuery.grep(activecat, function(value) {
                    return value != vid;
                });
            }
            // console.log("vid",vid)
            if (vid == "All" || (activecat.length == 0 && vid == null)) {
                var value = $widget.allcategoryfilter();

                if (value.length == 0) {
                    var html = "<div class='item product product-item'>Item not Found</div>"
                    $widget.resetslidervalue(html, 0)
                }
                var datavalue = $widget.slidervalue(value)
                $widget.resetslidervalue(datavalue["html"], datavalue["items"])
            } else {
                var value = $widget.activeclassvalue();

                if (value.length == 0) {
                    var html = "<div class='item product product-item'>Item not Found</div>"                    
                    $widget.resetslidervalue(html,0)
                } else {
                    var datavalue = $widget.slidervalue(value);
                    $widget.resetslidervalue(datavalue["html"], datavalue["items"]);
                }
            }
        },
        resetslidervalue: function(prohtml,items){
                if(prohtml){
                    $(".product-slider .owl-stage").html('');
                    if(items < 1){
                        owlproduct.trigger('replace.owl.carousel', prohtml,{
                          options: { loop: true,mouseDrag:false}
                        });
                        owlproduct.trigger('refresh.owl.carousel');
                        $(".product-slider  .owl-controls").css("display","none")
                        $(".product-slider .owl-stage").addClass("jcenter");
                        var top = $('.owl-stage.jcenter .owl-item.active').height()/2 ;
                        $(".column.main .product-item").css("padding-top","0px");
                        $(".column.main .product-item").css("margin-top",top+"px")
                    }else if(items >= 1 && items <= 5){
                        owlproduct.trigger('replace.owl.carousel', prohtml,{
                          options: { loop: true,mouseDrag:false}
                        });
                        owlproduct.trigger('refresh.owl.carousel');
                        $(".product-slider  .owl-controls").css("display","none")
                        $(".product-slider .owl-stage").addClass("jcenter");
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
                        $(".product-slider .owl-controls").css("display","")
                        $(".product-slider .owl-stage").removeClass("jcenter");
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
            // return result.length > 0 ? result : this.getallproductcollectionwise()
            var filteroption = this._morefilter(result);
            return filteroption;
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
            // return result
            var filteroption = this._morefilter(result);
            return filteroption;
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
            // return  result
            var filteroption = this._morefilter(result);
            return filteroption;
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
            // return result
            var filteroption = this._morefilter(result);
            return filteroption;
        },
         _resetfilter: function() {
            fromprice = 0;
            toprice = max;
            this.categoryclick();
            if($('#show_style').val()){
                $(".searchFromStyle").trigger("click");
            }
             $('.js-range-slider').data("ionRangeSlider").update({
                from: 0,
                to: max,
             });
        },
        _morefilter: function(result) {
           var $widget = this;
            var genderfilter = [];
             var data = [];
            $(".gender-filter input[type='checkbox']").each(function() {
                if ($(this).prop("checked") == true) {
                    genderfilter.push($(this).val())
                }
            })
            result = result.filter(x => x.UnitPrice >= parseFloat(fromprice) && x.UnitPrice <= parseFloat(toprice));
            result.forEach(function(item, index) {
                data = $widget.getProductArray(item['Style'],1)
                data.forEach(function(item1){
                    if(!(item1.UnitPrice >= parseFloat(fromprice) && item1.UnitPrice <= parseFloat(toprice))){
                        result = result.filter(function( obj ) {
                          return obj.Style !== item['Style'];
                        });
                    }
                })
            })
            if (genderfilter.length != 0) {
                var fr = result.filter(item => genderfilter.includes(item.Gender));
                return fr
            } else {
                return result
            }
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
                                                      
                sliderhtml += "<div class='item product product-item' gname="+val.GroupName+" id="+val.Style+"> <a href=javascript:' class='buyNowBtnMain product-item-link' data-toggle='modal' data-target='#popupModal' id="+val.Style+"><span class='product-image-wrapper' style='padding-bottom: 133.94495412844%; width: auto;'> <img class='img-responsive owl-lazys' src='"+(val.U_WImage1 ? val.U_WImage1: placeholder)+"' width='218' height='292' alt="+ItemName+" /></span> </a> <div class='product details product-item-details'><div class='show-product-dis-box'><span>"+parentid+"</span></div> <div class='show-product-dis-box-more'> <span> <lable>Style No.</lable> </span> <span>"+val.Style+"</span> </div><div> </div> </div> </div>";                        
            });

            obj["html"] = sliderhtml
            obj["items"] = res.length
            return obj;
        },
// <---------------------------------------- AB collection slider [Done] ----------------------------------------------------------->
    
});

    return $.mage.SwatchRenderer; //list
});
    
