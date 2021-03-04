/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterPopup
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'fireworks',
    'bioEp',
    'jquery/ui'
], function ($, firework) {
    'use strict';
     var readNojson = {};
    $.widget('import.notificationsection', {
        options: {

            /* notification read json*/
            readjson: {},

            /*ajax url*/
            updatedataurl : ''

        },

        _create: function () {

            var options = this.options,            
                $widget = this;
            readNojson = this.options.readjson.read_json;
            this.notificationsectionJS();
            $(document).on('click', '.notificationsection .openmodal', function(e){
                 $widget.clickSuccesspopup($(this).attr('data-id'));  
            });
            $("tr.openmodal").css("pointer-events","");
        },

        /**
         * Event click close popup button
         * @private
         */
        _clickClose: function () {
            $('#bio_ep_close').click(function () {
                $('#bio_ep').hide();
                $('#bio_ep_bg').hide();
                $('.btn-copy').text('Copy');
                $('canvas#screen').hide();
            })
        },

        /**
         * Event click success button
         * @private
         */
        clickSuccesspopup: function (no_id) {
            var self = this; 
            var data = this.options.readjson;
            var readnojosn = JSON.parse(readNojson);
            if(readnojosn[no_id] == 0){
                readnojosn[no_id] = 1;
                var updatedjson = readnojosn;
                 $.ajax({
                    url:this.options.updatedataurl,
                    type: "POST",
                    data:{
                        id:data.id,
                      Readjson:updatedjson,
                    },
                    showLoader: false,
                    cache: false,
                    success: function(response){
                        if(!response.errors){
                            readNojson = response.UpdateReadJosn;
                            $("[data-id='"+no_id+"']").removeClass("notread");
                            var readcount = 0;
                            $("tr.openmodal.notread").each(function(){
                                readcount++;
                            })
                            $(".impnotification.notify.show-count").attr("data-count",readcount);
                        }
                    }
                });
            }
        },
        notificationsectionJS: function(){
            $('.InstadSlider').css('width',"660px");
                $( window ).resize(function() {
                  $('.InstadSlider').css('width',"660px");
                  $('.InstadSlider').trigger('refresh.owl.carousel');
                });    
                $('.openmodal').on('click',function(){
                    var id = $(this).attr('data-id');
                    $('#notificationinfo').addClass('d-flex');
                    $('#data-target-'+id).css({'display':'block'}); 

                    $(".InstadSlider").owlCarousel({
                        loop:true
                        ,autoplay:true
                        ,autoplayTimeout:4500           
                        ,nav:true           
                        ,items:1
                    }); 
                    $('.InstadSlider').trigger('refresh.owl.carousel');
                     $('.InstadSlider').trigger('owl.next');
                     $(".owl-next").trigger("click");
                })
                $('#notificationinfo').on('hidden.bs.modal',function(){
                    $('#notificationinfo .modal-content').css({'display':'none'});
                    $('#notificationinfo').removeClass('d-flex');

                })
                $(document).on('keydown', function(event) {
                   if (event.key == "Escape") {
                        $(".notificationinfo .modal-content .close").trigger('click')
                   }
                });
            }
    });

    return $.import.notificationsection;
});