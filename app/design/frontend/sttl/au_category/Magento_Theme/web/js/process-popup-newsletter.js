/*
* @Author: Ngo Quang Cuong
* @Date:   2017-10-29 22:28:37
* @Last Modified by:   https://www.facebook.com/giaphugroupcom
* @Last Modified time: 2017-10-30 00:08:01
*/
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/mage',
    'jquery/ui'
], function ($, modal) {
    'use strict';

    $.widget('phpcuong.processPopupNewsletter', {

        /**
         *
         * @private
         */
        _create: function () {
            var self = this;
            this.element.find('form').submit(function() {
                if ($(this).validation('isValid')) {
                    $.ajax({
                        url: $(this).attr('action'),
                        cache: true,
                        data: $(this).serialize(),
                        dataType: 'json',
                        type: 'POST',
                        showLoader: true
                    }).done(function (data) {
                        self.element.find('.messages .message div').html(data.message);
                        if (data.error) {
                            self.element.find('.messages .message').addClass('message-error error');
                            self.element.find('.messages').show();
                        } else {
                            $('#signupBtn-contchnage').html('<h3>Thank you</h3><p>Thank you for signing up!</br>Check your inbox soon for special offers, our latest innovation and products, and more!</p><div class="inputArea"><button class="signupBtn" title="Close" data-dismiss="modal" type="submit"> <span>Close</span> </button></div>');
                            /**self.element.find('.messages .message').addClass('message-success success');
                            setTimeout(function() {
                                self.element.modal('closeModal');
                            }, 1000);**/
                        }
                        setTimeout(function() {
                            self.element.find('.messages').hide();
                        }, 5000);
                    });
                }
                return false;
            });

            //this._resetStyleCss();
        },

        /**
         * Set width of the popup
         * @private
         */
        _setStyleCss: function(width) {

            width = width || 400;

            if (window.innerWidth > 786) {
                this.element.parent().parent('.modal-inner-wrap').css({'max-width': width+'px'});
            }
        },

        /**
         * Reset width of the popup
         * @private
         */
        _resetStyleCss: function() {
            var self = this;
            $( window ).resize(function() {
                if (window.innerWidth <= 786) {
                    self.element.parent().parent('.modal-inner-wrap').css({'max-width': 'initial'});
                } else {
                    self._setStyleCss(self.options.innerWidth);
                }
            });
        }
    });

    return $.phpcuong.processPopupNewsletter;
});
