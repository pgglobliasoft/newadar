requirejs(["jquery", 'neworder', "jquery.bootstrap","magnificPopup"], function($, neworder) {

    var product_Active = null;
    var stocky = '';

    $('[data-toggle="tooltip"]').tooltip();
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    /*
     *
     */
    $(document).on('click', '.product-slider .product-item', function() {
        if (product_Active != $(this).attr('id')) {
            $('.owl-item').removeClass('sticky')
            $(".column.main .product-item").removeClass("pro_active")
        }
        $(this).parent().toggleClass('sticky');
        $(this).toggleClass("pro_active")
        if ($(this).hasClass('pro_active')) {
            $("#show_style").attr("value", $(this).attr('id'))
            product_Active = $(this).attr('id');
            var content = $('.owl-item.sticky').html();
            var newdiv = $("<div class='product-slider-sticky'>");
            stocky = newdiv.css({ 'width': jQuery('.owl-item.sticky').width(), 'right': 'unset', 'left': '0' }).html(content);
        } else {
            stocky = null;
            $(this).parent().removeClass('sticky');
            $(this).removeClass("pro_active");
            $('.product-slider-sticky').remove();
        }

    })



    $(document).on('click', '.product-slider .owl-next', function(e) {

        // var i = 0;		
        // if(!$('.product-slider .owl-item.active').hasClass('sticky')){ i = 1; }else{
        // 	i = 0;$('.product-slider-sticky').remove() }

        $('.product-slider .owl-prev').show();

        if (jQuery('.owl-item.active').nextAll('.owl-item:not(.active)').length < 1) {
            $('.product-slider .owl-next').hide();
        }

        //  if(jQuery('.product-slider .sticky').nextAll('.owl-item.active').length > 3) {		  
        //   i = 1;
        // }

        // if(i >= 1){
        // 	if($('.product-slider-sticky').length < 1){
        // 		var content = $('.owl-item.sticky').html();
        // 		var newdiv = $("<div class='product-slider-sticky'>");
        // 			newdiv.css( {'width':jQuery('.owl-item.sticky').width() ,'left':'0','right':'unset'}).html(content);
        // 		$('.product-slider .owl-stage').after(newdiv);
        // 	}

        // }else{
        // 	$('.product-slider-sticky').remove();
        // }
    })


    $(document).on('click', '.product-slider .owl-prev', function(e) {
        var i = 0;
        $('.product-slider').attr('data-event', 'prev');

        $('.product-slider .owl-next').show();

        if (jQuery('.owl-item.active').prevAll('.owl-item:not(.active)').length < 1) {
            $('.product-slider .owl-prev').hide();
        }



        if (!$('.product-slider .owl-item.active').hasClass('sticky')) { i = 1; } else {
            i = 0;
            $('.product-slider-sticky').remove()
        }
        if (jQuery('.product-slider .sticky').prevAll('.owl-item.active').length >= 3 && jQuery('.product-slider .sticky.active').length === 0) {
            i = 1;
        }
        if (jQuery('.product-slider .sticky.active').next('.owl-item:not(.active)').length == 1) { i = 1; }

        if (i >= 1) {
            if ($('.product-slider-sticky').length < 1) {
                var content = $('.owl-item.sticky').html();
                var newdiv = $("<div class='product-slider-sticky'>");
                newdiv.css({ 'width': jQuery('.owl-item.sticky').width(), 'right': 'unset', 'left': '0' }).html(content);
                $('.product-slider .owl-stage').after(newdiv);
            }

        } else {
            $('.product-slider-sticky').remove();
        }
    })


    $(window).resize(function() {
        // console.log($(".product-slider .owl-item").width())
        $(".product-image-sticky .product-item").css("width", $(".product-slider .owl-item").width())
    });
    /*
     *
     */


    $(document).on('click', '.product-slider-sticky', function(e) {
        $(this).remove();
        $(".column.main .product-item").removeClass("pro_active");
        $('.product-slider .owl-stage .owl-item').removeClass('sticky');
        product_Active = '';
        $('#show_style').val('');
        $('.product-slider-sticky').remove();

    })

    $(document).on('click', '.group-col', function(e) {
        /*.product-slider.owl-carousel.owl-theme.owl-loaded*/
        setTimeout(function(e) {

            var active = (jQuery('.product-slider-sticky').find('.product-item').attr('id')) || product_Active;
            if ($('#show_style').val()) {
                active = $('#show_style').val();
            }

            jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').addClass("pro_active").parent().addClass('sticky');
            if (jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').length > 0) {
                $('.product-slider-sticky').css({ 'right': 'unset', 'left': '0' });
                if ($('.product-slider-sticky').length < 1) {
                    var content = $('.owl-item.sticky').html();
                    var newdiv = $("<div class='product-slider-sticky'>");
                    newdiv.css({ 'width': jQuery('.owl-item.sticky').width(), 'right': 'unset', 'left': '0' }).html(content);
                    $('.product-slider .owl-stage').after(newdiv);
                }

            } else {
                if (jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').length < 1 && stocky != null && $(".pro-slider").attr("sticky") != "false") {
                    $('.product-slider .owl-stage').after(stocky);
                    var owl = $('.product-slider').data('owlCarousel');
                    if (jQuery('.product-slider .owl-stage .owl-item').length < owl.settings.items && stocky != "") {
                        jQuery('.product-slider .owl-stage').prepend('<div class="owl-item active" style="width:' + jQuery('.pro-slider .owl-item.active').width() + 'px" >' + jQuery('.product-slider-sticky').html() + '<div>');
                        $('.product-slider-sticky').remove();
                    }
                }
            }


            if ($('.owl-item.sticky.active').length > 0)
                $('.product-slider-sticky').remove();
        }, 500)
    })

    /*
     *
     */
    $(document).on('click', '.option3_error_message div.error > b', function() {
        $(this).attr('data-toggle', "tooltip");
        $(this).trigger('mouseenter');
        neworder.copyToClipboard($(this).parent().text());

    });


    /*
     *
     */
    $(document).on('mouseout', '.option3_error_message div.error > b', function() {
        $(this).attr('data-toggle', "");
    });


    /*
     *
     */
    $(document).on('click', '.option3_error_message div.error>span', function() {
        $(this).parent().html('')
    });


    $(document).on('click', '.close.mfp-close-inside', function(e) {
        $('body').removeClass('quickView-wapper noscroll');
    });


    $(document).on("click", 'a.open-popup-link', function(e) {
        var that = this,
            tpl = $('.quickViewbodyloader').html(),
            el = $('#quick-popup');
        if (el.length) {
            $.magnificPopup.open({
                items: {
                    src: el
                },
                type: 'inline',
                midClick: true,
                mainClass: "mfp-fade",
                // removalDelay: 500,
                preloader: false,
                closeOnBgClick: false,
                fixedContentPos: false,
                tLoading: '',
                callbacks: {
                    beforeOpen: function() {
                        this.st.mainClass = 'mfp-move-from-top quickViewContainer';
                        $('body').addClass('quickView-wapper');
                    },
                    open: function() {
                        jQuery('body').addClass('noscroll');
                        $('html').css('overflow', 'hidden');
                        t = $(that);
                        var URL = t.data('quickviewUrl').replace('/view/', '/ProView/')
                        $('#quick-popup').html('ajaxStart');
                        $.ajax({
                            url: t.data('quickviewUrl'),
                            type: 'GET',
                            async: true,
                            dataType: 'html',
                            cache: true,
                            beforeSend: function() {
                                $('.mfp-content').html('').append(tpl);
                                $('.loading-mask.lezzy-popup').css('display', 'block');
                            },
                            success: function(data) {
                                var data = data.replace('<body', '<body><div id="quickViewbody"').replace('</body>', '</div></body>');
                                var body = $(data).filter('#quickViewbody');
                                $('.mfp-content').css('height', '640px');
                                body.find('#product-gotoproduct-button').attr('target', '_blank');
                                body.find('.quickViewCont .buyBtns.buytBtnLogin').remove();
                                $('.mfp-content').html(body).trigger('contentUpdated');
                                $('.loading-mask.lezzy-popup').css('display', 'none');
                                $('.mfp-content .quickViewContent').addClass('contentload');
                            }
                        });
                    },
                    close: function() {
                        $('html').css('overflow', '');
                    }
                }


            });
        }
    });


    $(document).on('change', '#seleteMultiRecord', function() {
        var that = this;
        jQuery('.deleteMultiRecord').each(function() {
            if (that.checked)
                jQuery(this).attr('checked', true);
            else
                jQuery(this).attr('checked', false);
        })
    });


    $(document).on("click", "a.btn-gallery", function(e) {
        // console.log("Aaaaaa");
        $.magnificPopup.open({
            items: {
                src: $(this).find("img").attr("src"),
            },
            type: "image",
            closeOnBgClick: true,
            mainClass: 'mfp-with-zoom mfp-zoom-in mfp-img-mobile',
            preloader: true,
            tLoading: "",
            callbacks: {
                beforeOpen: function() {
                    // just a hack that adds mfp-anim class to markup
                    this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
                    this.st.mainClass = 'mfp-zoom-in';
                },
                open: function() {
                    $('.mfp-content figure').prepend('<div class="container order-img-header bg-primary p-2" ><div class="row"><div class="col-md-4 offset-md-4 text-center">	<img src="' + require.toUrl('images/mobile-logo-white.png') + '"></div><div class="col-md-4"><button type="button" class="close mfp-close"></button></div></div></div>');
                    $('.mfp-content .mfp-figure .mfp-close').first().remove();
                },
            },
        });
    });




})