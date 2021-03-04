define([
        'jquery',
        'customer',          
        'Magento_Ui/js/modal/modal',   
        'jquery/jquery.cookie'               
    ], function($ , script, modal) {
        
        
        var popup1 = $.cookie("close_popup");
        var data = new Array;
        var tqest = [];
        var res_error = false;
        var totalorders;
                
        $('.action-close').click(function() {
            $.cookie("close_popup", 1);
        });


        var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                autoOpen:false,
                clickableOverlay: true,
                modalClass: 'custom-popup-modal',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }],
                opened: function($Event) {
                    // $('.modal-content-1').append($('.action-close').clone());                
                    $('.modal-header , .modal-footer').hide();                
                },
                closed:function($Event) {
                    $('.modal-header , .modal-footer').hide();        
                }
            };
            // var totalorders = this.options.ordertotal;
            var popup = modal(options, $('#custom-popup-modal'));


        $.widget('mage.orderview', {

            options: {
                /* order collection */
                BaseUrl : {},

                /**/
                orderlist : new Array(),

                paramBaseUrl: {},

                tqest: {},

            },
            _init: function () { 
                this._EventListener();               
            },
            /*
            *
            */
            _create: function () {
                var self = this;                    
                 $.ajax({
                        type:"GET",
                        url:this.options.paramBaseUrl,
                        dataType:"json",
                        success: function(data) {                                                    
                           var orderlist = JSON.parse(data.order);
                           orderlist.map(function(item){
                                self.options.orderlist.push(item);
                           })                          
                           
                        }
                    });
                if(popup1 != 1)
                   $('#custom-popup-modal').modal('openModal');
                this.PoAjaxreturn();   
                totalorders = this.options.ordertotal;
            },

            /*
            * orde tbody grenated 
            * @return  array
            */
            tbodyGenrated: function(){

                var $widget = this;

            },


            /**
             * Event listener
             *
             * @private
             */
            _EventListener: function () {
                 var $widget = this,
                    options = this.options,
                    target;

                $(document).on('click','#searchfindorder',function(e){                    
                    event.preventDefault();
                        var current_var = $("input#order_search_ins");
                        var len = $('#order_search_ins').val().trim().length;
                        var first = $('#order_search_ins').val().substring(0,1);
                        if(len < 5 || first == ' ')
                        {
                            $('.errormessageorderpage').html('The search string must be at least 5 characters long and can`t start with a space');
                             setTimeout(function(){
                                 $('.errormessageorderpage').html('');
                            },3000);
                            return false;
                        }

                        if(len >= 5 && first != ' '){
                        var moreval = $('#order_search_ins').val().toLowerCase();
                            script.showLoader();
                            setTimeout(function(){
                            script.getSearchedOrderData(options.BaseUrl,moreval,this).then(function(response){
                                if(response.order_data.length == 0){
                                    res_error = true;
                                }
                                if(!response.errors){
                                        $.each(response.order_data,function(key,val){
                                            var can = $(options.orderlist).filter(function(key,a) {
                                                if(a.DocNum == val.DocNum && a.NumatCardPo == val.NumatCardPo && a.Id == val.Id  ){
                                                    return a
                                                }
                                            });
                                            if(can.length <= 0){
                                            options.orderlist.push(val)
                                            }
                                        })
                                }
                            });
                            },500);
                        }
                    script.showLoader();
                         setTimeout(function(){
                            script.searchOrder(options.BaseUrl, options.orderlist,current_var ,totalorders,moreorder="1",keyCode=13);
                        },500)
                });
                $(document).on("mouseover", "#searchfindorder", function() {
                    $(this).css("background","#0e4169");
                    $(this).css("color","#fff");
                });
                $(document).on("mouseout", "#searchfindorder", function() {
                    $(this).css("color","#0e4169");
                    $(this).css("background","#fff");
                });

                $(document).on('click','.paginate_button',function(e){                    
                    event.preventDefault();
                    script.filterOrder(options.BaseUrl, options.orderlist, $(this));
                });

                $(document).on('input','#order_search_ins',function(e){                    
                    var order_po = $('#order_search_ins').val();
                    // var keyCode = e.keyCode || e.which;
                    script.searchOrder(options.BaseUrl, options.orderlist, $(this), totalorders);
                   
                });

                $(document).on('keypress','#order_search_ins',function(e){                    
                    var order_po = $('#order_search_ins').val();
                    var keyCode = e.keyCode || e.which;
                    if(keyCode == 13){

                        var len = $('#order_search_ins').val().trim().length;
                        var first = $('#order_search_ins').val().substring(0,1);

                        if(len < 5 || first == ' ')
                        {
                            $('.errormessageorderpage').html('The search string must be at least 5 characters long and can`t start with a space');
                             setTimeout(function(){
                                 $('.errormessageorderpage').html('');
                            },3000);
                            return false;
                        }

                    
                        if(len >= 5 && first != ' ' && keyCode == 13){
                        var moreval = $('#order_search_ins').val().toLowerCase();
                            script.showLoader();
                            setTimeout(function(){
                            script.getSearchedOrderData(options.BaseUrl,moreval,this).then(function(response){
                                if(response.order_data.length == 0){
                                    res_error = true;
                                }
                                if(!response.errors){
                                        $.each(response.order_data,function(key,val){
                                            var can = $(options.orderlist).filter(function(key,a) {
                                                if(a.DocNum == val.DocNum && a.NumatCardPo == val.NumatCardPo && a.Id == val.Id  ){
                                                    return a
                                                }
                                            });
                                            if(can.length <= 0){
                                            options.orderlist.push(val)
                                            }
                                        })
                                }
                            });
                            },500);
                        }                        
                        var current_var = $(this);
                        setTimeout(function(){
                            script.searchOrder(options.BaseUrl, options.orderlist,current_var ,totalorders,moreorder="1",keyCode);
                        },500)
                    }
                   
                });


                $(document).on('change', '#order_stats', function(e) {
                    var ser = this.value;   
                    var filteredOrder = script.DateRangeFiltering(options.orderlist)
                    $('input.direct-serach').attr('data-dt-idx',1);
                    script.filterOrder(options.BaseUrl, options.orderlist,  $('.direct-serach'));
                });

                // $().dateRange({
                $('#date-range').dateRange({
                    buttonText: 'Select Date',
                    dateFormat: 'MM-dd-yyyy',
                    changeMonth: true,
                    changeYear: true,
                    maxDate:  new Date($.now()),
                    from: {
                        id: 'date-from'
                    },
                    to: {
                        id: 'date-to'
                    }
                });

                $(document).on('click', 'button.themeBtn.action.dateRangeFilter', function(e) {
                    e.preventDefault();
                    var ser = this.value;                    
                    $('input.direct-serach').attr('data-dt-idx',1);
                    script.filterOrder(options.BaseUrl, options.orderlist,  $('.direct-serach'));
                });

                $(document).on('click', 'button.themeBtn.btnreset.action.save.ml-2', function(e) {
                    event.preventDefault();
                    var ser = this.value;  
                    var dates = $("input#date-from, input#date-to")
                    dates.each(function(){
                            $.datepicker._clearDate(this);
                    });                  
                    $('input.direct-serach').attr('data-dt-idx',1);
                        $('select#order_stats').prop('selectedIndex',0);
                    script.filterOrder(options.BaseUrl, options.orderlist,  $('.direct-serach'));
                });


                $(document).on('click','.orderList tr .option-redirect',function(e){
                    var redirect = $(this).closest('tr').attr('option-redirect')
                    console.log(redirect)
                    if(redirect != ''){
                        window.location.href = redirect;
                    }
                });

                $(document).on('keypress','input.direct-serach',function(e){
                    var key = e.which;
                    var currentPage = $(this).attr('data-dt-idx');
                    var val = parseInt($(this).val());
                    var total = $('.paginate_button.last').attr('data-dt-idx');
                    if(key == 13){
                        if(val != currentPage && val != ''  && val > 0 &&  val < parseInt(total))
                            $(this).attr('data-dt-idx',val)
                            script.filterOrder(options.BaseUrl,options.orderlist, $(this));
                    }
                });

                var sortORder = this.options.orderlist;
                $(document).on('click','th.item-sort',function(e){
                    var sortColumn = $(this).attr('data-sort-by');
                    var type = $(this).attr('sort-type');
                    if(type == 'ASC'){
                        $(this).attr('sort-type','DESC');
                    }else{
                        $(this).attr('sort-type','ASC');
                    }
                    if(sortColumn !== 'CreateDate'){
                        var filteredOrder = script.SortArray(sortORder,sortColumn,type)
                    }else{
                        var filteredOrder = script.SortDateArray(sortORder,type)
                    }
                    var res = script.filterOrder(options.BaseUrl,filteredOrder, $('.direct-serach'),$(this));
                });
            



            },

            /*
            * tfoot created
            */
            _tfooter: function(){

            },
            PoAjaxreturn : function(){
                               

            },

            initialize: function (config) {
               
                this._super();


                return this;
            },            
            
        });

        return $.mage.orderview;
  }); 