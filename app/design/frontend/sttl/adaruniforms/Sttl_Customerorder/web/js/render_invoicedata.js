/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

requirejs([
    'jquery',   
    'magnificPopup',
    'mage/template',
    'text!Sttl_Customerorder/template/discount_popup.html',
    "text!Sttl_Customerorder/template/shipment.html",
    'text!Sttl_Customerorder/template/dashboard/render_discount_section.html',
    'text!Sttl_Customerorder/template/dashboard/render_invoice_header.html'
], function ($,magnificPopup, mageTemplate, discountPopup,shiptracking,discount_render_page, render_invoice_header_page) {
    'use strict';  
    var invoicedata = [],
        permissionjson = '',
        invoice = true,
        order = true,
        shiptrackingdata = false,
        payinvoice = true,
        currentIndex = 10,
        allorderdata_fetched = false;
    var old_inv_data = $('.recent-order.inv .inventory_table tbody').html();
    var old_order_data = $('.recent-order.order .order_table tbody').html();
    let options = {
        base_url: window.location.origin,
        customer_order_data: {},
        trackingdata:{}
    }
      if(window.permissionjson.length){
        permissionjson = JSON.parse(window.permissionjson);
      }

          if(window.permission){
                    invoice = false,
                    order = false,
                    payinvoice = false;
                if($.inArray("order", permissionjson)){
                    if(_.contains(permissionjson.order,"view_order")){
                        order = true;
                     }
                }
                if($.inArray("invoice", permissionjson)){
                    if(_.contains(permissionjson.invoice,"view_invoice")){
                        invoice = true;
                     }
                }
                if($.inArray("invoice", permissionjson)){
                    if(_.contains(permissionjson.invoice,"pay_invoice")){
                        payinvoice = true;
                        invoice = true;
                     }
                }
            }

            if(order){
              const OrderDataUrl = options.base_url+"/customerorder/dashboard/getOrderData";
              $.ajax({
                url: OrderDataUrl,
                type: "POST",
                data: {is_fetch_data_order: 1},
                showLoader: false,
                cache: false,
                success: function(response) {
                 options.customer_order_data = response.orderData;
                    $(".orderDevelop").hide()
                    $("#order_stats").show() 
                    $(".filter_loading").removeClass("filter_loading",800);
                    setTimeout(function(){
                      if(typeof response.orderData != 'undefined' && response.orderData.length){
                        allorderdata_fetched = true;
                      }
                    },10000)
                  },
              });

              setInterval(function(){
                if(allorderdata_fetched){
                  var syncOrder = (ReadCookie('syncOrder') == 'yes') ? 1 : 0;
                  if(true){
                    var last_record = options.customer_order_data[0].Id;
                    _FetchRecentOrder(last_record);
                  }
                }
              }, 10000);


              function _FetchRecentOrder(last_record){
                  $.ajax({
                    url: OrderDataUrl,
                    type: "GET",
                    data: {is_fetch_recent_order_data: 1, recentOrderId: 1},
                    showLoader: false,
                    cache: false,
                    success: function(response) {
                        if(response.syncData){

                          let newData = response.syncData,
                                oldData = options.customer_order_data;
                          _updateRecentOrderDom(response.syncData);

                          var newAddedItemOrderID = [],
                              oldItemOrderID = [];

                          newData.forEach(function(newitem, index) {
                              var flag = _.filter(oldData, function(value) {
                                  return value["Id"] === newitem.Id;
                              });
                              newAddedItemOrderID.push(newitem.Id);
                              if(flag.length){
                                options.customer_order_data.forEach(function(olditem, oldindex) {
                                  if(olditem.Id === newitem.Id){
                                    options.customer_order_data[oldindex] = newitem;
                                  }  
                                });
                              }else{
                                const newAddedOrderitem = [newitem].concat(oldData);
                                options.customer_order_data = newAddedOrderitem;
                              }
                          });

                          var deleteOrder = _.filter(options.customer_order_data, function(value) {
                              if(value["DocStatus"] == 'Draft'){
                                oldItemOrderID.push(value["Id"]);
                                return true;
                              }
                          });

                          if(deleteOrder.length != newData.length){
                            let difference = oldItemOrderID.filter(x => !newAddedItemOrderID.includes(x)).concat(newAddedItemOrderID.filter(x => !oldItemOrderID.includes(x)));
                            difference.forEach(function(item) {
                              $("[mysql_order_id='"+item+"']").hide(1000, function(){
                                  $("[mysql_order_id='"+item+"']").remove();
                              });
                              var flag = _.filter(options.customer_order_data, function(value) {
                                  return value["Id"] !== item;
                              }); 
                              options.customer_order_data = flag;
                            });
                          }
                        }
                        eraseCookie('syncOrder');
                      },
                  });
              }

              function _updateRecentOrderDom(data){
                data.forEach(function(item, index) {
                    if (item.Id !== "") {
                      if(isitemUpdated(item)){
                        switch(isitemUpdated(item)) {
                          case 1:
                            $("[mysql_order_id='"+item.Id+"']").html(_RenderAutoOrderItemTr(item,1));
                            break;
                          case 2:
                            $(".recent-order.order .ord_table").find('table.order_table tr').first().before(_RenderAutoOrderItemTr(item,0));
                            break;
                        }
                        $("[mysql_order_id='"+item.Id+"']").addClass('newitem-animation');
                        setTimeout(function(){
                          $("[mysql_order_id='"+item.Id+"']").removeClass('newitem-animation');
                        },3000)
                      }
                    }
                });
              }

              function isitemUpdated(newItem){
                const oldData = options.customer_order_data;
                var isUpdated = 0;

                var falg = _.filter(oldData, function(value) {
                    return value["Id"] === newItem.Id;
                });

                if(falg.length){
                  falg = falg[0];
                  if(falg.NumatCardPo != newItem.NumatCardPo || falg.DocTotal != newItem.DocTotal){
                    isUpdated = 1;
                  }else{
                    isUpdated = 0;
                  }
                }else{
                  isUpdated = 2;
                }

                return isUpdated;
              }

              function ReadCookie(cookie_name){
                 var allcookies = document.cookie;
                 cookiearray = allcookies.split(';');
                 var changed_coolie = 'false';
                 for(var i=0; i<cookiearray.length; i++) {
                    var cookie_key = cookiearray[i].split('=')[0];
                    cookie_key = cookie_key.trim();
                    if(cookie_key == cookie_name){
                      changed_coolie = cookiearray[i].split('=')[1];
                    }
                 }
                 changed_coolie = changed_coolie.toString();
                 return changed_coolie;
              }

              function eraseCookie(name) {
                  setCookie(name,"no",0);
              }

              function setCookie(name, value, days){
                if (days) {
                  var date = new Date();
                  date.setTime(date.getTime() + (days * 24 * 60 * 60 *1000));
                  var expires = "; expires=" + date.toGMTString();
                } else {
                    var expires = "";
                }
                document.cookie = name + "=" + value + expires + "; path=/";
              }

            }else{
              $(".orderDevelop #lodingshippdata").hide();
              $("input.order_search").css({ "opacity": "0.8","pointer-events": "none"})
            }

            $.ajax({
                url: options.base_url+"/customerorder/dashboard/gettrackinfojs",
                type: "POST",
                data: { is_fetch_data: 1},
                showLoader: false,
                cache: false,
                success: function(response) {
                   shiptrackingdata = true;
                  if(response.trackingData){
                    $(".tracking_disabled").removeClass("tracking_disabled",700);
                    options.trackingdata = response.trackingData;
                  }
                },
            });

            function _renderRecentItems(limit){
              var recent_order_items = _.filter(options.customer_order_data, function(value,index) {
                  return index <= limit-1;
              });

              let recentItemHTML = '';
              recent_order_items.forEach(function(item, index) {
                  recentItemHTML += _RenderAutoOrderItemTr(item,0);
              });

              return recentItemHTML;
           }

          $(document).on('change','#order_stats', function (e) {
                var optionSelected = $("option:selected", this);
                var valueSelected = this.value;
                if(shiptrackingdata == true){
                  $("td.Shipped.custom_loading").removeClass("tracking_disabled");
                  $("td.Shipped tracking_disabled").removeClass("tracking_disabled");
                  $("table.order_table").find("tracking_disabled").removeClass("tracking_disabled");
                }

                $(".order_search").val("")
                if(valueSelected == 'recent_order'){
                  $(this).parents('.recent-order').find('.order_table tbody').html(_renderRecentItems(4)); return false;
                  return false;
                }
                _OnChangePochange($(this),valueSelected);
            });

            $(document).on('click','.order-link', function (e) {
              // if($('.order_search').val() == '' && $('#order_stats').val() == 'recent_order'){
                $("#order_stats").val("viewall").attr("selected","selected");
                $('#order_stats').trigger('change');
                // $("table.order_table > tbody > tr").slice(currentIndex, currentIndex + 10).show();

              // }else{
              //   // $("table.order_table > tbody > tr").slice(currentIndex, currentIndex + 30).show();
              //   // currentIndex += 30;
              // }
            });

            $(document).on('click', '.recent-order.order .action.save', function (e) {
                return _OnChangePochange($(this));
            });

            $(document).on('input','.order_search',function(){
              var isAbletoSearch = $("#isAbletoSearch").val(),
                  isAbletoSearch = parseInt(isAbletoSearch);
              if(isAbletoSearch == 0){
                return _OnChangePochange($(this));
              }
            });

        function _OnChangePochange($this,filter=''){

            var $this = $(".recent-order.order .order_search")
            var itemnot,i,j = 0, 
                val = $this.val(),
                input='',
                res = [],
                arr = options.customer_order_data,
                selected_filter_value = $("#order_stats option:selected").val();
            var po_numer = val.toLowerCase();    
            const allorder = options.customer_order_data;
            itemnot = '<tr class= "item-not-found" colspan="4"><td style="text-align:center;display: contents">Item Not Found</td><tr>';
            if(selected_filter_value == '' || (selected_filter_value == 'recent_order' && val.length <1 )){
              if(val.length <1 ){ 
                  $this.parents('.recent-order').find('.order_table tbody').html(_renderRecentItems(5)); return false; 
              }
            }

            if(filter != ''){
              for (i = 0; i < allorder.length; i++) {
                if(allorder[i]['DocStatus']){
                  if(allorder[i]['DocStatus'] == filter){
                    res.push(allorder[i]);
                    input += _RenderAutoOrderItemTr(allorder[i]);
                  }else if(filter == 'viewall'){
                    res.push(allorder[i]);
                    input += _RenderAutoOrderItemTr(allorder[i]);
                  }
                }
              }
            }else{
              if(selected_filter_value != 'viewall' && selected_filter_value != 'recent_order'){
                let falg = _.filter(options.customer_order_data, function(value) {
                    return value['DocStatus'] === selected_filter_value;
                });
                arr = falg;
              }
              for (i = 0; i < arr.length; i++) {
                    if(arr[i]['NumatCardPo']){
                        if(arr[i]['NumatCardPo'].toLowerCase().indexOf(po_numer) > -1 && j < 10){
                            res.push(arr[i]);
                            input += _RenderAutoOrderItemTr(arr[i]);
                          }
                        
                    }
                    if(arr[i]['DocNum']){
                        if(arr[i]['DocNum'].toLowerCase().indexOf(po_numer) > -1 && j < 10){
                            res.push(arr[i]);
                            input += _RenderAutoOrderItemTr(arr[i]);
                        }
                    }
              }
              if(val.length > 0){
                for (i = 0; i < arr.length; i++) {
                  if(arr[i]['Id']){
                    if(arr[i]['Id'].toString().substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                      res.push(arr[i]);
                      input += _RenderAutoOrderItemTr(arr[i]);
                    }
                  }
                }
              }
            }

            if(res.length < 1){
                if($this.val().length > 0 || filter != ''){
                  $this.parents('.recent-order').find('.order_table tbody').html(itemnot);
                  $('.recent-order-col-section .order-link').css('border-top','2px solid #d8d8d88c')
                  return false;
                }else{
                  $("#opt_two_message").hide();
                }
            
            }
            if(res.length >= 1){
                $this.parents('.recent-order.order').find('.order_table tbody').html(input);
            }
            if(res.length < 4){
              $('.recent-order-col-section .order-link').css('border-top','2px solid #d8d8d88c')
            }else{
               $('.recent-order-col-section .order-link').css('border-top','')
            }
            console.log("res_len",res.length)
            $("table.order_table > tbody > tr").hide().slice(0, 30).show();
        }




        function _RenderAutoOrderItemTr(config, syncData=0){
            var id_b_e = btoa(config.Id);
            if(config.dataFrom == 'V')
            {
                id_b_e = btoa(config.DocNum);
            }
            var redirect_url = options.base_url+"/customerorder/customer/orderview/id/"+id_b_e+"/df/"+btoa(config.dataFrom);
            var demourl = options.base_url+'/customerorder/customer/neworder/id/'+btoa(config.Id)+'/ncp/'+btoa(config.NumatCardPo)+'#block-title';
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

            var ponumbertable  = "",
                tooltipText = '';
            if(config.NumatCardPo == '' || config.NumatCardPo == null)
            {
              var ponuber = config.DocNum,
                  tooltipText = config.DocNum;
              if(ponuber.length > 8){
                  ponuber = ponuber.substring(0, 8)+"...";              
              }
              ponumbertable = "Order# "+ponuber;
            }
            else
            {
              var ponuber = config.NumatCardPo,
                  tooltipText = config.NumatCardPo;
              if(ponuber.length > 8){
                  ponuber = ponuber.substring(0, 8)+"...";              
              }
              ponumbertable = "PO# "+ponuber;
            }

            if(syncData){
              return '<td><a class="order-redirect-url" data-toggle="tooltip" data-placement="right" title='+tooltipText+'  href='+redirect_url+'>'+ponumbertable+'</a></td>'+
                      '<td>$'+_convertcurrency(parseFloat(config.DocTotal).toFixed(2))+'</td>'+
                      '<td>'+config.CreateDate.replaceAll('-','/')+'</td>'+
                      '<td class='+config.DocStatus+'>'+status_tag+'</td>';
            }else{
              return '<tr mysql_order_id='+config.Id+'>'+
                      '<td><a class="order-redirect-url" data-toggle="tooltip" data-placement="right" title='+tooltipText+'  href='+redirect_url+'>'+ponumbertable+'</a></td>'+
                      '<td>$'+_convertcurrency(parseFloat(config.DocTotal).toFixed(2))+'</td>'+
                      '<td>'+config.CreateDate.replaceAll('-','/')+'</td>'+
                      '<td class='+config.DocStatus+'>'+status_tag+'</td>'+
                      '</tr>';
            }
        }

        if(invoice){
          var postdata = {is_invoice:1, is_catalog_slider: 1}
        }else{
          var postdata = {is_catalog_slider: 1}
        }


    $.ajax({
        url: options.base_url+"/customerorder/dashboard/getdataafterload",
        type: "POST",
        data: postdata,
        showLoader: false,
        cache: false,
        success: function(response) {
          if(response.invoicedata){
            invoicedata = response.invoicedata;
            var duedata = filterdata(invoicedata);
            if(duedata.length < 5)
            {
              duedata = invoicedata.slice(0,5);
            }
            var opendata = openinvoice(invoicedata)
            if(opendata.length < 5)
            {
              opendata = invoicedata.slice(0,5);
            }
            if(duedata.length > 0){
              invoicedatavalue(duedata);  
              $(".due_by_week").children().addClass("active_invoice");
            }else if(opendata.length > 0){
              $(".current_balance").children().addClass("active_invoice");
              invoicedatavalue(opendata);  
            }else{
              invoicedatavalue(invoicedata);  
            }
            if(invoice){
                $.ajax({
                  url: options.base_url+"/customerorder/dashboard/getdataafterload",
                  type: "POST",
                  data: {is_invoice:1,all_invoice: 1},
                  showLoader: false,
                  cache: false,
                  success: function(response) {
                    if(response.invoicedata){
                      invoicedata = response.invoicedata; 
                    }
                  },
              });
            }
          }
          if(response.catalogslider){
              catalogslidervalue(response.catalogslider);
            }
        },
    });


    $.ajax({
        url: options.base_url+"/customerorder/dashboard/getdataafterload",
        type: "POST",
        data: {is_fetch_discount_data:1},
        showLoader: false,
        cache: false,
        success: function(response) {
          if(response.discountData){
              var mweb_bd_data = response.discountData,
                  customerdata = response.customerdata,
                  bulkdiscount_variations = response.bulkdiscount_variations;

              bulkdiscount_variations.sort(function(a, b) {
                  return a.U_QtyFrom - b.U_QtyFrom;
              });

              if(customerdata.length > 0){
                var ytg_amount = parseFloat(customerdata[0].YTDSALE),
                    lys_amount = parseFloat(customerdata[0].LastYearSale);
                ytg_amount = ytg_amount.toFixed(2);
                lys_amount = lys_amount.toFixed(2);
                $('[render_yeartodate_spent]').html("$"+convertcurrency(ytg_amount)).attr('render_yeartodate_spent',customerdata[0].YTDSALE).removeClass("custom_loader",1000);
                $('[render_lastyear_spent]').html("$"+convertcurrency(lys_amount)).attr('render_lastyear_spent',customerdata[0].LastYearSale).removeClass("custom_loader",1000);
              }

              const discount_section_content = mageTemplate(discount_render_page, {
                  customer_Program: customerdata[0].Program,
                  customer_Tier: customerdata[0].Tier,
                  customer_FlatDiscount: customerdata[0].FlatDiscount,
                  customer_BulkDiscount: customerdata[0].BulkDiscount,
              });
              $('.discount_section_content').html(discount_section_content).removeClass('backLoader',2000);

              var active_invoice_section = "",
                  duedata = filterdata(invoicedata),
                  opendata = openinvoice(invoicedata);
              if(duedata.length > 0){
                active_invoice_section = "due_invocie";
              }else if(opendata.length > 0){
                active_invoice_section = "current_invocie";
              }

            if(invoice){
                const render_invoice_header_page_content = mageTemplate(render_invoice_header_page, {
                    customer_AccountBalance: customerdata[0].AccountBalance,
                    customer_PastDueAmount: customerdata[0].PastDueAmount,
                    base_url : options.base_url,
                    payinvoiceper : payinvoice,
                    currencyconvert: $.proxy(convertcurrency, this),
                    active_invoice_section: active_invoice_section
                });
                $('.render_invoice_header_page').append(render_invoice_header_page_content);
              }

              $(".disabled_dicount_popup").removeClass('disabled_dicount_popup');
              const discount_popup = mageTemplate(discountPopup, {
                  customerProgram: customerdata[0].Program,
                  customer_tier: customerdata[0].Tier,
                  mweb_bd: mweb_bd_data,
                  bulkdiscount_variations: bulkdiscount_variations,
                  currencyconvert: $.proxy(convertcurrency, this)
              });
              $('.discount_popup_data').html(discount_popup);
            }
        },
    });

    function convertcurrency(price){
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
    }

    function filterdata(invoices) {
      var temp = [];
      $.each(invoices,function(key,value){
        if(value.DocStatus == 'Open'){
          var date1 = value.DueDate.replace(/ /g,"T")
          var date = new Date(date1);
          var actual_print = date.getMonth()+"-"+date.getDate()+"-"+date.getFullYear().toString().substr(-2);
          var current_date = new Date();
             if(date <= current_date)
             {
                temp.push(value)
             }  
        }
      })
      return temp;
    }
    function openinvoice(invoices){
      return invoices.filter(function(value,key) {
            return value.DocStatus == "Open";
        });
    }
    $(document).on("click",".current_balance",function(){
        $(".invoice-link").show();
        $('.active_invoice').removeClass("active_invoice");
        $(this).children().first().addClass("active_invoice");
        $(".bottom-section-outstanding-invoice").html(addloader());
        setTimeout(function(){
            removeLoader($(".bottom-section-outstanding-invoice"));
            var final_invoicedata_due = openinvoice(invoicedata);
            if(final_invoicedata_due.length < 5)
            {
              final_invoicedata_due = invoicedata.slice(0,5);

            }
            invoicedatavalue(final_invoicedata_due);
            changeButton('current');
            var button_height = $(".invoice-link").height();
            $('.invoice-link').prev().height($(".invoice-link").height() - button_height);
        },300)

    })
    $(document).on("click",".due_by_week",function(){
        $(".invoice-link").show();
        $('.active_invoice').removeClass("active_invoice");
        $(this).children().first().addClass("active_invoice");
        $(".bottom-section-outstanding-invoice").html(addloader());
        setTimeout(function(){
            removeLoader($(".bottom-section-outstanding-invoice"));
             var final_invoicedata_due = filterdata(invoicedata);
            if(final_invoicedata_due.length < 5)
            {
              // var count = 5 - final_invoicedata_due.length;
              // var demoarray = openinvoice(invoicedata);
              // $.each(demoarray, function(inde,value){
              //     if(demoarray.indexOf(value) > 0){
              //       final_invoicedata_due.push(value);
              //     }
              // })

              // if(final_invoicedata_due.length < 5)
              // {
              //   var count = 5 - final_invoicedata_due.length;
              //   var demoarray = invoicedata;
              //   $.each(demoarray, function(index,value){
              //     if(demoarray.indexOf(value) > 0)
              //     {
              //       final_invoicedata_due.push(value);
              //     }      
              //   }) 
              // }
              final_invoicedata_due = invoicedata.slice(0,5);
              
            }
            invoicedatavalue(final_invoicedata_due);
            changeButton('due');
            var button_height = $(".invoice-link").height();
            $('.invoice-link').prev().height($(".invoice-link").height() - button_height);
        },300)
    });

    $(document).on("click",".invoice-link a",function(){
      $('.active_invoice').removeClass("active_invoice");
      $(".bottom-section-outstanding-invoice").html(addloader());
      setTimeout(function(){
        removeLoader($(".bottom-section-outstanding-invoice"));
        invoicedatavalue(invoicedata);
        var actual_button_length = $(this).parents('.invoice-link').height(),
            abovetag_height = $(this).parents('.invoice-link').prev().height(),
            shouldbe_height = actual_button_length + abovetag_height;
        $(this).parents('.invoice-link').prev().height(shouldbe_height);
        $(this).parents('.invoice-link').hide();
      },300);

    });

    function changeButton(type){
      if(type == 'due'){
        var url = options.base_url+'/customerinvoices/customer/index?order_stats=pastdue&order_by=DueDays&opt=DESC&dash=pay_due',
            btn_text = "PAY PAST DUE AMOUNT",
            selector = $('.pay_invoice').find("a");
        selector.attr('href',url);
        selector.removeClass("past_due");
        selector.addClass("past_due");
        selector.text(btn_text);
      }else{
        var url = options.base_url+'/customerinvoices/customer/index?order_stats=Open&order_by=DueDays&opt=DESC&dash=pay_current',
            btn_text = "PAY CURRENT BALANCE",
            selector = $('.pay_invoice').find("a");
        selector.css("color","#393939");
        selector.removeClass("past_due");
        selector.attr('href',url);
        selector.text(btn_text);
      }
    }

    function invoicedatavalue(invoicevalue){

          var html = ''
            if(invoicevalue.length > 0){
              // $(".invoice-link").show();
              html += '<table><tbody>'
              $.each(invoicevalue,function(key,value){
                var invoice_view_url = ''
                if(value.DocNum){
                  
                invoice_view_url = options.base_url+'/customerinvoices/customer/view/docnum/'+btoa(value.DocNum);
                }
                html += '<tr><td><a href="'+invoice_view_url+'" class="invoice-redirect-url">Invoice# '+value.DocNum+'</a></td>'
                if(value.NumatCardPo == null || value.NumatCardPo == '')
                  {
                    html +=  '<td>-</td>'
                  }
                  else
                  { 
                    html += '<td>'+value.NumatCardPo+'</td>'
                  }
                html += '<td>'+(value.CreateDate).replace(/-/g,"/")+'</td><td>'+value.DocStatus+'</td>'
                if(value.DocStatus != 'Paid')
                {
                  var date = new Date(value.DueDate.replace(/ /g, "T"));
                  var actual_print = date.getMonth()+"-"+date.getDate()+"-"+date.getFullYear().toString().substr(-2);
                  var current_date = new Date();
                     if(date <= current_date)
                     {
                      html += '<td class="warning">'+value.DueDays+'</td>'
                     }
                     else
                     {
                      html += '<td class="success">Due in '+value.DueDays+'</td>'
                     }
                }
                else
                {
                    html += '<td> - </td>'
                 
                }

                html += '<td>Paid: $'+_convertcurrency(parseFloat(value.PaidAmount).toFixed(2))+'</td>'
                if(date <= current_date)
                {

                   html += '<td class="warning">$'+_convertcurrency(parseFloat(value.OpenBalance).toFixed(2))+'</td>'

                }
                else
                {

                   html += '<td>$'+_convertcurrency(parseFloat(value.OpenBalance).toFixed(2))+'</td>'

                }
              })
              html += '</table></tbody>'

            }else{
              html += "<div class='emptyinvoice'><p>You don't yet have any invoice! </br>Start your first order now!</p><div class='neworderinvoice'><a href='"+options.base_url+"/customerorder/customer/neworder'>Place New Order</a></div> </div>"
              $(".invoice-link").hide();

            }
            $(".bottom-section-outstanding-invoice").html(html)
        }

        function addloader(){
          return '<span class="invoice-item-loading"></span> <span class="invoice-item-loading"></span> <span class="invoice-item-loading"></span> <span class="invoice-item-loading"></span> <span class="invoice-item-loading"></span>';
        }

        function removeLoader($this){
          $this.find(".orderItem-loader").remove();
        }

        function _convertcurrency(price) {
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
        }
        function catalogslidervalue(slidervalue){
            var html = '<ul class="catalogslider">'
            $.each(slidervalue,function(key,value){
            var imag  = options.base_url+'/pub/media/'+value.small_image
              html +=  '<li> <a href="'+value.custom_url+'" download><img src="'+imag+'"></a> <h6><a href="'+value.custom_url+'" target="_blank">'+value.name+'</a></h6> </li>'
            })
            html +="</ul>"
            $('.catalogslider-div').html(html);
           $(".catalogslider").owlCarousel({
              loop:true
              ,autoplay:true
              ,autoplayTimeout:3000
              ,nav:true
              ,navText:['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>'
                            ,'<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>']
              ,dots:false
              ,items:1
            }); 
        }

         function getShippingInfobyDocEntry(SoDocEntry) {
            SoDocEntry = parseInt(SoDocEntry);
            var falg = _.filter(options.trackingdata, function(value) {
                    return value['SODocEntry'] === SoDocEntry;
                });
            return falg;
        }

            $('#shipment-track').on('shown.bs.modal', function (event) {
                $(".row.shipdatainfo").html("");
                $('.loading-mask').remove();
                var button = $(event.relatedTarget),
                    recipient = button.attr('data-num'),
                    orderTrackingData = getShippingInfobyDocEntry(recipient),
                    currentshippingPopup = $("#popup_tracking_id").val(),
                    currentshippingPopup = parseInt(currentshippingPopup),
                    recipient = parseInt(recipient);

                if(currentshippingPopup != recipient){
                  $('.loading-mask').remove();
                  $('.orderItem-loader').css('display','block');
                    $.ajax({
                        url: options.base_url + "/customerorder/dashboard/gettrackinfojs",
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

});
