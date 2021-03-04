define(['jquery'],
    function($) {
        'use strict';
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
                    var po_numer = $('#order_po_serach').val().toLowerCase();
                    var data = aData.filter(item => {                   
                         var date = parseInt( this.normalizeDate(item['CreateDate'])) || 0; // use data for the date column
                        if ( ( isNaN( min ) && isNaN( max ) ) ||
                                 ( isNaN( min ) && date <= max ) ||
                                 ( min <= date   && isNaN( max ) ) ||
                                 ( min <= date   && date <= max ) )
                            {
                                if(selected !== '' && typeof selected !== "undefined")
                                    return item.DocStatus == selected;
                                else if(po_numer !== '' && typeof po_numer !== "undefined" && item.NumatCardPo  && typeof item.NumatCardPo !== "undefined"){
                                    var str1 = item.NumatCardPo.toLowerCase() || 0;
                                    var found = str1.indexOf(po_numer) > -1;
                                    if(item.NumatCardPo == po_numer){
                                        return item;
                                    }else if(found){
                                        return item;
                                    }
                                }
                                else
                                    return item;

                            }
                    })                    
                    return data;                        
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
            filterOrder: function(url,data,target, columntarget = '')
            {  
                if($('#order_stats').val() != '' || $('#date-from').val() != '')
                    data = this.DateRangeFiltering(data)                
                    var orders = {};                
                    var pages = target.attr('data-dt-idx');
                    var page = parseInt(target.attr('data-dt-idx')- 1);                
                    var start = pages > 1 ? page * 30 : 0;    
                    // if(data.length)            
                    orders = data.slice(start,start+30);  
                   
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'POST',                                                        
                        data:  {data:orders || ['0'], page:page ,total :data.length},
                        beforeSend: function() {                    
                            $('.orderItem-loader').show();
                        }
                    }).done(function(data) {  
                        $('.orderItem-loader').hide();                  
                        if(!data.errors){
                            $(".orderList").html(data.html);                                                
                            if(columntarget != ''){
                                var type = columntarget.attr('sort-type');
                                var column = columntarget.attr('data-sort-by');                                                 
                                if( type === 'DESC'){                             
                                    $('th[data-sort-by="'+column+'"]').attr('sort-type','ASC');                                
                                    $('th[data-sort-by="'+column+'"]').find('i').removeClass('fa-sort fa-sort-down').addClass('fa-sort-up');
                                }else{
                                    $('th[data-sort-by="'+column+'"]').attr('sort-type','DESC')
                                    $('th[data-sort-by="'+column+'"]').find('i').removeClass('fa-sort fa-sort-up').addClass('fa-sort-down');
                                }
                            }
                        }else{
                            $('.orderList tbody').html('<tr><td>No data found. </td></tr>')                        
                            return true;
                        }                   
                    });
                    
                }

            }

    });