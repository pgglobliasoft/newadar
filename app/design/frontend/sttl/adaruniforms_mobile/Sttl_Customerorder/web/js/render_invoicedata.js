/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

requirejs([
    'jquery',   
    'magnificPopup',
    'mage/template',
    'text!Sttl_Customerorder/template/discount_popup.html',
], function ($,magnificPopup, mageTemplate, discountPopup) {
    'use strict';  
    var invoicedata = [],
        startid = 0,
        endid = 500,
        page = 1,
        totalpage = 0;
    var old_inv_data = $('.recent-order.inv .inventory_table tbody').html();
    var old_order_data = $('.recent-order.order .order_table tbody').html();
    let options = {
        base_url: window.location.origin,
        customer_order_data: {}
    } 

           $.ajax({
            url: options.base_url+"/customerorder/dashboard/getOrderData",
            type: "POST",
            data: {is_fetch_data_order: 1},
            showLoader: false,
            cache: false,
            success: function(response) {
            console.log(response)
             options.customer_order_data = response.orderData;
                $(".orderDevelop").hide()
                $("#order_stats").show() 
                $(".filter_loading").removeClass("filter_loading",800);
              },
          });

          $(document).on('change','#order_stats', function (e) {
                var optionSelected = $("option:selected", this);
                var valueSelected = this.value;

                $(".order_search").val("")
                if(valueSelected == 'recent_order'){
                  $(this).parents('.recent-order').find('.order_table tbody').html(old_order_data); return false;
                  return false;
                }
                _OnChangePochange($(this),valueSelected);
            });

            $(document).on('click', '.recent-order.order .action.save', function (e) {
                return _OnChangePochange($(this));
            });

            $(document).on('input','.order_search',function(){

                return _OnChangePochange($(this));                
            });

        function _OnChangePochange($this,filter=''){

            var $this = $(".recent-order.order .order_search")
            var itemnot,i,j = 0, 
                val = $this.val(),
                input='',
                res = [],
                arr = options.customer_order_data,
                selected_filter_value = $("#order_stats option:selected").val();
            const allorder = options.customer_order_data;
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
                  if(arr[i]['NumatCardPo'].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                    res.push(arr[i]);
                    input += _RenderAutoOrderItemTr(arr[i]);
                  }       
                }
                if(val.length >= 0){
                  if(arr[i]['DocNum']){
                    if(arr[i]['DocNum'].toString().substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                      res.push(arr[i]);
                      input += _RenderAutoOrderItemTr(arr[i]);
                    }
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
                  return false;
                }else{
                  $("#opt_two_message").hide();
                }
            
            }
            if(res.length >= 1){
                $this.parents('.recent-order.order').find('.order_table tbody').html(input);
            }
        }




        function _RenderAutoOrderItemTr(config){
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

            var ponumbertable  = "";
            if(config.NumatCardPo == '' || config.NumatCardPo == null)
            {
              var ponuber = config.DocNum;
              if(ponuber.length > 8){
                  ponuber = ponuber.substring(0, 8)+"...";              
              }
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

            var drafrurl = '';
            if(config.DocStatus != 'Draft')
            {
              drafrurl = "href ="+redirect_url;
            }

            var CreateDate = config.CreateDate.replace(/-/g,"/");
            var newdate = new Date(CreateDate);
            var year = newdate.getFullYear();
            year = year.toString().substr(2,2); 
            var day = newdate.getDate();
            day = ('0'+day).slice(-2);
            var month = newdate.getMonth()+1;
              month = ('0'+month).slice(-2);
            var CreateDateformated = month+'/'+day+'/'+year; 
            // console.log();

            return '<tr>'+
                    '<td><a class="order-redirect-url" '+drafrurl+'>'+ponumbertable+'</a></td>'+
                    '<td>$'+_convertcurrency(parseFloat(config.DocTotal).toFixed(2))+'</td>'+
                    '<td>'+CreateDateformated+'</td>'+
                    '<td class='+config.DocStatus+'>'+status_tag+'</td>'+
                    '</tr>';
        }
    $.ajax({
        url: options.base_url+"/customerorder/dashboard/getdataafterload",
        type: "POST",
        data: {is_invoice:1 },
        showLoader: false,
        cache: false,
        success: function(response) {
          if(response.invoicedata){
            invoicedata = response.invoicedata;
            var duedata = filterdata(invoicedata)
            var opendata = openinvoice(invoicedata)
            if(duedata.length > 0){
              invoicedatavalue(duedata);  
              $(".due_by_week").children().addClass("active_invoice");
            }else if(opendata.length > 0){
              $(".current_balance").children().addClass("active_invoice");
              invoicedatavalue(opendata);  
            }else{
              invoicedatavalue(invoicedata);  
            }
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

              console.log(bulkdiscount_variations);


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
        invoicedatavalue(openinvoice(invoicedata));
        changeButton('current');

        // var button_height = $(".invoice-link").height();
        // $('.invoice-link').prev().height($(".invoice-link").height() - button_height);

    })
    $(document).on("click",".due_by_week",function(){
        $(".invoice-link").show();
        $('.active_invoice').removeClass("active_invoice");
        $(this).children().first().addClass("active_invoice");
        invoicedatavalue(filterdata(invoicedata));
        changeButton('due');
        // var button_height = $(".invoice-link").height();
        // $('.invoice-link').prev().height($(".invoice-link").height() - button_height);
    });

    $(document).on("click",".invoice-link a",function(){
      $('.active_invoice').removeClass("active_invoice");
        invoicedatavalue(invoicedata);
        var actual_button_length = $(this).parents('.invoice-link').height(),
            abovetag_height = $(this).parents('.invoice-link').prev().height(),
            shouldbe_height = actual_button_length + abovetag_height;

        $(this).parents('.invoice-link').prev().height(shouldbe_height);

        $(this).parents('.invoice-link').hide();

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

      if(invoicevalue.length <= 6){       
        $(".invoice-link").hide();
      }
      else{      
        $(".invoice-link").show();
       }

          var html = ''
            if(invoicevalue.length > 0){
              // $(".invoice-link").show();
              html += '<table><tbody>'
              $.each(invoicevalue,function(key,value){
                var invoice_view_url = ''
                if(value.DocNum){
                  
                invoice_view_url = options.base_url+'/customerinvoices/customer/view/docnum/'+btoa(value.DocNum);
                }
                html += '<tr><td><a class="invoice-redirect-url">'+value.DocNum+'</a></td>'
                if(value.NumatCardPo == null || value.NumatCardPo == '')
                  {
                    // html +=  '<td>-</td>'
                  }
                  else
                  { 
                    // html += '<td>'+value.NumatCardPo+'</td>'
                  }
                  var ddd = new Date(value.CreateDate);
                  var year = ddd.getFullYear().toString().substr(-2)
                  var date = ("0" + ddd.getDate()).slice(-2);
                  var month = ("0" + (ddd.getMonth() + 1)).slice(-2);
                  var date1 = value.CreateDate;
                  var dd = new Date(date1.replace(/-/g,'/'));
                  var actual_date;
                    actual_date =  ("0" + (dd.getMonth() + 1)).slice(-2)+"/"+("0" + dd.getDate()).slice(-2)+"/"+dd.getFullYear().toString().substr(-2);
                html += '<td>'+actual_date+'</td><td>'+value.DocStatus+'</td>'
                if(value.DocStatus != 'Paid')
                {
                  var date = new Date(value.DueDate);
                  var actual_print = date.getMonth()+"-"+date.getDate()+"-"+date.getFullYear().toString().substr(-2);
                  var current_date = new Date();
                     if(date <= current_date)
                     {
                      html += '<td class="warning invoiceday forhide">'+value.DueDays+'</td>'
                     }
                     else
                     {
                      html += '<td class="success forhide">Due in '+value.DueDays+'</td>'
                     }
                }
                else
                {
                    html += '<td class="forhide"> - </td>'
                 
                }

                // html += '<td>Paid: $'+_convertcurrency(parseFloat(value.PaidAmount).toFixed(2))+'</td>'
                if(value.DocStatus == "Open")
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
            // if($( window ).width());
            $(".bottom-section-outstanding-invoice").html(html)
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

});
