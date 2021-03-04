require(['jquery'], function($) {

  jQuery(document).on("click",".catBtns .customBtns", function(){
         var getUrl = window.location;
            var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";
            url = baseUrl + "adaruniforms/index/productswatch";
          var new_style = jQuery(this).attr("product-sku");
          var data = '';
          var x =  jQuery(this)
          jQuery(".catBtns .customBtns").removeClass("activeCat")
          x.addClass("activeCat");
          jQuery.ajax({
          url: url,
          enctype: 'multipart/form-data',
          type: "POST",
          data:{productstyle:new_style},
          showLoader: true,
          cache: true,
          success: function(response){
              if(response.error){
                adderror(response.error)
                pstyleid.addClass();
                jQuery(".catBtns .customBtns").removeClass("activeCat")
                x.addClass("activeCat");
              }else{
                jQuery("#color-data").html("")
                if(jQuery("#color-data").html(response.html)){
                  jQuery('.swtach .swatch-option').removeClass('active');
                  jQuery('.swtach .swatch-option').first().trigger('click').addClass('active');
                  // jQuery(".swtach .swatch-option").first();
                }

              }
              }
        });
        });
});
