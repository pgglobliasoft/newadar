require([
    "jquery"
], function($) {
    "use strict";
    $(document).ready(function(){
        var productjson = {};
        var productid = window.productid;
        
        if(window.productid.length){
            $.ajax({
                url: window.location.origin+"/adaruniforms/product/view",
                type: "POST",
                data: {productid: window.productid},
                showLoader: false,
                cache: true,
                success: function(response) {
                    productjson = response;
                    setActionbutton(productjson);
                    setprodata(productjson);
                  },
              });         
        }     
        
    });

    function setprodata(productjson){
        
        var getUrl = window.location;
        var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";

        var product_Feature = productjson.profeature;
        var product_Description = productjson.prodescription;
        var product_Details = productjson.prodetails;
        var product_Fabriccontent = productjson.profabriccontent;
        var product_Fabricimageurl = productjson.profabricimageurl;

        var FEATURE = productjson.lable.profeature;
        var DESCRIPTION = productjson.lable.prodescription;
        var DETAILS = productjson.lable.prodetails;
        var FABRIC = productjson.lable.profabriccontent

            
        var renderhtml = '';
        for ( var key in product_Feature){
            if(product_Feature[key].small_image != null){
                renderhtml += "<li class='value'><img src='"+baseUrl+"pub/media/"+product_Feature[key].small_image+"'> "+product_Feature[key].name+"</li>"
            }else{
                renderhtml += "<li class='value'>"+product_Feature[key].name+"</li>"
            }
        }

        if($(window).width() < 768){
          renderhtml = "<div class='u-fabric-care' style='display: block;'><div class='box'><a class='fabric-care-chart' data-toggle='modal' data-target='#fabriccarePopupModal'>(Fabric features &amp; care)</a></div> </div>"+renderhtml;
        }

        if(renderhtml == ''){
            // renderhtml = "Feature Informaion not Availabl";
            $('.product-info-main').find('.u-fabric-care .fabric-care-chart').html('');
            $('.product-info-main').find('.type.features').html('');
            $('.product-info-main').find('.product.attribute.features').css({'display':'none'});
        }
        else
        {
            $('.product-info-main').find('.type.features').html(FEATURE);

        }
        $('.product-info-main').find('.productQuality.product.feature').html(renderhtml);

        if(typeof product_Description != "undefined"){
            $('.product-info-main').find('.product.attribute.description .value').html(product_Description);
            $('.product-info-main').find('.type.productdescription').html(DESCRIPTION);
        }else{
            $('.product-info-main').find('.product.attribute.description .value').html("");
            $('.product-info-main').find('.product.attribute.description').css({'display':'none'});

        }
        if(typeof product_Details != 'undefined'){
            $('.product-info-main').find('.product.attribute.bulletsdetails .value').html(product_Details);
            $('.product-info-main').find('.type.details').html(DETAILS);
        }else{
            $('.product-info-main').find('.product.attribute.bulletsdetails .value').html("");
            $('.product-info-main').find('.product.attribute.bulletsdetails').css({'display':'none'});

        }
        if(typeof product_Fabriccontent != 'undefined'){
            $('.product-info-main').find('.fabriccontent-subdiv .value').html(product_Fabriccontent);
            $('.product-info-main').find('.type.fabric').html(FABRIC);
        }else{
            $('.product-info-main').find('.fabriccontent-subdiv .value').html("");
            $('.product-info-main').find('.fabriccontent-subdiv').css({'display':'none'});
            
            
        }
        if(typeof product_Fabricimageurl != 'undefined' && product_Fabricimageurl != null){
            $('.column.main').find('.fabriccarepopupContainer .modal-content img.fabric_pop_img').attr("src",product_Fabricimageurl);
        }else{
            $('.column.main').find('.fabriccarepopupContainer .modal-content img.fabric_pop_img').attr("src",baseUrl+"pub/media/fabricurl/placeholder/fabric_placeholder_text.png");
        }
    }
    function setActionbutton(productjson){
        var regula = productjson.topactionbuttondata.regula;
        var petite =  productjson.topactionbuttondata.petite;
        var tail =  productjson.topactionbuttondata.tail;

        var productsku = window.productsku
        var final_html = ''; 

        if(petite != '' || tail != '')
        {
            if(regula != '')
            {
                var active = '';
                if(productjson.topactionbuttondata.regula.sku == productsku)
                {
                    active = 'activeCat';
                }
                else
                {
                    active = '';
                }
                final_html += "<a href='"+productjson.topactionbuttondata.regula.url+"' class='customBtns "+active+"'>Regular</a>";
            }
            if(petite != '')
            {
                var active = '';
                if(productjson.topactionbuttondata.petite.sku == productsku)
                {
                    active = 'activeCat';
                }
                else
                {
                    active = '';
                }
                final_html += "<a href='"+productjson.topactionbuttondata.petite.url+"' class='customBtns "+active+"'>Petite</a>";
            }
            if(tail != '')
            {
                console.log(productjson.topactionbuttondata.tail);
                var active = '';
                if(productjson.topactionbuttondata.tail.sku == productsku)
                {
                    active = 'activeCat';
                }
                else
                {
                    active = '';
                }
                final_html += "<a href='"+productjson.topactionbuttondata.tail.url+"' class='customBtns "+active+"'>Tall</a>";
            }
            console.log(final_html);
            $('.product_media_container').find('.catBtns').html(final_html);
        }
        else
        {
            $('.product_media_container').find('.catBtns').css({'display':'none'});
        }

    }
});


