define([
    'jquery',
    'mage/template',
    'mage/storage',
    'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
    "text!Sttl_Customerorder/template/shipment.html",
    'text!Sttl_Customerorder/template/dashbord_productdata_popup.html',
    'text!Sttl_Customerorder/template/discount_popup.html',
    'mage/validation/validation'
], function ($, mageTemplate,storage, invdata, shiptracking,popuptemplate, discountPopup) {
    'use strict';

    var old_inv_data = '',
        old_order_data = '',
        shiptrackingdata = false,
        simpleproduct = [],
        configurationproduct = [],
        sizeoption = [];

    $.widget('mage.DashboardRenderer', {
        options: {
            useAjax: false,
            jsonConfig: {},
            baseurl: '',
            trackingdata: {},
            customer_order_data: {},
            customer_data: {},
            orderLimitData: {}
        },
        _init: function () {
            var $widget = this;
            this._EventListener();
            this._saveLastcontent();
            this.options.jsonConfig = invdata;
            this.options.baseurl = window.location.origin;

        },
        _create: function() {
          var $widget = this;
          // this._prepareDiscountPopupData($widget);
          // this._prepareDataAfterPageLoad($widget);
          // if($widget.options.action === "customerorder-customer-dashboard"){
                $widget.dashboardpagequickview();
          // }
        },

        getShippingInfobyDocEntry: function(SoDocEntry) {
            SoDocEntry = parseInt(SoDocEntry);
            var falg = _.filter(this.options.trackingdata, function(value) {
                    return value['SODocEntry'] === SoDocEntry;
                });
            return falg;
        },

        _EventListener: function(){

            var $widget = this;

            $(document).ready(function(){
              $('#order_stats option:first').attr('selected','selected');
            })
            $(document).on('click', '.trackPack .nav-link', function (event) {
              var targetsection = $($(this).attr('href')),
                  selector = [];
                  selector.push(targetsection.find(".trackingStatusBar .step"))

              var time = 200;
              $('.trackingStatusBar .shipStatus').addClass('move');
              $('.trackingStatusBar .shipStatus.move').css('display','none');
              $('.trackingStatusBar .step').removeClass('add_animation');
              selector.forEach(element => 
                element.each(function(index) {
                    var $parent = $(this);
                    var dd= index;
                    setTimeout( function()
                      { 
                      if(dd==element.length-1){
                        $('.trackingStatusBar span.shipStatus.move').css('display','block');
                      }
                        $parent.addClass('add_animation'); }, 
                    time)
                    time += 500;
                    
                }),              

              );
            });
            $(document).on('change','#order_stats', function (e) {
                  if(shiptrackingdata==true){
                    $(".tracking_disabled").removeClass("tracking_disabled");
                  }
            });
           
            $(document).on('click','#shipttreckdata', function (e) {
                $(".row.shipdatainfo").html("");
            });
            $('#shipment-track').on('shown.bs.modal', function (event) {
                $(".row.shipdatainfo").html("");
                $('.loading-mask').remove();
                var button = $(event.relatedTarget),
                    recipient = button.attr('data-num'),
                    orderTrackingData = $widget.getShippingInfobyDocEntry(recipient),
                    currentshippingPopup = $("#popup_tracking_id").val(),
                    currentshippingPopup = parseInt(currentshippingPopup),
                    recipient = parseInt(recipient);

                if(currentshippingPopup != recipient){
                  $('.loading-mask').remove();
                  $('.orderItem-loader').css('display','block');
                    $.ajax({
                        url: $widget.options.baseurl + "/customerorder/dashboard/gettrackinfojs",
                        type: "POST",
                        data: { order_track_data: orderTrackingData },
                        showLoader: false,
                        cache: false,
                        success: function(response) {
                          $('.orderItem-loader').css('display','none');
                          var shiptrack = mageTemplate(shiptracking, {
                              shipData:response
                          });
                          $(".row.shipdatainfo").html(shiptrack);
                          $('.trackingStatusBar .shipStatus').addClass('move');
                          $('.trackingStatusBar .shipStatus.move').css('display','none');
                          
                         var selector = [];
                          selector.push($("#v-pills-tabContent").find(".tab-pane.show.active .step"))

                          $('.trackingStatusBar .step').removeClass('add_animation');
                          var time = 200;
                          selector.forEach(element => 
                            element.each(function(index) {
                                var dd= index;
                                var $parent = $(this);
                                setTimeout( function(){ 
                                  if(dd==element.length-1){
                                    $('.trackingStatusBar span.shipStatus.move').css('display','block');
                                  }
                                  $parent.addClass('add_animation'); 
                                }, time)
                                time += 500;
                            })
                          );
                        },
                    });
                }
            });


            $widget.element.on('click', '.recent-order.inv .action.save', function (e) {
                return $widget._OnChangeInvStatus($(this), $widget);
            });


            // $(document).on('change','#order_stats', function (e) {
            //     var optionSelected = $("option:selected", this);
            //     var valueSelected = this.value;

            //     $(".order_search").val("")
            //     if(valueSelected == 'recent_order'){
            //     	$(this).parents('.recent-order').find('.order_table tbody').html(old_order_data); return false;
            //     	return false;
            //     }


            //     $widget._OnChangePochange($(this),$widget,valueSelected);
            // });

            // $widget.element.on('click', '.recent-order.order .action.save', function (e) {
            //     return $widget._OnChangePochange($(this),$widget);
            // });

            // $(document).on('input','.order_search',function(){
            //     return $widget._OnChangePochange($(this),$widget);                
            // });

             
              $(".order_search").keydown(function(e) {
                var is_able_to_search = 0;
                var res = $widget.options.isAbletoSearch;
                if(res == '1'){
                  is_able_to_search = 1;
                }
                var char = $(this).val().trim().length;
                var firstspace = $(this).val().substring(0,1);

                if(is_able_to_search && !searching_order){                
                  if(e.keyCode == 13 && char >= 5 && firstspace != ' ')
                  {
                      searching_order = true;
                      var searchval = $(".order_search").val();
                      $widget.startLoader($(".recent-order.order"));
                      $.ajax({
                          url: $widget.options.baseurl + "/customerorder/dashboard/getSearchRes",
                          type: "POST",
                          data: { is_search: 1,search:searchval},
                          showLoader: false,
                          cache: false,
                          success: function(response) {
                            searching_order = false;
                            $widget.stopLoader($(".recent-order.order"));
                            if(!response.errors){
                              var orderData = response.order_data,
                                  itemnot = '<tr class= "item-not-found" colspan="4"><td style="text-align:center">Item Not Found</td><tr>',
                                  input = ''
                               for (var i = 0; i < orderData.length; i++) {
                                  input += $widget._RenderAutoOrderItemTr(orderData[i],$widget)      
                                }
                                if(orderData.length <= 0){
                                  $('.order_table tbody').html(itemnot);
                                }else{
                                  $('.order_table tbody').html(input);
                                }
                            }
                          }
                      });
                  }
                  if(e.keyCode == 13){

                      if(char < 5  || firstspace == ' ')
                      {
                        $('.errormessage').html('The search string must be at least 5 characters long and can not start with a space')
                        setTimeout(function(){
                          $('.errormessage').html('');
                        },3000);
                      }
                    }
                }
             });


             $("input[name=po_number]").keydown(function(e) {
                // alert(e.keyCode);
                if(e.keyCode == 9)
                {
                   e.preventDefault();
                }
                if(e.keyCode == 13 || e.keyCode == 9)
                {
                    $('.recent-order.order .action.save').trigger('click');

                }


             });

            $(document).on('input','#style_number_search',function(){
                 var colorval = $('#inv_search_color').val();
                 var arr = invdata;
                 var optioncolors = [];
                 var res =[];
           
                 var val = $(this).val(); 
                 if(val.length <= 0){
                  $("#inv_search_color").val("")
                 }
                  for (var i = 0; i < arr.length; i++) {
                    // console.log(arr[i]);
                   if(arr[i]['ItemCode'].substr(0, val.length).toUpperCase() == val.toUpperCase()){
                        res.push(arr[i]);
                   }
                }
                 
                 res.filter(function (index,values){
                    var inv_search = $('#style_number_search').val();

                    if(index['ItemCode'].substr(0, val.length).toUpperCase() == inv_search.toUpperCase())
                    {
                        optioncolors.push(index['Color']);
                    }
                    else
                    {
                        optioncolors = [];

                    }
                 });
                  var unicoptioncolors = $widget.uniquevalue(optioncolors);
                  var dropdown = [];
                  unicoptioncolors.filter(function(key,value){

                     dropdown += "<li class='autosearchcolorli'>"+key+"</li>"
                  })
                  $('.autosearchcolor').html(dropdown);
                  var colortrue = dropdown.indexOf(colorval);
                  if(colortrue < 0)
                  {
                    $('#inv_search_color').val('');
                    dropdown = [];
                  }

                return $widget._OnChangeInvStatus($(this), $widget);
                
            })
            $(document).on('input','#inv_search_color',function(){
                return $widget._OnChangeInvStatus($(this), $widget);
            })
            $(document).on('click','.autosearchcolorli',function(){
                $('#inv_search_color').val($(this).html());
                $('.autosearchcolor').fadeOut();
                return $widget._OnChangeInvStatus($(this), $widget);
            })
            var currentFocus;
            $(document).on('focus',"#inv_search_color",function(){
                var lenth = $('.autosearchcolor').children().length
                var val = $('#style_number_search').val(); 
                if(lenth > 0 && val != '')
                {
                    $('.autosearchcolor').fadeIn();
                }
               currentFocus = -1;           
                

            })

           $(document).on('focusout',"#inv_search_color",function(){
                $('.autosearchcolor').fadeOut();
            })
           $(document).on('mouseover','.swatch-option-color',function(){

           var lable = $(this).attr('aria-label');

            $('swatch-attribute-color-selected-option').html(lable); 
           
            });
           $(document).on('click','#quickviewpopupid',function(){

                var demo = $widget.getProductArray('2900',1);

                var popupdeta = mageTemplate(popuptemplate, {
                    data:demo,
                });
                // $('#featuredproductpopup').toggleClass('featuredproductpopupdisplay');
                // $('#featuredproductpopup').css({'display':'block'});
                $('.featuredproductpopupdata').html(popupdeta);

             
           })
             $(document).on('change',"#inv_search_color",function(){
                 var lenth = $('.autosearchcolor').children().length
                if(lenth > 0)
                {
                    $('.autosearchcolor').fadeIn();
                }
            })

            $(document).on('mouseenter','.forhovereffact',function() {
              if(!$(this).hasClass('blure')){

                $(this).find('.quickviewpopup1 img').addClass('slideritemtransition');
              }
            });

            $(document).on('mouseleave','.forhovereffact',function() {
                $(this).find('.quickviewpopup1 img').removeClass('slideritemtransition');
            });

            $(document).on('hover','.pro-slider .product-item',function() {
                $(this).find('.img-responsive').toggleClass('slideritemtransition');
           
            });

               
             $(document).on('input',"#inv_search_color",function(){
                 var lenth = $('.autosearchcolor').children().length
                if(lenth > 0)
                {
                    $('.autosearchcolor').fadeIn();
                }
            })
           // $(document).on('click',".trackingPopup .close.mfp-close-inside",function(){
           //      $('.shipdatainfo').html("")
           //  })
    
              $("#inv_search_color").on("keydown", function(e) {  
                   var x1 = $(".autosearchcolor");                
                    if (x1) var x = $(".autosearchcolor .autosearchcolorli");                   
                    if (e.keyCode == 40) {
                      currentFocus++;
                      addActive(x);
                    } else if (e.keyCode == 38) { //up
                      currentFocus--;
                      addActive(x);
                    } else if (e.keyCode == 13) {
                      e.preventDefault();
                      if (currentFocus > -1) {
                        if (x) x[currentFocus].click();
                      }
                   }
                   
                   // var pos = $(".autosearchcolor-active").position().top;
                   // $(this).next('.autosearchcolor').animate({scrollTop:pos}, 10, 'swing');

                  if (e.keyCode == 40) {
                      var ul = $(".autosearchcolor"); 
                      var li = $('.autosearchcolor-active:first');
                      if(li.offset().top > (ul.offset().top + ul.height()) || li.offset().top < ul.offset().top)
                      {                               
                          x1.scrollTop(top);
                          x1.scrollTop($('.autosearchcolor-active').position().top-$(".autosearchcolor").height()+$(".autosearchcolorli").height());    

                       
                       }             
                   }
                   if(e.keyCode == 38)
                   {
                       var ul = $(".autosearchcolor"); 
                      var li = $('.autosearchcolor-active:first');
                      if(li.offset().top > (ul.offset().top + ul.height()) || li.offset().top < ul.offset().top)
                      {                               
                          x1.scrollTop(top);
                          x1.scrollTop($('.autosearchcolor-active').position().top);    

                       
                       }  
                   }

              });
              function addActive(x) {
                  if (!x) return false;
                  removeActive(x);                    
                  if (currentFocus >= x.length) currentFocus = 0;
                  if (currentFocus < 0) currentFocus = (x.length - 1);
                      x[currentFocus].classList.add("autosearchcolor-active");
              }

              function removeActive(x) {                  
                  for (var i = 0; i < x.length; i++) {                        
                      x[i].classList.remove("autosearchcolor-active");
                  }
              }              
        },



        catalogslidervalue: function($widget,slidervalue){
           $('.catalogslider').css('width',jQuery('.catalogslider-div').width() -50 + "px")
            var html = '<ul class="catalogslider">'
            $.each(slidervalue,function(key,value){
            var imag  = $widget.options.baseurl+'/pub/media/'+value.small_image
              html +=  '<li> <a href="'+value.custom_url+'" download><img src="'+imag+'"></a> <h6><a href="'+value.custom_url+'" target="_blank">'+value.name+'</a></h6> </li>'
            })
            html +="</ul>"
            $('.catalogslider-div').html(html);
           $(".catalogslider").owlCarousel({
              loop:true
              ,autoplay:true
              ,autoplayTimeout:3000
              ,nav:true
              ,autoWidth:true
              ,navText:['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>'
                            ,'<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>']
              ,dots:false
              ,items:1
            }); 
        },

        dashboardpagequickview: function(){
            var $widget = this
            var url = $widget.options.baseurl+"/customerorder/customer/quickview";
            $.get(url, function(data, status){
                if(data){
                    var produs = data;
                    simpleproduct = produs[0]['simapleproduct'];
                    sizeoption = produs[0]['sizeoption'];
                    configurationproduct = produs[0]['configurationproduct'];
                    // $(".quickviewpopup1").css("pointer-events","")
                    // $(".grid-item.grid-sizer.forhovereffact").removeClass("blure");
                    $widget.setfeaturedpro($widget);
                } 
              });
        },
        setfeaturedpro: function($widget){
            var html = ''
            $.each(configurationproduct,function(key,value){
               html +=  '<div class="grid-item grid-sizer forhovereffact"> <div class="featureborder"><a href="'+value.producturl+'"> <img src="'+$widget.options.baseurl+"/pub/media/catalog/product"+value.productimgurl+'"></a></div></div>'
            })
            $(".featured-row .orderItem-loader").hide();
            $(".featured-pro").append(html);
        },

        _prepareDataAfterPageLoad($widget){

          var customerdata = $widget.options.customer_data,
              customer_tier = customerdata.Tier,
              dis_program = customerdata.Program,
              order_data_url = $widget.options.baseurl + "/customerorder/dashboard/getOrderData",
              data_url = $widget.options.baseurl + "/customerorder/dashboard/gettrackinfojs";
             
	            $.ajax({
	                url: data_url,
	                type: "POST",
	                data: { is_fetch_data: 1},
	                showLoader: false,
	                cache: false,
	                success: function(response) {
	                  if(response.trackingData){
                      shiptrackingdata = true;
	                    $(".tracking_disabled").removeClass("tracking_disabled",700);
	                    $widget.options.trackingdata = response.trackingData;
	                  }
	                },
	            });
        },

       uniquevalue: function(arrry = ''){
            return arrry.filter(function(itm, i, a) {
                return i == a.indexOf(itm);
            });
        },
        _saveLastcontent: function(){
            old_inv_data = $('.recent-order.inv .inventory_table tbody').html();
            old_order_data = $('.recent-order.order .order_table tbody').html();
        },
            getProductArray: function(sku , option){
               var key = option == 1 ? 'Style' : 'ItemCode',
                   falg = _.filter(this.options.jsonConfig , function (value) {
                        return value[key] === sku;
                });
               return falg;

            },
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
            return otherNumbers.replace(/\B(?=(\d{3})+(?!\d))/g, ",") + lastThree + afterPoint;
        },
        _RenderAutoItemTr: function(config){
            var $widget = this;
            var configETA1relpace = config.ETA1.replace(/ /g,"T");
            var temp_eta_date = new Date(configETA1relpace),
                temp_eta_day = temp_eta_date.getDate(),
                eta_date = (temp_eta_date.getMonth()+1 + "/" + temp_eta_day + "/" + temp_eta_date.getFullYear().toString().substr(-2));
            var etaData = $widget._renderETAs(config, $widget),
                currentdate = new Date();            
            currentdate.setDate(currentdate.getDate() - 7);
              var searchstyle = $('#style_number_search').val();
                  searchstyle = searchstyle.toUpperCase();
                var lenth = searchstyle.length;
                var configstyle = config.Style.toString();
                var arr = searchstyle.split('');
                var avoid = [];
                var avoidcolor =[];
                var avoidsize =[];
                arr.filter(function(index,value){
                    
                    if(configstyle.indexOf(index) != -1)
                    {
                        avoid.push(index);
                    }
              
                })
                var finalstring = '';
                avoid.filter(function(index,value){
                    finalstring += index;
                })
                var mystring = configstyle.replace(finalstring,'')
                  
                

                var qty = (config.ActualQty >= 500) ? '500+' : parseInt(config.ActualQty);
            if(temp_eta_date > currentdate){ 
                return '<tr>'+
                        '<td><span class="stylecolor">'+finalstring+'</span>'+mystring+'-'+config.ColorCode+'-'+config.Size+'</td>'+
                        '<td>$'+config.UnitPrice+'</td>'+
                        '<td><b>'+ qty+'</b></td>'+
                        '<td class="inventory_table_ETA" eta_js_tool="'+etaData+'">ETA '+eta_date+'</td>'+
                        '</tr>';
            }else{

                return '<tr>'+
                        '<td><span class="stylecolor">'+finalstring+'</span>'+mystring+'-'+config.ColorCode+'-'+config.Size+'</td>'+
                        '<td>$'+config.UnitPrice+'</td>'+
                        '<td><b>'+ qty+'</b></td>'+
                        '<td>-</td>'+
                        '</tr>';
            }

        },

        _renderETAs: function(_config, $widget){


            var value = _config;

            var eta1 = value.ETA1.replace(/ /g,"T");
            var eta2 = value.ETA2.replace(/ /g,"T");
            var eta3 = value.ETA3.replace(/ /g,"T");

            var  ETA1_DATE = new Date(eta1),
                ETA2_DATE = new Date(eta2),
                ETA3_DATE = new Date(eta3),
                currentdate = new Date(),
                eta_avail_to_show = false,
                rendredETAs = '';
            currentdate.setDate(currentdate.getDate() - 7);
            var eta_date = {
                'ETA1' : {
                    "Date" : ETA1_DATE,
                    "Qty" : value.EtaQty1
                },
                'ETA2' : {
                    "Date" : ETA2_DATE,
                    "Qty" : value.EtaQty2
                },
                'ETA3' : {
                    "Date" : ETA3_DATE,
                    "Qty" : value.EtaQty3
                },

            };
            if(!_.isEmpty(eta_date)){
                var eta_avail_to_show = false;
                _.each(eta_date, function(value, index) {
                    var current_itration_date = value.Date;
                    if(value.Date > currentdate && current_itration_date.getTime() && value.Qty > 0 && eta_avail_to_show == false){
                        eta_avail_to_show = true;
                    }
                });
            }
            var eta_count_top = 0;

            if(eta_avail_to_show){
             _.each(eta_date, function(value, index) {
                var current_itration_date = value.Date,
                    temp_eta_date = value.Date,
                    temp_eta_day = temp_eta_date.getDate(),
                    temp_eta_month = temp_eta_date.getMonth()+1;

                if(temp_eta_day < 10 && temp_eta_day > 0){
                    temp_eta_day = "0"+temp_eta_day;
                }

                if(temp_eta_month < 10 && temp_eta_month > 0){
                    temp_eta_month = "0"+temp_eta_month;
                }

                var ETA_Actual_print = (temp_eta_month + "/" + temp_eta_day + "/" + temp_eta_date.getFullYear().toString());

                var eta_qty_show = value.Qty;
                if(eta_qty_show > 500){
                    eta_qty_show = '500+';
                }

                if(value.Date > currentdate && current_itration_date.getTime() && value.Qty > 0 && eta_count_top==0){
                    rendredETAs = "<span class='eta-date'>";
                    eta_count_top++;
                }
                if(value.Date > currentdate && current_itration_date.getTime() && value.Qty > 0 ){
                    var qty = (value.Qty > 500) ? '500' : value.Qty;
                    if(currentdate < value.Date && value.Date < new Date()){
                        rendredETAs += "<p class='eta_date_product_option_span eta_date_past_date'> <svg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 286.054 286.054' style='enable-background:new 0 0 286.054 286.054;'' xml:space='preserve'> <g> <path style='fill:#A70417;'' d='M143.027,0C64.04,0,0,64.04,0,143.027c0,78.996,64.04,143.027,143.027,143.027 c78.996,0,143.027-64.022,143.027-143.027C286.054,64.04,222.022,0,143.027,0z M143.027,259.236 c-64.183,0-116.209-52.026-116.209-116.209S78.844,26.818,143.027,26.818s116.209,52.026,116.209,116.209 S207.21,259.236,143.027,259.236z M143.036,62.726c-10.244,0-17.995,5.346-17.995,13.981v79.201c0,8.644,7.75,13.972,17.995,13.972 c9.994,0,17.995-5.551,17.995-13.972V76.707C161.03,68.277,153.03,62.726,143.036,62.726z M143.036,187.723 c-9.842,0-17.852,8.01-17.852,17.86c0,9.833,8.01,17.843,17.852,17.843s17.843-8.01,17.843-17.843 C160.878,195.732,152.878,187.723,143.036,187.723z'/> </g> </svg>";
                        rendredETAs += "<span>"+ETA_Actual_print+"  -  <span class='eta_dash_qty'>"+eta_qty_show+"</span></span></p>";
                    }else{
                        rendredETAs += "<p class='eta-date-list'>"+ETA_Actual_print+"  -  <span class='eta_dash_qty'>"+eta_qty_show+"</span></p>";
                    }
                }

             });
             if(eta_count_top > 0){
                rendredETAs += "</span>";
             }
            }

            return rendredETAs;
        },

        _RenderAutoOrderItemTr: function(config , $widget){
            var id_b_e = btoa(config.Id);
            if(config.dataFrom == 'V')
            {
                id_b_e = btoa(config.DocNum);
            }
            var redirect_url = $widget.options.baseurl+"/customerorder/customer/orderview/id/"+id_b_e+"/df/"+btoa(config.dataFrom);
            var demourl = $widget.options.baseurl+'/customerorder/customer/neworder/id/'+btoa(config.Id)+'/ncp/'+btoa(config.NumatCardPo)+'#block-title';
            var status_tag = '<td class='+config.DocStatus+'>'+config.DocStatus+'</td>';
            if(config.DocStatus == 'PartiallyShipped')
            {
                var temp_PartiallyShipped = 'Partially Shipped';
                status_tag = "<a href='#' data-backdrop='false' data-toggle='modal' data-target='#shipment-track' id='shipttreckdata' data-num='"+config.DocEntry+"'>"+temp_PartiallyShipped+"</a>";
            }
            if(config.DocStatus == 'Shipped' || config.DocStatus == 'DocStatus'){
                status_tag = "<a href='#' data-backdrop='false' data-toggle='modal' data-target='#shipment-track' id='shipttreckdata' data-num='"+config.DocEntry+"'>"+config.DocStatus+"</a>";
            }
            if(config.DocStatus == 'Draft')
            {
                status_tag = "<a href='"+demourl+"'><i class='fa fa-pencil editdraftdashboard'></i></a><span> "+config.DocStatus+"</span>";

            }
            if(config.DocStatus == 'processing' || config.DocStatus == 'Processing')
            {
              status_tag = 'Processing';
            }
            if(config.DocStatus == 'Submitted'){
              status_tag = 'Submitted';
            }

            var ponumbertable  = "";
            if(config.NumatCardPo == '' || config.NumatCardPo == null)
            {
              var ponuber = config.DocNum;
              if(ponuber.length > 8){
                  ponuber = ponuber.substring(0, 8)+"...";              
              }
              // ponumbertable = "Order# "+ponuber;
              ponumbertable = ponuber;
            }
            else
            {
              var ponuber = config.NumatCardPo;
              if(ponuber.length > 8){
                  ponuber = ponuber.substring(0, 8)+"...";              
              }
              ponumbertable = ponuber;
            }


            return '<tr>'+
                    '<td><a class="order-redirect-url" href='+redirect_url+'>'+ponumbertable+'</a></td>'+
                    '<td>$'+$widget._convertcurrency(parseFloat(config.DocTotal).toFixed(2))+'</td>'+
                    '<td>'+config.CreateDate.replaceAll('-','/')+'</td>'+
                    '<td class='+config.DocStatus+'>'+status_tag+'</td>'+
                    '</tr>';
        },


        _OnChangeInvStatus: function($this, $widget){
            var itemnot,j = 0,input='',res = [];
            var arr = this.options.jsonConfig;
            var cres =   []                
            var cval =   $("input[name='style_color']").val() ? $("input[name='style_color']").val() : '';
            var val =   $("input[name='style_number']").val() ? $("input[name='style_number']").val() : ''; 
            itemnot = '<tr class= "item-not-found" colspan="4"><td style="text-align:center">Item Not Found</td><tr>';
            if(val == '' && cval == ''){
                $this.parents('.recent-order').find('.inventory_table tbody').html(old_inv_data); return false; 
            }
            if(val != ''){
              var demo = [];
                for (var i = 0; i < arr.length; i++) {
                    // console.log(arr[i]);
                   if(arr[i]['ItemCode'].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                        demo.push(arr[i]);
                   }
                }
                
            }else{
                res = arr;
            }
            demo.filter(function(index,value){
              if(index['Style'] == val.toUpperCase())
              {
                res.push(index);
              }

            });
            demo.filter(function(index,value){
              if(res.indexOf(index) == -1)
              {
                res.push(index);
              }              
            });
            if(cval != '' && res.length > 0){
                for (var i = 0; i < res.length; i++) {
                   if(res[i]['Color'].substr(0, cval.length).toUpperCase() == cval.toUpperCase() && j < 10){
                        input += this._RenderAutoItemTr(res[i]);
                   }
                }
            }else{
                for (var i = 0; i < res.length; i++) {
                    input += this._RenderAutoItemTr(res[i]); 
                    j++;
                }
            }
            if(res.length < 1){
                if($this.val().length > 0){
                  console.log('Item not found');
                  $this.parents('.recent-order').find('.inventory_table tbody').html(itemnot);
                  return false;
                }else{
                  $("#opt_two_message").hide();
                }
            
            }
            if(res.length >= 1 && input != ''){
                $this.parents('.recent-order.inv').find('.inventory_table tbody').html(input);
            }else{
                $this.parents('.recent-order').find('.inventory_table tbody').html(old_inv_data); return false; 
            }
        },

        _OnChangePochange: function($this,$widget,filter=''){

            var $this = $(".recent-order.order .order_search")
            var itemnot,i,j = 0, 
                val = $this.val(),
                input='',
                res = [],
                arr = this.options.customer_order_data,
                selected_filter_value = $("#order_stats option:selected").val();
            const allorder = this.options.customer_order_data;
            itemnot = '<tr class= "item-not-found" colspan="4"><td style="text-align:center">Item Not Found</td><tr>';
            if(selected_filter_value == '' || (selected_filter_value == 'recent_order' && val.length <1 )){
              if(val.length <1 ){ 
                  $this.parents('.recent-order').find('.order_table tbody').html(old_order_data); return false; 
              }
            }

            if(filter != ''){
              for (i = 0; i < allorder.length; i++) {
                if(allorder[i]['DocStatus']){
                  if(allorder[i]['DocStatus'] == filter){
                    res.push(allorder[i]);
                    input += this._RenderAutoOrderItemTr(allorder[i],$widget);
                  }else if(filter == 'viewall'){
                    res.push(allorder[i]);
                    input += this._RenderAutoOrderItemTr(allorder[i],$widget);
                  }
                }
              }
            }else{
              if(selected_filter_value != 'viewall' && selected_filter_value != 'recent_order'){
                let falg = _.filter(this.options.customer_order_data, function(value) {
                    return value['DocStatus'] === selected_filter_value;
                });
                arr = falg;
              }
              for (i = 0; i < arr.length; i++) {
                if(arr[i]['NumatCardPo']){
                  if(arr[i]['NumatCardPo'].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                    res.push(arr[i]);
                    input += this._RenderAutoOrderItemTr(arr[i],$widget);
                  }       
                }
                if(val.length >= 0){
                  if(arr[i]['DocNum']){
                    if(arr[i]['DocNum'].toString().substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                      res.push(arr[i]);
                      input += this._RenderAutoOrderItemTr(arr[i],$widget);
                    }
                  }
                }
              }
              if(val.length > 0){
                for (i = 0; i < arr.length; i++) {
                  if(arr[i]['Id']){
                    if(arr[i]['Id'].toString().substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                      res.push(arr[i]);
                      input += this._RenderAutoOrderItemTr(arr[i],$widget);
                    }
                  }
                }
              }
            }

            if(res.length < 1){
                if($this.val().length > 0 || filter != ''){
                  $this.parents('.recent-order').find('.order_table tbody').html(itemnot);
                  return false;
                }else{
                  $("#opt_two_message").hide();
                }
            
            }
            if(res.length >= 1){
                $this.parents('.recent-order.order').find('.order_table tbody').html(input);
            }
        },


    });
    return $.mage.DashboardRenderer; //list
});