/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

requirejs([
    'jquery',   
    'magnificPopup',
    'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
    'mage/mage' ,       
    'datatables.net' ,      
    'datatables.net-buttons',
    'datatables.buttons.html5',
    'mage/template',
], function ($,magnificPopup,inventory,DataTable) {
    'use strict';  

    var table =  '';

        _init();
        function _init() {
            _EventListener();
            _renderInventoryTable();
        }

        function _EventListener(){
            var $widget = this;
            $(document).on('keyup', '#DataTable1_filter input', function (event) {
                return _SearchfromTable($(this), event);
            });

            $(document).on('click', 'a#DataTable1_next,a#DataTable1_last,a#DataTable1_first,a#DataTable1_previous', function () {
                    highlight($("#DataTable1_filter input").val());
            });
            $(document).on('keypress', '.cdatatableDetails input.direct-serach', function (e) {
                this.value = this.value.replace(/\D/g,'');
                if (e.which == 13) {
                  setTimeout(function(){
                        highlight($("#DataTable1_filter input").val());
                   },500)
              }
            });
            $(document).on('mouseover', '.customerorder-customer-inventorydata .close.mfp-close', function () {
                $(this).css('cursor','pointer');
            });
            $(document).on('input', '#DataTable1_filter input', function (e) {
                this.value = this.value.replace(/[^a-zA-Z0-9 _]/g, '');
            });

            $(document).on('click', '#DataTable1 tbody tr td:first-of-type', function () {
                return _openImagePopup($(this));
            });
            $(document).on('click', '#DataTable1 th[class^="sorting"]', function () {
               setTimeout(function(){
                    highlight($("#DataTable1_filter input").val());
               },500)
            });
            
        }

        function _openImagePopup($this){
            var baseurl = window.location.origin;
            var mobilelogo = baseurl+"/pub/media/logo/image/mobile-logo-white.png"
            $.magnificPopup.open({
              items: {
                src: $this.closest('tr').attr("u_image"),
              },        
              type: "image",          
              closeOnBgClick: true,
              mainClass: 'mfp-with-zoom mfp-zoom-in mfp-img-mobile',
              preloader: true,
              tLoading: "",       
              callbacks: {
                    beforeOpen: function() {
                       this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
                       this.st.mainClass = 'mfp-zoom-in';
                    },
                    open: function() {
                        $('.mfp-content figure').prepend('<div class="container order-img-header bg-primary p-2" ><div class="row"><div class="col-md-4 offset-md-4 text-center">   <img src="'+mobilelogo+'"></div><div class="col-md-4"><button type="button" class="close mfp-close"></button></div></div></div>');
                        $('.mfp-content .mfp-figure .mfp-close').first().remove();
                    },              
                },
            });
        }

        function _SearchfromTable($this, event){
            var val = $this.val();
            
            table.columns(0).search(val).draw();
                  
            var tr = $('#DataTable1 tbody tr.selected');
            if (event.keyCode == 40){ //arrow down
                 table.$('tr.selected').removeClass('selected');
                 if(tr.next().length > 0){
                     var trnuevo = tr.next().addClass('selected');                     
                }else{
                    var trnuevo = tr.first().addClass('selected');

                }
                tr = table.$('tr.selected'); // this is what i needed
            }
            if (event.keyCode == 38){ //arrow up
                table.$('tr.selected').removeClass('selected');
                if(tr.prev().length > 0){
                    tr.prev().addClass('selected');
                }else{
                    tr.first().addClass('selected');
                }
                tr = table.$('tr.selected'); // same here                        
            }
           highlight(val)
        }


        function highlight(val){
            var termp = [];
            $("table#DataTable1 td:first-child.sorting_1").each(function(){ 
                termp.push($(this))
            })
            // console.log("data",termp)
            $.each(termp,function(key,$this){ 
                var src_str = $this.text();
                var term = val;
                    term = term.replace(/(\s+)/,"(<[^>]+>)*$1(<[^>]+>)*");
                    var pattern = new RegExp("("+term+")", "gi");
                    src_str = src_str.replace(pattern, "<span class='highlight'>$1</span>");
                    src_str = src_str.replace(/(<span class='highlight'>[^<>]*)((<[^>]+>)+)([^<>]*<\/span>)/,"<span>$1</span>$2<span>$4</span>");
                    $this.html(src_str);
            })
        }

        function _renderInventoryTable(){
            var $widget = this;
            var dataSet = new Array;
            var inventorydata = inventory;
            if(inventory){
                $.each(inventory, function (index, value) {
                    var tempArray = new Array;
                    if(value['ItemCode'] != ''){
                        tempArray.push(value['ItemCode']);
                        tempArray.push(value['Style']);
                        tempArray.push(value['ColorCode']);
                        tempArray.push(value['Size']);
                        tempArray.push(value['ActualQty']);

                        var eta_date_sap = new Date(value['ETA']);
                        var ETA_date = '';
                        if(isNaN(eta_date_sap.getMonth()) || isNaN(eta_date_sap.getDate()) || isNaN(eta_date_sap.getFullYear())){
                            ETA_date = '';
                        }else{
                            ETA_date = Number(eta_date_sap.getMonth() + 1) + '-' + eta_date_sap.getDate() + '-' + eta_date_sap.getFullYear();
                        }

                        tempArray.push(ETA_date);
                        tempArray.push(value['UnitPrice']);
                        tempArray.push(value['DisPrice']);
                        tempArray.push(value['images']);
                        tempArray.push(value['U_WImage1']);
                    }

                    dataSet.push(tempArray);
                });

                // console.log(inventory);

                table =  $('#DataTable1').DataTable({
                    "deferRender": true,
                    dom: 'Bfrtip',
                    buttons: [
                         {
                                extend: 'csv',
                                text : 'Export to CSV',
                                exportOptions : {
                                    modifier : {
                                        // DataTables core
                                        order : 'current',  // 'current', 'applied', 'index',  'original'
                                        page : 'all',      // 'all',     'current'
                                        search : 'applied'     // 'none',    'applied', 'removed'
                                    }
                                }
                            }
                    ],
                    // searchHighlight: true,
                    select: false,
                    "pageLength": 60,
                    "info": false,
                    "ordering": true,
                    "processing": true,
                    // "dom": 't<"top"if><"clear">',  
                    data: dataSet,                           
                    "oLanguage": {
                      "oPaginate": {
                        "sFirst": "<span class='pageIcon first'></span>",
                        "sLast":"<span class='pageIcon last'></span>",
                        "sNext":"<span class='pageIcon next'></span>",
                        "sPrevious":"<span class='pageIcon previous'></span>"
                      }
                    },
                    "pagingType":"full_numbers",
                    "bLengthChange": false, 
                    select: 'single',
                    columns: [
                        { title: "ItemCode" },
                        { title: "Style" },
                        { title: "ColorCode" },
                        { title: "Size" },
                        { title: "Available Qty" },
                        { title: "ETA" },
                        { title: "UnitPrice" },
                        { title: "DisPrice" },
                    ],
                    createdRow: function( row, data, dataIndex ) {
                        $( row ).attr('u_image',data[9]);
                    },
                    drawCallback: function() {
                        var info = this.api().page.info();
                        var start = info.start +1 ;
                        var end = info.end ;
                        $('.cdatatableDetails').remove();
                        $('.paginationRow .recordTotal').text(' Displaying '+start +' to '+end +' of '+ info.recordsDisplay);
                        if(info.recordsDisplay > 0){
                            $('.paginationRow').removeClass('disabled');
                            $('.paginate_button.next').before($('<span>',{
                                'html':'<input type="text" value='+ (info.page+1) +' class="direct-serach"/> / '+info.pages,
                                class:'cdatatableDetails'
                              }));
                        }else{
                            $('.paginationRow').addClass('disabled');
                        }
                        
                        $('#DataTable1_paginate').appendTo('tfoot .paginationRow');                             
                    
                    },
                    footerCallback: function ( row, data, start, end, display ) {
                        var api = this.api(), data; 
                        var intVal = function ( i ) {
                                return typeof i === 'string' ?
                                    i.replace(/[\$,]/g, '')*1 :
                                    typeof i === 'number' ?
                                        i : 0;
                            };        
                    }
                });

                $(".orderItem-loader").hide();

                // table.search('').draw();
                // table.on( 'draw', function () {
                //     var body = $( table.table().body() );
                //     body.unhighlight();
                //     body.highlight( table.search() );  
                // });
                // var info = table.page.info();
               //  $("#invsearch").keyup(function(){
               //      if($(this).val() != ''){
               //          table.search($(this).val()).draw();             
               //      }else{
               //          var info = table.page.info();
               //          $('.paginate_button.next').before($('<span>',{
               //              'html':'<input type="text" value='+ (info.page) +' class="direct-serach"/> / '+info.pages,
               //              class:'cdatatableDetails'
               //            }));
               //          table.search('').draw();
               //      }
               // });

                // $(document).on('input', '#DataTable1_filter input', function(e) {
                //     var val = $(this).val();
                    
                //     var tr = $('#DataTable1 tbody tr:first-of-type');
                //     $('#DataTable1 tbody tr').removeClass('selected');
                //     tr.addClass('selected')
                // });
                

                // $(document).on('keydown', '#DataTable1_filter input', function(e) {
                //     var keycode = (event.keyCode ? event.keyCode : event.which);
                //     if(keycode == 40 || keycode == 38){
                //         var myElement = document.getElementsByClassName("selected"),
                //             topPos = myElement[0].offsetTop;
                //         document.getElementsByClassName('new-container column main')[0].scrollTop = topPos;
                //     }
                // });

               $(document).on('keypress','.direct-serach',function(e){
                    var keycode = (event.keyCode ? event.keyCode : event.which);
                    if(keycode == '13' && $(this).val()){
                        table.page(parseInt($(this).val() - 1)).draw(false);    
                    }
                })
            }
        }
});
