/*
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'MW_EasyFaq/js/faq/grid'
    ], function($, ko, Component, Faq) {
        'use strict';
        return Component.extend({
            
            defaults: {
                template: 'Sttl_Customerorder/template/neworder-popup.html'
            },

            initialize: function (config) {
               
                this._super();

                return this;
            },

            firstItemSelected: function(){
                $('.faq-category-list .faq-category-item').first().click();
            },

            categorySelected: function (category, event) {
                if($(event.target).hasClass('selected')){
                    return false;
                }
                $('.faq-category-item').removeClass('selected');
                $(event.target).addClass('selected');
                var categoryId = category.category_id;

                if(!this.isAjaxPageType()){
                    let target = '#category-container-'+categoryId;
                    $('html,body').stop().animate({
                        scrollTop: $(target).offset().top - 15
                    }, 1000);
                    event.preventDefault();
                    return false;
                }

                $('.loading-mask').show();
                $.get(this.getFaqUrl()+'category_id/'+categoryId, function(res){
                    var res = JSON.parse(res);
                    Faq().setItems(res);
                    $('.loading-mask').hide();
                });
            }
        });


  });