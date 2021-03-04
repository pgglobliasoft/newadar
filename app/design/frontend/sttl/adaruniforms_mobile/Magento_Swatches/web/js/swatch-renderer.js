    /**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/smart-keyboard-handler',
    'mage/translate',
    'priceUtils',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'mage/validation/validation'
], function ($, _, mageTemplate, keyboardHandler, $t, priceUtils) {
    'use strict';

    /**
     * Extend form validation to support swatch accessibility
     */
    $.widget('mage.validation', $.mage.validation, {
        /**
         * Handle form with swatches validation. Focus on first invalid swatch block.
         *
         * @param {jQuery.Event} event
         * @param {Object} validation
         */
        listenFormValidateHandler: function (event, validation) {
            var swatchWrapper, firstActive, swatches, swatch, successList, errorList, firstSwatch;

            this._superApply(arguments);

            swatchWrapper = '.swatch-attribute-options';
            swatches = $(event.target).find(swatchWrapper);

            if (!swatches.length) {
                return;
            }

            swatch = '.swatch-attribute';
            firstActive = $(validation.errorList[0].element || []);
            successList = validation.successList;
            errorList = validation.errorList;
            firstSwatch = $(firstActive).parent(swatch).find(swatchWrapper);

            //keyboardHandler.focus(swatches);

            $.each(successList, function (index, item) {
                $(item).parent(swatch).find(swatchWrapper).attr('aria-invalid', false);
            });

            $.each(errorList, function (index, item) {
                $(item.element).parent(swatch).find(swatchWrapper).attr('aria-invalid', true);
            });

            if (firstSwatch.length) {
                //$(firstSwatch).focus();
            }
        }
    });

    /**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - option-type (integer, 0-3)
     *  - option-label (string)
     *  - option-tooltip-thumb
     *  - option-tooltip-value
     */
    $.widget('mage.SwatchRendererTooltip', {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.attr('option-type'), 10),
                label = $this.attr('option-label'),
                thumb = $this.attr('option-tooltip-thumb'),
                value = $this.attr('option-tooltip-value'),
                $image,
                $title,
                $corner;

            if (!$element.size()) {
                $element = $('');
                $('body').append($element);
            }

            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            $this.hover(function ()  {
                if (!$this.hasClass('disabled')) {
                    if(type == 2)
                    {
                        $(this).parents('.swatch-attribute-options').siblings('.swatch-attribute-selected-option').text(label);
                    }
                    timer = setTimeout(
                        function () {
                            var leftOpt = null,
                                leftCorner = 0,
                                left,
                                $window;

                            if (type === 2) {
                                // Image
                                /*$image.css({
                                    'background': 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial'
                                });*/
                                $image.hide();
                            } else if (type === 1) {
                                // Color
                                $image.css({
                                    background: value
                                });
                                $image.show();
                            } else if (type === 0 || type === 3) {
                                // Default
                                $image.hide();
                            }

                            $title.text(label);

                            leftOpt = $this.offset().left;
                            left = leftOpt + $this.width() / 2 - $element.width() / 2;
                            $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            leftCorner = 0;

                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                            if (type === 0 || type === 3) {
                                // Default
                                $element.hide();
                            }
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                var tmp_val = '';
                if($(this).siblings('.swatch-option.selected').length > 0)
                {
                    tmp_val = $(this).siblings('.swatch-option.selected').attr('option-label');
                }
                if($(this).hasClass('selected'))
                {
                    tmp_val = $(this).attr('option-label');
                }
                $(this).parents('.swatch-attribute-options').siblings('.swatch-attribute-selected-option').text(tmp_val);
                clearTimeout(timer);
            });

            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });

    /**
     * Render swatch controls with options and use tooltips.
     * Required two json:
     *  - jsonConfig (magento's option config)
     *  - jsonSwatchConfig (swatch's option config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - onlySwatches (hide selectboxes)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */
     var temp_config_content = [];
    $.widget('mage.SwatchRenderer', {
        options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},

            // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProduct: '.product-info-main',

            // selector of price wrapper (need to know where set price)
            selectorProductPrice: '[data-role=priceBox]',

            //selector of product images gallery wrapper
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',

            // selector of category product tile wrapper
            selectorProductTile: '.product-item',

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // control label id
            controlLabelId: '',

            // text for more button
            moreButtonText: 'More',

            // Callback url for media
            mediaCallback: '',

            // Local media cache
            mediaCache: {},

            // Cache for BaseProduct images. Needed when option unset
            mediaGalleryInitial: [{}],

            // Use ajax to get image data
            useAjax: false,

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',

            // whether swatches are rendered in product list or on product page
            inProductList: false,

            // sly-old-price block selector
            slyOldPriceSelector: '.sly-old-price',

            // tier prise selectors start
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            // tier prise selectors end

            // A price label selector
            normalPriceLabelSelector: '.normal-price .price-label'
        },

        /**
         * Get chosen product
         *
         * @returns int|null
         */
        getProduct: function () {
            var products = this._CalcProducts();

            return _.isArray(products) ? products[0] : null;
        },

        /**
         * @private
         */
        _init: function () {
            if (_.isEmpty(this.options.jsonConfig.images)) {
                this.options.useAjax = true;
                // creates debounced variant of _LoadProductMedia()
                // to use it in events handlers instead of _LoadProductMedia()
                this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
            }

            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                // store unsorted attributes
                this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                this._sortAttributes();
                this._RenderControls();
                this._setPreSelectedGallery();
                $(this.element).trigger('swatch.initialized');
            } else {
                console.log('SwatchRenderer: No input data received');
            }
            this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            // var swatch_section = [];
            var section_selector = $('.swatch-attribute');
            section_selector.each(function(){
                if(!$(this).find(".swatch-option.image").is(":visible")){
                  $(this).hide();
                }
            });

            var swatchLength = $('.swatch-attribute').length;
  			if(swatchLength >= 1){
  			    if($('.swatch-attribute').hasClass("color")){
  			        $('.swatch-option.image').first().trigger('click');
  			    }
  			}
            // if(swatch_section.length > 0){
            //     // var selector = swatch_section[0];
            //     var selector = swatch_section[0].replace(/\s+/g, '.');
            //     // console.log(selector);
            //     $("."+selector).find(".swatch-option").first().trigger('click');
            // }

          if($(".swatch-attribute .swatch-option").hasClass("selected")){
            $(".mobile_action_button #productviewloding").hide();
            $(".buyNowBtnMain").css({"pointer-events": "all", "opacity": "1"});
            var selected_element = $(".swatch-attribute .swatch-option.selected").attr("option-label");
            var actual_active_swatch = jQuery(".swatch-option.image.selected").attr("option-label");
              var actual_active_color = jQuery(".swatch-option.image.selected").attr("aria-describedby");
              var color_type = "";
              if(actual_active_color = "option-label-color-93"){
                var color_type = "Solid";
              }else if(actual_active_color = "option-label-color-152"){
                var color_type = "Seasonal";
              }else{
                var color_type = "Heather";
              }
            $(".selected_color_size").html(color_type);
            jQuery('.catalog-product-view .search_stock_price_data .tab-pane.fade').removeClass("active");
            var size_table_id = selected_element.replace(/\s+/g, '');
            jQuery('#product_color_'+size_table_id).addClass("active");
          }

        },

        /**
         * @private
         */
        _sortAttributes: function () {
            this.options.jsonConfig.attributes = _.sortBy(this.options.jsonConfig.attributes, function (attribute) {
                return parseInt(attribute.position, 10);
            });
        },

        /**
         * @private
         */
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                productData = this._determineProductData(),
                $main = productData.isInProductView ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (productData.isInProductView) {
                 if(!gallery.data('gallery')){
                    this._onGalleryLoad($(".product-view-custom-media- img"));
                }
                gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }

            this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
            this.inProductList = this.productForm.length > 0;
        },

        /**
         * Determine product id and related data
         *
         * @returns {{productId: *, isInProductView: bool}}
         * @private
         */
        _determineProductData: function () {
            // Check if product is in a list of products.
            var productId,
                isInProductView = false;

            productId = this.element.parents('.product-item-details')
                    .find('.price-box.price-final_price').attr('data-product-id');

            if (!productId) {
                // Check individual product.
                productId = $('[name=product]').val();
                isInProductView = productId > 0;
            }

            return {
                productId: productId,
                isInProductView: isInProductView
            };
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText;

            $widget.optionsMap = {};

            var sw_color = '';
            var sw_size = '';
            var sw_s_color = '';
            var sw_h_color = '';

            // console.log(this.options.jsonConfig.attributes);
            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    controlLabelId = 'option-label-' + item.code + '-' + item.id,
                    options = $widget._RenderSwatchOptions(item, controlLabelId),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    listLabel = '',
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {

                    return;
                }

                if ($widget.options.enableControlLabel) {
                    var sizechart = '';
                    if(item.code == 'size')
                    {
                        sizechart = '<div class="box"><a class="show-size-chart" data-toggle="modal" data-target="#sizechartPopupModal">(View Size Chart)</a></div>';
                    }
                    label +=
                        '<span id="' + controlLabelId + '" class="' + classes.attributeLabelClass + '">' +
                            item.label +
                        '</span>'+
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>'+sizechart;
                }

                if ($widget.inProductList) {
                    $widget.productForm.append(input);
                    input = '';
                    listLabel = 'aria-label="' + item.label + '"';
                } else {
                    listLabel = 'aria-labelledby="' + controlLabelId + '"';
                }

                // console.log(item)

                if(item.code == 'color')
                {
                    sw_color = '<div class="' + classes.attributeClass + ' ' + item.code + '" ' +
                         'attribute-code="' + item.code + '" ' +
                         'attribute-id="' + item.id + '">' +
                        label +
                        '<div aria-activedescendant="" ' +
                             'tabindex="0" ' +
                             'aria-invalid="false" ' +
                             'aria-required="true" ' +
                             'role="listbox" ' + listLabel +
                             'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                            options + select +
                        '</div>' + input +
                    '</div>';
                }
                else if(item.code == 'size')
                {
                    sw_size = '<div class="' + classes.attributeClass + ' ' + item.code + '" ' +
                         'attribute-code="' + item.code + '" ' +
                         'attribute-id="' + item.id + '">' +
                        label +
                        '<div aria-activedescendant="" ' +
                             'tabindex="0" ' +
                             'aria-invalid="false" ' +
                             'aria-required="true" ' +
                             'role="listbox" ' + listLabel +
                             'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                            options + select +
                        '</div>' + input +
                    '</div>';
                }
                else if(item.code == 'seasonalcolors')
                {
                    sw_s_color = '<div class="' + classes.attributeClass + ' ' + item.code + '" ' +
                         'attribute-code="' + item.code + '" ' +
                         'attribute-id="' + item.id + '">' +
                        label +
                        '<div aria-activedescendant="" ' +
                             'tabindex="0" ' +
                             'aria-invalid="false" ' +
                             'aria-required="true" ' +
                             'role="listbox" ' + listLabel +
                             'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                            options + select +
                        '</div>' + input +
                    '</div>';
                }else if(item.code == 'heather_colors')
                {
                    // console.log("aaaaaaaaaaaaa");
                    sw_h_color = '<div class="' + classes.attributeClass + ' ' + item.code + '" ' +
                         'attribute-code="' + item.code + '" ' +
                         'attribute-id="' + item.id + '">' +
                        label +
                        '<div aria-activedescendant="" ' +
                             'tabindex="0" ' +
                             'aria-invalid="false" ' +
                             'aria-required="true" ' +
                             'role="listbox" ' + listLabel +
                             'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                            options + select +
                        '</div>' + input +
                    '</div>';
                    // console.log(sw_h_color);
                }

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });
            // console.log(sw_color);
            //sw_color + sw_s_color + sw_size
            if(sw_color != '')
            {
                container.append(sw_color);
            }

            if(sw_s_color != '')
            {
                container.append(sw_s_color);
            }

            if(sw_h_color != '')
            {
                container.append(sw_h_color);
            }
            if(sw_size != '')
            {
              // console.log(sw_size);
                // container.append(sw_size);
            }

            container.find('.swatch-attribute.color ').each(function()
            {
                var seasonalcolors_label = $(this).attr('aria-label');
                if(seasonalcolors_label == 'No Color')
                {
                    $(this).hide();
                }
                if (typeof seasonalcolors_label !== typeof undefined && seasonalcolors_label !== false)
                {
                    seasonalcolors_array.push(seasonalcolors_label.trim());
                }
            });

            var seasonalcolors_array = [];
            container.find('[aria-describedby="option-label-seasonalcolors-152"]').each(function()
            {
                var seasonalcolors_label = $(this).attr('aria-label');
                if(seasonalcolors_label == 'No Color')
                {
                    $(this).hide();
                }
                if (typeof seasonalcolors_label !== typeof undefined && seasonalcolors_label !== false)
                {
                    seasonalcolors_array.push(seasonalcolors_label.trim());
                }
            });
            var heather_array = [];
            container.find('[aria-describedby="option-label-heather_colors-171"]').each(function()
            {
                var heather_label = $(this).attr('aria-label');
                if(heather_label == 'No Color')
                {
                    $(this).remove();
                }
                if (typeof heather_label !== typeof undefined && heather_label !== false)
                {
                    heather_array.push(heather_label.trim());
                }

            });
            if(heather_array.length <= 1){
              if(heather_array[0] == "No Color"){
                $(".swatch-attribute.heather_colors").remove();
              }
            }
            container.find('[aria-describedby="option-label-color-93"]').each(function()
            {
            	// console.log('test 124',$(this));
                var colors_label = $(this).attr('aria-label').trim();
                if (typeof colors_label !== typeof undefined && colors_label !== false)
                {
                    if((jQuery.inArray( colors_label, seasonalcolors_array ) !== -1) || (jQuery.inArray( colors_label, heather_array ) !== -1) )
                    {
                        // $(this).hide();
                        // console.log($(this));
                        $(this).remove();
                    }
                }
            });
            container.find('[aria-describedby="option-label-seasonalcolors-152"]').each(function()
            {
                var s_colors_label = $(this).attr('aria-label').trim();
                // console.log(s_colors_label);
                if (typeof s_colors_label !== typeof undefined && s_colors_label !== false)
                {
                    if((jQuery.inArray( s_colors_label, heather_array ) !== -1))
                    {
                        // $(this).hide();
                        // console.log("aaaaaaaaaaaaaa");
                        // console.log($(this));
                        $(this).remove();
                        // console.log($(this).hide());
                    }

                }
            });
            var sc_vis_cnt = 0;
            container.find('[aria-describedby="option-label-seasonalcolors-152"]').each(function()
            {
                if($(this).is(":visible"))
                {
                    sc_vis_cnt = 1;
                }
            });

            if(sc_vis_cnt == 0)
            {
                $('.swatch-attribute.seasonalcolors').hide();
            }
            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .SwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
        },

        /**
         * Render swatch options by part of config
         *
         * @param {Object} config
         * @param {String} controlId
         * @returns {String}
         * @private
         */
        _RenderSwatchOptions: function (config, controlId) {
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function () {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    attr;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '">' + moreText + '</a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                label = this.label ? this.label : '';
                attr =
                    ' id="' + controlId + '-item-' + id + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + controlId + '"' +
                    ' tabindex="0"' +
                    ' option-type="' + type + '"' +
                    ' option-id="' + id + '"' +
                    ' option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' option-tooltip-thumb="' + thumb + '"' +
                    ' option-tooltip-value="' + value + '"' +
                    ' role="option"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        ' style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        ' style="background: url(' + thumb + ') no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Default
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        /**
         * Render select by part of config
         *
         * @param {Object} config
         * @param {String} chooseText
         * @returns {String}
         * @private
         */
        _RenderSwatchSelect: function (config, chooseText) {
            var html;

            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + config.code + '">' +
                '<option value="0" option-id="0">' + chooseText + '</option>';

            $.each(config.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        /**
         * Input for submit form.
         * This control shouldn't have "type=hidden", "display: none" for validation work :(
         *
         * @param {Object} config
         * @private
         */
        _RenderFormInput: function (config) {
            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select" ' +
                'name="super_attribute[' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + config.id + ']" ' +
                'data-validate="{required: true}" ' +
                'aria-required="true" ' +
                'aria-invalid="false">';
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var $widget = this,
                options = this.options.classes,
                target;

            $widget.element.on('click', '.' + options.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $widget.element.on('emulateClick', '.' + options.optionClass, function () {
                return $widget._OnClick($(this), $widget, 'emulateClick');
            });

            $widget.element.on('change', '.' + options.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + options.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });

            $widget.element.on('keydown', function (e) {
                if (e.which === 13) {
                    target = $(e.target);

                    if (target.is('.' + options.optionClass)) {
                        return $widget._OnClick(target, $widget);
                    } else if (target.is('.' + options.selectClass)) {
                        return $widget._OnChange(target, $widget);
                    } else if (target.is('.' + options.moreButton)) {
                        e.preventDefault();

                        return $widget._OnMoreClick(target);
                    }
                }
            });
        },

        /**
         * Load media gallery using ajax or json config.
         *
         * @param {String|undefined} eventName
         * @private
         */
        _loadMedia: function (eventName) {
            var $main = this.inProductList ?
                    this.element.parents('.product-item-info') :
                    this.element.parents('.column.main'),
                images;

            if (this.options.useAjax) {
                this._debouncedLoadProductMedia();
            }  else {
                images = this.options.jsonConfig.images[this.getProduct()];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                this.updateBaseImage(images, $main, !this.inProductList, eventName);
            }
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @param {String|undefined} eventName
         * @private
         */
        _OnClick: function ($this, $widget, eventName) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                if(this._getAttributeCodeById(attributeId) == 'color'){
                    $('.swatch-attribute.seasonalcolors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.seasonalcolors .swatch-attribute-selected-option').text('');
                    $('.swatch-attribute.heather_colors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.heather_colors .swatch-attribute-selected-option').text('');
                }
                if(this._getAttributeCodeById(attributeId) == 'seasonalcolors'){
                    $('.swatch-attribute.color .swatch-attribute-selected-option').text('');
                    $('.swatch-attribute.color').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.heather_colors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.heather_colors .swatch-attribute-selected-option').text('');
                }
                if(this._getAttributeCodeById(attributeId) == 'heather_colors'){
                    $('.swatch-attribute.color .swatch-attribute-selected-option').text('');
                    $('.swatch-attribute.color').removeAttr('option-selected').find('.selected').removeClass('selected');
                     $('.swatch-attribute.seasonalcolors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.seasonalcolors .swatch-attribute-selected-option').text('');
                }

                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                if(this._getAttributeCodeById(attributeId) == 'color'){
                    $('.swatch-attribute.seasonalcolors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.seasonalcolors .swatch-attribute-selected-option').text('');
                    $('.swatch-attribute.heather_colors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.heather_colors .swatch-attribute-selected-option').text('');
                }
                if(this._getAttributeCodeById(attributeId) == 'seasonalcolors'){
                    $('.swatch-attribute.color .swatch-attribute-selected-option').text('');
                    $('.swatch-attribute.color').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.heather_colors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.heather_colors .swatch-attribute-selected-option').text('');
                }
                if(this._getAttributeCodeById(attributeId) == 'heather_colors'){
                    $('.swatch-attribute.color .swatch-attribute-selected-option').text('');
                    $('.swatch-attribute.color').removeAttr('option-selected').find('.selected').removeClass('selected');
                     $('.swatch-attribute.seasonalcolors').removeAttr('option-selected').find('.selected').removeClass('selected');
                    $('.swatch-attribute.seasonalcolors .swatch-attribute-selected-option').text('');
                }
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            $widget._Rebuild();

            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }

            // $(".popup_magnify_icon").hide();
            $widget._loadMedia(eventName);
            $input.trigger('change');
        },

        /**
         * Get human readable attribute code (eg. size, color) by it ID from configuration
         *
         * @param {Number} attributeId
         * @returns {*}
         * @private
         */
        _getAttributeCodeById: function (attributeId) {
            var attribute = this.options.jsonConfig.mappedAttributes[attributeId];

            return attribute ? attribute.code : attributeId;
        },

        /**
         * Toggle accessibility attributes
         *
         * @param {Object} $this
         * @param {Object} $wrapper
         * @private
         */
        _toggleCheckedAttributes: function ($this, $wrapper) {
            $wrapper.attr('aria-activedescendant', $this.attr('id'))
                    .find('.' + this.options.classes.optionClass).attr('aria-checked', false);
            $this.attr('aria-checked', true);
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.productForm.length > 0) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();
            $widget._loadMedia();
            $input.trigger('change');
        },

        /**
         * Event for more switcher
         *
         * @param {Object} $this
         * @private
         */
        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.blur().remove();
        },

        /**
         * Rewind options for controls
         *
         * @private
         */
        _Rewind: function (controls) {
            controls.find('div[option-id], option[option-id]').removeClass('disabled').removeAttr('disabled');
            controls.find('div[option-empty], option[option-empty]').attr('disabled', true).addClass('disabled');
        },

        /**
         * Rebuild container
         *
         * @private
         */
        _Rebuild: function () {
            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.size() <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.size() === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        /* var customerlogin = $("#customerlogin").val();
                        if(customerlogin == 1)
                        {
                            $element.attr('disabled', true).addClass('disabled');
                        } */
                    }
                });
            });


            if ($('body.catalog-product-view').size() >= 0) {
                var getUrl = window.location;
                var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";

                if(temp_config_content.length <= 0){
                    temp_config_content.push({description:$widget.element.parents('.product-info-main').find('.product.attribute.description .value').html(),
                        feature:$widget.element.parents('.product-info-main').find('.productQuality.product.feature').html(),
                        details:$widget.element.parents('.product-info-main').find('.product.attribute.bulletsdetails .value').html(),
                        fabric:$widget.element.parents('.product-info-main').find('.fabriccontent-subdiv .value').html(),
                        fabricimg:$widget.element.parents('.column.main').find('.fabriccarepopupContainer .modal-content img.fabric_pop_img').attr("src")
                    });
                }

                var product_Feature = '',product_Description = '', product_Details = '', product_Fabriccontent= '', product_Fabricimageurl= '';

                product_Feature = $widget.options.jsonConfig.profeature[this.getProduct()];
                product_Description = $widget.options.jsonConfig.prodescription[this.getProduct()];
                product_Details = $widget.options.jsonConfig.prodetails[this.getProduct()];
                product_Fabriccontent = $widget.options.jsonConfig.profabriccontent[this.getProduct()];
                product_Fabricimageurl = $widget.options.jsonConfig.profabricimageurl[this.getProduct()];

                var renderhtml = '';
                for ( var key in product_Feature){
                    if(product_Feature[key].small_image != null){
                        renderhtml += "<li class='value'><img src='"+baseUrl+"pub/media/"+product_Feature[key].small_image+"'> "+product_Feature[key].name+"</li>"
                    }else{
                        renderhtml += "<li class='value'>"+product_Feature[key].name+"</li>"
                    }
                }
                if(renderhtml == ''){
                    renderhtml = "";
                    $widget.element.parents('.product-info-main').find('.product.attribute.features').css({'display':'none'});

                }

                renderhtml = "<div class='fabric-care-button'><a class='fabric-care-chart' data-toggle='modal' data-target='#fabriccarePopupModal'>(Fabric features &amp; care)</a></div>"+renderhtml;

                $widget.element.parents('.product-info-main').find('.productQuality.product.feature').html(renderhtml);
                if(typeof product_Description != "undefined"){
                    $widget.element.parents('.product-info-main').find('.product.attribute.description .value').html(product_Description);
                }else{
                    $widget.element.parents('.product-info-main').find('.product.attribute.description .value').html("");
                    $widget.element.parents('.product-info-main').find('.product.attribute.description').css({'display':'none'});

                }
                // console.log(product_Details);
                if(typeof product_Details != 'undefined'){
                    $widget.element.parents('.product-info-main').find('.product.attribute.bulletsdetails .value').html("<ul>"+product_Details+"</ul>");
                }else{
                    $widget.element.parents('.product-info-main').find('.product.attribute.bulletsdetails .value').html("");
                    $widget.element.parents('.product-info-main').find('.product.attribute.bulletsdetails').css({'display':'none'});
                }
                if(typeof product_Fabriccontent != 'undefined'){
                    $widget.element.parents('.product-info-main').find('.fabriccontent-subdiv .value').html(product_Fabriccontent);
                }else{
                    $widget.element.parents('.product-info-main').find('.fabriccontent-subdiv .value').html("");
                    $widget.element.parents('.product-info-main').find('.fabriccontent-subdiv').css({'display':'none'});
                }
                if(typeof product_Fabricimageurl != 'undefined' && product_Fabricimageurl != null){
                    $widget.element.parents('.column.main').find('.fabriccarepopupContainer .modal-content img.fabric_pop_img').attr("src",product_Fabricimageurl);
                }else{
                    $widget.element.parents('.column.main').find('.fabriccarepopupContainer .modal-content img.fabric_pop_img').attr("src",baseUrl+"pub/media/fabricurl/placeholder/fabric_placeholder_text.png");
                }
            }
        },

        /**
         * Get selected product list
         *
         * @returns {Array}
         * @private
         */
        _CalcProducts: function ($skipAttributeId) {
            var $widget = this,
                products = [];

            // Generate intersection of products
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var id = $(this).attr('attribute-id'),
                    option = $(this).attr('option-selected');

                if ($skipAttributeId !== undefined && $skipAttributeId === id) {
                    return;
                }

                if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                    return;
                }

                if (products.length === 0) {
                    products = $widget.optionsMap[id][option].products;
                } else {
                    products = _.intersection(products, $widget.optionsMap[id][option].products);
                }
            });

            return products;
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result,
                tierPriceHtml;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });

            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );

            if (typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }

            if (typeof result != 'undefined' && result.tierPrices.length) {
                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(
                        this.options.tierPriceTemplate,
                        {
                            'tierPrices': result.tierPrices,
                            '$t': $t,
                            'currencyFormat': this.options.jsonConfig.currencyFormat,
                            'priceUtils': priceUtils
                        }
                    );
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }

            $(this.options.normalPriceLabelSelector).hide();

            _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                    if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                        _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                            if ($(dropdown).val() === '0') {
                                $(this.options.normalPriceLabelSelector).show();
                            }
                        }.bind(this));
                    } else {
                        $(this.options.normalPriceLabelSelector).show();
                    }
                }
            }.bind(this));
        },

        /**
         * Get prices
         *
         * @param {Object} newPrices
         * @param {Object} displayPrices
         * @returns {*}
         * @private
         */
        _getPrices: function (newPrices, displayPrices) {
            var $widget = this;

            if (_.isEmpty(newPrices)) {
                newPrices = $widget.options.jsonConfig.prices;
            }

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Gets all product media and change current to the needed one
         *
         * @private
         */
        _LoadProductMedia: function () {
            var $widget = this,
                $this = $widget.element,
                productData = this._determineProductData(),
                mediaCallData,
                mediaCacheKey,

                /**
                 * Processes product media data
                 *
                 * @param {Object} data
                 * @returns void
                 */
                mediaSuccessCallback = function (data) {
                    if (!(mediaCacheKey in $widget.options.mediaCache)) {
                        $widget.options.mediaCache[mediaCacheKey] = data;
                    }
                    $widget._ProductMediaCallback($this, data, productData.isInProductView);
                    setTimeout(function () {
                        $widget._DisableProductMediaLoader($this);
                    }, 300);
                };

            if (!$widget.options.mediaCallback) {
                return;
            }

            mediaCallData = {
                'product_id': this.getProduct()
            };

            mediaCacheKey = JSON.stringify(mediaCallData);

            if (mediaCacheKey in $widget.options.mediaCache) {
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
            } else {
                mediaCallData.isAjax = true;
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                $widget.xhr = $.get(
                    $widget.options.mediaCallback,
                    mediaCallData,
                    mediaSuccessCallback,
                    'json'
                ).done(function () {
                    $widget._XhrKiller();
                });
            }
        },

        /**
         * Enable loader
         *
         * @param {Object} $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;
            if ($('body.catalog-product-view').size() > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .addClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .addClass($widget.options.classes.loader);
            }
        },

        /**
         * Disable loader
         *
         * @param {Object} $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;
            if ($('body.catalog-product-view').size() > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .removeClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .removeClass($widget.options.classes.loader);
            }
        },

        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @param {Boolean} isInProductView
         * @private
         */
        _ProductMediaCallback: function ($this, response, isInProductView) {
            var $main = isInProductView ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if (_.size($widget) < 1 || !support(response)) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isInProductView);

                return;
            }

            images.push({
                full: response.large,
                img: response.medium,
                thumb: response.small,
                isMain: true
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        full: this.large,
                        img: this.medium,
                        thumb: this.small
                    });
                });
            }

            this.updateBaseImage(images, $main, isInProductView);
        },

        /**
         * Check if images to update are initial and set their type
         * @param {Array} images
         */
        _setImageType: function (images) {
            var initial = this.options.mediaGalleryInitial[0].img;

            if (images[0].img === initial) {
                images = $.extend(true, [], this.options.mediaGalleryInitial);
            } else {
                images.map(function (img) {
                    if (!img.type) {
                        img.type = 'image';
                    }
                });
            }

            return images;
        },

        /**
         * Start update base image process based on event name
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         * @param {String|undefined} eventName
         */
        // updateBaseImage: function (images, context, isInProductView, eventName) {
        //     var gallery = context.find(this.options.mediaGallerySelector).data('gallery');
        //     if (eventName === undefined && gallery !== undefined) {
        //         this.processUpdateBaseImage(images, context, isInProductView, gallery);
        //     } else {
        //         context.find(this.options.mediaGallerySelector).on('gallery:loaded', function (loadedGallery) {
        //             loadedGallery = context.find(this.options.mediaGallerySelector).data('gallery');
        //             this.processUpdateBaseImage(images, context, isInProductView, loadedGallery);
        //         }.bind(this));
        //     }
        // },
         updateBaseImage: function (images, context, isInProductView, eventName) {
            this.processUpdateBaseImage(images, context, isInProductView);
        },
        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         * @param {Object} gallery
         */
        processUpdateBaseImage: function (images, context, isInProductView, gallery) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                imagesToUpdate,
                isInitial;

            if (isInProductView)
            {

              if($("body").find(".popup-product-images").hasClass("popup-product-images")){
                var renderpopupimage = '<div class="popupimages">';
                var renderpopupthumb = '<div class="popupthumbs">';

                for (var i = 0; i < images.length; i++)
                {
                  if(images[i].img != "") {
                    renderpopupimage += "<input type='hidden' class='popup-img-"+i+"' value='"+images[i].img+"' />";
                    renderpopupthumb  += "<input type='hidden' class='popup-thumnb-"+i+"' value='"+images[i].thumb+"' />";
                  }
                }
                renderpopupimage += "</div>"+renderpopupthumb+"</div>";
                $(".popup-product-images").html(renderpopupimage);
              }
                if(!$( "body" ).hasClass( "weltpixel-quickview-catalog-product-view" ))
                {
                    if(images.length > 0)
                    {
                        //check if require to remove extra
                        var remove_elem = 0;
                        if($("div[class^='image-']").length+1 > images.length)
                        {
                            remove_elem = ($("div[class^='image-']").length+1) - images.length;
                        }

                        //To change the images when change the color.
                        for (var i = 0; i < images.length; i++)
                        {
                            if(images[i].type == "video")
                            {
                                var showImgUrl = '<iframe src="'+images[i].videoUrl+'" frameborder="0" allowfullscreen></iframe>';
                            }
                            else
                            {
                                if($('.image-'+(i+1)).length > 0)
                                {
                                    $( ".product-view-custom-media- .image-"+(i+1)+" img" ).attr("src",images[i].img);
                                }
                                else
                                {
                                    $( ".product-view-custom-media-" ).append( '<div class="image-'+(i+1)+'"><img src="'+images[i].img+'"></div>' );
                                }
                            }
                        }

                        // To remove extra element of images when change the color.
                        if(remove_elem > 0)
                        {
                            for(var i = 0; i < $("div[class^='image-']").length+1; i++)
                            {
                                if(i > images.length)
                                {
                                    $(".image-"+(i+1)).remove();
                                }
                            }
                        }
                    }
                }

                // imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                // isInitial = _.isEqual(imagesToUpdate, initialImages);

                // if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                //     imagesToUpdate = imagesToUpdate.concat(initialImages);
                // }

                // imagesToUpdate = this._setImageIndex(imagesToUpdate);
                // gallery.updateData(imagesToUpdate);

                // setTimeout(function(){
                //   $(".popup_magnify_icon").show();
                // },100);

                // if (isInitial) {
                //     $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                // } else {
                //     $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                //         selectedOption: this.getProduct(),
                //         dataMergeStrategy: this.options.gallerySwitchStrategy
                //     });
                // }
                if (isInitial) {
                    if ($.isFunction($(this.options.mediaGallerySelector).AddFotoramaVideoEvents)) {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    }
                }

                // gallery.first();

            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        },

        /**
         * Set correct indexes for image set.
         *
         * @param {Array} images
         * @private
         */
        _setImageIndex: function (images) {
            var length = images.length,
                i;

            for (i = 0; length > i; i++) {
                images[i].i = i + 1;
            }

            return images;
        },

        /**
         * Kill doubled AJAX requests
         *
         * @private
         */
        _XhrKiller: function () {
            var $widget = this;

            if ($widget.xhr !== undefined && $widget.xhr !== null) {
                $widget.xhr.abort();
                $widget.xhr = null;
            }
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @private
         */
        _EmulateSelected: function (selectedAttributes) {
            $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                this.element.find('.' + this.options.classes.attributeClass +
                    '[attribute-code="' + attributeCode + '"] [option-id="' + optionId + '"]').trigger('click');
            }, this));
        },

        /**
         * Emulate mouse click or selection change on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @param {String} triggerClick
         * @private
         */
        _EmulateSelectedByAttributeId: function (selectedAttributes, triggerClick) {
            $.each(selectedAttributes, $.proxy(function (attributeId, optionId) {
                var elem = this.element.find('.' + this.options.classes.attributeClass +
                    '[attribute-id="' + attributeId + '"] [option-id="' + optionId + '"]'),
                    parentInput = elem.parent();

                if (triggerClick === null || triggerClick === '') {
                    triggerClick = 'click';
                }

                if (elem.hasClass('selected')) {
                    return;
                }

                if (parentInput.hasClass(this.options.classes.selectClass)) {
                    parentInput.val(optionId);
                    parentInput.trigger('change');
                } else {
                    elem.trigger(triggerClick);
                }
            }, this));
        },

        /**
         * Get default options values settings with either URL query parameters
         * @private
         */
        _getSelectedAttributes: function () {
            var hashIndex = window.location.href.indexOf('#'),
                selectedAttributes = {},
                params;

            if (hashIndex !== -1) {
                params = $.parseQuery(window.location.href.substr(hashIndex + 1));

                selectedAttributes = _.invert(_.mapObject(_.invert(params), function (attributeId) {
                    var attribute = this.options.jsonConfig.mappedAttributes[attributeId];

                    return attribute ? attribute.code : attributeId;
                }.bind(this)));
            }

            selectedAttributes

            return selectedAttributes;

        },

        /**
         * Callback which fired after gallery gets initialized.
         *
         * @param {HTMLElement} element - DOM element associated with a gallery.
         */
        _onGalleryLoaded: function (element) {
            var galleryObject = element.data('gallery');

            this.options.mediaGalleryInitial = galleryObject.returnCurrentImages();
        },
        _onGalleryLoad: function (element) {
                var image = []
                element.each(function(){
                    image.push($(this).attr("data-img"));
                })

            this.options.mediaGalleryInitial = image;
        },
        /**
         * Sets mediaCache for cases when jsonConfig contains preSelectedGallery on layered navigation result pages
         *
         * @private
         */
        _setPreSelectedGallery: function () {
            var mediaCallData;

            if (this.options.jsonConfig.preSelectedGallery) {
                mediaCallData = {
                    'product_id': this.getProduct()
                };

                this.options.mediaCache[JSON.stringify(mediaCallData)] = this.options.jsonConfig.preSelectedGallery;
            }
        }
    });

    return $.mage.SwatchRenderer;
});
