define([
    'jquery',
    'mage/template',
    'text!Magento_Customer/template/payment_info.html',
    'text!Magento_Customer/template/shippinginformation.html',
    'mage/validation/validation'
], function ($,mageTemplate,payment_info,shippinginformationtemp) {
    'use strict';

    $.widget('mage.SwatchRenderer', {
        options: {
            dataship: {},
            datapayment: {},
            formkey: {},
            baseurl: {},
            saved_cards:{},
            Countrys:{},
            baseurl: {},
            States:{},
            custNum: {},
            viewfileurl: {},
        },
        _init: function () {
            var $widget = this;          
        },

        _create: function() {
            var $widget = this;
            const url = $widget.options.baseurl+"adaruniforms/account/accountdatarender"
              $.get(url, function(response){
                if(!response.error){
                    $widget.options.Countrys = response.contrylist
                    $widget.options.States = response.statuslist
                    $widget.options.saved_cards = response.savecard.savecard
                    $widget.options.custNum = response.savecard.custnum   
                    $widget.options.dataship = response.shippingaddress
                    $widget._EventListener();   
                }
              });    
        },

        _EventListener: function(e){
            var $widget = this;

            $widget.changeShipmemt($widget)
            $widget.changePayment($widget)
            
          $(document).on('show.bs.modal','#customer-edit', function(e){
                var type = $(e.relatedTarget);
                var recipient = type.attr('attr-type');
                
                if (recipient == "email") {
                  $("#change-email").attr("checked", true).change();
                  $(".field-name-lastname, .field-name-firstname").hide();
                }
                if (recipient == "password") {
                  $("#change-password").attr("checked", true).change();
                  $(".field-name-lastname, .field-name-firstname").hide();
                }

            });

            $(document).on('hide.bs.modal','#customer-edit', function(e){
                var type = $(e.relatedTarget);
                var recipient = type.attr('attr-type');
                
                $("#change-email").attr("checked", false).change();
                $("#change-password").attr("checked", false).change();
                $(".field-name-lastname, .field-name-firstname").show();
            });
            
            
            $(document).on('hidden.bs.modal','.modal.block-customer-add-payment',function(){
                $('#customer-add-payment-validate').find('input[type="text"]').val('');

                $('#customer-add-payment-validate').find('.error-not-found').remove();
                 $('#customer-add-payment-validate').find("input[type='text']").css({'border':'1px solid'});

            
                $("input[name='card_type']").removeClass("selected_cc").prop('checked', false).attr("disabled", true).next('span').removeClass("img-container");
     
            })



        // $(document).on("click","button.themeBtn.action.save",function(e) {
        //     var dataForm = $('#customer-add-payment-validate');
        //     console.log("val",dataForm.validation())

        // });

          $(document).on("change", ".paymentMethod", function(){
            var Objsel = $(this).find('option:selected');

            var cc_no = $(Objsel).attr("attr-ccno"),
            cc_expiry = $(Objsel).attr("attr-ccexpiry"),
            cc_type = $(Objsel).attr("attr-cctype"),
            cc_details = [];

            if (typeof cc_type !== typeof undefined && cc_type !== false && cc_type.trim() != '') {
                var img = '';
                if (cc_type == "V") {
                    cc_details.push("<img src='"+$widget.options.viewfileurl+"/images/visa-card.jpg' >");
                }
                if (cc_type == "M") {
                    cc_details.push("<img src='"+$widget.options.viewfileurl+"/images/master-card.jpg' >");
                }
                if (cc_type == "A") {
                    cc_details.push("<img src='"+$widget.options.viewfileurl+"/american-express.jpg' >");
                }
                if (cc_type == "DS") {
                    cc_details.push("<img src='"+$widget.options.viewfileurl+"/images/discover.jpg' >");
                }
            }

            if (typeof cc_no !== typeof undefined && cc_no !== false && cc_no.trim() != '') {
                cc_details.push('<span>'+cc_no+'</span>');
            }

            if (typeof cc_expiry !== typeof undefined && cc_expiry !== false && cc_expiry.trim() != '') {
                cc_details.push('<span>'+cc_expiry+'</span>');
            }

            if (cc_details.length) {
                $(".paymentAddress").html(cc_details.join(" "));
            }
            else
            $(".paymentAddress").html("");
        });
        $('#customer-add-payment-validate').on('submit', function(event){
            console.log("hi123")
            var form = $('#customer-add-payment-validate');
            form.validation();
                    return $widget._addNewPaymentInformation($(this), $widget, event);
        }); 

         $(document).on("click","#important .mfp-close-inside",function(e) {
            $('div#important div.mage-error[generated]').css('display','none')
            $('div#important').removeClass('mage-error[generated]')
         })

           
            $(document).on("click",".myaccount_nav .block-title",function(e) {
                var x = $(this).attr("aria-expanded")
                if(x == 'false'){
                    setTimeout(function(){
                    $(".account_menu_nav .collapse").removeClass("show");
                    $(".account_menu_nav .block-title").addClass("collapsed");
                        $(".account_menu_nav .block-title").attr("aria-expanded","false");
                    },800)
                }
            });
           


          
        },
         _validateCard: function(cardnumber, cardname){


            var cards = new Array();
            cards [0] = {name: "Visa", length: "13,16", prefixes: "4", checkdigit: true};
            cards [1] = {name: "MasterCard", length: "16", prefixes: "51,52,53,54,55", checkdigit: true};
            cards [2] = {name: "DinersClub", length: "14,16", prefixes: "36,38,54,55", checkdigit: true};
            cards [3] = {name: "CarteBlanche", length: "14", prefixes: "300,301,302,303,304,305", checkdigit: true};
            cards [4] = {name: "AmEx", length: "15", prefixes: "34,37", checkdigit: true};
            cards [5] = {name: "Discover", length: "16", prefixes: "6011,622,64,65", checkdigit: true};
            cards [6] = {name: "JCB", length: "16", prefixes: "35", checkdigit: true};
            cards [7] = {name: "enRoute", length: "15", prefixes: "2014,2149", checkdigit: true};
            cards [8] = {name: "Solo", length: "16,18,19", prefixes: "6334,6767", checkdigit: true};
            cards [9] = {name: "Switch", length: "16,18,19", prefixes: "4903,4905,4911,4936,564182,633110,6333,6759", checkdigit: true};
            cards [10] = {name: "Maestro", length: "12,13,14,15,16,18,19", prefixes: "5018,5020,5038,6304,6759,6761,6762,6763", checkdigit: true};
            cards [11] = {name: "VisaElectron", length: "16", prefixes: "4026,417500,4508,4844,4913,4917", checkdigit: true};
            cards [12] = {name: "LaserCard", length: "16,17,18,19", prefixes: "6304,6706,6771,6709", checkdigit: true};
                       
            // Establish card type
            var cardType = -1;
            for (var i=0; i<cards.length; i++) {

                // See if it is this card (ignoring the case of the string)
                if (cardname.toLowerCase () == cards[i].name.toLowerCase()) {
                  cardType = i;
                  break;
                }
            }
            // If card type not found, report an error
            if (cardType == -1) {
                ccErrorNo = 0;
                return false; 
            }

            // Ensure that the user has provided a credit card number
            if (cardnumber.length == 0)  {
                ccErrorNo = 1;
                return false; 
            }

            // Now remove any spaces from the credit card number
            cardnumber = cardnumber.replace (/\s/g, "");

            // Check that the number is numeric
            var cardNo = cardnumber
            var cardexp = /^[0-9]{13,19}$/;
            if (!cardexp.exec(cardNo))  {
                ccErrorNo = 2;
                return false; 
            }
               
            // Now check the modulus 10 check digit - if required
            if (cards[cardType].checkdigit) {
                var checksum = 0;                                  // running checksum total
                var mychar = "";                                   // next char to process
                var j = 1;                                         // takes value of 1 or 2

                // Process each digit one by one starting at the right
                var calc;
                for (i = cardNo.length - 1; i >= 0; i--) {

                  // Extract the next digit and multiply by 1 or 2 on alternative digits.
                  calc = Number(cardNo.charAt(i)) * j;

                  // If the result is in two digits add 1 to the checksum total
                  if (calc > 9) {
                    checksum = checksum + 1;
                    calc = calc - 10;
                  }

                  // Add the units element to the checksum total
                  checksum = checksum + calc;

                  // Switch the value of j
                  if (j ==1) {j = 2} else {j = 1};
                } 

                // All done - if checksum is divisible by 10, it is a valid modulus 10.
                // If not, report an error.
                if (checksum % 10 != 0)  {
                 ccErrorNo = 3;
                 return false; 
                }
            }  

            // Check it's not a spam number
            if (cardNo == '5490997771092064') { 
                ccErrorNo = 5;
                return false; 
            }

            // The following are the card-specific checks we undertake.
            var LengthValid = false;
            var PrefixValid = false; 
            var undefined; 

            // We use these for holding the valid lengths and prefixes of a card type
            var prefix = new Array ();
            var lengths = new Array ();

            // Load an array with the valid prefixes for this card
            prefix = cards[cardType].prefixes.split(",");
              
            // Now see if any of them match what we have in the card number
            for (i=0; i<prefix.length; i++) {
                var exp = new RegExp ("^" + prefix[i]);
                if (exp.test (cardNo)) PrefixValid = true;
            }
              
            // If it isn't a valid prefix there's no point at looking at the length
            if (!PrefixValid) {
                ccErrorNo = 3;
                return false; 
            }

            // See if the length is valid for this card
            lengths = cards[cardType].length.split(",");
            for (j=0; j<lengths.length; j++) {
                if (cardNo.length == lengths[j]) LengthValid = true;
            }

            // See if all is OK by seeing if the length was valid. We only check the length if all else was 
            // hunky dory.
            if (!LengthValid) {
                ccErrorNo = 4;
                return false; 
            };

            // The credit card is in the required format.
            return true;
        },
        _addNewPaymentInformation: function($this, $widget, event){
            var changeText = "Please Wait...";
            var changeSubmit = "Submit";
            var findButton = $('#customer-add-payment-validate').find('button[type=submit]');
            var form = $('#customer-add-payment-validate');

            var card_type = '';

            if($("[name='card_type']").hasClass("selected_cc")){
                var short_card_type = $(".selected_cc").attr("value");
                (short_card_type == "MC") ? card_type="MasterCard" : '';
                (short_card_type == "VI") ? card_type="Visa" : '';
                (short_card_type == "DI") ? card_type="Discover" : '';
                (short_card_type == "AE") ? card_type="AmEx" : '';
            }else{
                card_type = '';
            }
            var selector = new Array();
            selector = ['#fullname','#card_no','#security_code','#expiration_date'];
            var count = 0;
            var is_valid = true;
            var tmp_focused = new Array();
            selector.forEach(function(value, index){
                if(form.find(value).val().length <= 0){
                    form.find(value).next(".error-not-found").remove();
                    form.find(value).parent().append('<span class="error-not-found" Style="color:red; font-size:12px; display:block; margin-top:-5px;">This is required field</span>');
                    form.find(value).css("border", "1px solid red")
                    if(!_.contains(tmp_focused, value)){
                        if(tmp_focused.length == 0){
                            form.find(value).focus()
                            tmp_focused.push(value);
                        }
                    }
                    is_valid = false;
                    count++;
                }else{
                    form.find(value).next(".error-not-found").remove();
                    form.find(value).css("border", "")
                    if(count == 4){
                        is_valid = true;
                    }
                }

            })

            if(!is_valid){
                return false;
            }else{
                $('span.error-not-found').remove('');
                selector.forEach(function(value, index){
                    form.find(value).css("border", "")
                });
            }

            var year_validation = true;

            if($("#expiration_date").val().length > 0){
                var tmp_date = $("#expiration_date").val().split("/"),
                    month = tmp_date[0],
                    year = "20"+tmp_date[1];

                    var today, someday,
                        exMonth= month,
                        exYear= year;
                    today = new Date();
                    someday = new Date();
                    someday.setFullYear(exYear, exMonth, 1);

                    if (someday < today) {
                        $("#expiration_date").next(".error-not-found").remove();
                        $("#expiration_date").parent().append('<span class="error-not-found" Style="color:red; font-size:12px; display:block; margin-top:-5px;">Expiration date should be in MM/YY format and not expired.</span>');
                        year_validation = false; 
                       return false;
                    }else{
                        $("#expiration_date").next(".error-not-found").remove();
                        year_validation = true;
                    }

            }

            if($widget._validateCard($("#card_no").val(), card_type) && year_validation){
                event.preventDefault();
                $("#card_no").next(".error-not-found").remove();
                findButton.text(changeText);
                findButton.attr("disabled", "disabled");
                var url = $this.attr('action');
                var formData = $widget.getFormData($this);
                $.ajax({
                    type: "POST",
                    url: url,
                    showLoader: true,
                    data: formData,
                    cache: false,
                    success: function(data) {
                        $widget.showResponse(data,'','false','payment');
                        if(data.error) {
                            findButton.text(changeSubmit);
                            findButton.removeAttr('disabled');
                        } else {
                            setTimeout(function(){ location.reload(); }, 3000);                            
                        } 
                    }
                });
            }else{
                $("#card_no").next(".error-not-found").remove();
                $("#card_no").parent().append('<span class="error-not-found" Style="color:red; font-size:12px; display:block; margin-top:-5px;">'+ccErrors[ccErrorNo]+'</span>');
            }


            return false;
        },

 
        changeShipmemt: function($widget){
             var statement = mageTemplate(shippinginformationtemp, {
                        dataship: $widget.options.dataship,
                        Countrys: $widget.options.Countrys,
                        States: $widget.options.States,
                        formkey: $widget.options.formkey,
                        baseurl: $widget.options.baseurl
                    });
            $("#collapseFour").html(statement).delay(2000);
               

                $(document).on("change", "#shippingaddress", function(){
                    var Objsel = $(this).find('option:selected');               

                    var addline0 = $(Objsel).attr("attr-cardname"),
                    addline1 = $(Objsel).attr("attr-addr1"),
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

                    if (typeof addline0 !== typeof undefined && addline0 !== false && addline0.trim() != '') {
                        address.push("<b>"+addline0+"</b>");
                    }   
                    
                    if (typeof addline1 !== typeof undefined && addline1 !== false && addline1.trim() != '') {
                        address.push(addline1);
                    }   
                    
                    if (typeof addline2 !== typeof undefined && addline2 !== false && addline2.trim() != '') {
                        address.push(addline2);
                    }
                    
                    if (typeof addline3 !== typeof undefined && addline3 !== false && addline3.trim() != '') {
                        address.push(addline3);
                    }
                    
                    if (typeof addline4 !== typeof undefined && addline4 !== false && addline4.trim() != '') {
                        address.push(addline4);
                    }
                    
                    if (address.length)
                    $(".shipAddress").html(address.join("<br/>"));
                    else 
                    $(".shipAddress").html("Please Select");
                });
                $("#shippingaddress").change();
                $widget.addnewaddresspopup($widget);
        },

        addnewaddresspopup: function($widget){
                $('#Country [value="US"]').attr('selected', 'true');
                setTimeout(
                    function(){ 
                        $("#Country").trigger('change');
                        }, 3000);
                $("#state").children('option:gt(0)').hide();
                $("#Country").change(function() {
                        var selectedoption = $(this).find('option:selected').val();
                        $('#ContryLable').val($(this).find("option:selected").text());
                        $widget.showstateoption(selectedoption)
                    }).trigger('change');

                    $("#state").children('option:gt(0)').hide();
                    $("#state").change(function() {
                        $('#StateLable').val($(this).find("option:selected").text())
                     })
                    $widget.editadressdataAjax($widget);
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

        editadressdataAjax: function($widget){
            var changeText = "Please Wait...";
            var changeSubmit = "Submit";
            var findButton = $('#customer-edit-address-validate').find('button[type=submit]'),
              form = $('#customer-edit-address-validate');
            form.submit(function (event) {
                // if (form.validation('isValid')) {
                event.preventDefault();
                var selectors = new Array();
                selectors = ['#fullname','#state','#Country','#city','#zipcode'];
                var count = 0;
                var is_valid = true;
                var tmp_focused = new Array();
                selectors.forEach(function(value, index){
                    if(form.find(value).val().length <= 0){
                        form.find(value).siblings(".error-not-found").remove();
                        form.find(value).parent().append('<span class="error-not-found" Style="color:red; font-size:12px; display:block; margin-top:-5px;">This is required field</span>');
                        form.find(value).css("border", "1px solid red")
                        if(!_.contains(tmp_focused, value)){
                            if(tmp_focused.length == 0){
                                form.find(value).focus()
                                tmp_focused.push(value);
                            }
                        }
                        is_valid = false;
                        count++;
                    }else{
                        form.find(value).siblings(".error-not-found").remove();
                        form.find(value).css("border", "")
                        if(count == selectors.length){
                            is_valid = true;
                        }
                    }
                });

                if(!is_valid){
                    return false;
                }else{
                    form.find('span.error-not-found').remove('');
                    selectors.forEach(function(value, index){
                        form.find(value).css("border", "")
                    });
                }

                if (is_valid) {
                    var url = form.attr('action');
                    var formData = $widget.getFormData($(this));
                    var getalloptions = $("#shippingaddress>option");
                    var valIsExists = false;
                    $(getalloptions).each(function() {
                        if ($.trim($(this).attr("attr-cardname")).toLowerCase() == $.trim(formData.fullname).toLowerCase()) 
                        {
                            valIsExists = true;
                        }
                    });
                    if(valIsExists)
                    {
                        this.showResponse('',"It seems like this address was previously added and saved. If you'd like to add it anyway please change the name in the 'Full Name' field to differ from the one you already added.",'false');
                        return false;
                    }
                    findButton.text(changeText);
                    findButton.attr("disabled", "disabled");
                    $.ajax({
                      type: "POST",
                      url: url,
                      showLoader: true,
                      data: formData,
                      success: function(data) {                           
                          if(data.error) {
                            $widget.showResponse(data,'','false');
                            findButton.text(changeSubmit);
                            findButton.removeAttr('disabled');
                          } else {
                            $widget.showResponse(data,'','true');
                            setTimeout(function(){ location.reload(); }, 3000);                            
                          }
                        }
                    });
                }
            });
        },

        showResponse: function(data,Errordata,close,view_section='') {
            if(view_section == 'payment'){
                if(data.error) {
                    $('.block-customer-add-payment .response-msg').html("<div class='error'>"+data.error+"</div>");
                    } else {
                        $('.block-customer-add-payment .response-msg').html("<div class='success'>You saved the payment information.</div>");
                    }
                    setTimeout(function(){ $('.block-customer-add-payment .response-msg').html(null); }, 5000);
                    return false;
                }


              if(data.error || Errordata) {
                if(Errordata != '')
                {
                    var error = Errordata;
                }else{
                    var error = data.error;
                }
                $('.block-customer-edit-address .response-msg').html("<div class='error'>"+error+"</div>");
              } else {
                $('.block-customer-edit-address .response-msg').html("<div class='success'>"+data.success+"</div>");
              }
              if(close == 'true')
              {
                      setTimeout(
                    function(){ 
                        $('.response-msg').html(null); 
                        $(".mfp-close-inside").trigger("click");
                         var findButton = $('#customer-edit-address-validate').find('button[type=submit]');
                             findButton.text('Submit');
                            findButton.removeAttr('disabled');
                            
                    }, 5000);
              }
            
            },

        getFormData: function(formElem){
                var unindexed_array = formElem.serializeArray();
                var indexed_array = {};

                jQuery.map(unindexed_array, function(n, i){
                    indexed_array[n['name']] = n['value'];
                });

                return indexed_array;
            },

        changePayment: function($widget){
             var statementhtml = mageTemplate(payment_info, {
                        datapayment: $widget.options.datapayment,
                        $saved_cards: $widget.options.saved_cards,
                        custNum: $widget.options.custNum,
                        viewfileurl: $widget.options.viewfileurl,
                        formkey: $widget.options.formkey,
                        baseurl: $widget.options.baseurl,
                    });
            $("#collapseThree").html(statementhtml).delay(2000);
        }

    });
    return $.mage.SwatchRenderer; 
});