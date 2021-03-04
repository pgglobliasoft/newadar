define(['jquery'],
    function($) {
        'use strict';
        var totalpage,
            alltotalorders,
            res_error = false,
            searched_order;
        return { 

            

            displayCustomer: function(url) {                
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'GET',    
                    beforeSend: function() {                    
                        $("#ajax-loading-mask").show();
                    }
                }).done(function(data) {  
                    $("#ajax-loading-mask").hide();  
                    if(!data.errors)
                    {
                        $('.orderTable').html(data.html);
                    }else{
                        $('.orderSearchForm').append(data.message);
                    }
                });
            },         
            filterOrder: function(url) {                
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'GET',    
                    beforeSend: function() {                    
                        $("#ajax-loading-mask").show();
                    }
                }).done(function(data) {  
                    $("#ajax-loading-mask").hide();  
                    if(!data.errors)
                    {
                        $('.orderTable').html(data.html);
                    }else{
                        $('.orderSearchForm').append(data.message);
                    }
                });

            },
            sortDate: function(aData, prop, asc) {
                 aData.sort(function(a, b) {
                    var aprops = new Date(a[prop].replace(/-/g,'/'));
                    var bprops = new Date(b[prop].replace(/-/g,'/'));                                       
                    if (asc) {
                         return bprops-aprops;
                    } else {
                        return aprops- bprops;
                    }
                }); 
                return aData;  
            },
            DateRangeFiltering : function(aData) {  
                   

                    var dateFrom = $('#date-from').val()
                    var to = $('#date-to').val();                      
                    var min = parseInt(this.normalizeDate(dateFrom));
                    var max = parseInt(this.normalizeDate(to));  
                    var selected = $('#order_stats option:selected').val();                  
                    var data = aData.filter(item => {                   
                         var date = parseInt( this.normalizeDate(item['CreateDate'])) || 0; // use data for the date column
                        if ( ( isNaN( min ) && isNaN( max ) ) ||
                                 ( isNaN( min ) && date <= max ) ||
                                 ( min <= date   && isNaN( max ) ) ||
                                 ( min <= date   && date <= max ) )
                            {
                                if(selected !== '' && typeof selected !== "undefined")
                                    return item.DocStatus == selected;
                                else
                                    return item

                            }
                    })                    
                    return data;  
               
            },
            DateRangeFiltering2 : function(aData) {                    

                    var dateFrom = $('#date-from').val()
                    var to = $('#date-to').val();   
                    if(dateFrom == '' || to == ''){
                        return aData;
                    }      

            },
            normalizeDate : function(dateString) {                
                var date = new Date(dateString.replace(/-/g,'/'));
                var newDate = date.toString('dd-MM-yy');              
                var normalized = date.getFullYear() + '' + (("0" + (date.getMonth() + 1)).slice(-2)) + '' + ("0" + date.getDate()).slice(-2);
                    return normalized;
            },
            SortDateArray: function(aData,type)
            {

                var ctype = type == 'ASC' ? true : false;
                this.sortDate(aData, 'CreateDate', ctype);
                return aData;    

            },
            SortArray: function(aData, column, type)
            {
                var ctype = type == 'ASC' ? true : false;
                this.sortResults(aData,column, ctype);
                return aData;                
            },            
            sortResults: function(aData, prop, asc) {
                 aData.sort(function(a, b) {                    
                    if (asc) {
                        if($.isNumeric(a[prop]) && $.isNumeric(b[prop])){
                            // console.log(a[prop],'<',b[prop])
                            return $.isNumeric(a[prop]) && $.isNumeric(b[prop]) ? parseInt(Math.round(a[prop])) - parseInt(Math.round(b[prop])) : a[prop].localeCompare(b[prop]);
                        }
                        return (a[prop] > b[prop]) ? 1 : (( a[prop] < b[prop]) ? -1 : 0);
                    } else { 
                        if($.isNumeric(a[prop]) && $.isNumeric(b[prop])){
                            // console.log(a[prop],'<',b[prop])
                            return $.isNumeric(a[prop]) && $.isNumeric(b[prop]) ? parseInt(Math.round(b[prop])) - parseInt(Math.round(a[prop])) : b[prop].localeCompare(a[prop]);
                        }
                        return (b[prop] > a[prop]) ? 1 : ((b[prop] < a[prop]) ? -1 : 0);
                    }
                });                    
            },  

            _RenderAutoItemDivLi: function (data) {
                
                return 
            },

            trTemplate:function(url , data , i){

                  
                function GetParameterValues(param) {  
                    var url = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');  
                    for (var i = 0; i < url.length; i++) {  
                        var urlparam = url[i].split('=');  
                        if (urlparam[0] == param) {  
                            return urlparam[1];  
                        }  
                    }  
                } 

                var html = '',
                    backurl = url+'customerorder/customer/index';
                var q = GetParameterValues('q'); 
                    
                $.each(data, function(index, value) { 
                    var id_b_e = btoa(value.Id);
                    if(value.dataFrom == 'V')
                    {
                        id_b_e = btoa(value.DocNum);
                    }
                    var back_redirect = "0";
                    if(q == 'd'){
                        back_redirect = "1";
                    }
                    var order_view_url = url+'customerorder/customer/orderview/id/'+id_b_e+'/back/'+btoa(back_redirect)+'/df/'+btoa(value.dataFrom);  
            
                        const formatter = new Intl.NumberFormat('en-US', {
                            minimumFractionDigits: 2,      
                            maximumFractionDigits: 2,
                        });

                        const formatterINT = new Intl.NumberFormat('en-US', {
                            minimumFractionDigits: 0,      
                            maximumFractionDigits: 0,
                        });
                     // console.log(value.NumatCardPo);
                     var status = ''
                     if(value.DocStatus == 'PartiallyShipped'){
                        status = 'Partially<br>Shipped'
                     }else if (value.DocStatus == 'PartiallyShipped') {
                        status = '<span class="shipped-span"> <i class="fa fa-check"></i> Shipped</span>';
                     }else if (value.DocStatus != null){
                        status = value.DocStatus;
                     }
                     var numatCardPo = ''
                     if(value.NumatCardPo != null && value.NumatCardPo.length > 6){
                        numatCardPo = value.NumatCardPo.substring(0,6) + '...';
                     }else if(value.NumatCardPo != null){
                        numatCardPo = value.NumatCardPo;
                     }else{
                        numatCardPo = '-';
                     }
                     var createDate = value.CreateDate.split("-")
                     var newdate  = createDate[0]+'-'+createDate[1]+'-'+createDate[2].substring(createDate[2].length,2)
                     

                    html +=  '<tr style="cursor: pointer; width: 100%;" class="main-row">'+
                                '<td class="option-redirect '+value.DocStatus+'">' + status +'</td>';
                        if($('.accountPageContainer').hasClass('order_status')){
                             html += '<td class="option-redirect">' + (value.DocNum == null ? '-' : value.DocNum) +'</td>';
                        }                                     
                    html +=  '<td class="option-redirect">' + numatCardPo +'</td>'+
                             '<td class="option-redirect"><div class="d-flex"><span>' + newdate +'</span> <span class="icon-fa"> <i class="fa fa-chevron-down"></i> </span> <div></td>'
                    
                       html += '<tr class="toggleTable"> <td colspan="4" class="custom-td"> <div class="expandable_row" style="display:none;"> <table class="togg-tlb"> <thead> <tr> <th>Ordered</th> <th>Open</th> <th>Shipped </th> <th>Total Order</th> </tr> </thead> <tbody> <tr>'
                        html += '<td>'+ formatterINT.format(value.TotalQTYUnits)+ '</td>'+
                                    '<td>'+ (value.TotalOpen  !== null ? formatterINT.format(value.TotalOpen) : "0") +'</td>'+
                                    '<td>' + (value.TotalShipped  !== null ? formatterINT.format(value.TotalShipped) : "0") + '</td>'+
                                    '<td>' + (value.DocTotal > 0 ? '$'+ formatter.format(value.DocTotal) : '0')  +'</td>'
                                    '</tr>'
                        if(value.DocStatus == "Draft"){                            
                            html += '<tr><td colspan="4">'+
                                    '<a class="newLinkText" href="'+url+'customerorder/customer/neworder/id/'+btoa(value.Id)+'/ncp/'+btoa(value.NumatCardPo)+'" >Edit Order</a>'+
                                    '<a class="newLinkText deletedraft mobile-button" link="'+url+'customerorder/customer/delete/id/'+btoa(value.Id)+'/back/'+btoa(value.NumatCardPo)+'">Delete Order</a>'+
                                    '</td></tr>';
                                



                        }else{
                            html += '<tr><td colspan="4">'+
                                     '<a class="newLinkText" href="'+order_view_url+'" >View Order</a>'
                            if(value.DocStatus == 'Shipped' || value.DocStatus == 'Processing'){ 
                                html += '<a class="newLinkText" data-backdrop="false" data-toggle="modal" data-target="#shipment-track" id="shipttreckdata" data-num="'+value.DocEntry+'">Track Order</a>'
                            }
                            html += '</td></tr>';
                        }   
                        html += '</tbody> </table> </div> </td> </tr>'

                    html += '</tr>';
                    i++;
                   

                });  
                if(data.length < 1){
                    if(alltotalorders > 1000){
                          if(res_error == true){
                            html +=  '<tr><td colspan="10">No Data Found</td></tr>'
                          }else{
                            html +=  '<tr><td colspan="10">Click on Search button to find more results</td></tr>'
                          }
                    }else{
                        html +=  '<tr><td colspan="10">No Data Found</td></tr>'
                    }

                }
                return html;
            },

            setseachdata: function(data){
                searched_order = data
            },

            filterOrder: function(url , data,target)
            {  
                var searchfilter = $("#order_po_serach").val();
                if(searchfilter.length > 0){
                    data = searched_order;
                }
                if($('#date-from').val() == ''){
                    data = this.DateRangeFiltering2(data) 
                }

                if($('#order_stats').val() != '' || $('#date-from').val() != '')
                    data = this.DateRangeFiltering(data) 

                if(searchfilter.length > 0){
                    data = this.orderSearched(data,searchfilter);
                }
                    var  totalpage1;
                if(totalpage && data.length < 0){
                     totalpage1  = totalpage;
                 }else{
                    totalpage1  = Math.ceil((data.length)/30);
                 }   

               
console.log("totalpage1",totalpage1)
                var orders = {},                
                    pages = $(target).attr('data-dt-idx'),
                    page = parseInt($(target).attr('data-dt-idx')- 1),            
                    start = pages > 1 ? page * 30 : 0,               
                    orders = data.slice(start,start+30),                      
                    html = this.trTemplate(url,orders,(start+1)),                    
                    id = $(target).attr('id');
                    // console.log("ordersorders",orders)
                    // if(orders.length>0){
                    //     totalpage1 = Math.ceil((orders.length)/30)
                    // }
                    
                    if(parseInt(page) < 1){
                        $('.paginate_button.first,.paginate_button.previous').addClass('disabled');                        
                        $('.paginate_button.next,.paginate_button.last').removeClass('disabled');
                      
                    }else if(parseInt(page)+1 >= $('#example_last').attr('data-dt-idx')){
                        $('.paginate_button.first,.paginate_button.previous').removeClass('disabled');                        
                        $('.paginate_button.next,.paginate_button.last').addClass('disabled');                        
                    }else{
                        $('.paginate_button.next,.paginate_button.last,.paginate_button.last,.paginate_button.first,.paginate_button.previous').removeClass('disabled');
                    }
                    
                     const formatte = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,      
                        maximumFractionDigits: 2,
                     });


                    var order = 0,open = 0,ship = 0,total = 0;
                    for(var i=0; i < orders.length; i++){
                        order += (orders[i].TotalQTYUnits > 0) ? parseFloat(orders[i].TotalQTYUnits) : 0;
                        open += (orders[i].TotalOpen > 0) ? parseFloat(orders[i].TotalOpen) : 0;
                        ship += (orders[i].TotalShipped > 0) ? parseFloat(orders[i].TotalShipped) : 0;
                        total += (orders[i].DocTotal > 0) ? parseFloat(orders[i].DocTotal) : 0;
                    }
                    
                    $(".orderList tbody").html(html)
                    $(".orderList tfoot tr td:nth-child(2)").html(order);
                    $(".orderList tfoot tr td:nth-child(3)").html(open);
                    $(".orderList tfoot tr td:nth-child(4)").html(ship);
                    $(".orderList tfoot tr td:nth-child(5)").html("$"+formatte.format(total));
                    var d1= start+1;
                    var d2= parseInt(start)+parseInt(orders.length);
                    var d3= data.length;
                  
                    if(orders.length > 0)
                    $(".orderList tfoot tr#paginationId td.testTable .fa-pull-left.recordTotal").html("Displaying " +  d1 + " to " + d2 + " out of " + d3);
                    else{
                        $(".orderList tfoot tr#paginationId td.testTable .fa-pull-left.recordTotal").html('-');
                        $('.pagination .cdatatableDetails .direct-serach').val(0)
                        $('.pagination .cdatatableDetails .total').val(0)
                        $('a#example_next,a#example_last').addClass('disabled');
                    }
                    if(totalpage1 < 2){
                        $('a#example_next,a#example_last').addClass('disabled');
                    }


                    if(id == 'example_next'){                                              

                        $('.paginate_button.next').attr('data-dt-idx',parseInt(pages)+1)
                        $('.paginate_button.previous').attr('data-dt-idx',parseInt(pages))

                    }else if(id == 'example_previous'){

                        $('.paginate_button.next').attr('data-dt-idx',parseInt(pages))
                        $('.paginate_button.previous').attr('data-dt-idx',parseInt(pages)-1)

                    }else if(id == 'example_last'){

                        $('.paginate_button.next').attr('data-dt-idx',parseInt(pages))
                        $('.paginate_button.previous').attr('data-dt-idx',parseInt(pages)-1)

                    }else if(id == 'example_first'){

                        $('.paginate_button.next').attr('data-dt-idx',parseInt(pages)+1)
                        $('.paginate_button.previous').attr('data-dt-idx',parseInt(pages))
                    }
                    
                    $('.direct-serach').attr('data-dt-idx',parseInt(pages)).val((pages< 1?1:pages));
                    $('.paginate_button.last').attr('data-dt-idx', parseInt(totalpage1));
                    if(totalpage1 == 0){
                        totalpage1 = 1;
                    }
                    $('.cdatatableDetails span.total').html(parseInt(totalpage1)).attr('data-dt-idx', parseInt(totalpage1));

                
            },

            searchOrder: function(url , data,target,totalorders,moreorder,keyCode = '')
            {  
                alltotalorders = totalorders;
                searched_order = data;
                if($('#date-from').val() == ''){
                    data = this.DateRangeFiltering2(data) 
                }

                var res = [],i,j = 0,val = target.val(),input='';

                res = this.orderSearched(data,val);

               

                 var orders = {},                
                    pages = 0,
                    page = parseInt(0),
                    start = pages > 1 ? page * 30 : 0,               
                    orders = res.slice(start,start+30),                      
                    html = this.trTemplate(url,orders,(start+1)),                    
                    id = $(target).attr('id');



                if(orders.length >= 30){
                    if(parseInt(page) < 1){
                        $('.paginate_button.first,.paginate_button.previous').addClass('disabled');                        
                        $('.paginate_button.next,.paginate_button.last').removeClass('disabled'); 
                    }else if(parseInt(page)+1 >= $('#example_last').attr('data-dt-idx')){
                        $('.paginate_button.first,.paginate_button.previous').removeClass('disabled');                        
                        $('.paginate_button.next,.paginate_button.last').addClass('disabled');                        
                    }else{
                        $('.paginate_button.next,.paginate_button.last,.paginate_button.last,.paginate_button.first,.paginate_button.previous').removeClass('disabled');
                    }
                }else{
                    $('.paginate_button.next,.paginate_button.last,.paginate_button.last,.paginate_button.first,.paginate_button.previous').addClass('disabled');
                }


                const formatte = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,      
                        maximumFractionDigits: 2,
                     });

                var order = 0,open = 0,ship = 0,total = 0;
                    for(var i=0; i < orders.length; i++){
                        order += (orders[i].TotalQTYUnits > 0) ? parseFloat(orders[i].TotalQTYUnits) : 0;
                        open += (orders[i].TotalOpen > 0) ? parseFloat(orders[i].TotalOpen) : 0;
                        ship += (orders[i].TotalShipped > 0) ? parseFloat(orders[i].TotalShipped) : 0;
                        total += (orders[i].DocTotal > 0) ? parseFloat(orders[i].DocTotal) : 0;
                    }

                $(".orderList tbody").html(html)
                    $(".orderList tfoot tr td:nth-child(2)").html(order);
                    $(".orderList tfoot tr td:nth-child(3)").html(open);
                    $(".orderList tfoot tr td:nth-child(4)").html(ship);
                    $(".orderList tfoot tr td:nth-child(5)").html("$"+formatte.format(total));

                var d1= 1;
                var d2= res.length;
                if(res.length > 30){
                    var d2= 30;
                }
                var d3= res.length;
              
                if(orders.length > 0){
                    $(".orderList tfoot tr#paginationId td.testTable .fa-pull-left.recordTotal").html("Displaying " +  d1 + " to " + d2 + " out of " + d3);
                }else{
                    $(".orderList tfoot tr#paginationId td.testTable .fa-pull-left.recordTotal").html('-');
                    $('.pagination .cdatatableDetails .direct-serach').val(0)
                    $('.pagination .cdatatableDetails .total').val(0)
                    $('a#example_next,a#example_last').addClass('disabled');
                }

                if(id == 'order_po_serach'){
                    console.log("It`s Order Search");
                }

                totalpage  = Math.ceil((res.length)/30);
                console.log("totalpage",totalpage)
                $('.direct-serach').attr('data-dt-idx',parseInt(pages)).val((pages< 1?1:pages));
                $('.paginate_button.last').attr('data-dt-idx', parseInt(totalpage));
                if(totalpage == 0){
                    totalpage = 1;
                }
                $('.cdatatableDetails span.total').html(parseInt(totalpage)).attr('data-dt-idx', parseInt(totalpage));
            },

            orderSearched: function(arr,value){
                var res = [],i,j = 0,val = value,input='';
                var po_numer = val.toLowerCase();
                for (i = 0; i < arr.length; i++) {
                    if(arr[i]['NumatCardPo']){
                        if(arr[i]['NumatCardPo'].toLowerCase().indexOf(po_numer) > -1 && j < 10){
                            res.push(arr[i]);
                          }
                        
                    }
                    if(arr[i]['DocNum']){
                        if(arr[i]['DocNum'].toLowerCase().indexOf(po_numer) > -1 && j < 10){
                            res.push(arr[i]);
                        }
                    }
                }
                for (i = 0; i < arr.length; i++) {
                    if(arr[i]['Id']){
                       if(arr[i]['Id'].toString().substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10){
                            res.push(arr[i]);
                          }
                        }
                }

                if(val == '')
                {
                    res = arr;
                }

                return res;
            },


            getSearchedOrderData: function(url,input,$widget){
                 $widget = this
                return $.ajax({
                  url: url + "customerorder/dashboard/getSearchRes",
                  type: "POST",
                  async: false ,
                  data: { is_search: 1,search:input},
                  showLoader: false,
                  cache: false,
                  success: function() { $widget.hideLoader(); }
                });
            },

            showLoader: function() {
                $(".custome_order_loader").css("display", "block");
            },

            hideLoader: function() {
                    $(".custome_order_loader").css("display", "none");
            }


        }

    });