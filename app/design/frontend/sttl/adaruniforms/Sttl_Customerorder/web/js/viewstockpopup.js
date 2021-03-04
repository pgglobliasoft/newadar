require(
['jquery', 
'Magento_Ui/js/modal/modal',
'mousewheelScroll',
],function($, modal){
        jQuery(document).ready(function() {

            /**  popup dragble start **/

        // function makeDragable(dragHandle, dragTarget) {
        //   let dragObj = null; //object to be moved
        //   let xOffset = 0; //used to prevent dragged object jumping to mouse location
        //   let yOffset = 0;

        //   document.querySelector(dragHandle).addEventListener("mousedown", startDrag, true);
          
        //   function startDrag(e) {
        //     e.preventDefault();
        //     e.stopPropagation();
        //     dragObj = document.querySelector(dragTarget);
        //     dragObj.style.position = "absolute";
        //     let rect = dragObj.getBoundingClientRect();
            

        //     if (e.type=="mousedown") {
        //       xOffset = e.clientX - rect.left; 
        //       yOffset = e.clientY - rect.top;
        //       window.addEventListener('mousemove', dragObject, true);
        //     }
        //   }

        //   /*Drag object*/
        //   function dragObject(e) {
        //     e.preventDefault();
        //     e.stopPropagation();
        //     if(dragObj == null) {
        //       return; 
        //     } else if(e.type == "mousemove") {
        //         $('#popupModal.modal.show .modal-dialog').addClass('drag-popup');

        //         var cx = e.clientX-xOffset,
        //             cy =  e.clientY-yOffset;
        //             if (cx < 0) {cx = 0; }
        //             if (cy < 0) {cy = 0; }

        //             var width = dragObj.offsetWidth,
        //             height = dragObj.offsetHeight;
                    
        //             if (window.innerWidth - e.clientX + xOffset < width) {
        //               cx = window.innerWidth - width;
        //             }
        //             if($('#popupModal .modal-content').height() > window.innerHeight){
        //                  cy = 0;
        //             }else{
        //                if (e.clientY > window.innerHeight - height + yOffset) {
        //                   cy = window.innerHeight - height;
        //                 }
        //             }                    

        //           dragObj.style.left = cx +"px"; 
        //           dragObj.style.top = cy +"px";
        //     } 
        //   }

        //   /*End dragging*/
        //   document.onmouseup = function(e) {
        //     var p = $('#popupModal .modal-content');
        //          var offset = p.offset();
        //          var scrollTop = p.scrollTop();
        //          // console.log("offset",offset,scrollTop)

        //     if (dragObj) {
        //       dragObj = null;
        //       console.log("remove")
        //       window.removeEventListener('mousemove', dragObject, true);
        //       console.log("dragObject",dragObject)
        //     }
        //   }
        // }

        // makeDragable('#popupModal .modal-dialog #cart-form .modalContainer .bg-primary', '#popupModal .modal-content')

            /**  popup dragble end **/

                var base_url = window.location.origin;
                var mainselector = $("#color-data");

                $(document).on("click", ".product-info .swatch-option.image",function()   {              
                    $('.swtach div , .swatch-option.image').removeClass("active");
                    $(this).addClass("active");
                    
                    const id = $(this).attr('option-color-code');
                    const idcolor = $(this).attr('option-core-color-name') ?  $(this).attr('option-core-color-name') : $(this).attr('option-fashion-color-name');
                   $('span.selectcolorsampletable').html(idcolor)
                    $(".option-thumbnail div , .colorImage").removeClass("active");
                    $('#'+$(this).attr("option-id")+'DR'+id).addClass("active");
                    
                    $("#"+$(this).attr('id')+'Class').addClass('active');
                    
                    var newSta = $(this).attr('option-color-status');
                    $(".colorstatus #Status").text(newSta);
                    
                    var fid = $(this).attr("href");
                    var itemId = fid.substring(1, fid.length);
                    setTimeout(function(){
                        if($('#'+itemId+' .colorContainer table.table.table-bordered.table-responsive tbody').width() < $('#'+itemId+' .colorContainer table.table.table-bordered.table-responsive tbody tr').width()){
                             $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','move')
                        }else{
                            $('#popupModal .colorContainer table.table.table-bordered.table-responsive tbody').css('cursor','default')
                        }
                    },200) 
                    
                    var newcolor = $(this).attr('option-core-color-name');
                    $(".core-color-name").text(newcolor);
                    if(newcolor){
                    $(".fashion-color-name").text("");
                        $(".core-color-name").text(newcolor);
                    }
                    var facolor = $(this).attr('option-fashion-color-name');
                    if(facolor){
                        $(".core-color-name").text("");
                        $(".fashion-color-name").text(facolor);

                    }
                 // setTimeout(function() {  $('#popupModal').first().find('.tabactive').focus(); },500);
                });

                $( document ).on( "click", "#removeUser .mfp-close-inside", function( event, ui ) {
                    $('#goback').click();
                });
                


                if($(".product_options").length){
                    $(".product_options").mCustomScrollbar();
                }

                // $(document).keyup(function(e) {
                //     if (e.which === 27) {
                //       $(".productview-modal-close-inside").trigger("click");
                //       $('button.mfp-close.close-image-chart-popup').trigger('click');
                //     }
                // });
                 /**
                 * Event on hice collapse popup
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('hide.bs.collapse', ".collapse", function(e){
                    if($("#"+this.id).parent().find(".qty_change").val() > 0)
                    {
                        return false;
                    }
                });

                $(document).on("click",".colorContainer table.sampaltetabledesable tr td.qtyTd",function() {
                    $("div#nav-tab .error-color span").html("Please Select color.");
                    $(".error-color").slideDown(500);
                    setTimeout(function(){ $(".error-color").slideUp()}, 4000);
                });
                /**
                 * Event on show collapse popup
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('show.bs.collapse', ".collapse", function(e){

                    if($('.collapse.show').length > 0)
                    {
                        var nextopenid = this.id;
                        $('#activetab_id').val(nextopenid);
                        var activetavinputs = $(".collapseContainer a[aria-expanded=true]").next('div.show').find('.checkvalue');
                        var valIsExists = false;
                        var is_qty_change = $(".collapseContainer a[aria-expanded=true]").find(".qty_change").val();
                        var prev_obj_id = $(".collapseContainer a[aria-expanded=true]").next('div.show').attr("id");
                        var is_qty_change = 0;
                        if (typeof prev_obj_id !== "undefined") {

                            is_qty_change = $("#qty_change_"+prev_obj_id.replace("/", "")).val();
                        }

                        $(activetavinputs).each(function() {
                            if ($(this).val() != '')
                            {
                                valIsExists = true;

                            }
                        });

                        if(valIsExists && is_qty_change == 1)
                        {
                            var delid = '';
                            var opt = {autoOpen: false};
                                var theDialog = $("#removeUser").dialog(opt);
                                theDialog.dialog("open");
                                return false;

                        } else {
                            showtotal();
                        }

                        $("#cart-form a[data-toggle='collapse']").removeClass("collapse").addClass("collapsed").attr("aria-expanded", false);
                        var getactivetab_id = $('#activetab_id').val();
                        $('#'+getactivetab_id).siblings('a').attr("aria-expanded", true).removeClass("collapsed").addClass("collapse");
                        $('.collapse').removeClass("show");
                        $('.collapse').addClass("hide");
                        $(this).focus();
                    }            
                    showtotal();
                });
                /**
                 * popupModal on close add custom code 
                 * @Custom https://i.imgur.com/d3RUdGb.png
                */     
                $('#popupModal').on('hidden.bs.modal', function () {
                    $("#cart-form #sap_ponumber_id, #cart-form .tabactive, #order_id").val('');
                    //$('.discardChng').hide();
                    $("#cart-form .qty_change").val(0);
                    $("#cart-form #select_existing option:selected").prop("selected", false);
                    $("#cart-form #select_existing, #cart-form .tabactive").prop("disabled", false);
                    $('.collapse').removeClass("show");
                    if($("#quickviewpopup1").is(":visible")){
                        $("body").addClass("modal-open");
                    }
                    $("#msg_text").html('');
                        $("#cart-form .checkvalue").each(function() {
                            $(this).val('')
                            $(this).next("span").html('');
                            var selectprice = $(this).closest('td').find('input[type=hidden]').val();
                            var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                            var selectsize = $(this).closest('td').find('input[name=selectsize]').val();
                            $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
                            $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
                        });
                });
                /**
                 * popupModal on open add custom code 
                 * @Custom https://i.imgur.com/d3RUdGb.png
                */ 
                // getExstingPONumber("<?php echo $customerSession->getCustomerId();?>")
                // $('#popupModal').on('shown.bs.modal', function () {                 
                //     $('#closepopup').val('');
                //     if($(".core-color-section").is(":visible")){
                //         $(".core-color-section").find(".swatch-option.image").first().trigger("click");
                //     }
                //     $(this).find('.modal-body').css({'max-height':'100%'}); 
                //     $(this).find('.modal-dialog').css({'width': '100%'});
                //     // $(this).find('.modal-dialog').css({'margin-right': ($(document).width()/4+20), 'margin-left': ($(document).width()/4+20) });                                                                  
                // });

                /**
                 * Event goback click 
                 * @Custom 
                */
                $(document).on('click','#goback', function(e) {
                    $('#removeUser').dialog('close');
                    $("#closepopup").val(0);
                    var closepopup = $('#closepopup').val();
                    showtotal();

                });

                 /**
                 * Event savecontinue click save po 
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('click','#savecontinue', function(e) {
                    $('#removeUser').dialog('close');
                    $('.collapse').removeClass("show");
                    $('.collapse').addClass("hide");
                    var getactivetab_id = $('#activetab_id').val();
                    $("#cart-form a[data-toggle='collapse']").removeClass("collapse").addClass("collapsed").attr("aria-expanded", false);
                    $('#'+getactivetab_id).siblings('a').attr("aria-expanded", true).removeClass("collapsed").addClass("collapse");
                    submitform();
                    $('#'+getactivetab_id).addClass("show");
                    $('#'+getactivetab_id).focus();
                    $("#cart-form .qty_change").val(0);

                });

                /**
                 * Event checkvalue keypress check valid value or not
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('focusout','.checkvalue', function(e) {
                    $('.discardChng').show();
                    checkvalueUpdate($(this), false);
                });

                $(document).on('keypress','.checkvalue', function(e) {
                    if (e.which != 13 && e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                        return false
                    }
                        if(e.which == 13)
                        {
                          $(".checkvalue").trigger("focusout");
                          $('.tab-pane.fade.show.active .colorContainer .viewpouplink').find('.saveChng').trigger('click');   
                          $(".checkvalue").removeClass("valuess");
                        }
                });
                /**
                 * Event checkvalue keypress check valid value or not
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('keypress,focusout,input','.checkvalue', function(e) {

                    var existponumberText = $('#select_existing :selected').val()
                    var ponumberText = $(".tabactive").val();
                    if(existponumberText != '' || $.trim(ponumberText).length > 0)
                        {
                            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                                  // alert('dfsssssdf');
                                return false;
                            }
                        }
                        else
                        {
                            adderror("Please enter a P.O. number or Select an Existing P.O. before entering Qty’s");
                            return false;

                        }
                });
                /**
                 * Event tabactive keypress check valid po number or not
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('keypress',".tabactive", function(e) {
                    var inputVal = this.value + String.fromCharCode(e.keyCode);
                    var regexAlphanumeric = /^([a-zA-Z0-9\!\@\#\&\*\(\)\-\_\+\/\\\:\;\.\,\>\<\=\|\}\{\]\[]+)$/;
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if (regexAlphanumeric.test(inputVal) == false && keycode != '13') {
                        $(this).next("span").html("Please provide valid PO Number.").show().fadeOut(2500);
                        return false;
                    }

                    if(keycode == '13'){
                        e.preventDefault();
                        if($(".ponum-exist").hasClass("message-error") == false){
                            if(inputVal.length > 4){
                                var authors = $("div#nav-tab .swatch-option.image");
                                authors.each(function() {
                                    if ($(this).hasClass("active")){
                                       $(".tab-pane.show.active").find(".checkvalue").first().focus();
                                    }
                                });
                            }
                        }
                    }
                });
                 $(document).on('click','.editpodashboard',function(){
                    var ponumberval = $(".tabactive").val();
                    $(".tabactive").attr("disabled", false);
                    $(".tabactive").focus();
                    $(this).attr('po_number',ponumberval);
                    $(this).hide();
                 })   

                /**
                 * Event click order discardChng button
                 * @Custom https://i.imgur.com/j9jqr6E.png
                */
                $(document).on('click',".discardChng", function() {
                    var sap_ponumner = $('#sap_ponumber_id, #order_id').val();
                    if(sap_ponumner !='')
                    {
                        removepopupdata(sap_ponumner)
                        updatepopupdata(sap_ponumner)
                        $('#closepopup').val('');
                        $("#cart-form .qty_change").val(0);
                    }else{
                        $("#cart-form .qty_change").val(0);
                        $('#closepopup').val('');
                        $(".checkvalue").each(function() {
                                $(this).val('');
                                $(this).removeClass('valuess');
                                $(this).next("span").html('');
                                var selectprice = $(this).closest('td').find('input[type=hidden]').val();
                                var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                                var selectsize = $(this).closest('td').find('input[name=selectsize]').val();
                                $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
                                $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
                            });
                    }
                })
                /**
                 * Event click order table input qty 
                 * @Custom https://i.imgur.com/9hO1xoK.png
                 */
                $(document).on('click',"#chekout", function() {
                    $('#closepopup').val(0);
                    var existponumberText = $('#select_existing :selected').val()
                    var ponumberText = $(".tabactive").val();
                    if(existponumberText != '' || jQuery.trim(ponumberText).length > 0 )
                    {
                        var checkinputval = $(".product_options").find('.checkvalue');
                        var valIsExists = false;
                        $(checkinputval).each(function() {
                            if ($(this).val() != '')
                            {
                                valIsExists = true;
                            }
                        });
                        var IsExists = false;
                            $('#select_existing option').each(function(){
                                 if (this.text == ponumberText){  
                                        IsExists = true;
                                 }
                            });
                                if(jQuery.trim(ponumberText).length >= 4 || existponumberText != ''){

                                    if($('table.orderList.lineItemsList').length != 0){
                                        $("#chekouthidden").val(1);
                                    submitform();
                                    }else{
                                        var checkinputval = $(".product_options").find('.checkvalue');
                                        var valIsExists = false;
                                        $(checkinputval).each(function() {
                                            if ($(this).val() != '')
                                            {
                                                valIsExists = true;
                                            }
                                        });
                                        if(valIsExists){
                                            $("#chekouthidden").val(1);
                                            submitform();
                                        }else{
                                            adderror('Please provide at least one item quantity to proceed.')
                                        }
                                    }
                                }
                                else
                                {
                                    adderror("PO Number must be a number or letter special character and at least 4 characters long.");
                                }
                    }
                    else
                    {
                        adderror("Please enter a P.O. number or Select an Existing P.O. before entering Qty’s");
                    }

                });
            
                $(document).on('change',"#select_existing", function() {
                    

                    var existponumberText = $('#select_existing :selected').val();
                    var existingorderText = $('#select_existing :selected').attr('order_id');
                    // console.log(existingorderText);
                    if(existponumberText !='')
                    {
                        var opnumber = $('#select_existing :selected').text();
                        $(".tabactive").attr('value',opnumber);
                        // $(".tabactive").prop("readonly", true);
                        $(".tabactive").attr("disabled", true);
                        $('.editpodashboard').show();
                    }
                    updatepopupdata(existponumberText,existingorderText)



                });
                $(document).on('click',".product_options", function() {

                    var existponumberText = $('#select_existing :selected').val()
                    var ponumberText = $(".tabactive").val();
                    if(existponumberText != '' || jQuery.trim(ponumberText).length > 0)
                    {
                        return true;
                    }
                });

                /**
                 * Change for set stick class to product
                 * @Custom
                 */
                var stickclassavil = false;
                $(".product-info-main #buyBtns .buyNowBtnMain").bind("click",function(){    
                        // console.log("stick");

                    $(".product-info-main").addClass("nonstick");
                      if($(".product-info-main").hasClass("stick")){
                        stickclassavil = true;
                        $(".product-info-main").removeClass("stick");
                      }       
                });
                /**
                 * on productview-modal-close-inside close event deduct https://i.imgur.com/jmynUdd.png
                 */
                $(document).on("click","#popupModal .col-md-2.col-sm-2.close_popup", function(){
                    console.log("close")
                      $('.modal-dialog.modal-dialog-centered.modal-lg.drag-popup').removeClass('drag-popup');
                      $('#popupModal .modal-content').css('left','0px');
                });
                
                $('.productview-modal-close-inside').bind('click', function()
                {
                    $('#popupModal.modal.show .modal-dialog').removeClass('drag-popup');
                    $('#popupModal .modal-content').css('left','0px');
                    $(".product-info-main").removeClass("nonstick");
                    $(".checkvalue").removeClass("valuess");
                    setTimeout(function(){
                        $("#popupModal .modal-content").css("left",0);
                        $("#popupModal .modal-content").css("top",0);
                    },10)
                    if(stickclassavil){
                        $(".product-info-main").addClass("stick");
                        stickclassavil = false;     
                    }
                    $('#closepopup').val(1)
                    var activetavinputs = $(".collapseContainer a[aria-expanded=true]").next('div.show').find('.checkvalue');
                    var valIsExists = false;
                    var is_qty_change = $(".collapseContainer a[aria-expanded=true]").find(".qty_change").val();
                    var prev_obj_id = $(".collapseContainer a[aria-expanded=true]").next('div.show').attr("id");
                    var is_qty_change = 0;
                    if (typeof prev_obj_id !== "undefined") {
                        is_qty_change = $("#qty_change_"+prev_obj_id.replace("/", "")).val();
                    }
                    $("#cart-form a[data-toggle='collapse']").removeClass("collapse").addClass("collapsed").attr("aria-expanded", false);

                    $(activetavinputs).each(function() {
                        if ($(this).val() != '')
                        {
                            valIsExists = true;

                        }
                    });
                    if(valIsExists && is_qty_change == 1)
                    {
                        var delid = '';
                        var opt = {autoOpen: false};
                        var theDialog = $("#removeUser").dialog(opt);
                        theDialog.dialog("open");
                        return false;
                    } else {
                        showtotal();
                    }
                });

              
                var image_option = { 
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                                title: 'popup modal title',
                                modalClass: "imgquickViewCont",
                                buttons: [{
                                    text: $.mage.__('Continue'),
                                    class: '',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            };

              
                var imgquickViewCont = modal(image_option, $('#imgquickViewCont'));
                $(document).on("click", "div.option-thumbnail", function(e) {   
                    $("#popupModal > .modal-dialog .modal-content").addClass("custombackshadow")
                    $(".modal-backdrop.show").css({"opacity":"0"});
                    $("#imgquickViewCont").modal("openModal");
                    $('#imgquickViewCont .ProductShow').attr('src',$(this).find('.active img').attr("src"));    
                    
                });

          
                $(document).on("click",".close-image-chart-popup",function () {
                    // $('.modal-backdrop.fade.show').removeClass('z-indexadd');
                    // $('.modals-overlay').removeClass('added');
                    $('.modals-overlay').removeClass('modals-overlay');
                    $("#popupModal > .modal-dialog .modal-content").removeClass("custombackshadow")
                    $('#imgquickViewCont').modal('closeModal');   
                    // $('.modals-overlay').css('opacity', '0');
                    $(".modal-backdrop.show").css({"opacity":"0.5"});

                    $('.modal-backdrop.fade.show').css('opacity', '0.5');
                    this.imgquickViewCont = null;
                }); 

                /*All helper function */

                /*
                 ** after qty add success popup show
                */
                posuccesspopup();

                function posuccesspopup(){
                  var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    modalClass: "po-success-popup",
                    modalCloseBtn: true,
                  };

                  var popup = modal(options, jQuery('#posuccess-message'));
                }
                  function adderror(message)
                  {
                    jQuery("#posuccess-message p").html(message);
                    jQuery("#posuccess-message").modal("openModal");
                    $("#popupModal > .modal-dialog .modal-content").addClass("custombackshadow");
                    jQuery(".modalContainer").css("pointer-events","none");
                    
                  setTimeout(function(){
                    jQuery(".modalContainer").css("pointer-events","");
                    jQuery("#posuccess-message").modal("closeModal");
                    $("#popupModal > .modal-dialog .modal-content").removeClass("custombackshadow")
                  }, 3000);
                    return true;
                  }

                   $(document).on('click', 'aside.modal-popup.po-success-popup.modal-slide._inner-scroll', function(event) {
                        jQuery(".modalContainer").css("pointer-events","");
                        jQuery("#posuccess-message").modal("closeModal");
                  });

                  function addsuccess(message)
                  {
                    jQuery("#posuccess-message p").html(message);
                    jQuery("#posuccess-message").modal("openModal");
                    $("#popupModal > .modal-dialog .modal-content").addClass("custombackshadow");
                    jQuery(".modalContainer").css("pointer-events","none");
                    setTimeout(function(){
                    jQuery(".modalContainer").css("pointer-events","");
                    jQuery("#posuccess-message").modal("closeModal");
                    $("#popupModal > .modal-dialog .modal-content").addClass("custombackshadow");
                  }, 3000);
                    return true;
                  }

                /*
                *Retunn Status on save po call ajax to save po updated/new data
                */
                function removepopupdata(existponumberText){
                    $(".checkvalue").each(function() {
                        $(this).val('')
                        $(this).next("span").html('');
                        var selectprice = $(this).closest('td').find('input[type=hidden]').val();
                        var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                        var selectsize = $(this).closest('td').find('input[name=selectsize]').val();
                        $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
                        $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
                    });
                }
                function updatepopupdata(existponumberText,existingorderText)
                {
                    $(".quickViewbodyloader").css("display", "block");
                    if(existponumberText != '')
                    {
                        $('[data-toggle="collapse"]').prop('disabled',false);
                        $('#overlay').hide()
                        $("#msg_text").html("");
                        $('.discardChng').show();
                        $('#sap_ponumber_id').val(existponumberText);
                        $('#order_id').val(existingorderText);
                        var url = base_url+'/adaruniforms/cart/update';
                        var style_id = $(".product_options").attr("id");
                       
                        // console.log(existponumberText);
                        showtotal();
                        jQuery.ajax({
                            url: url,
                            type: "POST",
                            data: {'po_number' : existponumberText, 'style_id' : style_id},
                            cache: false,
                            success: function(response){
                                $(".quickViewbodyloader").css("display", "none");
                                if(response.success) {
                                    var data = response.data;
                                    if (data != null) {
                                        jQuery.each( data, function( key, value ) {
                                            var colorname = value.ColorName,
                                            size = value.Size,
                                            qty = value.QTYOrdered;

                                            var inputQty = 'qty['+colorname+']['+size+']';
                                            if ($("[name='"+inputQty+"']")) {
                                                var value = $("[name='"+inputQty+"']").val(qty);
                                                checkvalueUpdate($("[name='"+inputQty+"']"), true);
                                            }
                                        });

                                    }
                                }
                            }
                        });

                    }else{
                        $(".quickViewbodyloader").css("display", "none");
                        $(".tabactive").prop("readonly", false);
                        $(".tabactive").val('');
                        $('#sap_ponumber_id, #order_id').val('');
                        //$('.discardChng').hide();
                        $('.collapse').removeClass("show");
                        $("#cart-form a[data-toggle='collapse']").addClass("collapsed").attr("aria-expanded", false);
                        $(".checkvalue").each(function() {
                            $(this).val('')
                            $(this).next("span").html('');
                            var selectprice = $(this).closest('td').find('input[type=hidden]').val();
                            var selectcolor = $(this).closest('td').find('input[name=selectcolor]').val();
                            var selectsize = $(this).closest('td').find('input[name=selectsize]').val();
                            $('input[name="inpprice['+selectcolor+']['+selectsize+']"').val('');
                            $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('');
                        });
                            showtotal();
                    }
                }
                /*
                 *update po swatches table value 
                 */
                function checkvalueUpdate(obj, update)
                {
                    
                    var qty = $(obj).val();                   
                    var maxQty = $(obj).attr("max");
                    var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
                    var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
                    var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();
                    if(qty != '' && $.isNumeric(qty))
                    {
                        // console.log('222')
                        $(obj).addClass("valuess");
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
                        $('input[name="inpprice['+selectcolor+']['+selectsize+']"').closest('td').find('span').html('$'+convertcurrency(price.toFixed(2)));
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
                    showtotal();

                }
                /*
                 *update po swatches qty add to cart
                 */
                function submitform()
                {
                    var $widget = this,
                    current_options = 1;
                    $("#msg_text").html("");
                    var url = base_url+"/customerorder/customer/createorder";
                    var ponumberText = $(".tabactive").val();
                    // _renderLineitembeforeAJAX($widget,current_options);
                    var is_savedata = 'true';
                    jQuery.ajax({
                        url: url,
                        type: "POST",
                        data: $("#cart-form").serialize()+"&is_savedata="+is_savedata+"&po_number="+ponumberText+"&view_stock_js=1",
                        showLoader: true,
                        cache: false,
                        success: function(response){
                            
                            if(response.order_id)
                            {
                                $('#sap_ponumber_id, #order_id').val(response.order_id);
                                $('.discardChng').show();
                            }
                            if(response.success)
                            {
                                addsuccess(response.message);

                            }
                            else
                            {
                                var Exist = false;
                                $("#cart-form .checkvalue").each(function() {
                                    if ($(this).val() != '') {
                                        Exist = true;
                                    }
                                });
                                if (Exist) {
                                    adderror(response.message);
                                }
                                
                                $(".colorContainer .checkvalue").val("");
                                $(".colorContainer .showprice").html("");
                                $(".colorContainer .maxqtyvaldi").html("");
                                $(".colorContainer .checkvalue").removeClass('valuess');
                            }
                            if(response.message) {
                                $("#message").show();
                            }
                            var closepopup = $("#closepopup").val();
                            if(closepopup == 1)
                            {
                                $('.productview-modal-close-inside').trigger( "click" );

                            }
                            var valuescheckout = $("#chekouthidden").val();
                            if(valuescheckout == 1 && response.base64_order_id && response.base64_ncp_id)
                            {
                                var nexturl = base_url+"/customerorder/customer/neworder/id/"+response.base64_order_id+"/ncp/"+response.base64_ncp_id;
                                top.location = nexturl;
                            }
                        }
                    });
                }

                // function _renderLineitembeforeAJAX($this, _current_options, styles = {}, click_event = "") {
                //     quickline.lineitem($this,_current_options,styles = {}, click_event = "");
                // } 
                /*
                 *gte all extistin po number form past order & daft
                 */
                function getExstingPONumber(customid)
                {
                    var ponumber = '';
                    $('.tabactive').val();
                    $(".tabactive").prop("readonly", false);
                    $('#closepopup').val();
                    var url = base_url+'/adaruniforms/index/ponumber';
                    jQuery.ajax({
                        url: url,
                        type: "POST",
                        data: {custom_id: customid,ponumber:ponumber},
                        showLoader: false,
                        cache: false,
                        success: function(response){
                            if(response.success)
                            {
                                $('#select_existing').html(response.success);
                                }else{
                                adderror('Session timed out, please refresh the page.');
                            }
                        }
                    });
                }
                /*
                 *check enter po number in extise in list or not
                 */
                function checkEnterPONumber(ponumber)
                {
                    var url = base_url+"/adaruniforms/index/Ponumbernew";
                    var result = ''
                    jQuery.ajax({
                        url: url,
                        dataType: 'json',
                        type: "POST",
                        data: {ponumber:ponumber},
                        showLoader: false,
                        cache: false,
                        success: function(response){
                            if(response.success)
                            {
                                //addsuccess(response.success);
                                $("#msg_text").removeClass("error");
                                $("#msg_text").html('');
                                $('[data-toggle="collapse"]').prop('disabled',false);
                                $('#overlay').hide()
                                $("#select_existing").prop("disabled", true);
                                $("#select_existing option:selected").prop("selected", false)
                                $('#sap_ponumber_id, #order_id').val('')
                                //$('.discardChng').hide();
                                result = 'true';
                                }else{
                                adderror(response.error);
                                //$('#overlay').show()
                                $('[data-toggle="collapse"]').prop('disabled',true);
                                $("#select_existing").prop("disabled", true);
                                result = 'false';
                            }
                        }
                    });
                    return result;
                }
                /*
                 * After added aty in checkvalue ibput next totl colum show total price * qty
                 */
                function showtotal()
                {
                    var unittotals = $('.product_options').find('.unittotal');
                    var gd_total = 0;
                    $(unittotals).each(function() {
                        if($(this).val() != '')
                        {
                            var total = parseFloat($(this).val());
                            gd_total = gd_total + total;
                        }

                    });
                    var totalprice = convertcurrency(parseFloat(gd_total).toFixed(2));
                    // $('#hi_grandtotal').val(parseFloat(gd_total).toFixed(2));
                    // $('.grandtotal').html('');
                    // $('.grandtotal').html('$'+ totalprice);
                }

                jQuery(window).resize(function(){
                    // alert("aaaaa")
                   jQuery ('.modal-dialog').css({'margin-right': '', 'margin-left': '', 'margin':'1.75rem auto' }); 
                  jQuery('div#importinverdata .modal-content.ui-draggable').css({'top':'0px','left':'0px'});
                });
                /*
                 * convertcurrency total price * qty
                 */
                function convertcurrency(price)
                {
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

                }  

        }); 
        
        
    })