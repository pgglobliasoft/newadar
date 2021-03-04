define([
	'jquery',
	'mage/template',
	"text!Globalia_Quickcheckout/template/quickcheckout.html",
  "text!Globalia_Quickcheckout/template/checkoutlinetable.html",
  "text!Globalia_Quickcheckout/template/orderconformetion.html",
  'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
  'jquery/validate',
	],
	
	function($,mageTemplate,quickcheckout,checkoutlinetable,orderconformetion,inventory) {
		
    var finalitems = [];
    var viewstockHtml = '';

	$.widget('mage.QuickcheckoutRendere', {
      
        options: {
            baseurl: '',
            parentstyledata: inventory,
            customerdata: [],
            cmsdata: ''   
        },
        
        _init: function () {
             var $widget = this;
            this._EventListener();
            

        },
        _create: function() {
          var $widget = this;
           var xLimit = $(window).width() - $("#popupModal .modal-content.ui-draggable").width();
             var yLimit = $(window).height() - $("#popupModal .modal-content.ui-draggable").height()

            $("#popupModal .modal-content").draggable({
                handle: ".container.bg-primary.p-2",
                containment: ".modal-backdrop"
            });
            /* 
             *popup dragable event
             */
            $("#popupModal").mouseup(function(){
                if($('.move').hasClass("move-click")){
                    $(".move").removeClass("move-click")
                    $("#popupModal .modal-body").css({"cursor":""})
                    $("#color-data,.container.bg-light.p-2,#popupModal #message").css({"pointer-events":""})
                }
            })
            $("#popupModal .container.bg-primary.p-2").mousedown(function(){
                $(".move").addClass("move-click")
                $("#popupModal .modal-body").css({"cursor":"move"})
                $("#color-data,.container.bg-light.p-2,#popupModal #message").css({"pointer-events":"none"})
            })
            $(document).on('click',function(e){     
             if (!$(e.target).closest(".container.bg-primary.p-2").length) {              
                if($('.move.movea.move-click').length > 0){
                    $('.move.movea.move-click').removeClass('move-click');
                }
               }
            });
        },
        _EventListener: function(){		
        	var $widget = this;
        	$(document).ready(function(){
            $("input:radio[name=shiiping_method]:selected").find("lable").css({'color':'#0e4169'});

            $('#Country [value="US"]').attr('selected', 'true');
                setTimeout(
                  function(){ 
                    $("#Country").trigger('change');
                    }, 3000);
            $widget.shippingchnage();
            $("#state").children('option:gt(0)').hide();
                $("#Country").change(function() {
                  var selectedoption = $(this).find('option:selected').val()
                  $('#ContryLable').val($(this).find("option:selected").text());
                  $('#show_country').val($(this).find("option:selected").val());
                  $widget.showstateoption(selectedoption)
                }).trigger('change');
                $("#state").children('option:gt(0)').hide();
                $("#state").change(function() {
                  $('#StateLable').val($(this).find("option:selected").text())
                })
 
          })
          document.addEventListener("newordercheckout",function(e){
            if(e.detail.orderod != '' && e.detail.ponumber != ''){
                $widget.quickcheckoutajax(e.detail.ponumber, btoa(e.detail.orderod));
            }
          })

          document.addEventListener("afterupdatepocheckout",function(e){
            if(e.detail.orderod != '' && e.detail.ponumber != ''){
                $widget.quickcheckoutajax(e.detail.ponumber, btoa(e.detail.orderod));
            }
          })

          $(document).on('change',"#select_existing", function() {
            setTimeout(function(){
              if(typeof $('.tabactive').val() != typeof undefined && $('.tabactive').val() != '' && typeof $('.tabactive').attr('base_data') != typeof undefined && $('.tabactive').attr('base_data') != ''){
                $widget.quickcheckoutajax($('.tabactive').val(),$('.tabactive').attr('base_data'));
              }else{
                $('#quickcheckoutelink').addClass("opacity-down");
                $('.container.bg-light.p-2.bottomBtn a#chekout').addClass("opacity-down");
              }
            },200)
          })


            $(document).on("change", ".paymentMethod", function(){
                  var Objsel = $(this).find('option:selected');
                  var cc_no = $(Objsel).attr("attr-ccno"),
                  cc_expiry = $(Objsel).attr("attr-ccexpiry"),
                  attrMethodName = $(Objsel).attr("attr-MethodName"),
                  cc_type = $(Objsel).attr("attr-cctype"),
                  cc_details = [];
                  if (typeof cc_type !== typeof undefined && cc_type !== false && cc_type.trim() != '') {
                    var img = '';
                    if (cc_type == "V") {
                      cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/visa-card.jpg'>");
                    }
                    if (cc_type == "M") {
                      cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/master-card.jpg'>");
                    }
                    if (cc_type == "A") {
                      cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/american-express.jpg'>");
                    }
                    if (cc_type == "DS") {
                      cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/discover.jpg'>");
                    }
                  }
                  
                  if (typeof cc_no !== typeof undefined && cc_no !== false && cc_no.trim() != '') {
                    cc_details.push('<span>'+cc_no+'</span>');
                    cc_details.push('<input type="hidden" id="cc_no_hidden" name="cc_no_hidden" value="'+cc_no+'">');

                  }
                  
                  if (typeof cc_expiry !== typeof undefined && cc_expiry !== false && cc_expiry.trim() != '') {
                    cc_details.push('<span>'+cc_expiry+'</span>');
                    cc_details.push('<input type="hidden" id="cc_expiry_hidden" name="cc_expiry_hidden" value="'+cc_expiry+'">');
                  }
                  if (typeof attrMethodName !== typeof undefined && attrMethodName !== false && attrMethodName.trim() != '') {
                    cc_details.push('<input type="hidden" id="cc_attrMethodName_hidden" name="cc_attrMethodName_hidden" value="'+attrMethodName+'">');
                  }
                  
                  if (cc_details.length) {
                    $(".paymentAddress").html(cc_details.join(" "));
                  }
                  else{ 
                  $(".paymentAddress").html("Please Select");
                  }
              });

            $(document).on('show.bs.modal', '#quickcheckout', function (e) {
                $('div#popupModal .modalContainer').addClass('custombackshadow');
                $('#customer-edit-address').attr('data-keyboard','false');
                $('#customer-edit-address').attr('data-backdrop','static');
                // $('div#popupModal .modalContainer').modal({ backdrop: 'static', keyboard: false });
            });
            $(document).on('hidden.bs.modal', '#popupModal', function (e) {
                if($('#popupModal .modal-dialog #cart-form').hasClass("hide") == true){
                  finalitems =[];
                }
                $('.QuickViewContenthtml div#quickviewpopup1 .modalContainer').removeClass('custombackshadow');
                $('#customer-edit-address').attr('data-keyboard','true');
                $("#quickcheckoutcont").html("").addClass("hide").removeClass("show");
                $("#popupModal .modal-dialog #cart-form").removeClass("show").removeClass("hide");
                $('#quickcheckoutelink').addClass("opacity-down");
                $('.container.bg-light.p-2.bottomBtn a#chekout').addClass("opacity-down");
                $(".backbuttonclose,.quickcheckoutsubmit").css("display","none")
                $("#quickcheckoutelink,.bottomBtn #chekout").css("display","block")      
                $(".popup-animation").removeClass("scroll-colordata");

            });

            $(document).on('hidden.bs.modal', '#quickcheckout', function (e) {
                $('div#popupModal .modalContainer').removeClass('custombackshadow');
                $('.quickcheckouthtml div#quickcheckout div#quickcheckoutdetail').html("<div class='orderItem-loader' style='/* display: none; */background: unset;'> <div class='cf loaderAdd'> <div class='lds-ellipsis' style='display: block;'><h1>Loading</h1> <div></div> <div></div> <div></div> <div></div> <div></div> <div></div></div> </div> </div>");
                $('body').addClass('modal-open');
                $('#customer-edit-address').attr('data-keyboard','true');
            });
            $(document).on('show.bs.modal', '#customer-edit-address', function (e) {
            	$('div#popupModal .modalContainer.quickViewCont').addClass('custombackshadow');
                // $('.quickcheckouthtml div#quickcheckout .modalContainer').addClass('custombackshadow');
            });
            $(document).on('hidden.bs.modal', '#customer-edit-address', function (e) {
            	$('div#popupModal .modalContainer.quickViewCont').removeClass('custombackshadow');
                // $('.quickcheckouthtml div#quickcheckout .modalContainer').removeClass('custombackshadow');
            });
            $(document).on('show.bs.modal', '#customer-add-payment', function (e) {
            	$('div#popupModal .modalContainer.quickViewCont').addClass('custombackshadow');
                // $('.quickcheckouthtml div#quickcheckout .modalContainer').addClass('custombackshadow');
            });
            $(document).on('hidden.bs.modal', '#customer-add-payment', function (e) {
            	$('div#popupModal .modalContainer.quickViewCont').removeClass('custombackshadow');
                // $('.quickcheckouthtml div#quickcheckout .modalContainer').removeClass('custombackshadow');
            });
            $(document).on('show.bs.modal', '#popupModal', function (e) {
                $('.QuickViewContenthtml div#quickviewpopup1 .modalContainer').addClass('custombackshadow');
                $('#customer-edit-address').attr('data-keyboard','false');
                $('#customer-edit-address').attr('data-backdrop','static');
                $("#quickcheckoutelink,.bottomBtn #chekout").css({"opacity":"","display": "block"})
                $(".backbuttonclose,.quickcheckoutsubmit").css({"opacity":"","display": "none"})

            });
            
            $(document).on("click","#popupModal .backbuttonclose", function(){
            	$("#popupModal .modal-dialog #cart-form").addClass("show").removeClass("hide");
            	$("#quickcheckoutcont").addClass("hide").removeClass("show");   
              $(".backbuttonclose,.quickcheckoutsubmit").css("display","none")
              $("#quickcheckoutelink,.bottomBtn #chekout").css("display","block")      
              $("#quickcheckoutelink,.bottomBtn #chekout").addClass("addanimation")      
              $(".popup-animation").removeClass("scroll-colordata");
            });

          $(document).on('click',".submitorder_sttl",function(e){
            e.preventDefault();
            $('.submitorder').trigger('click');
          });


        	$(document).on('click', '#quickcheckoutelink', function () {
            $('#quickcheckout').addClass("position");
            // $(".quickViewbodyloader").css("display", "block");
            // $widget.quickcheckoutajax();   
            $("#quickcheckoutcont").removeClass("hide").addClass("show"); 
            $("#popupModal .modal-dialog #cart-form").removeClass("show").addClass("hide"); 
            $(".backbuttonclose,.quickcheckoutsubmit").css("display","block")
            $("#quickcheckoutelink,.bottomBtn #chekout").css("display","none") 
            $(".popup-animation").addClass("scroll-colordata");
            $("#popupModal #quickcheckoutdetail").css({"height": $(".container.product-info").height(), "overflow":"auto"})
        	});

         

            $(document).on("keyup", "#card_no", function(){
                var value = $(this).val();
                var ctype = '';
                // visa
                var re = new RegExp("^4");
                if (value.match(re) != null)
                  ctype =  "VI";
                if (/^(5[1-5][0-9]{14}|2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12}))$/.test(value)) 
                  ctype =  "MC";
                // AMEX
                re = new RegExp("^3[47]");
                if (value.match(re) != null)
                  ctype = "AE";
                
                // Discover
                re = new RegExp("^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)");
                if (value.match(re) != null)
                  ctype = "DI";
                $("input[name='card_type']").removeClass("selected_cc").prop('checked', false).attr("disabled", true).next('span').removeClass("img-container selected_cc");
                if(value != ''){
                $("input[name='card_type'][value='"+ctype+"']").prop('disabled', false).prop('checked', true).addClass("selected_cc").next('span').addClass("img-container selected_cc");
                }

            });

              $(document).on("keyup", "#expiration_date", function(){
                  var value = $(this).val().replace("/","");
                  var str = '';
                  for (var i=0,ic=value.length;i<ic;i++) {
                    str += value[i];
                    if (str.length == 2)
                      str += "/";
                  }
                  $(this).val(str);
              }); 


                  var changeText = "Please Wait...";
                  var changeSubmit = "Submit";
                  var findButton = $('#customer-add-payment-validate').find('button[type=submit]'),
                  paymentform = $('#customer-add-payment-validate');
                  paymentform.submit(function (e) {
                    if (paymentform.validation('isValid')) {
                      findButton.text(changeText);
                      findButton.attr("disabled", "disabled");
                      var url = paymentform.attr('action');
                      var formData = $widget.getFormData($(this));
                      e.preventDefault();
                        $.ajax({
                          type: "POST",
                          url: url,
                          showPopupLoader: true,
                          data: formData,
                          success: function(data) {
                            var message_from = "payment";
                            $widget.showResponse(data,message_from);
                            if(data.error) {
                                            findButton.text(changeSubmit);
                                            findButton.removeAttr('disabled');
                              }  
                              else 
                              {
                                    if(data.html != '')
                                    {
                                      $('#selectcard_id').html(data.html)
                                    }
                                     if(data.html_popup == 'done'){
                                      findButton.text(changeSubmit);
                                      findButton.removeAttr('disabled');
                                     }
                                  $widget.selectcardinfo();
                                  $(".mfp-close-inside").trigger("click");
                                  $('#paymentshoe').show();
                                  $('#selectcard_id').show();
                                } 
                            }
                          });
                        }
                      });                
              


                    
                    var addressform = $('#customer-edit-address-validate');
                    addressform.submit(function (e) 
                    {
                      e.preventDefault();
                      var changeText = "Please Wait...";
                      var changeSubmit = "Submit";
                      var findButton = $('#customer-edit-address-validate').find('button[type=submit]');
                      if (addressform.validation('isValid')) 
                      {
                          var url = addressform.attr('action');
                          var formData = $widget.getFormData($(this));
                          var getalloptions = $("#shippingaddress>option");
                          var valIsExists = false;
                          $(getalloptions).each(function() 
                          {
                             if ($.trim($(this).attr("attr-cardname")).toLowerCase() == $.trim(formData.fullname_shiiping).toLowerCase()) 
                             {
                                valIsExists = true;
                             }
                          });
                          if(valIsExists)
                          {
                              $widget._showResponse('',"It seems like this address was previously added and saved. If you'd like to add it anyway please change the name in the 'Full Name' field to differ from the one you already added.");
                              return false;
                          }
                          findButton.text(changeText);
                          findButton.attr("disabled", "disabled");
                          if ($('#save_future').is(":checked"))
                          {
                              $.ajax({
                                    type: "POST",
                                    url: url,
                                    showPopupLoader: true,
                                    data: formData,
                                    success: function(data) {
                                       var message_from = "";
                                        $widget._showResponse(data,'');
                                        if(data.error) {
                                          findButton.text(changeSubmit);
                                          findButton.removeAttr('disabled');
                                        } else {
                                          //$("#customer-edit-address").modal('hide');
                                       }
                                    }
                                 });
                          }
                          var shippingaddresscount = $('#shippingaddress option').length;
                          if(formData.blindDropship == '')
                          {
                            formData.blindDropship = 0;
                          }
                          $('#shippingaddress').append($("<option></option>")
                          .attr("value",shippingaddresscount)
                          .attr("attr-cardname",formData.fullname_shiiping)
                          .attr("attr-addressid",formData.fullname_shiiping)
                          .attr("attr-addr1",formData.AddStreetNo)
                          .attr("attr-addr2",formData.address2)
                          .attr("attr-state",formData.state)
                          .attr("attr-zipcode",formData.zipcode)
                          .attr("attr-country",formData.Country)
                          .attr("attr-city",formData.city+', '+formData.state+' '+formData.zipcode)
                          .attr("attr-city1",formData.city)
                          .attr("attr-blindDropship",formData.blindDropship)
                          .attr("attr-tel",'')
                          .text(formData.fullname_shiiping)); 

                        
                          var data = '';
                          $widget._showResponse(data,'')
                          $("#shippingaddress").val(shippingaddresscount).change();
                          $widget.shippingchnage();

                      }
                  });
               
          
              $('.modal.block-customer-edit-address').on('hidden.bs.modal', function(){
                $('#customer-edit-address-validate').find('input[type="text"]').val('');
                $('#Country [value="US"]').attr('selected', 'true');
                $("#Country").trigger('change');
                $('#customer-edit-address-validate').find('input[type=checkbox]').attr('checked', false);
                var validator = $("#customer-edit-address-validate").validate();
                validator.resetForm();
              });
              $('.modal.block-customer-add-payment').on('hidden.bs.modal', function(){
                $('#customer-add-payment-validate').find('input[type="text"]').val('');
                var validator = $("#customer-add-payment-validate").validate();
                validator.resetForm();

                $("input[name='card_type']").removeClass("selected_cc").prop('checked', false).attr("disabled", true).next('span').removeClass("img-container");
              });
              $(".paymentMethod").change();
              $(document).on("click",".submitorder",function(e){
                    form = $('#payemtnaction');
                    if (form.validate('isValid')) {
                        e.preventDefault();
                        var url = $widget.options.baseurl + "customerorder/customer/submitorder";
                        var payment = $widget.getFormData($("#payemtnaction"));
                        var cardform = $widget.getFormData($('.custom-customer-add-payment'));
                        var billingform = $widget.getFormData($("#customer-edit-billaddress-validate"));
                        var shiipingform = $widget.getFormData($("#customer-edit-address-validate"));
                        var selectcard_id = $("#selectcard_id :selected").val();
                            if(selectcard_id == '')
                            {
                                $('.selectcard_id_error').html("Please Select Payment Method.").fadeIn();
                                setTimeout(function(){   $('.selectcard_id_error').fadeOut(1000); }, 3000);
                                $('#selectcard_id').focus();
                                return false;
                            }
                            var shiiping_method = $("input[name='shiiping_method']:checked").val();
                            var shippingaddress = $("#shippingaddress :checked").val();
                            if(shippingaddress == '' && (shiiping_method != '4'))
                            {
                                $('.shiiping_method_shipAddress').html("Please Select Shipping Address.").fadeIn();
                                setTimeout(function(){   $('.shiiping_method_shipAddress').fadeOut(1000);  }, 3000);
                                $('#shippingaddress').focus();
                                return false;
                            }
                            
                            if(shiiping_method  == 'checked')
                            {
                                $('.shiiping_method_error').html("Please Select.").fadeIn();
                                setTimeout(function(){   $('.shiiping_method_error').fadeOut();}, 3000);
                                $('#shiiping_method').focus();
                                return false;
                            }
                              if ($('input[name="shiiping_method"]:checked').length == 0) {
                                  $('.shiiping_method_error').html("Please Choose Shipping Method.").fadeIn();
                                    setTimeout(function(){   $('.shiiping_method_error').fadeOut();}, 3000);
                                    $('#shiiping_method').focus();
                                 return false; 
                                } 

                        // $('body').trigger('processStart');
                        $widget.showPopupLoader()
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: {selectcard_id:selectcard_id,payment:payment,cardform:cardform,billingform:billingform,shiipingform:shiipingform},
                            success: function(data) {
                            if(data.errors == 'false') {
                                  // var nexturl = $widget.options.baseurl + "customerorder/customer/viewordersummary";
                                  //     $("#payemtnaction").attr('action', nexturl);
                                  //    $('#payemtnaction').submit()
                                   // $("#ordersummary").submit();
                                    var url = $widget.options.baseurl +"quickcheckout/index/Orderconfirmation",
                                        formData = $widget.getFormData($('#ordersummary')),
                                        order_id = btoa($('#ordersummary input[name = order_id]').val()),
                                        WebOrderId = btoa($('#ordersummary input[name = WebOrderId]').val());
                                        orderid = $('#ordersummary input[name = order_id]').val();
                                    
                                    $.ajax({
                                          type: "POST",
                                          url: url,
                                          showPopupLoader: false,
                                          data: ({order_id : order_id, WebOrderId: WebOrderId}),
                                          success: function(data) {
                                             if(data.output){
                                              var doctotal = $("input[name='doctotalconfirm']").val();
                                              var confirmetiondata = mageTemplate(orderconformetion, {
                                                      WebOrderId: atob(WebOrderId),
                                                      baseurl: $widget.options.baseurl,
                                                      order_id: order_id,
                                                      customerdata: $widget.options.customerdata,
                                                      submitted: btoa('Submitted'),
                                                      dataform: btoa("T"),
                                                      convertcurrency: $.proxy($widget._convertcurrency),
                                                      doctotal: doctotal,
                                                      blockhtml: $widget.options.cmsdata,
                                                });

                                              // $('#quickcheckoutconfirmetion').html(confirmetiondata);
                                              // $("#quickcheckoutconfirmetion").removeClass("hide").addClass("show") ; 
                                              // $("#popupModal .modal-dialog #cart-form").addClass("hide").removeClass("show");
                                              // $("#quickcheckoutcont").addClass("hide").removeClass("show");
                                              
                                              $('#quickcheckoutdetail').html(confirmetiondata);
                                              
                                              let orderDespatchEvent = new CustomEvent("ordersubmited",{detail: orderid});
                                              document.dispatchEvent(orderDespatchEvent);
                                              $("#quickcheckoutelink,.bottomBtn #chekout,.backbuttonclose,.quickcheckoutsubmit").css("opacity",0)  
                                              $widget.hideLoader();
                                             }
                                          }
                                      });
                                }
                                else 
                                {

                                   // $('body').trigger('processStop');
                                    if(data.errors == 'true')
                                    {
                                      if(data.orderId != '')
                                      {
                                        $('div#popupModal div#quickcheckoutdetail').html("<div class='errormessage'><svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:svgjs='http://svgjs.com/svgjs' version='1.1' width='20' height='20' x='0' y='0' viewBox='0 0 512.001 512.001' style='enable-background:new 0 0 512 512' xml:space='preserve' class='><g> <g xmlns='http://www.w3.org/2000/svg'> <g> <path d='M503.839,395.379l-195.7-338.962C297.257,37.569,277.766,26.315,256,26.315c-21.765,0-41.257,11.254-52.139,30.102    L8.162,395.378c-10.883,18.85-10.883,41.356,0,60.205c10.883,18.849,30.373,30.102,52.139,30.102h391.398    c21.765,0,41.256-11.254,52.14-30.101C514.722,436.734,514.722,414.228,503.839,395.379z M477.861,440.586    c-5.461,9.458-15.241,15.104-26.162,15.104H60.301c-10.922,0-20.702-5.646-26.162-15.104c-5.46-9.458-5.46-20.75,0-30.208    L229.84,71.416c5.46-9.458,15.24-15.104,26.161-15.104c10.92,0,20.701,5.646,26.161,15.104l195.7,338.962    C483.321,419.836,483.321,431.128,477.861,440.586z' fill='#000000' data-original='#000000' style=' class='></path> </g> </g> <g xmlns='http://www.w3.org/2000/svg'> <g> <rect x='241.001' y='176.01' width='29.996' height='149.982' fill='#000000' data-original='#000000' style=' class='></rect> </g> </g> <g xmlns='http://www.w3.org/2000/svg'> <g> <path d='M256,355.99c-11.027,0-19.998,8.971-19.998,19.998s8.971,19.998,19.998,19.998c11.026,0,19.998-8.971,19.998-19.998    S267.027,355.99,256,355.99z' fill='#000000' data-original='#000000' style=' class='></path> </g> </g> </g></svg>"+data.message+"</div>"); 
                                        $("#quickcheckoutelink,.bottomBtn #chekout,.backbuttonclose,.quickcheckoutsubmit").css("opacity",0)  
                                        $widget.hideLoader();
                                        return false;
                                      } 
                                    }
                                    else
                                    {
                                      return false;
                                    }
                                } 
                            }
                        });
                    }else{
                        
                    }
            });

           $(document).on('click',"input:radio[name=shiiping_method]",function() {

            var selectedVal = $(this).val();

                
            if(selectedVal == '4')
            {
                $('.shippingAdds').hide();
                $('.removeeditshipurl').hide();
            }else{
                $('.shippingAdds').show();
                $('.removeeditshipurl').show();
            }
        });

       $(document).on("change", "#shippingaddress", function(){
          $widget.shippingchnage();
        });


        },

        _showResponse: function(data,Errordata) {
              if(data.error || Errordata) {
                if(Errordata != '')
                {
                  var error = Errordata;
                }else{
                  var error = data.error;
                }
                $('.block-customer-edit-address .response-msg').html("<div class='error'>"+error+"</div>");
              } else {
                $('.block-customer-edit-address .response-msg').html("<div class='success'>You saved the shipping information.</div>");
              }
              setTimeout(
                  function(){ 
                    $('.response-msg').html(null); 
                    $(".mfp-close-inside").trigger("click");
                     var findButton = $('#customer-edit-address-validate').find('button[type=submit]');
                         findButton.text('Submit');
                        findButton.removeAttr('disabled');
                        
                }, 5000);
            },

        quickcheckoutajax: function(back_po_number,back_order_id){
            var $widget = this;
            var data = [];
            var orderdata = "";
            var _current_options = 1;
            var styles = {};
            
             $.ajax({
                  url: $widget.options.baseurl + "quickcheckout/index/paymentpopup",
                  type: "POST",
                  data: { back_order_id: back_order_id,back_po_number:back_po_number},
                  // showPopupLoader: true,
                  cache: false,
                  success: function(response) {
                    $(".quickViewbodyloader").css("display", "none");
                   data = response;
                   if(data.error == true)
                   {
                      if(data.orderId == '')
                       {
                        $widget.addsuccess(data.message);
                        return false;
                       }
                       else
                       {
                       	$('#quickcheckoutcont').html(popupdeta);
                          $('#quickcheckoutdetail').html("<div class='errormessage'><svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:svgjs='http://svgjs.com/svgjs' version='1.1' width='20' height='20' x='0' y='0' viewBox='0 0 512.001 512.001' style='enable-background:new 0 0 512 512' xml:space='preserve' class='><g> <g xmlns='http://www.w3.org/2000/svg'> <g> <path d='M503.839,395.379l-195.7-338.962C297.257,37.569,277.766,26.315,256,26.315c-21.765,0-41.257,11.254-52.139,30.102    L8.162,395.378c-10.883,18.85-10.883,41.356,0,60.205c10.883,18.849,30.373,30.102,52.139,30.102h391.398    c21.765,0,41.256-11.254,52.14-30.101C514.722,436.734,514.722,414.228,503.839,395.379z M477.861,440.586    c-5.461,9.458-15.241,15.104-26.162,15.104H60.301c-10.922,0-20.702-5.646-26.162-15.104c-5.46-9.458-5.46-20.75,0-30.208    L229.84,71.416c5.46-9.458,15.24-15.104,26.161-15.104c10.92,0,20.701,5.646,26.161,15.104l195.7,338.962    C483.321,419.836,483.321,431.128,477.861,440.586z' fill='#000000' data-original='#000000' style=' class='></path> </g> </g> <g xmlns='http://www.w3.org/2000/svg'> <g> <rect x='241.001' y='176.01' width='29.996' height='149.982' fill='#000000' data-original='#000000' style=' class='></rect> </g> </g> <g xmlns='http://www.w3.org/2000/svg'> <g> <path d='M256,355.99c-11.027,0-19.998,8.971-19.998,19.998s8.971,19.998,19.998,19.998c11.026,0,19.998-8.971,19.998-19.998    S267.027,355.99,256,355.99z' fill='#000000' data-original='#000000' style=' class='></path> </g> </g> </g></svg>"+data.message+"</div>"); 
  	                  	$("#quickcheckoutcont").removeClass("hide").addClass("show") ; 
  	                  	$("#popupModal .modal-dialog #cart-form").addClass("hide").removeClass("hide")
                          var orderId = data.orderId
                          let changepoconfigDespatchEvent = new CustomEvent("changepoconfig",{detail: orderId});
                          document.dispatchEvent(changepoconfigDespatchEvent);
                          return false;
                       }
                   }
                   else
                   {
                    
                      var orderdata = data.orderdatalineitem;               
                      var allorderitems = $widget._getOrderDataItems($widget, orderdata, _current_options, styles);
                    
                      var current_active_style = $('#current_active_style_head').html();
                      
                      var popupdeta = mageTemplate(quickcheckout, {
                            data: data,
                            back_order_id: back_order_id,
                            back_po_number: back_po_number,
                            finalorderrendere: allorderitems,
                            baseurl:$widget.options.baseurl,
                            databystylegroup: $widget._DatabyStyle(),
                            convertcurrency: $.proxy($widget._convertcurrency),
                            current_active_style: current_active_style,
                      });
                      $('#quickcheckoutcont').html(popupdeta);
                      $('#quickcheckoutelink').removeClass("opacity-down");
                      $('.container.bg-light.p-2.bottomBtn a#chekout').removeClass("opacity-down");                        
                        
                      // $("#quickcheckoutcont").removeClass("hide").addClass("show"); 
                      // $(".backbuttonclose,.quickcheckoutsubmit").css("display","block")
                      // $("#quickcheckoutelink,.bottomBtn #chekout").css("display","none")  
                      //  // $widget.makeDragableQC('#popupModal .modal-dialog #quickcheckoutcont .modalContainer .bg-primary', '#popupModal .modal-content')
                      // $("#popupModal .modal-dialog #cart-form").addClass("hide").removeClass("show");
                      // if($("#popupModal #quickcheckoutcont").height() >= window.innerHeight){
                      //   $("#popupModal #quickcheckoutcont #quickcheckoutdetail").css({"height":window.innerHeight-250, "overflow-y":"auto","overflow-x":"hidden"})
                      // }                         
                      $widget.selectcardinfo();

                      $('#Country [value="US"]').attr('selected', 'true');
                      setTimeout(
                                  function(){ 
                                    $("#Country").trigger('change');
                                    }, 3000);
                      $widget.shippingchnage();
                      $("#state").children('option:gt(0)').hide();
                          $("#Country").change(function() {
                            var selectedoption = $(this).find('option:selected').val()
                            $('#ContryLable').val($(this).find("option:selected").text());
                            $('#show_country').val($(this).find("option:selected").val());
                            $widget.showstateoption(selectedoption)
                          }).trigger('change');
                          $("#state").children('option:gt(0)').hide();
                          $("#state").change(function() {
                            $('#StateLable').val($(this).find("option:selected").text())
                          })
                    }
                  }
                });
           },
           _renderOrderSubmittedWarning(message){
          return '<div id="orderWarningWrapper" class="orderWarning-wrapper">'+
                  '<div class="orderWarning-content scaleAnimation">'+
                  '<p class="orderWarning-message">'+
                  '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" version="1.1" width="20" height="20" x="0" y="0" viewBox="0 0 512.001 512.001" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g> <g xmlns="http://www.w3.org/2000/svg"> <g> <path d="M503.839,395.379l-195.7-338.962C297.257,37.569,277.766,26.315,256,26.315c-21.765,0-41.257,11.254-52.139,30.102    L8.162,395.378c-10.883,18.85-10.883,41.356,0,60.205c10.883,18.849,30.373,30.102,52.139,30.102h391.398    c21.765,0,41.256-11.254,52.14-30.101C514.722,436.734,514.722,414.228,503.839,395.379z M477.861,440.586    c-5.461,9.458-15.241,15.104-26.162,15.104H60.301c-10.922,0-20.702-5.646-26.162-15.104c-5.46-9.458-5.46-20.75,0-30.208    L229.84,71.416c5.46-9.458,15.24-15.104,26.161-15.104c10.92,0,20.701,5.646,26.161,15.104l195.7,338.962    C483.321,419.836,483.321,431.128,477.861,440.586z" fill="#000000" data-original="#000000" style="" class=""></path> </g> </g> <g xmlns="http://www.w3.org/2000/svg"> <g> <rect x="241.001" y="176.01" width="29.996" height="149.982" fill="#000000" data-original="#000000" style="" class=""></rect> </g> </g> <g xmlns="http://www.w3.org/2000/svg"> <g> <path d="M256,355.99c-11.027,0-19.998,8.971-19.998,19.998s8.971,19.998,19.998,19.998c11.026,0,19.998-8.971,19.998-19.998    S267.027,355.99,256,355.99z" fill="#000000" data-original="#000000" style="" class=""></path> </g> </g> </g></svg>'+
                  message+
                  '</p>'+
                  '</div>'+
                  '</div>';
        },
        showstateoption: function(selectedoption){
            $('#state option[value=""]').prop('selected', true);
            $("#state").children('option').hide();
            if($("#state").children('option[contry-code^="'+selectedoption+'"]').length > 0)
            {
              $("#state").children('option[contry-code^="'+selectedoption+'"]').show(); 
            }else{
              $('#state option[value=""]').prop('selected', true);
              $("#state").children('option[contry-code^="selected"]').show();
              
            }
          },
          addsuccess: function(message)
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
          },

        getFormData: function(formElem){
              var unindexed_array = formElem.serializeArray();
              var indexed_array = {};
              jQuery.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
              });
              return indexed_array;
            },
        showResponse: function(data) {
            if(data.error) {
                      $('.block-customer-add-payment .response-msg').html("<div class='error'>"+data.error+"</div>");
              } else {
                      $('.block-customer-add-payment .response-msg').html("<div class='success'>You saved the payment information.</div>");
            }
            setTimeout(function(){ $('.block-customer-add-payment .response-msg').html(null); }, 5000);
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
              return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;
          },
          selectcardinfo: function(){
              var Objsel = $("#selectcard_id").find('option:selected');
              var cc_no = $(Objsel).attr("attr-ccno"),
              cc_expiry = $(Objsel).attr("attr-ccexpiry"),
              attrMethodName = $(Objsel).attr("attr-MethodName"),
              cc_type = $(Objsel).attr("attr-cctype"),
              cc_details = [];
              if (typeof cc_type !== typeof undefined && cc_type !== false && cc_type.trim() != '') {
                var img = '';
                if (cc_type == "V") {
                  cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/visa-card.jpg'>");
                }
                if (cc_type == "M") {
                  cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/master-card.jpg'>");
                }
                if (cc_type == "A") {
                  cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/american-express.jpg'>");
                }
                if (cc_type == "DS") {
                  cc_details.push("<img src='"+$widget.options.baseurl +"pub/media/cardimages/images/discover.jpg'>");
                }
              }
              
              if (typeof cc_no !== typeof undefined && cc_no !== false && cc_no.trim() != '') {
                cc_details.push('<span>'+cc_no+'</span>');
                cc_details.push('<input type="hidden" id="cc_no_hidden" name="cc_no_hidden" value="'+cc_no+'">');

              }
              
              if (typeof cc_expiry !== typeof undefined && cc_expiry !== false && cc_expiry.trim() != '') {
                cc_details.push('<span>'+cc_expiry+'</span>');
                cc_details.push('<input type="hidden" id="cc_expiry_hidden" name="cc_expiry_hidden" value="'+cc_expiry+'">');
              }
              if (typeof attrMethodName !== typeof undefined && attrMethodName !== false && attrMethodName.trim() != '') {
                cc_details.push('<input type="hidden" id="cc_attrMethodName_hidden" name="cc_attrMethodName_hidden" value="'+attrMethodName+'">');
              }
              if (cc_details.length) {
                $(".paymentAddress").html(cc_details.join(" "));
              }
              else{ 
              $(".paymentAddress").html("Please Select");
              } 
            },

        showstateoption: function(selectedoption){
          $('#state option[value=""]').prop('selected', true);
          $("#state").children('option').hide();
          if($("#state").children('option[contry-code^="'+selectedoption+'"]').length > 0)
          {
            $("#state").children('option[contry-code^="'+selectedoption+'"]').show(); 
          }else{
            $('#state option[value=""]').prop('selected', true);
            $("#state").children('option[contry-code^="selected"]').show();
            
          }
        },
        shippingchnage: function()
        {
          var Objsel = $('#shippingaddress').find('option:selected');
          if(Objsel.val() != '')
          {
          var addline1 = $(Objsel).attr("attr-addr1"),
          addline0 = $(Objsel).attr("attr-addressID"),
          addline2 = $(Objsel).attr("attr-addr2"),
          addline3 = $(Objsel).attr("attr-city"),
          addline4 = $(Objsel).attr("attr-tel"),

          attrcardname = $(Objsel).attr("attr-cardname"),
          attraddressid = $(Objsel).attr("attr-addressid"),
          attrzipcode = $(Objsel).attr("attr-zipcode"),
          attrcountry = $(Objsel).attr("attr-country"),
          attrstate = $(Objsel).attr("attr-state"),
          attrcity1 = $(Objsel).attr("attr-city1"),
          attrblindDropship = $(Objsel).attr("attr-blindDropship"),
          address = [];
          var address_concet = "";
          
          
          if (typeof addline0 !== typeof undefined && addline0 !== false && addline0.trim() != '') {
            address.push(addline0);
            $('#hidden_AddStreetNo').val(addline0);
            //$('#AddStreetNo').text(addline1);
          }
          if (typeof addline1 !== typeof undefined && addline1 !== false && addline1.trim() != '') {
            address.push(addline1);
            $('#hidden_AddStreetNo').val(addline1);
            //$('#AddStreetNo').text(addline1);
          }
          
          if (typeof addline2 !== typeof undefined && addline2 !== false && addline2.trim() != '') {
            address.push(addline2);
            $('#hidden_address2').val(addline2);
            //$('#address2').text(addline2);
          } 
          /*if (typeof addline2 !== typeof undefined && addline2 !== false && addline2.trim() != '') {
            address.push(addline2);

            if (typeof addline2 !== typeof undefined && addline1 !== false && addline1.trim() != '' && typeof addline2 !== typeof undefined && addline2 !== false && addline2.trim() != '') {
              address_concet = ',';
            }
            $('#hidden_AddStreetNo').val(addline1);
            $('#AddStreetNo').text(addline1);
          }*/
          if (typeof addline3 !== typeof undefined && addline3 !== false && addline3.trim() != '') {
            address.push(addline3);
          }
          
          if (typeof addline4 !== typeof undefined && addline4 !== false && addline4.trim() != '') {
            address.push(addline4);
          }
          if (typeof attrcountry !== typeof undefined && attrcountry !== false && attrcountry.trim() != '') {
            //address.push(attrcountry);
            $("#hidden_country").val(attrcountry).change();
          }
          if (typeof attraddressid !== typeof undefined && attraddressid !== false && attraddressid.trim() != '') {
            $('#hidden_fullname_shiiping').val(attraddressid);
            //$('#fullname_shiiping').text(attraddressid);

          }
          if (typeof attrstate !== typeof undefined && attrstate !== false && attrstate.trim() != '') {
            // showstateoption(attrcountry)
            $("#hidden_state").val(attrstate).change();
            //$('#state').val(attrstate).attr("selected", "selected");
            //$('#state').val(attrstate);
            //$('#state').text(attrstate);

          }
          if (typeof attrcity1 !== typeof undefined && attrcity1 !== false && attrcity1.trim() != '') {
            $('#hidden_city').val(attrcity1);
            //$('#city').text(attrcity1);

          }
          if (typeof attrblindDropship !== typeof undefined && attrblindDropship !== false && attrblindDropship.trim() != '') {
            $('#hidden_blindDropship').val(attrblindDropship);
            //$('#city').text(attrcity1);

          }else{
            $('#hidden_blindDropship').val(0);
          }
          
          if (typeof attrzipcode !== typeof undefined && attrzipcode !== false && attrzipcode.trim() != '') {
            $('#hidden_zipcode').val(attrzipcode);
            //$('#zipcode').text(attrzipcode);
          }
          
          
          
          if (address.length)
          $(".shipAddress").html(address.join("<br/>"));
          else 
          $(".shipAddress").html("Please Select");
          }else{
            $(".shipAddress").html("Please Select");
          }
        },
           _getOrderDataItems: function($this, _response, _current_options, styles = {}) {
            var $widget = $this,
                response = _response,
                mainresponce = {},
                style = "",
                submitcolor = "",
                viewmode = "",
                stylebyInventory = $widget._stylebyInventory();
                // data = this.options.jsonConfig;
            if ($widget._DatabyStyle() && $widget._stylebyInventory()) {
                var databyStyle = $widget._DatabyStyle();
                var allorderdata = "";
                if (response != "") {
                    allorderdata = response;
                } else {
                    allorderdata = $widget._generateqtyarray(_current_options, $widget, styles);
                }
                var tmp_distinstyle = allorderdata.map(function(item) {
                    return item.Style;
                });
                const uniqueArray = [...new Set(tmp_distinstyle)];
                var distinstyle = uniqueArray;
                var sizegrouparray = {};
                if (distinstyle) {
                    distinstyle.forEach(function(item, index) {
                        if (item in stylebyInventory) {
                            var stylesize = stylebyInventory[item].SizeGroup;
                            sizegrouparray[stylesize] = {};
                        }
                    });
                    var count = 0;
                    distinstyle.forEach(function(item, index) {
                        if (item in stylebyInventory) {
                            var stylesize = stylebyInventory[item].SizeGroup;
                            sizegrouparray[stylesize][count] = stylebyInventory[item].Style;
                            count++;
                        }
                    });
                    for (var index in sizegrouparray) {
                        var item_size = sizegrouparray[index];
                        var groupstyle = item_size;
                        var current_style = "viewtype" + index;
                        var datastyle_index = databyStyle.index;
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                mainresponce[current_style] = {};
                            }
                        });
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size[index_a];
                                    if (stylegroup == item.Style) {
                                        mainresponce[current_style][stylegroup] = {};
                                    }
                                }
                            }
                        });
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size[index_a];
                                    var colorcode = item.ColorCode;
                                    if (stylegroup == item.Style) {
                                        mainresponce[current_style][stylegroup][colorcode] = {};
                                    }
                                }
                            }
                        });
                        var order_item_count = 0;
                        allorderdata.forEach(function(item, index) {
                            if (item.Type != "gift") {
                                for (var index_a in item_size) {
                                    var stylegroup = item_size[index_a];
                                    var colorcode = item.ColorCode;
                                    if (stylegroup == item.Style) {
                                        mainresponce[current_style][stylegroup][colorcode][order_item_count] = item;
                                        order_item_count++;
                                    }
                                }
                            }
                        });
                    }
                }
            }
            return mainresponce;
        },
        _stylebyInventory: function() {
            var data = this.options.parentstyledata,
                temp_items = [];
            data.forEach(function(item, index) {
                var style = item.Style;
                temp_items[style] = item;
            });
            return temp_items;
        },
         _DatabyStyle: function() {
            var data = this.options.parentstyledata;
            var temp_databyStyle = {};
            data.forEach(function(item, index) {
                var sizegroup = item.SizeGroup;
                temp_databyStyle[sizegroup] = {};
            });
            data.forEach(function(item, index) {
                var sizegroup = item.SizeGroup;
                var sizeorder = item.SizeOrder;
                var size = item.Size;
                temp_databyStyle[sizegroup][sizeorder] = size;
            });
            return temp_databyStyle;
        },
         _generateqtyarray: function(_current_options, $widget, styles = {}) {
            removedskus = [];
            var data_selector = $(".colorContainer").find(".checkvalue");
            var current_options = _current_options;
            item_edited = false;
            if (current_options == 1 && current_options != 0 && current_options != "") {
                $(data_selector).each(function() {
                    var count = 0;
                    if ($(this).val() != "") {
                        var selectcolor = $(this).closest("td").find("input[name=selectcolor]").val();
                        var selectsize = $(this).closest("td").find("input[name=selectsize]").val();
                        var base_price = $('input[name="mainprice[' + selectcolor + "][" + selectsize + ']"').val();
                        var disprice = $('input[name="DiscountPrice[' + selectcolor + "][" + selectsize + ']"').val();
                        var added_qty = $(this).val();
                        var itemcode = $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val(),
                            order_item = $widget._getItemfromOrderList(itemcode);
                        if (order_item.length > 0) {
                            if (order_item[0].QTYOrdered !== added_qty) {
                                item_edited = true;
                            }
                        } else {
                            item_edited = true;
                        }
                        var pafterdiscount = parseFloat();
                        var pbeforediscount = added_qty * base_price;
                        pbeforediscount = parseFloat(pbeforediscount);
                        if (disprice < base_price) {
                            pafterdiscount = added_qty * disprice;
                        } else {
                            pafterdiscount = added_qty * base_price;
                        }
                        var tmpitem = {};
                        var current_itemcode = $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val();
                        tmpitem = {
                            ColorCode: $('input[name="colorcode[' + selectcolor + "][" + selectsize + ']"').val(),
                            ItemCode: $('input[name="itemscode[' + selectcolor + "][" + selectsize + ']"').val(),
                            ColorStatus: $('input[name="ColorStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            DiscountPer: $('input[name="DiscountPer[' + selectcolor + "][" + selectsize + ']"').val(),
                            DiscountPrice: $('input[name="DiscountPrice[' + selectcolor + "][" + selectsize + ']"').val(),
                            OrderOption: "1",
                            PriceAfterDiscount: "" + pafterdiscount + "",
                            QTYOrdered: added_qty,
                            Size: selectsize,
                            Style: $(".product_options.active").attr('id'),
                            StyleStatus: $('input[name="StyleStatus[' + selectcolor + "][" + selectsize + ']"').val(),
                            TotalPrice: "" + pafterdiscount + "",
                            UnitPrice: base_price,
                            PriceBeforeDiscount: "" + pbeforediscount + "",
                            Color: selectcolor,
                            Type: "normal",
                        };
                        var itemcodeexistinarray = false;
                        var array_index = -1;
                        finalitems.forEach(function(item, index) {
                            if (item.ItemCode == current_itemcode && itemcodeexistinarray == false) {
                                itemcodeexistinarray = true;
                                array_index = index;
                            }
                        });
                        if (itemcodeexistinarray) {
                            if (added_qty > 0) {
                                finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                                finalitems[array_index].QTYOrdered = added_qty;
                                finalitems[array_index].TotalPrice = pafterdiscount;
                            } else {
                                var tml_finalitems = _.filter(finalitems, function(item) {
                                    if (item.ItemCode == current_itemcode) {
                                        removedskus.push(current_itemcode);
                                    }
                                    return item.ItemCode !== current_itemcode;
                                });
                                finalitems = tml_finalitems;
                            }
                        } else {
                            if (added_qty > 0) {
                                finalitems.push(tmpitem);
                            }
                        }
                    }
                });
            }
            return finalitems;
        },
        showPopupLoader: function() {
            $(".quickViewbodyloader").css("display", "block");
        },

        hideLoader: function() {
                $(".quickViewbodyloader").css("display", "none");
        }

	})
	return $.mage.QuickcheckoutRendere;

});