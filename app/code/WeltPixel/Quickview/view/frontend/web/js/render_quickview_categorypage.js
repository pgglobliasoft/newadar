define(['jquery', 
  'mage/template',
  'text!WeltPixel_Quickview/template/quickviewpopup.html',
  'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
  'mage/requirejs/json!Sttl_Customerorder/template/MageProJson.json',
], function($,mageTemplate,quickviewpopup,inventory,mageprojson) {
    'use strict';
    var simpleproduct = [],
        configurationproduct = [],
        sizeoption = [],
        proskuss = [],
        login = 1,
        orderDevelopquick1 = false;
    $.widget('mage.PopupRenderer', {
        options: {
            viewfileurl:{},
            parentstyledata: inventory,
            baseurl:{},
            action: {},
            login : {}
        },
        _init: function () { 
        },
        _create: function () {
            var $widget = this
            login = $widget.options.login  
            $widget._EventListener(); 
            if($widget.options.action === "catalog-category-view" || $widget.options.action === "catalogsearch-result-index"){
                $widget.setdata();
            }
        },
        setdata: function(){
            simpleproduct = mageprojson[0]['simapleproduct'];
            sizeoption = mageprojson[0]['sizeoption'];
            configurationproduct = mageprojson[0]['configurationproduct'];
            $(".orderDevelopquick").removeClass("orderDevelopquick");
            $(".loadDots123").removeClass("loadDots123");
            $(".quickviewpopup1").parent().removeClass();
            $(".quickdots").remove();
            $('.customquickviewpopup.quickviewpopup1').attr("href", "#quickviewpopup1");
            $('.customquickviewpopup.quickviewpopup1').attr("data-target", "#quickviewpopup1");


        },
        feachsiteattrvalue: async function(){
            const response = await fetch("https://dev.adaruniforms.com/rest/V1/attribute/options?id=size")
            var res = await response.json()
            sizeoption = res[0].option;
            login = res[0].customerdata;            
        },
        getproductskus: function(){
            var selector = $('.products.list.items').find(".product-item").children();
            proskuss = [];
            selector.each(function(){
                const item_sku = $(this).attr('data-config-item');
                if(!_.contains(proskuss, item_sku)){
                    proskuss.push(item_sku);
                }
            })

            return proskuss
        },
         getProductArrayforviewstock: function(sku , option){
           var key = option == 1 ? 'Style' : 'ItemCode',
               falg = _.filter(this.options.parentstyledata , function (value) {
                    return value[key] === sku;
            });
           return falg;

        },
        dashboardpagequickview: function(){
            var $widget = this
            var url = $widget.options.baseurl+"customerorder/customer/quickview";
                    simpleproduct = featurepro[0]['simapleproduct'];
                    sizeoption = featurepro[0]['sizeoption'];
                    configurationproduct = featurepro[0]['configurationproduct'];
                    $(".quickviewpopup1").css("pointer-events","")
                    $(".grid-item.grid-sizer.forhovereffact").removeClass("blure");
                    $widget.setfeaturedpro($widget);
        },
        catalogpagequickview: function(todoIdList){
            const tem = [];
            for(var i = 0; i < todoIdList.length; i=i+3) {
                var str = ''
                for (var j = i; j < i+3; j++) {
                    if(todoIdList[j]){
                     
                     str += todoIdList[j]+",";
                    }
                    
                 }
                str = str.substring(0,str.length - 1);
                tem.push(str); 
            }  
            
            Promise.all(
              tem.map(id => {
                return new Promise((resolve) => {
                  fetch("https://dev.adaruniforms.com/rest/V1/ProductApi?id="+id,{
                    method: 'GET',
                    headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json'
                    }
                  })
                    .then(response => {
                      return new Promise(() => {
                        response.json()
                          .then(todo => {
                            simpleproduct = $.merge(simpleproduct, todo[0].simapleproduct);
                            configurationproduct = $.merge(configurationproduct, todo[0].configurationproduct);
                            // console.log(simpleproduct)
                            // console.log(configurationproduct)
                            orderDevelopquick1 = true;
                            $.each(todo[0].configurationproduct,function(key,value){
                                $(".quickviewpopup1#"+value.id).parent().removeClass();
                                $(".quickdots#"+value.id).remove();
                                $(".orderDevelopquick").removeClass("orderDevelopquick");
                                $(".loadDots123").removeClass("loadDots123");
                                $('.customquickviewpopup.quickviewpopup1').attr("href", "#quickviewpopup1");
                                $('.customquickviewpopup.quickviewpopup1').attr("data-target", "#quickviewpopup1");
                            })
                            resolve(0)
                          })
                      })
                    })
                })
              })
            )
        },
        _EventListener: function () {
             var $widget = this,
                options = this.options;   
                $('.customquickviewpopup').on('click', function(e){
                    e.preventDefault();
                    if($(this).find('.quickdots').length <= 1 && $(this).find('.quickdots').is(":visible")){
                        return false;
                    }
                })
            $(document).keyup(function(e){
                 if (e.keyCode === 27) {
                     $('.QuickViewContenthtml .modalContainer.quickViewCont button.close').trigger('click');
                     $('button.mfp-close.close-image-chart-popup').trigger('click');
                 }
                 if (e.keyCode === 13) {
                    if($(".checkvalue").val()){
                        $(".saveChng").trigger("click")
                    }
                 }
             });

            document.addEventListener("newproduct_rendered", function (e) {
                $(".orderDevelopquick").removeClass("orderDevelopquick");
                $(".loadDots123").removeClass("loadDots123");
                $(".quickviewpopup1").parent().removeClass();
                $(".quickdots").remove();
                $('.customquickviewpopup.quickviewpopup1').attr("href", "#quickviewpopup1");
                $('.customquickviewpopup.quickviewpopup1').attr("data-target", "#quickviewpopup1");
            });

            $("#quickviewpopup1 .modal-content").draggable({
                handle: ".container.bg-primary.p-2",
                containment: ".modal-backdrop"
            });

            $(document).on("click",".quickviewpopup1",function(){
                var id = $(this).attr('id');
                $widget.quickviewpopuphtml($widget,id) ;

            })
            $( ".product-item-info" ).mouseover(function() {
              if(orderDevelopquick1 == true){
                $('.orderDevelopquick').removeClass("orderDevelopquick");
                    $("div").remove(".quickdots");
                }else{
                    $('.customquickviewpopup.quickviewpopup1').unbind("click");
                    $('.orderDevelopquick').css("display", "block");
                }
            });
            $(document).on("click",".QuickViewContenthtml .swatch-option.image",function(){
                    $(".QuickViewContenthtml .swatch-option.image").removeClass("selected");
                    $(this).toggleClass("selected");
                var productsku = $(this).attr("product-id"),
                    prodata = $widget.getProductArray(simpleproduct,productsku),
                    colortype = $(this).attr("color-type"),
                    colorname = $(this).attr("aria-label"),
                    parentsku = $(this).attr("parent-id");
                    if(colortype != "size"){
                        $("span.swatch-attribute-selected-option.color").html("");
                        $("span.swatch-attribute-selected-option.heather").html("");
                        $("span.swatch-attribute-selected-option.seasonal").html("");
                    }
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html(colorname);
                $widget._onclickchangeproduct($widget,prodata,colorname,colortype,parentsku);
            })

            $(document).on("mouseover",".QuickViewContenthtml .swatch-option.image",function(){
                var  colorname = $(this).attr("aria-label");
                var  colortype = $(this).attr("color-type");
                $(this).addClass("selected2")
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html("");
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html(colorname);

            })
            $(document).on("mouseout",".QuickViewContenthtml  .swatch-attribute .swatch-option.image",function(){
                
                var  colortype = $(this).attr("color-type");
                var  colorname = $('[color-type='+colortype+'].selected').attr("aria-label");  
                $(this).removeClass("selected2")
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html("");
                $(".QuickViewContenthtml .swatch-attribute-selected-option."+colortype).html(colorname);
            })
           
            $(document).on("click",".QuickViewContenthtml .swatch-option.text.swatch-option-size",function(){
                var size = $(this).attr('option-tooltip-value');
                $('span.swatch-attribute-color-selected-option').html(size);
                $(".QuickViewContenthtml .swatch-option.text.swatch-option-size").removeClass("selected");
                    $(this).toggleClass("selected");
            })
           
        },  
        setfeaturedpro: function($widget){
            var html = ''
            $.each(configurationproduct,function(key,value){
               html +=  '<div class="grid-item grid-sizer forhovereffact"> <a href="#quickviewpopup1" data-target="#quickviewpopup1" class="quickviewpopup1" id ="'+value.id+'"  data-toggle="modal"><div class="featureborder"> <img src="'+$widget.options.baseurl+"pub/media/catalog/product"+value.productimgurl+'"></div></a></div>'
            })
            $(".featured-row .orderItem-loader").hide();
            $(".featured-pro").append(html);
        },
        _onclickchangeproduct: function($widget,productdata,colorname,colortype,parentsku){
            var data = productdata
            var featuhtml = ''
            $.each(data[0].feature,function(index,value){ 
                featuhtml+= "<li class='value'><img src="+$widget.options.baseurl+'pub/media/'+value.small_image+">"+value.name+"</li>"
            })
            $(".productQuality.product.feature").html(featuhtml);
            var fabric_chaturl = data[0].fabric_chat == '' ? this.options.baseurl+"pub/media/fabricurl/placeholder/fabric_placeholder_text.png" : data[0].fabric_chat;
            $("img.fabric_pop_img").attr("src",fabric_chaturl)
            var itemno = $("#big .owl-item.active").find(".item").attr("itemno");
            
            $("div#sizechartPopupModal .size-image img").attr("src",data.size_chat)


            var imagesliderhtml = $widget.slidervlaue(data[0].image);
                if(data[0].image.length == 1){
                    $("#big").html(imagesliderhtml);
                    $("#thumbs").trigger('replace.owl.carousel', imagesliderhtml);
                    $("#thumbs").trigger('refresh.owl.carousel');
                }


            if(data[0].image.length > 1){
                
                $("#big").trigger('replace.owl.carousel', imagesliderhtml);
                $("#thumbs").trigger('replace.owl.carousel', imagesliderhtml);
                $("#big").trigger('refresh.owl.carousel');
                $("#thumbs").trigger('refresh.owl.carousel');
                $("#big").data("owlCarousel").to(itemno,1, true);
                
                var current = itemno
                var thumbs = $("#thumbs"); 
                  thumbs.find(".owl-item").removeClass("current").eq(current).addClass("current"); 
                var onscreen = thumbs.find(".owl-item.active").length - 1; 
                var start = thumbs.find(".owl-item.active").first().index();
                var end = thumbs.find(".owl-item.active").last().index();
                if (current+1 > end) {
                  thumbs.data("owlCarousel").to(current, 100, true);
                }
                if (current < start) {
                  thumbs.data("owlCarousel").to(current - onscreen, 100, true);
                }
              
            }
            var simp = $widget.getProductArray(simpleproduct,parentsku,'parent_sku'),
                 sizess =  $widget.sizefilter(simp,colorname,colortype),
                 sizehtml = '';

            $.each(sizess, function(key,value){
                var sizelable = $widget.sizevalue(value.size)

                sizehtml += '<div class="swatch-option text swatch-option-size image" sort="'+sizelable.index+'" color-type="size" aria-label="'+sizelable.label+'" role="option">'+sizelable.label+'</div>'
            })
            $("#size-attr").html(sizehtml);

            $widget.changeSizeOrderr();
            // $("#big").data("owlCarousel").to(itemno, 0, true);
        //     setTimeout(function(){
        //         $("#thumbs").data("owlCarousel").to(itemno, 0, true);
        // },100)

        },
        sizevalue: function(size){
            var obj = {}
            $.map(sizeoption,function(index,val){
                if(index.value == size){
                    obj['label'] = index.label;
                    obj['index'] = val;
                }
            })
            return obj
        },      
        slidervlaue: function(res){
            var html = '';
            for (var i = 0; i < res.length; i++) {
                      
                      html += "<div class='item' itemno="+i+">"
                      html += "<img src="+res[i]+">"
                      
                      html += "</div>"
            }
            return html
        },
        quickviewpopuphtml: function($widget,psku){
            var simp = $widget.getProductArray(simpleproduct,psku,'parent_sku'),
                config = $widget.getProductArray(configurationproduct,psku),
                fabric_chaturl = config[0].fabric_chat == '' ? this.options.baseurl+"pub/media/fabricurl/placeholder/fabric_placeholder_text.png" : config[0].fabric_chat;
            var images = simp[0].image;
            if(images.length <= 0){
                $.each(simp,function(key,value){
                    if(value.image.length > 0){
                        images = value.image
                    }
                })
            }
            var brandurl ='';
            if(config[0].productBrandUrl){
                
                if(config[0].productBrandUrl.length > 0){

                    // var brandUrl = config[0].productBrandUrl[0];
                    // var demo = jQuery.inArray('brand_url',brandUrl);
                    // if(demo >= 0)
                    // {
                        brandurl = config[0].productBrandUrl[0].brand_url;
                    // }
                }



                else
                {
                    brandurl ='#';
                }
            }
            var configid = $('.product-item-info').attr('data-config-item');
            var products = this.getProductArrayforviewstock(configid , 1);
            var hasviewstockdata = true;
            // console.log(products);
            if(products.length <= 0)
            {
                hasviewstockdata = false;
            }

            // console.log(config);
            $('.modal-content.ui-draggable').css('left','0px');
            $('.modal-content.ui-draggable').css('top','0px');
                var styleconfiguration = mageTemplate(quickviewpopup, {
                        sku: config[0].id,                       
                        name: config[0].name,  
                        producturl: config[0].producturl,                     
                        collection: config[0].collection,                       
                        color: $widget.filterbyattr(simp,'color',1),                       
                        heathercolor: $widget.filterbyattr(simp,'heathercolor',1),                       
                        seasonalcolor: $widget.filterbyattr(simp,'seasonalcolor',1),                       
                        size: $widget.filterbyattr(simp,'size'),
                        featureddata:simp[0].feature, 
                        productimage: config[0].productimages,
                        baseurl: this.options.baseurl,  
                        sizeoption: sizeoption,
                        sizevaluee: $.proxy(this.sizevalue, this),
                        productbaseurl : brandurl,
                        logindata: login,
                        hasviewstockdata : hasviewstockdata
                    });

            
            $("#featuredproductpopupdata").html(styleconfiguration);
            $widget.imageslider();
            $widget.removedublicatecolor($widget,psku);
            
            setTimeout(function(e){
             // $(".QuickViewContenthtml .swatch-option.image").first().trigger("click");
             $widget.changeorder();
            },1000);

        },
        removedublicatecolor: function($widget,psku){
            var simp = $widget.getProductArray(simpleproduct,psku,'parent_sku'),
                color = $widget.filterbyattr(simp,'color',1),                       
                heathercolor = $widget.filterbyattr(simp,'heathercolor',1),                       
                seasonalcolor = $widget.filterbyattr(simp,'seasonalcolor',1);
                $.each(heathercolor,function(key,value){
                    $('[color-type="color"]').each(function(){
                       var color = $(this).attr("aria-label")
                        if(value.heathercolor == color){
                            $(this).remove();
                        }
                    });
                });
        },


        changeorder: function(){

                if ($('.swatch-option.image').is(':visible')) { //if the container is visible on the page
                    changeOrder($('[aria-labelledby=option-label-color-93]')); //Adds a grid to the html
                    changeOrder($('[aria-labelledby=option-label-seasonalcolors-152]'));
                    changeOrder($('[aria-labelledby=option-label-heather_colors-227]'));
                    changeSizeOrder($('[aria-labelledby=option-label-size-145]'));

                } 
            function changeOrder(lbl) {
                var $wrapper = lbl;
                $wrapper.find('.swatch-option').sort(function(a, b) {
                    if ($(a).attr('aria-label') > $(b).attr('aria-label')) {
                        return 1;
                    } else {
                        return -1;
                    }
                }).appendTo($wrapper);
            }
            function changeSizeOrder(lbl) {
                var $wrapper = lbl;
                $wrapper.find('.swatch-option').sort(function(a, b) {
                    if (parseInt($(a).attr('sort')) > parseInt($(b).attr('sort'))) {
                        return 1;
                    } else {
                        return -1;
                    }
                }).appendTo($wrapper);
            }
        },

        changeSizeOrderr: function(){
            var $wrapper = $('[aria-labelledby=option-label-size-145]');
            $wrapper.find('.swatch-option').sort(function(a, b) {
                if (parseInt($(a).attr('sort')) > parseInt($(b).attr('sort'))) {
                    return 1;
                } else {
                    return -1;
                }
            }).appendTo($wrapper);
        },
        imageslider: function(){
            var bigimage = $("#big");
              var thumbs = $("#thumbs");
              var syncedSecondary = true;
              bigimage
                .owlCarousel({
                items: 1,
                slideSpeed: 2000,
                lazyLoad: true,
                nav: true,
                autoplay: false,
                dots: false,
                loop: ($(".owl-carousel .owl-item").length >= 1) ? true : false,
                dots: false,
                responsiveRefreshRate: 200,
                navText: [
                  '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                  '<i class="fa fa-angle-right" aria-hidden="true"></i>'
                ]
              })
                .on("changed.owl.carousel", syncPosition);

              thumbs
                .on("initialized.owl.carousel", function() {
                thumbs
                  .find(".owl-item")
                  .eq(0)
                  .addClass("current");
              })
                .owlCarousel({
                items: 4,
                dots: true,
                margin:10,
                nav: true,
                dots: false,
                navText: [
                  '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                  '<i class="fa fa-angle-right" aria-hidden="true"></i>'
                ],
                smartSpeed: 200,
                slideSpeed: 500,
                slideBy: 4,
                responsiveRefreshRate: 100
              })
                // .on("changed.owl.carousel", syncPosition2);

              function syncPosition(el) {
                //if loop is set to false, then you have to uncomment the next line
                //var current = el.item.index;

                //to disable loop, comment this block
                var count = el.item.count - 1;
                var current = Math.round(el.item.index - el.item.count / 2 - 0.5);

                if (current < 0) {
                  current = count;
                }
                if (current > count) {
                  current = 0;
                }
                if(current == 5){
                 current = 0;
                }
                //to this
                thumbs.find(".owl-item").removeClass("current").eq(current).addClass("current"); 
                var onscreen = thumbs.find(".owl-item.active").length - 1; 
                var start = thumbs.find(".owl-item.active").first().index();
                var end = thumbs.find(".owl-item.active").last().index();
   
                if (current+1 > end) {
                  thumbs.data("owlCarousel").to(current, 100, true);
                }
                if (current < start) {
                  thumbs.data("owlCarousel").to(current - onscreen, 100, true);
                }
              }

              function syncPosition2(el) {
                if (syncedSecondary) {
                  var number = el.item.index;
                  $("#big").data("owlCarousel").to(number, 100, true);
                }
              }

              thumbs.on("click", ".owl-item", function(e) {
                e.preventDefault();
                var number = $(this).index();
                $("#big").data("owlCarousel").to(number, 300, true);
              });
                  
        },
        getProductArray: function(arr,sku ,option=''){
           var key = option == 'parent_sku' ? 'parent_sku' : 'id',
               falg = _.filter(arr , function (value) {
                    return value[key] === sku;
            });
           return falg;
        },
        sizefilter: function(arr,color,type){
            var $widget = this, key = '', ukey = '';
                switch (type) { 
                case 'color': 
                    key = 'color'  
                    ukey = 'colorurl'   
                    break;
                case 'heather': 
                    key = 'heathercolor'    
                    ukey = 'heathercolorurl'   
                    break;
                case 'seasonal': 
                    key = 'seasonalcolor'    
                    ukey = 'seasonalcolorurl'
                    break;
                }
            
                var dataaa = [];
               $.each(arr,function(vkey,value){
                    if(!_.contains(dataaa, value.size)){
                        if(value[key] === color){
                            dataaa.push(value);
                        }
                    }
               })

           return dataaa;
        },
        filterbyattr: function(simpleproduct,attr,atype = 0){
            var $widget = this, key = '', ukey = '';
            switch (attr) { 
                case 'color': 
                    key = 'color'  
                    ukey = 'colorurl'   
                    break;
                case 'heathercolor': 
                    key = 'heathercolor'    
                    ukey = 'heathercolorurl'   
                    break;
                case 'seasonalcolor': 
                    key = 'seasonalcolor'    
                    ukey = 'seasonalcolorurl'
                    break;
                case 'size': 
                    key = 'size'    
                    break;
            }
            var attr = [],
                temp = []
           $.each(simpleproduct,function(vkey,value){
                if(value.image){
                if(!_.contains(temp, value[key])){
                    temp.push(value[key])
                    var obj = {}
                    if(atype == 1){
                        if(value[key] != "No Color" && value[key] != null && value[ukey] != null ) {
                            attr.push(value)
                        }
                    }else{
                        
                        attr.push(attr.push(value))
                    }
                }
            }
           })
           return attr;
        },
    });

    return $.mage.PopupRenderer;
});