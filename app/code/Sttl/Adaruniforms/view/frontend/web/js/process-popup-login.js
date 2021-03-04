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

    $.widget('phpcuong.processPopuplogin', {

        /**
         *
         * @private
         */
        _create: function () {
            var self = this;
            var redirect_url = $('.redirect_url').val();
            this.element.find('form').submit(function() {
                if ($(this).validation('isValid')) {
                    $.ajax({
                        url: $(this).attr('action'),
                        cache: true,
                        data: $(this).serialize(),
                        dataType: 'json',
                        type: 'POST',
                        showLoader: true
                    }).done(function (response) {
                            $('body').loader().loader('hide');
                            self._showResponse(response, redirect_url);
                    }).fail(function (response) {
                            self._showResponse(response, redirect_url);
                            $('body').loader().loader('hide');
                    });
                }
                return false;
            });

            //this._resetStyleCss();
        },
        _displayMessages: function(className, message) {
            alert('_displayMessages');
            $('<div class="message '+className+'"><div>'+message+'</div></div>').appendTo(this.element.find('.messages'));
        },
        _showResponse: function(response, locationHref) {
            
            window.location.href = locationHref;
            var self = this,
                timeout = 800;
            setTimeout(function() {
                if (!response.errors) {
                    self.element.modal('closeModal');
                    window.location.href = locationHref;
                }
            }, timeout);
        },
         _showFailingMessage: function() {
            alert('_showFailingMessage');
            this.element.find('.messages').html('');
            this._displayMessages('message-error error', $('An error occurred, please try again later.'));
            this.element.find('.messages .message').show();
        }

    });

    return $.phpcuong.processPopuplogin;
});
