require(['functions','jquery.bootstrap'], function ($,jquery)
{
		'use strict';
		jQuery.noConflict();
		jQuery(document).ready( function(){
			if(jQuery(window).width() < 1024){
				//console.log('less than 1024');
				var isMobile = false; //initiate as false
				// device detection
				//console.log('var12');
				if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
					|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) { 
					isMobile = true;
					//console.log('2122');
				}
			}
			
			jQuery(document).on('keydown','#amform-form-7 input', function (e) {
			  if (this.value.length === 0 && e.which === 32) e.preventDefault();
			})
			jQuery(".login-menu-items").hover(function(){
			  jQuery(".afterLoginMenu").show();
		   });
		   jQuery(".login-menu-items").mouseout(function(){
			  jQuery(".afterLoginMenu").hide();
		   });
			/*if( jQuery(".trackPack").length) {
				jQuery('[data-toggle="tooltip"]').tooltip()
			}*/
			if( jQuery(".prodSlider").length) {
				jQuery('.prodSlider').owlCarousel({
					 loop:true
					,autoplay:true
					,autoplayTimeout:3000
					,smartSpeed:1200
					,margin:20
					,nav:false
					,dots:false
					,responsive:{
						0:{
							items:1
						},
						600:{
							items:3
						},
						1000:{
							items:7
						}
					}
				})
			};
			
			if(jQuery(window).width() < 769){	
				if(jQuery(".bgBackground").length){
					jQuery(".bgBackground").each(function(){
					var imagePath = jQuery(this).find("img").attr("src");		
					jQuery(this).css("background-image","url( " + imagePath  + " )" );
					});
				}
				
				if(jQuery(".bgBackground").length){
					jQuery(".bgBackground").each(function(){
					var imagePath = jQuery(this).find("img").attr("src");		
					jQuery(this).css("background-image","url( " + imagePath  + " )" );
					});
				}
			}
			if(jQuery(window).width() < 1024){	
				var navHeight = jQuery('.navigation').height() + 60;
				var	winHeight = jQuery(window).height();
				if(navHeight > winHeight){
					navHeight = winHeight - 60;
					navHeight = winHeight;
					jQuery('.navigation').height(winHeight - 60);
				}		
			}
			else{
				jQuery('.navigation').removeAttr("style");
				jQuery('.nav-toggle').removeClass("closeToggle");
				jQuery('.submenu').removeAttr("style");
			}
		});
		
		jQuery(document).ready(function(){
			
			if(jQuery("#removeUser").length){
				var delid = '';
				jQuery('#removeUser').on('show.bs.modal', function(e) {
					delid = jQuery(e.relatedTarget).data('id');
					//alert(delid);
				});

				jQuery('#remove-button').click(function() {
					//alert(delid);
					//Do what ever you like to do
					//$.post('/API/removeUser', {} );
				});
			}
			
			if(jQuery(".scrollStoreListing").length){
				jQuery(".scrollStoreListing").click(function(event){
					jQuery('.mCustomScrollBox').animate({scrollTop : '+=300'}, 800);
				});
			}
			if(jQuery(".storeListing").length){
				jQuery(".storeListing").mCustomScrollbar();			
			}
			/*if(jQuery(".trackPack").length){
				setTimeout(function(){
				jQuery(".trackPack").mCustomScrollbar();			
				},200);
			}*/
			
			if(jQuery(".langSelect").length){
					jQuery('.langSelect').customSelect();
				}
				if(jQuery("#sorter").length){
					jQuery('#sorter.sorter-options').customSelect();
				}
				setTimeout(function(){
				if(jQuery(".limiter").length){
					jQuery('.limiter select').customSelect();
				}
				},1000);
				if(jQuery(window).width() > 768){
					if(jQuery(".cms-download .sorting select").length){
						jQuery('.cms-download .sorting select').customSelect();
					}
				}
				
				/*jQuery(".buyOnline").on("click", function (e) {	
						e.preventDefault();
						var target = this.hash,
							$target = jQuery('.brand-slider-main');
					
						jQuery('html, body').stop().animate( {
						  'scrollTop': $target.offset().top-40
						}, 900, 'swing', function () {
						  window.location.hash = target;
						});
					});
				*/
				// console.log('genral .min .js');
				

				jQuery(document).on("click", ".cms-index-index a.action.remind",function(e){				
					jQuery('.cms-index-index .login-overlay').css('display','none');
				})

				jQuery(document).on("click", ".buyOnline",function(e){				
					jQuery(".buyOnline").toggleClass('active');
					e.preventDefault();
					if(jQuery(this).hasClass("active")){
							//var target = this.hash,
							var $target =  jQuery(".brand-slider-main");
							var divHeight = jQuery(".brand-slider-main").height();						
						if(jQuery(".product-info-main").hasClass("stick")){
						
						}else{												
							// jQuery('html, body').stop().animate( {
							//   'scrollTop': $target.offset().top  -  380 + 100
							// }, 900);
						}
					}
					
					var owl_product = jQuery('.brand_slider');
					owl_product.trigger('next.owl',[1000]);
					
					
					if(jQuery(".quickViewContent").length <= 0)
					{
						if(jQuery(".sttl_brand").is(':visible'))
						{
							jQuery(".sttl_brand").hide('slow').queue(function(n) {
							  sticky_relocate(true);n();
							  jQuery(".brand-slider-main").css('visibility', 'visible');
							});
						}
						else
						{
							jQuery(".brand-slider-main").css('visibility', 'hidden');
							jQuery(".sttl_brand").show('slow').queue(function(n) {
								sticky_relocate(true);
								jQuery(".brand-slider-main").css('visibility', 'visible');
								n();
							});
						}
					}
					else
					{
						jQuery(".sttl_brand").slideToggle('slow');
					}

					var auto_play = true;
					if ( jQuery(".quickViewContent").length ) {
						/*setTimeout(function(){*/
							if( jQuery(".brand_slider").length > 0) {
						        jQuery('.brand_slider').owlCarousel({
						            loop:true
						            ,autoplay:auto_play
						            ,autoplayTimeout:1000
						            ,smartSpeed:1000
						            ,margin:0
						            ,navigation: true
						            ,nav:true
						            ,navText: ["<i class='fa fa-angle-left' aria-hidden='true'></i>", "<i class='fa fa-angle-right' aria-hidden='true'></i>"]
						            ,dots:false
									,itemElement: 'owl-item'
									,items :3
						            ,responsive:{
										items:3
									}
						        });
						    }
						/*},600);*/
					}
				});
				jQuery("#map-tab").click(function () {
					setTimeout(function(){
						jQuery("#retailers").removeClass('active show');
						jQuery(".retailerSlider").hide();						
					},300);
				});
				jQuery("#list-tab").click(function () {
					setTimeout(function(){
						jQuery("#retailers").removeClass('active show');
						jQuery(".retailerSlider").hide();						
					},300);
				});
				jQuery("#retailer-tab").click(function () {
					setTimeout(function(){
						jQuery("#mapView").removeClass('active show');
						jQuery("#storeListing").removeClass('active show');
						jQuery(".retailerSlider").show();
					},300);
				});
				
				
				jQuery('.cart-popup > a').on('click', function() {
					var dataTag = jQuery(this).parent('.cart-popup');
					
					setTimeout(function(){
						jQuery(".mCustomScrollbar").mCustomScrollbar("scrollTo", dataTag);
						/*var hash= jQuery(this).parent();
						console.log(hash);
						var target= jQuery(this).parents(".mCustomScrollbar");
						console.log(target);
						target.mCustomScrollbar("scrollTo", hash.position().top, {
							// scroll as soon as clicked
							timeout:0,
							// scroll duration
							scrollInertia:200,
							});
							*/
					}, 350);
				});
				
			if(jQuery('.innerBanner img').length){
				var innerImg = jQuery('.innerBanner img').attr('src');
				jQuery('.innerBanner').css('background-image','url('+innerImg+')');		
			}	
			if(jQuery(window).width() < 768){
				jQuery(".customattribute .type").on("click", function () {
					event.preventDefault();
					jQuery(this).next().slideToggle();
					jQuery(this).toggleClass('togActive')
				});
				
			}
			else{
				jQuery('#hideshow').show();
				jQuery('#block-search').hide();				
				/*jQuery("#hideshow").on("click", function (event) {
					event.stopPropagation(event);
					 jQuery("#block-search").toggle('slow');
					 jQuery("#search").focus();
				});*/
				jQuery("#block-search").on("click", function (event) {
					event.stopPropagation();
				});
				jQuery(document).on("click", function () {
					jQuery("#block-search").hide('slow');
				});
			}
			jQuery("#hideshow").on("click", function (event) {
					event.stopPropagation(event);
					 // jQuery("#block-search").toggle('slow');
					 if(jQuery("#block-search").css('display') !== 'none'){
					 	if(jQuery("input#search").val()){
					 		jQuery("button.action.search").trigger("click");
					 	}else{
					 		jQuery("#block-search").hide('slow');
					 	}
					 }else{
					 	jQuery("#block-search").show('slow');
					 	jQuery("#search").focus();
					 }
				});
		});
		

		




		var stickyOffsetPls =  41;	
		jQuery(window).scroll(function(){
		  var sticky = jQuery('body'),
			  scroll = jQuery(window).scrollTop();
	
		  if (scroll >= stickyOffsetPls) {



			  if (jQuery("fixedHeader").length === 0){
				sticky.addClass('fixedHeader');
				if(jQuery(window).width() > 768){
					jQuery('.logo').addClass('condensed');
					jQuery('.logoDesktop').hide();
					jQuery('.mobileLogo').show();
				}
				//jQuery('.topStrip').hide();
			  }
			}
			else { 
				if (sticky.hasClass("fixedHeader")){
						sticky.addClass('fixedHeaderBack');
						setTimeout(function(){
							sticky.removeClass('fixedHeader');
							sticky.removeClass('fixedHeaderBack');
							jQuery('.logo').removeClass('condensed').addClass('logoFull');
							jQuery('.logoDesktop').show();
							//jQuery('.topStrip').show();
							jQuery('.mobileLogo').hide();
					  },5);
				  }	
			  }  
		
		});
		jQuery(".nav-toggle").click(function(){
		    jQuery(".navigation").toggle('slow');
		    jQuery( ".nav-toggle" ).toggleClass( 'closeToggle');
		});		
			
			
		jQuery(window).resize(function()
		{
			if(jQuery(window).width() < 769){	
				if(jQuery(".bgBackground").length){
					jQuery(".bgBackground").each(function(){
					var imagePath = jQuery(this).find("img").attr("src");		
					jQuery(this).css("background-image","url( " + imagePath  + " )" );
					});
				}
				
				if(jQuery(".bgBackground").length){
					jQuery(".bgBackground").each(function(){
					var imagePath = jQuery(this).find("img").attr("src");		
					jQuery(this).css("background-image","url( " + imagePath  + " )" );
					});
				}
			}
			if(jQuery('.desktop-slider .slides').length > 0)
			{
				jQuery('.sliderBanner iframe').height(jQuery('.slides .desktop-slider-img').height());
			}
			if(jQuery(window).width() < 1024){	
				//jQuery('.navigation').hide();
				var navHeight = jQuery('.navigation').height() + 60;
				var	winHeight = jQuery(window).height();
				if(navHeight > winHeight){
					navHeight = winHeight - 60;
					navHeight = winHeight;
					jQuery('.navigation').height(winHeight - 60);
				}
			}
			else{
				jQuery('.navigation').removeAttr("style");
				jQuery('.nav-toggle').removeClass("closeToggle");
				jQuery('.submenu').removeAttr("style");
				jQuery('#hideshow').show();
				jQuery('#block-search').hide();
				jQuery("#block-search").on("click", function (event) {
					event.stopPropagation();
				});
				jQuery(document).on("click", function () {
					jQuery("#block-search").hide('slow');
				});
			}				
		});			




	function sticky_relocate(t) {			
		// ssetTimeout(function(){
		//alert();
			if(jQuery(window).width() > 768){
				
			var window_top = jQuery(window).scrollTop();
			var footer_top = jQuery("footer").offset().top;
			//alert(footer_top);
			var related = 0;
			if(jQuery('.related').length > 0)
			{
				related = jQuery('.related').height();
			}
			var winHeight = jQuery(window).height();
			var contentDiv = jQuery(".product-info-main").height();
			// console.log(contentDiv);
			var headerHieght = jQuery("#header").height();
			//alert(window_top);
			//alert(headerHieght);
			//alert(winHeight);
			//alert(windowscroll);
			var contentDivMain = contentDiv + headerHieght;
			var contentMainScroll = contentDivMain - winHeight;	
			var footerHeight = jQuery(".footer").height();
			var mainContentHeight = jQuery("#maincontent").height();
			var realtedProduct = 0;
			if(jQuery(".block.related").length)
			{
				realtedProduct = jQuery(".block.related").height();
			}
			var calssremoveHeight = jQuery(window).scrollTop() + footerHeight + realtedProduct + headerHieght + 235; 	
			//alert(calssremoveHeight);
			//alert(mainContentHeight);
			
			if( window_top >= contentMainScroll  ){
			//	alert(1);
			if(contentDiv >= winHeight){
				//alert(1);
				jQuery('.product-info-main').addClass('stick');		
				 }else{
					// alert(2);
					if (!jQuery(window).scrollTop() < 53) 		
						// console.log('top',jQuery(window).scrollTop());		
						jQuery('.product-info-main').addClass('stick topStick');					

				}
				if(calssremoveHeight >= mainContentHeight){
				//alert(1);
					jQuery('.product-info-main').removeClass('stick').addClass("posbottomDiv");
					jQuery('.product-info-main').parents(".container").addClass("posrelative");
						if(jQuery(".block.related").length)
						{
							jQuery('.product-info-main').css("bottom",realtedProduct + "px");
							
						}
					}	else{
					jQuery('.product-info-main').removeClass('posbottomDiv');
					if(jQuery(".block.related").length)
					{
						jQuery('.product-info-main').css("bottom","0");
						
					}
				}
			}
			else{
				console.log('remove');
				jQuery('.product-info-main').removeClass('stick');
			}
				
				var div_top = jQuery('header').offset().top;
				var div_height = jQuery(".product-info-main").height();
				
				var padding = 41;  // tweak here or get from margins etc
			}
		// },100);
	}
	
	
	jQuery(window).scroll(function() {
        if (jQuery(window).width() > 1024) {
            if (jQuery(window).scrollTop() > 50) {
                if (jQuery('#maincontent').height() > jQuery('.product-info-main').height()) {
                    sticky_relocate();
                }
            } else {
                // jQuery('.product-info-main').removeClass('stick');
                jQuery('.product-info-main').removeClass('stick topStick');
            }
        }
      
       // setInterval(function(){
	      //  	if (jQuery(window).scrollTop() < 53 && jQuery('.product-info-main').hasClass('topStick')) {	       		
	      //  			jQuery('.product-info-main').removeClass('stick topStick');
	      //  	}
       // },700);

    });
	
	jQuery(document).ready(function(){
		// 30-04-19 Table
		/*jQuery(".toggleTable").hide();
		jQuery(".tableDataShow").click(function(){
			jQuery(".toggleTable").slideToggle();
		});*/
		jQuery("#sl_show_more").click(function(){
			var body = jQuery('body').loader();
			body.loader('show');
			jQuery('.stockists-results li').each(function( index, element ) 
			{
				if(index > jQuery("#sl_show").val())
				{
					jQuery( element ).hide();
				}
				else
				{
					jQuery( element ).show();
				}
					
			});
			if(jQuery('.stockists-results li').length >= jQuery("#sl_show").val())
			{
				jQuery("#sl_show_more").show();
			}
			else
			{
				jQuery("#sl_show_more").hide();
			}
			jQuery("#sl_show").val(parseInt(jQuery("#sl_show").val())+10);
			body.loader('hide');
		});
	});
	
	jQuery(document).on('click', 'a[href^="#retailers"]', function(e) {
		// target element id
		var id = jQuery(this).attr('href');
		
		// target element
		var $id = jQuery(id);
		if ($id.length === 0) {
			return;
		}
		
		// prevent standard hash navigation (avoid blinking in IE)
		e.preventDefault();
		
		// top position relative to the document
		var pos = $id.offset().top;
		
		// animated top scrolling
		jQuery('body, html').animate({scrollTop: pos}, 500);
	});
});


