/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
  'jquery',
  'mage/template',
  'Magento_Customer/js/customer-data',
  'mage/requirejs/json!Sttl_Customerorder/template/Inventory.json',
  'text!Sttl_Customerorder/template/neworder-style-mobile-render.html',
  'magnificPopup',
  'Magento_Ui/js/modal/modal',
  'text!Sttl_Customerorder/template/neworder-lineitem-mobile-render.html',
  'mage/validation/validation'
], function ($, mageTemplate, customerData, inventory, renderProductInfo, magnificPopup, modal, lineitemrenderer) {
  'use strict';
  var activecat = [],
    activecat_material = [],
    activecol = [],
    finalitems = [],
    removedskus = [],
    ordertotaldata = {},
    selector = '',
    owlproduct = '',
    owlmaterial = '',
    item_edited = false,
    message_timeout = '',
    json1Config = {},
    mark_invdata = {},
    mark_invdata_ral = [],
    config_total_data = new Array,
    product_Active = null,
    tool_timeout,
    skusdata = {};
  /**
   * Render neworder controls
   * Required two json:
   *  - jsonConfig (magento's sap product config)
   *  - poConfig (Po number config)
   *
   *  Tuning:
   *  - numberToShow (show "more" button if options are more)
   *  - moreButtonText (text for "more" button)
   *  - selectorProduct (selector for product container)
   *  - selectorProductPrice (selector for change price)
   */
  $.widget('mage.SwatchRenderer', {
    options: {
      // option's json config
      jsonConfig: inventory,

      // option's PO config
      poConfig: {},

      // option's PO config
      ConfigStyle: {},

      // Customer`s FlatDiscount From SAP`
      customersFlatDiscount: {},

      // BaseURl
      baseurl: {},

      // Simple SKU(s)
      SimpleStyle: {},

      // swatch's json config
      jsonSwatchConfig: {},

      // Use ajax to get image data
      useAjax: false,

      //Magento product listing page
      magento: {},

      //Get order id from URL
      base_order_id: {},

      //Return the Bulk discount of current customer
      customersBulcDiscount: {},

      // slider collection array
      slidercollections: {},

      //Ajax 
      usexhr: false
    },

    /*
     **  find confiurable/Simple product list
     *  * @returns array|null
     */
    // getProductArray: function(sku) {          
    //     var falg = _.filter(this.options.jsonConfig, function(value) {
    //         return value['key'] === sku;
    //     });
    //     return falg;

    // },
    getProductbysku: function (sku) {
      var falg = _.filter(this.options.ConfigStyle, function (value) {
        return value['Style'] === sku;
      });
      return falg;
    },
    /*
     *
     */
    skudatas: function () {
      var inventorydata = this.options.jsonConfig;
      $.each(inventorydata, function (key, value) {
        skusdata[value.ItemCode] = value
      })
    },
    /**
     * Div Input for Item.
     * This control shouldn have "type=hidden" :)
     *
     * @param {Object} config
     * @private
     */
    _RenderAutoItemDivLi: function (config, i, option) {
      var classActive = i < 1 ? 'active' : '';
      return '<div class="element ' + classActive + ' " ' +
        'data-index="' + i + '" >' +
        '<span>' + (option == 1 ? config.Style : config.ItemCode) +
        " - " + config.ShortDesc +
        '<input class="super-attribute-select" ' +
        'name="super_attribute"' +
        'type="hidden"' +
        'value="' + (option == 1 ? config.Style : config.ItemCode) + '"' +
        'aria-invalid="false" />' +
        '</span></div>';
    },

    /*
     * Po Input for Item.
     ** return po list dropdwon 
     */
    _RenderAutoItemDivPO: function (config, i) {
      var classActive = i < 1 ? 'active' : '';
      return '<div class="view-po element ' + classActive + ' " ' +
        'data-index="' + i + '" >' +
        '<span>' + config.NumatCardPo +
        '<input class="super-attribute-select" ' +
        'name="super_attribute"' +
        'type="hidden"' +
        'dyorderid="' + config.OrderId + '"' +
        'value="' + config.NumatCardPo + '"' +
        'aria-invalid="false" />' +
        '</span></div>';
    },

    _init: function () {
      var data = this.getConfigurableProduct(this.options.jsonConfig, 'Style');
      this._collectionlogoslider();
      this.productslider();
      this._EventListener();
      this.posuccesspopup();

      if (this.options.base_order_id && this.options.base_order_id != '') {
        this._getLineItemTable(this.options.base_order_id);
      }
      $(".orderItem-loader").hide();
      // $("#polistautocomplete-list").remove();
    },


    _create: function () {

      var options = this.options;
      var element = this.element;
      var $widget = this;
      console.log("draft_po_list",this.options.poConfig)
      $widget._OnChangeDropdownPO($('span.left_icon.left_icon1'), $widget, 'dropdwon');


    },

    /**
     * Event listener
     *
     * @private
     */
    _EventListener: function () {
      var $widget = this;

      var urlParams = new URLSearchParams(window.location.search);
      var myString = urlParams.get('item');
      if (myString == null || myString == '') {

      } else {
        $("#show_style").attr("value", myString);
        $widget._OnItemChange($(this), $widget, 0);
      }

      $widget.element.on('click', '.col-logo', function (e) {
        $widget.collectionlogosclickevent($(this));
      });

      $widget.element.on('click', ".group-col", function () {
        $widget.categoryclick($(this));
      });

      $(document).on('click', '.column.main .product-item', function () {
        $("#show_style").attr("readonly", true);
        $(".column.main .product-item").removeClass("pro_active");
        $(this).addClass("pro_active");
        $("#show_style").attr("value", $(this).attr('id'))
        // $(".searchFromStyle").trigger('click');
        setTimeout(function () {
          $("#show_style").attr("readonly", false);
        }, 1500)
      });

      $(window).resize(function () {
        $('.product-group-slider').css('width', jQuery('#createorder').width());
        $('.product-group-slider').trigger("refresh.owl.carousel")
        setTimeout(function () {
          $(".product-slider-sticky").css({
            "width": $(".product-slider .owl-item").width(),
            "height": $(".product-slider .owl-item").height()
          })
          var owl = $('.product-slider').data("owlCarousel");
          owl.onResize();
        }, 300)

      });

      $(document).on('click', '.product-slider .owl-prev', function (e) {
        var i = 0;
        $('.product-slider').attr('data-event', 'prev');

        if (!$('.product-slider .owl-item.active').hasClass('sticky')) {
          i = 1;
        } else {
          i = 0;
          $('.product-slider-sticky').remove()
        }
        if (jQuery('.product-slider .sticky').prevAll('.owl-item.active').length >= 3 && jQuery('.product-slider .sticky.active').length === 0) {
          i = 1;
        }
        if (jQuery('.product-slider .sticky.active').next('.owl-item:not(.active)').length == 1) {
          i = 1;
        }

        if (i >= 1) {
          if ($('.product-slider-sticky').length < 1) {
            var content = $('.owl-item.sticky').html();
            var newdiv = $("<div class='product-slider-sticky'>");
            newdiv.css({
              'width': jQuery('.owl-item.sticky').width(),
              'right': '0',
              'left': 'unset'
            }).html(content);
            $('.product-slider .owl-stage').after(newdiv);
          }

        } else {
          $('.product-slider-sticky').remove();
        }

        $('.product-slider .owl-next').show();
        if (jQuery('.owl-item.active').prevAll('.owl-item:not(.active)').length < 1) {
          $('.product-slider .owl-prev').hide();
        }
      })

      $(document).on('click', '.product-slider .owl-next', function (e) {
        $('.product-slider .owl-prev').show();
        if (jQuery('.owl-item.active').nextAll('.owl-item:not(.active)').length < 1) {
          $('.product-slider .owl-next').hide();
        }
      });


      $(document).on('click', '.product-slider .product-item', function () {
        $("#show_style").attr("value", $(this).attr('id'));
        product_Active = $(this).attr('id');
        $(".column.main .product-item").removeClass("pro_active");
        $(this).addClass("pro_active");
        $('.owl-item').removeClass('sticky');
        $(this).parent().toggleClass('sticky')
        $(".product-image-sticky").addClass("active")
        $(".product-image-sticky .owl-item").css("display", "block")
        // $(".searchFromStyle").trigger('click');
        $('.product-slider-sticky').remove();
      })

      $(document).on('click', '.group-col', function (e) {

        var active = (jQuery('.product-slider-sticky').find('.product-item').attr('id')) || product_Active;
        if ($('#show_style').val()) {
          active = $('#show_style').val();
        }

        jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').addClass("pro_active").parent().addClass('sticky');
        if (jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').length > 0) {
          $('.product-slider-sticky').css({
            'left': 'unset'
          });
          if ($('.product-slider-sticky').length < 1) {
            var content = $('.owl-item.sticky').html();
            var newdiv = $("<div class='product-slider-sticky'>");
            newdiv.css({
              'width': jQuery('.owl-item.sticky').width(),
              'left': '0'
            }).html(content);
            $('.product-slider .owl-stage').after(newdiv);
          }

        } else
          $('.product-slider-sticky').remove();

        if ($('.owl-item.sticky.active').length > 0)
          $('.product-slider-sticky').remove();
      })

      $(document).on('click', '#show_styleautocomplete-list .element', function () {
        return $widget._ItemSelect($(this), $widget);
      });

      /* Po seraching */
      $widget.element.on('input', '#po_number', function (e) {
        // if($(this).val().length < 1)                
        // $('#polist').removeClass('editsection').find('i').addClass('fa-caret-down').removeClass('fa-edit'); 
        if ($("#po_number").val().length >= 4) {
          $(".left_icon").hide();
          $(".checkPoAndInsert").show();
        } else {
          $('.left_icon.left_icon1').find('i').removeClass('fa-edit').addClass('fa-caret-down');
          $(".left_icon").show();
          $(".checkPoAndInsert").hide();
        }


        return $widget._OnChangeDropdownPO($(this), $widget);
      });

      /* Style seraching */
      $widget.element.on('input', '.dropdown', function (e) {
        return $widget._OnChangeDropdown($(this), $widget);
      });
      /* PO listdron on triger click seraching */
      $widget.element.on('click', '.left_icon.left_icon1.editsection', function (e) {
        $('#po_number').css('opacity', '1').attr('readonly', false).focus();
        $("#po_number").attr("readonly", false);
        $("#po_number").focus();
        $("#po_number").css("opacity", "1");
        $(".left_icon").hide();
        $(".checkPoAndInsert").show();
      })

      /* PO listdron on triger click seraching */
      $widget.element.on('click', '.left_icon.left_icon1:not(.editsection)', function (e) {
        $('.checkPoAndInsert').hide();
        $(this).toggleClass('open');
        if ($(this).hasClass('open') && $(".autocomplete-items").children().length > 0) {
          $(this).find('i').attr('class', 'fa fa-caret-up');
          $('#polistautocomplete-list').addClass('active');
          $('#polistautocomplete-list').show();
        } else {
          $(this).find('i').attr('class', 'fa fa-caret-down');
          $('#polistautocomplete-list').removeClass('active');
          $('#polistautocomplete-list').hide();
        }
        if ($("#po_number").val().length <= 0 && $("#po_number").is('[readonly]') == false && $("#po_numberautocomplete-list").length > 0) {
          $(this).find(".fa-caret-down").toggleClass("fa-caret-up");
          $(this).parent().find(".autocomplete-items.fullpo").slideToggle();
          var a = $(this);
          if ($(a).find('i').hasClass('fa-caret-down')) {
            $(a).find('i').removeClass('fa-caret-down');
            $(a).find('i').addClass('fa-caret-up');
          } else {
            $(a).find('i').addClass('fa-caret-down');
            $(a).find('i').removeClass('fa-caret-up');
          }
        }

      });


      /* PO dropdwon click */
      $widget.element.on('click', '#po_numberautocomplete-list .view-po.element,#polistautocomplete-list .view-po.element ', function (e) {
        return $widget._OnClickPoList($(this), $widget);
      });

      /* PO dropdwon click */
      $widget.element.on('click', '#show_styleautocomplete-list .element', function (e) {
        return $widget._OnClickStyleList($(this), $widget, 0);
      });

      $widget.element.on('focusout', '#po_number', function (e) {
        $('.checkPoAndInsert').trigger('click');
        $('#po_numberautocomplete-list').fadeOut(500);
        $(".checkPoAndInsert").hide();
        $(".left_icon").show();
      });

      $widget.element.on('focus', '#po_number', function (e) {

        $('#po_numberautocomplete-list').fadeIn(500);
      });

      $widget.element.on('focusout', '#show_style', function (e) {
        var item = $(this).closest('div').find('div.autocomplete-items:visible .view-po.element.active .super-attribute-select');
        if ($(this).val().length > 0 && item.length > 0) {
          var val = item.val();
          $(this).val(val);
          if ($(this).attr('id') == 'po_number')
            $('.checkPoAndInsert').trigger('click');
          else if ($(this).attr('id') == 'style')
            $('.box-actions').trigger('click');
        }
        $(this).closest('div').find('div.autocomplete-items:not(#polistautocomplete-list)').fadeOut(200);
        setTimeout(function () {
          $(this).closest('div').find('div.autocomplete-items:not(#polistautocomplete-list)').remove();
        }, 200);
        $('#polistautocomplete-list').fadeOut(800);
      });

      $(document).on("click", "#goback", function () {
        $("#contopaymentredirect").val('');
        $("#clickOnSaveAndDraft").val('');
        $('#nextstep').val('');
        $('#removeUser').dialog('close');
      });

      $(document).on("click", "#savecontinue", function () {
        $('#removeUser').dialog('close');
        $widget._optiononeAdddata($widget, 1);
      });

      $widget.element.on('keypress', '#po_number', function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which),
          val = $(this).val();

        if (keycode == '13') {
          e.preventDefault();
          if ($(this).length > 0 && $('#po_numberautocomplete-list').is(":visible")) {
            if ($('#po_numberautocomplete-list').children().length > 0) {
              $(this).val($('#po_numberautocomplete-list .view-po.element.active input').val())
            }
            $('#po_numberautocomplete-list').fadeOut(500);
            $('span#polist').hide();
          }
          $(".checkPoAndInsert").trigger("click");
        } else {
          var falg = _.filter($widget.options.poConfig, function (value) {
            return value['NumatCardPo'].indexOf(val) > -1;
          });

          if ($(this).length > 0 && falg.length > 0) {
            $('#polist').addClass('editsection').hide().find('i').removeClass('fa-caret-down').addClass('fa-edit').parent().show();

          } else if ($(this).length > 1) {
            $('#polist').hide();
            $('.checkPoAndInsert').show(300);
          }
        }
      });
      $widget.element.on('keyup','#po_number', function (e) {
        var droppo=  $('#po_numberautocomplete-list div').length;
        if(droppo==0)
        {
            $('#po_numberautocomplete-list').css("display","none");
        }
        else
        {
            $('#po_numberautocomplete-list').css("display","block");
        }
      });

      $widget.element.find('.checkPoAndInsert').on('click', function (e) {
        e.preventDefault();
        $('#po_number').validation();
        if (!$('#po_number').validation('isValid')) {
          $('#po_number').css('border', '1px solid red');
          return false;
        } else {
          $('#po_number').css('border', '');
        }

        return $widget._determineProductData($('#po_number').val());
      });

      $widget.element.on('click', '.searchFromStyle', function (event, data) {
        return $widget._OnItemChange($(this), $widget, data, 0);
      });
      $(document).on('click', '.product-slider .product-item', function (event, data) {
        if ($(this).hasClass('pro_active')) {
          $widget._OnItemChange($(".searchFromStyle"), $widget, data, 1)
        }
      })
      $widget.element.on('keypress', '#show_style', function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
          event.preventDefault();
          if ($("#show_styleautocomplete-list.autocomplete-items").children(".element.active").length == 1) {
            var autoselected_style = $("#show_styleautocomplete-list.autocomplete-items").find(".element.active input").val();
            $("#show_style").attr("value", autoselected_style);
          }
          $(".autocomplete-items").hide();

          $(".searchFromStyle").trigger("click");
          $(this).blur();
          return false;
        }
      });

      $(document).on("click", ".renderAllHtml .swatch-option.image", function () {
        $('.swtach div , .swatch-option.image').removeClass("active");
        $(this).addClass("active");
        const id = $(this).attr('option-color-code');
        $(".option-thumbnail div , .colorImage").removeClass("active");
        $('#DR' + id).addClass("active");
        $("#" + $(this).attr('id') + 'Class').addClass('active');
        var newSta = $(this).attr('option-color-status');
        $(".colorstatus #Status").text(newSta);
        var newcolor = $(this).attr('option-core-color-name');
        $(".core-color-name").text(newcolor);
        if (newcolor) {
          $(".fashion-color-name").text("");
          $(".core-color-name").text(newcolor);
        }
        var facolor = $(this).attr('option-fashion-color-name');
        if (facolor) {
          $(".core-color-name").text("");
          $(".fashion-color-name").text(facolor);
        }
      })

      $(document).on("click", "a.btn-gallery", function (e) {
        $.magnificPopup.open({
          items: {
            src: $(this).find("img").attr("src"),
          },
          type: "image",
          closeOnBgClick: true,
          mainClass: 'mfp-with-zoom mfp-zoom-in mfp-img-mobile',
          preloader: true,
          showCloseBtn: true,
          tLoading: "",
          callbacks: {
            beforeOpen: function () {
              this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
              this.st.mainClass = 'mfp-zoom-in';
            },

          },
        });
      });

      $widget.qtyaddpopup();
      $widget.qtyeditpopup();


      $widget.element.on('click', '.catBtns .customBtns', function () {
        var cur_button = $(this).attr("product-sku"),
          active_pro = $("config_product_info").attr("id");

        if (active_pro == "config_" + cur_button)
          return false;
        else {
          $("#show_style").val(cur_button);
          $(".searchFromStyle").trigger("click");
        }
      });


      $(document).on('click', ".saveData", function (e, data) {
        return $widget._optiononeAdddata($widget, 1);
      });
      $(document).on('click', '.orderList.mobile.lineItemsList td.toggleshow-line', function () {
        var a = $(this);
        // $(this).parent().next('tr').slideToggle("slow");
        event.preventDefault();
        // We break and store the result so we can use it to hide
        // the row after the slideToggle is closed
        var targetrow = $(this).closest('tr').next('.toggletable');

        targetrow.show().find('div').slideToggle('slow', function () {
          if (!$(this).is(':visible')) {
            targetrow.hide();
            targetrow.removeClass("active");
          } else {
            targetrow.addClass("active")
          }
        });

        if ($(a).parent().children().first().find('i').hasClass('fa-caret-down')) {
          // console.log($(this).parent().next('tr').offset().top );
          // $('html, body').animate({
          //      scrollTop: $(this).parent().next('tr').offset().top - 100
          //  }, 2000);
          // $(a).find('span.icon-fa i').removeClass('fa-chevron-up', 500);

          $.fn.getSize = function () {
            var $wrap = $("<div />").appendTo($("body"));
            $wrap.css({
              "position": "absolute !important",
              "visibility": "hidden !important",
              "display": "block !important"
            });

            var $clone = $(this).clone().appendTo($wrap);

            var sizes = {
              "width": $clone.width(),
              "height": $clone.height()
            };

            $wrap.remove();

            return sizes;
          };

          var position = $(this).offset().top;
          var toggle = targetrow.find('div.expandable_row').getSize();
          var windowSize = $(window).height();
          var limit = windowSize / 2;
          if (windowSize < 400) {
            // console.log('windw minizw');

            $("body, html").animate({
              scrollTop: position
            }, 1000);
          } else if (position > limit) {
            // console.log('windw minizw limit',toggle);

            $("body, html").animate({
              scrollTop: position - 30
            }, 1000);
          }

          $(a).parent().children().first().find('i').removeClass('fa-caret-down');
          $(a).parent().children().first().find('i').addClass('fa-caret-up');
        } else {

          $(a).parent().children().first().find('i').addClass('fa-caret-down');
          $(a).parent().children().first().find('i').removeClass('fa-caret-up');
        }
      });

      // delete order functionaliy
      var po_number = $("#po_number").val();
      $(document).on("click", ".delSingalRecords", function (e) {
        var opt = {
          autoOpen: false
        };
        var theDialog = $("#deletesingledata").dialog(opt);
        theDialog.dialog("open");
        $("#singledeletecontinue").attr("delete-id", $(this).attr("delete-id"))
        $("#singledeletecontinue").attr("delete-color", $(this).attr("delete-color"))
      })

      $(document).on("click", "#singledeletecontinue", function (e) {
        $('#deletesingledata').dialog('close');
        return $widget._Deleteorderdata($(this), $widget);
      });
      $(document).on("click", "#singlecancle", function (e) {
        $('#deletesingledata').dialog('close');
      });
      $(document).on("click", "#deletesingledata .mfp-close-inside", function (event, ui) {
        $('#singlecancle').click();
      });
      $('#cancle').click(function () {
        $('#deletedata').dialog('close');
      });

      // restock date tooltip
      $(document).on("click", ".eta-tooltip", function (e) {

        if ($(".eta-date").is(":visible")) {
          $(".eta-date").fadeOut();
          clearTimeout(tool_timeout);
        }

        var left = ($(this).outerWidth() / 2) - 30;
        $(this).find(".eta-date").css('left', left + 'px')
        $(this).find(".eta-date").fadeIn();
        tool_timeout = setTimeout(function () {
          $(".eta-date").fadeOut();
        }, 5000);
        e.stopPropagation();
      });

      $(document).on('click', '.saveasdraft', function (e) {
        return $widget._SaveasDraft($(this), $widget);
      });
      $(document).on('click', '.contopayment', function (e) {
        return $widget._Contopayment($(this), $widget);
      });
      $(document).on('click', ".deleteorder", function () {
        var opt = {
          autoOpen: false
        };
        var theDialog = $("#deletedata").dialog(opt);
        theDialog.dialog("open");
      })
      $(document).on('click', '#deletecontinue', function () {
        $('#deletedata').dialog('close');
        $('body').trigger('processStart');
        var baseorder_id = $("#base64_order_id").val(),
          nexturl = $widget.options.baseurl + 'customerorder/customer/deletejs/id/' + baseorder_id + '/'
        window.location.href = nexturl;
      });
      $(document).on("click", ".scroll-to-po", function (e) {
        $('html, body').animate({
            scrollTop: $($(".newOrderStep1")).offset().top,
          },
          500,
          'linear'
        )
      });

      $(document).on("focusout", ".checkvalue", function (e) {
        $widget._checkvalueUpdate($(this), false);
      });

    },


    _ItemSelect: function ($this, $widget) {
      var input = $this.parents('.box-content').find('input.dropdown');
      // console.log(input.addClass('opopop'));
      input.val($this.find('input').val());
      if (input.attr('data-option') == 2) {
        $('#opt_two_qty').focus();
      } //RB
      this.element.find('[data-action="' + input.attr('data-option') + '"]').trigger('click');
    },

    _SaveasDraft: function () {
      var checkinputval = $('.product_options.createOrder').find('.checkvalue'),
        valIsExists = false;

      $(checkinputval).each(function () {
        if ($(this).val() != '') valIsExists = true;
      });

      var is_qty_change = 0,
        prev_obj_id = $(".swatch-attribute-options.clearfix").find('.tab-pane.fade.active').attr("id");

      if (typeof prev_obj_id !== "undefined") {

        is_qty_change = $(".swatch-attribute-options.clearfix").find("#qty_change_" + prev_obj_id.replace("/", "")).val();

      }
      var opt = {
        autoOpen: false
      };

      if (valIsExists == 1 && is_qty_change == 1) {
        var getColorCode = '';
        if ($(".swatch-option.image.active").length > 0)
          getColorCode = $(".swatch-option.image.active").attr('option-color-code');
        $('#nextstep').val('saveasdraft');
        var theDialog = $("#removeUser").dialog(opt);
        $("#clickOnSaveAndDraft").val('1');
        theDialog.dialog("open");
        return false;
      } else {
        $('body').trigger('processStart');
        $.ajax({
          url: this.options.baseurl + 'customerorder/customer/shippingaddress',
          type: "POST",
          data: {
            'order_number': $('#order_id').val()
          },
          showLoader: true,
          cache: false,
          success: function (response) {
            if (response.error == true) {
              // console.log('No shipping adrress is Founds');
            }
          }
        });

        var nexturl = this.options.baseurl + 'customerorder/customer/index?q=d';
        setTimeout(function () {
          window.location.href = nexturl;
        }, 300);

      }
    },
    _Contopayment: function ($this, $widget) {
      $this.css('overflow', 'hidden');
      $this.css('position', 'relative');
      // jQuery(this).css('display','block');
      $this.addClass('animate-allcss');
      $this.addClass('hold-mouse')
      var x = event.offsetX - 10;
      var y = event.offsetY - 10;
      $this.find('.circle').remove();
      $this.append('<div class="circle grow" style="left:' + x + 'px;top:' + y + 'px;"></div>')

      var checkinputval = $('.swatch-attribute-options.clearfix').find('.checkvalue'),
        valIsExists = false;

      $(checkinputval).each(function () {
        if ($(this).val() != '') {
          valIsExists = true;
        }
      });

      var is_qty_change = 0,
        prev_obj_id = $(".swatch-attribute-options.clearfix").find('.tab-pane.fade.active').attr("id");

      if (typeof prev_obj_id !== "undefined") {
        is_qty_change = $("#qty_change_" + prev_obj_id.replace("/", "")).val();
      }
      var opt = {
        autoOpen: false
      };


      if (valIsExists == 1 && is_qty_change == 1) {
        var getColorCode = '';
        if ($(".swatch-option.image.active").length > 0)
          getColorCode = $(".swatch-option.image.active").attr('option-color-code');
        $('#nextstep').val('continuetopayment');
        var theDialog = $("#removeUser").dialog(opt);
        $("#contopaymentredirect").val('1');
        theDialog.dialog("open");
        return false;

      } else {
        var contopayment = $(".delSelectedRecords").html();
        if (contopayment != '' && typeof contopayment !== "undefined") {
          $('body').trigger('processStart');
          $('#createorder').submit();
          return false;
        }
      }
    },
    /**
     * Add a Data to Po using Option1 and use for AJAX
     *
     * @returns {{order_id: text, : bool}}
     * @private
     */

    _getLineItemTable: function (order_id, po_number = '') {

      var $widget = this,
        tmp_po_number = $("#po_number").attr("value"),
        req_url = this.options.baseurl + 'customerorder/customer/optiontwojs',
        current_options = '';

      $.ajax({
        url: req_url,
        type: "POST",
        data: {
          base_order_id: order_id,
          po_number: tmp_po_number
        },
        showLoader: false,
        cache: false,
        success: function (response) {
          if (response.session_distroy) {
            $widget.adderror(response.message);
            $('body').trigger('processStart');
            window.location.reload(true);
            return false;
          }
          if (response.success) {
            $widget._updatetmpOrderData($widget, response);

            $widget._renderLineitembeforeAJAX($widget, current_options);
            $('#order_id').val(parseInt(order_id));
            if (po_number != '' && order_id != '') {
              $widget._changeurl(btoa(order_id), btoa(po_number));
            }

            if (response.order_id) {
              $("#order_id").val(response.order_id);
            }

            if (response.base64_order_id) {
              $("#base64_order_id").val(response.base64_order_id);
            }

            if (response.base64_ncp_id) {
              $("#base64_ncp_id").val(response.base64_ncp_id);
            }

          }
        }
      });

      return true;
    },
    adderror: function (message) {
      // console.log('adderror',message);
      $("#msg_text").removeClass("success");
      $("#msg_text").addClass("error");
      $("#msg_text").html(message);
      $("#message").show();
      $("#msg_text").show();
      $("#message").focus();
      this.messagetimeout("#message");
      return true;
    },

    addsuccess: function (message) {
      $("#msg_text").removeClass("error");
      $("#msg_text").addClass("success");
      $("#msg_text").html(message);
      $("#message").show()
      $("#msg_text").show();
      $("#message").focus();
      this.messagetimeout("#message");
      return true;

    },

    _Deleteorderdata: function ($this, $widget) {
      var delete_styles = [];
      var delete_colors = [];
      var po_number = $("#po_number").val();

      var checkinputval = $('.colorContainer').find('.checkvalue');
      $('div.renderAllHtml').attr('data-value', 'edit');
      var valIsExists = false;
      var delete_color = $this.attr('delete-color');
      var deletestyle = $this.attr('delete-id');
      delete_styles.push(deletestyle);
      delete_colors.push(delete_color);
      var tmp_orderdata = _.filter(finalitems, function (item) {
        if (item.Style == deletestyle && item.ColorCode == delete_color) {} else {
          return true
        }
      });

      var is_holeorderdelete = false;
      if (tmp_orderdata.length <= 0) {
        var baseorder_id = $("#base64_order_id").val();
        $widget._deleteOrder($widget, baseorder_id)
        is_holeorderdelete = true;
      }
      var current_options = '';
      finalitems = tmp_orderdata;


      $widget._renderLineitembeforeAJAX($widget, current_options);
      if (!is_holeorderdelete) {
        $widget._deleteRecord(delete_styles, delete_colors, po_number);
      }
      return true;
    },

    _deleteOrder: function ($widget, order_id) {

      var isholeorder_delete = true,
        url = this.options.baseurl + 'customerorder/customer/deletejs';

      $.ajax({
        url: url,
        type: "POST",
        data: {
          order_id: order_id,
          isholeorder_delete: isholeorder_delete,
        },
        showLoader: true,
        cache: false,
        success: function (response) {
          if (response.session_distroy) {
            $widget.adderror(response.message);
            $('body').trigger('processStart');
            window.location.reload(true);
            return false;
          }
          if (response.success) {
            $("#order_id").val(response.order_id);
            $("#base64_order_id").val(response.base64_order_id);
            $("#base64_ncp_id").val(response.base64_ncp_id);
            $widget._changeemptyurl();
            $widget.addsuccess(response.message)
          }
        }
      });
    },
    _deleteRecord: function (style, color = [], po_number, removed_markitem = false) {
      // console.log(style);
      var $widget = this;
      var url = this.options.baseurl + 'customerorder/customer/deletejs';
      var isorder_delete = true;
      var order_id = $("#order_id").val();
      var flatDiscount = $("#flatDiscount").val();
      var customerdata = $widget.options.customersFlatDiscount;
      var tmp_ordertotaldata = JSON.stringify(ordertotaldata);
      customerdata = JSON.stringify(customerdata);
      $.ajax({
        url: url,
        type: "POST",
        data: {
          flatDiscount: flatDiscount,
          order_id: order_id,
          po_number: po_number,
          style: style,
          color: color,
          isorder_delete: isorder_delete,
          customerdata: customerdata,
          ordersummary: tmp_ordertotaldata,
        },
        showLoader: false,
        cache: false,
        success: function (response) {
          if (response.session_distroy) {
            $widget.adderror(response.message);
            $('body').trigger('processStart');
            window.location.reload(true);
            return false;
          }
          if (response.errors) {
            $widget.adderror(response.message);
          } else {
            $("#order_id").val(response.order_id);
            if (response.errors) {
              $widget.adderror(response.message);
              $(".delOrdLink").fadeOut(300);
              $(".line-item").fadeOut(300);
            }
            if (!removed_markitem) {
              $widget.addsuccess(response.message)
            }
          }
          var grandtotal = $("#grandtotal").val();
          if (grandtotal == "undefined" && grandtotal == "") {
            $(".delOrdLink").hide();
          }
        }
      });
    },

    /**
     * Determine product id and related data
     *
     * @returns {{productId: *, isInProductView: bool}}
     * @private
     */
    _determineProductData: function (PoVal, option = 1) { // this.options.poConfig


      if (!/^[^-\s][a-zA-Z0-9!%*@#$&()\\-`.+,\-\s=\"]{3,}$/.test(PoVal) && option == 1) {
        var $tmp_widget = this.element;
        $tmp_widget.find('.po-exist').addClass("message-error error").html('PO Number must be at least 4 characters long and cannot start with a space');
        setTimeout(function () {
          $tmp_widget.find('.po-exist').fadeOut(2000, function () {
            $tmp_widget.find('.po-exist').html('');
          });
        }, 2000)
      } else {
        this.element.find('.po-exist').html("");
        $('.or-txt,.checkPoAndInsert').fadeOut(200);
        $('#show_style').focus();
        $('#po_number').css('opacity', '0.5');
        $("#po_number").attr('readOnly', true);
        $('.left_icon.left_icon1:not(.draft)').addClass('editsection').find('i').removeClass('fa-caret-down').addClass('fa-edit').parent().fadeIn(200);
        var po_number = $("#po_number").val();
        if($('.cf.line-item table.orderList.mobile').hasClass('lineItemsList') == true){
          $('input#po_number').css('pointer-events','none');
          $('ispan#polist').css('pointer-events','none');
        }else{
          $('.left_icon.left_icon1:not(.draft)').addClass('editsection').find('i').removeClass('fa-caret-down').addClass('fa-edit').parent().fadeIn(200);
        }
        if (!$('.colorSwatches').is(':empty') && po_number) {
          $(".scroll-to-po").addClass("saveData");
          jQuery(".scroll-to-po").addClass("saveChng");
          var value_exist = false,
            value_selector = jQuery("[name^='qty[']");
          value_selector.each(function () {
            if (jQuery(this).val().length && value_exist == false)
              value_exist = true;
          });
          if (value_exist == false) {
            jQuery(".scroll-to-po").addClass("disabled", 1000, "easeOutBounce");
          } else {
            jQuery(".scroll-to-po").removeClass("disabled", 1000, "easeOutBounce");
          }
          $(".saveData").html("");
          $(".saveData").html("ADD/UPDATE P.O.");
          if ($(".saveData").hasClass("scroll-to-po")) {
            $(".saveData").removeClass("scroll-to-po");
          }
          $('html, body').animate({
            scrollTop: $($(".Collections .owl-stage-outer")).offset().top - 5,
          }, 1000, 'linear')

        }
      }

    },

    /*
     * Section show with in time period
     */
    _OnChangeDropdown: function ($this, $widget) {
      // $widget.inputvaleValidation($this);
      $("#opt_two_message").hide();
      var a, i, j = 0,
        val = $this.val(),
        input = '',
        res = [];
      var arr = this.options.ConfigStyle;
      a = '<div id="' + $this.attr('id') + 'autocomplete-list" class= "autocomplete-items" ><div>';
      for (i = 0; i < arr.length; i++) {
        var key = 'Style'
        if (arr[i][key].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10) {
          res.push(arr[i]);
          input += this._RenderAutoItemDivLi(arr[i], j, 1);
          j++;
        } else if (j > 10) {
          return false;
        } else if (val.length < 1) {
          $this.parent().find('div.autocomplete-items').remove();
          return false;
        }
      }
      if ($('#' + $this.attr('id') + 'autocomplete-list').length == 0) {
        $this.parent().append(a);
      }
      $this.parent().find('div.autocomplete-items').html(input);
      if (res.length < 1) {
        $("#qtyDetailPop").fadeOut(300);

      }

    },

    /*
     ** Po dropdwon
     */
    _OnChangeDropdownPO: function ($this, $widget, inputs = "") {
      // $widget.inputvaleValidation($this);       
      $("#opt_two_message").hide();
      var a, i, j = 0,
        val = $this.val(),
        input = '',
        res = [];
      var arr = this.options.poConfig;
      a = '<div id="' + $this.attr('id') + 'autocomplete-list" class= "autocomplete-items" ><div>';
      for (i = 0; i < arr.length; i++) {
        var key = 'NumatCardPo';
        if (inputs != 'dropdwon') {
          if (arr[i][key].substr(0, val.length).toUpperCase() == val.toUpperCase() && j < 10) {
            res.push(arr[i]);
            input += this._RenderAutoItemDivPO(arr[i], j);
            j++;
          } else if (j > 10) {
            return false;
          } else if (val.length < 1) {
            input = ''
            // $('#po_numberautocomplete-list').remove(); 
            // $('.checkPoAndInsert').fadeOut(200); 
            // $('.left_icon.left_icon1').fadeIn(300); 
            return false;
          }
        } else {
          res.push(arr[i]);
          input += this._RenderAutoItemDivPO(arr[i], j);
          j++;
        }
      }
      if ($('#' + $this.attr('id') + 'autocomplete-list').length == 0) {
        $this.parent().append(a);
      }
      if (inputs == 'dropdwon')($('#' + $this.attr('id') + 'autocomplete-list').hide());
      $this.parent().find('div.autocomplete-items').html(input);
      // if(res.length < 1){                           
      //    $('#po_numberautocomplete-list,.left_icon.left_icon1').fadeOut(300);               
      //    $('.checkPoAndInsert').fadeIn(300);            
      // }else if($this.val().length > 0){
      //      $('#polist').addClass('editsection').find('i').removeClass('fa-caret-down').addClass('fa-edit').parent().fadeIn(200);
      // }


    },


    /*
     ** get exits po data 
     */
    _OnClickPoList: function ($this, $widget) {
      /*ajax call*/
      $('#polistautocomplete-list').hide();
      var OrderId = $this.find('.super-attribute-select').attr("dyorderid"),
        PoNumber = $this.find('.super-attribute-select').val(),
        result = '';


      if (OrderId != '' && PoNumber != '') {
        result = this._getLineItemTable(OrderId, PoNumber);
      }


      $('.left_icon.left_icon1').remove();
      $('#po_number').val($this.find('.super-attribute-select').val())
      $('#po_numberautocomplete-list').fadeOut(300);
      $('#polistautocomplete-list').removeClass('active');
      $widget._determineProductData($('#po_number').val());

    },


    /*
     ** get exits po data 
     */
    _OnClickStyleList: function ($this, $widget) {
      /*ajax call*/
      var val = $this.find('input.super-attribute-select').val();

      $('input#show_style').val(val);
      $widget._OnItemChange($(this), $widget);
    },


    /*
     **  find all confiurable product Sky
     ** @returns array|null
     */
    getConfigurableProduct: function (events, key) {
      if (events.length > 0) {
        var result = events.reduce(function (memo, e1) {
          var matches = memo.filter(function (e2) {
            return e1.Style == e2.Style
          })
          if (matches.length == 0)
            memo.push(e1)
          return memo;
        }, [])
        this.options.ConfigStyle = result;
        return result;
      } else {
        return {};
      }
    },

    getRandomList: function () {
      var $widget = this,
        data1 = new Array(),
        data = $widget.getConfigurableProduct(this.options.jsonConfig, 'Style')

      data.forEach(function (item, index) {
        if (item.Collection !== '') {
          var result = $widget.getColorProductArray(item.Style, 1)
          if (result)
            data1.push(result);
        }
      });
      return data1;

    },
    getColorProductArray: function (sku) {

      var child_pro = this.getProductArray(sku, 1),
        final_vale_array = [],
        main_swatch_color_array = [];

      _.each(child_pro, function (value, index) {
        if (value.Color != '' && value.ColorSwatch != '') {
          if (!_.contains(main_swatch_color_array, value.ColorCode)) {
            main_swatch_color_array.push(value.ColorCode);
            final_vale_array.push(value);
          }
        }
      });
      if (final_vale_array.length < 0) {
        final_vale_array = child_pro;
      }
      var rendom = final_vale_array.length > 0 ? Math.floor(Math.random() * final_vale_array.length) : 0;
      rendom = rendom >= 0 && rendom < final_vale_array.length ? rendom : 0;
      return final_vale_array[rendom];

    },
    getProductArray: function (sku, option) {
      var key = option == 1 ? 'Style' : 'ItemCode',
        falg = _.filter(this.options.jsonConfig, function (value) {
          return value[key] === sku;
        });
      return falg;

    },
    /*
     **  find all confiurable product Sky
     * @returns array|null
     */
    getConfigurableProductList: function () {
      var dt = [],
        color = [],
        cat = [];
      var data1 = this.options.jsonConfig.map(item => item).filter(function (value, index, self) {
        if (dt.indexOf(value.Style) === -1) {
          if (color.indexOf(value.ColorCode) === -1 || value.ColorCode == '') {
            color.push(value.ColorCode);
            dt.push(value.Style);
            return value;
          } else {
            if (cat.indexOf(value.Style) === -1) {
              cat.push(value.Style);
            }
          }
        }
      });
      color = dt = [];
      var data2 = this.options.jsonConfig.map(item => item).filter(function (value, index, self) {
        if (cat.indexOf(value.Style) > 0 && dt.indexOf(value.Style) === -1) {
          if (color.indexOf(value.ColorCode) === -1 || value.ColorCode == '') {
            color.push(value.ColorCode);
            dt.push(value.Style);
            return value;
          }
        }
      });
      return data1.concat(data2);
    },


    // <--------------------------------------------------AB Collction slider [Start] ------------------------------------------------->
    _slidercollection: function () {
      var data = this.getRandomList(),
        d = this.options.jsonConfig,
        collection = []
      var $widget = this
      $.each(data, function (key, val) {
        collection.push(val.Collection);
      })

      var uniquecollection = $widget.uniquevalue(collection)
      var gruname = {};
      for (var i = 0; i < uniquecollection.length; i++) {
        var ids = [];
        $.each(data, function (key, val) {
          if (val.Collection === uniquecollection[i] && uniquecollection[i] != '') {
            ids.push(val.GroupName);

          }
        })
        gruname[uniquecollection[i]] = $widget.uniquevalue(ids);
      }

      $.each(gruname, function (key, val) {
        for (var i = 0; i < val.length; i++) {
          var proids = [];
          $.each(data, function (prokey, proval) {
            if ((proval.Collection == key) && proval.GroupName == val[i] && val[i] != '') {
              var obj = {};
              obj["Style"] = proval.Style;
              obj["ShortDesc"] = proval.ShortDesc;
              obj["U_WImage1"] = proval.U_WImage1;
              obj["Collection"] = proval.Collection;
              obj["GroupName"] = proval.GroupName;
              proids.push(obj)
            }
          })

          gruname[key][val[i]] = proids
        }
      })
      this.options.slidercollections = gruname;
      // console.log(gruname)
      return gruname;
    },
    _gruopnamcollection: function () {
      var data = this.getRandomList()
      var logos = this.options.logos;
      var collection = []
      $.each(logos, function (key, logos) {
        collection.push(logos.name)
      });
      var ids = [];
      $.each(data, function (key, val) {
        if (val.Collection && _.contains(collection, val.Collection)) {
          ids.push(val.GroupName.replace(" ", "_"));
        }
      })
      return this.uniquevalue(ids);
    },
    getcollectionlogo: function () {
      var $widget = this
      var logos = this.options.logos;
      var result = []
      $.each(logos, function (key, logos) {
        result.push(logos.name)
      })
      return result;
    },
    /*
     * SET collection slider html.
     * owlCarousel collection logo slider.
     */
    _collectionlogoslider: function () {

      $("#user-gird-conatiner .title").css("display", "Block")
      var $widget = this
      var logos = this.options.logos;
      var html = "";
      html += '<li class="col-logo" id="All"><h1 class="collection-logo">All Collections</h1></li>'
      $.each(logos, function (key, logos) {
        html += "<li class='col-logo' id =" + logos.name + ">"
        html += logos.image ? "<img src=" + logos.image + " alt=" + logos.name + ">" :
          "<h1 class='collection-logo'>" + logos.name + "</h1>"
        html += '</li>'
      })
      // html  += '<li class="col-logo" id="All"><h1 class="collection-logo">All</h1></li>'


      var coll = $(".Collections").owlCarousel({
        loop: false,
        autoplay: false,
        margin: 10,
        nav: true,
        navText: ['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>', '<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>'],
        dots: false,
        autoWidth: true,
        responsive: {
          714: {
            nav: false,
            loop: false,
            mouseDrag: false,
            touchDrage: false,
            autoplay: false
          }
        }
      });
      coll.trigger('replace.owl.carousel', html);
      coll.trigger('refresh.owl.carousel');

      setTimeout(function () {
        // $("li#Pro").parent().addClass("pro");
        // $("li#All").parent().addClass("pro");
        // $("li#Sivvan").parent().addClass("pro");
        // $("li#Pro").css("width", "50px");                
        $widget.addgruopname($widget._gruopnamcollection())

      }, 150);
      setTimeout(function (e) {
        $(".product-group-slider").find('.group-col').first().trigger("click");
      }, 160);

    },

    /*
     * collection logo click event.
     *
     * on click set prouct collection group name.
     */

    collectionlogosclickevent: function ($this) {
      var $widget = this
      var gruopname = this.options.slidercollections;
      if ($("li#All").parent().hasClass("active_collection")) {
        activecol = [];
      }
      $this.parent().toggleClass("active_collection");
      var vid = $this.attr("id");
      if (vid != "All") {
        $("li#All").parent().removeClass("active_collection");
      } else if (vid == "All" && $("li#All").parent().hasClass("active_collection")) {
        $(".owl-item").removeClass("active_collection");
        $("li#All").parent().addClass("active_collection");
        var logos = this.getcollectionlogo();
        logos.push("Other")
        activecol = logos.reverse();
      }
      if (vid != "All" && $this.parent().hasClass("active_collection")) {
        activecol.push(vid)
        activecol = $widget.uniquevalue(activecol);
      } else {
        activecol = $.grep(activecol, function (value) {
          return value != vid;
        });
      }
      var temp = []
      $(".owl-item.active_collection.active").each(function () {
        var collectionvalue = $(this).find(".col-logo").attr("id");
        $.each(gruopname[collectionvalue], function (key, val) {
          temp.push(val.replace(" ", "_"));
        })
      })
      var unique = $widget.uniquevalue(temp);
      if (unique.length > 0) {
        $widget.addgruopname(unique)
      } else {
        var group = $widget._gruopnamcollection()
        $widget.addgruopname(group)
      }
      if (activecat.length) {
        $(".product-group-slider span").each(function () {
          var gid = $(this).attr("child-id")
          if (_.contains(activecat, gid)) {
            $(this).trigger("click");
          }
        })
      }

      if (!$(".product-group-slider span").hasClass("active")) {
        $("span.group-col[child-id = 'All']").trigger("click")
      }
      if (!$(".product-group-slider span.group-col").hasClass("active")) {
        var result = $widget.getslidervalueusingcollection();
        var datavalue = $widget.slidervalue(result)
        $widget.resetslidervalue(datavalue["html"], datavalue["items"])
      } else {
        var result = $widget.getslidervaluewithactivecat();
        var datavalue = $widget.slidervalue(result)
        $widget.resetslidervalue(datavalue["html"], datavalue["items"])
      }
    },
    addgruopname: function (unique) {
      var html = ""
      var html = "<span class='group-col' child-id='All'><h1>All</h1></span>"
      for (var i = 0; i < unique.length; i++) {
        html += "<span class='group-col' style='width: fit-content !important' child-id=" + unique[i] + "><h1>" + unique[i].replace("_", " ") + "</h1></span>"
      }

      $('.product-group-slider').css('width', jQuery('#createorder').width());
      var catslider = $(".product-group-slider").owlCarousel({
        loop: false,
        autoplay: false,
        margin: 10,
        nav: true,
        dots: false,
        autoWidth: true,
        navText: ['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>', '<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>'],
        responsive: {
          789: {
            nav: false,
            loop: false,
            mouseDrag: false,
            touchDrage: false,
            autoplay: false
          }
        }
      });
      catslider.trigger('replace.owl.carousel', html);

      var $stage = $(".product-group-slider .owl-stage"),
        stageW = $stage.width(),
        $el = $('.product-group-slider .owl-item'),
        elW = 15;

      $el.each(function () {
        elW += $(this).width() + +($(this).css("margin-right").slice(0, -2))
      });


      if (stageW < $(window).width()) {
        $stage.css('min-width', elW);
      };


      catslider.trigger('refresh.owl.carousel');

      // this.changeOrder($(".product-group-slider"));
    },
    changeOrder: function (lbl) {
      var wrapper = lbl;
      wrapper.find('.group-col').sort(function (a, b) {
        //return +a.dataset.name - +b.dataset.name;
        if ($(a).attr('child-id') > $(b).attr('child-id')) {
          return 1;
        } else {
          return -1;
        }
      }).appendTo(wrapper);
    },

    uniquevalue: function (arrry = '') {
      return arrry.filter(function (itm, i, a) {
        return i == a.indexOf(itm);
      });
    },
    /*
     * Product slider html.
     * owlcarousel slider.
     */
    productslider: function () {
      // product collection slider
      owlproduct = $('.product-slider').owlCarousel({
        loop: true,
        autoplay: true,
        // autoplayTimeout: 6000,
        margin: 0,
        rewind: true,
        touchDrage: true,
        mouseDrag: false,
        nav: true,
        dots: false,
        navText: ['<button class="flickity-button flickity-prev-next-button previous" type="button" aria-label="Previous"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>', '<button class="flickity-button flickity-prev-next-button next" type="button" aria-label="Next"><svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>'],
        responsive: {
          320: {
            items: 3
          },
          375: {
            items: 3
          },
          425: {
            items: 4
          },
          800: {
            items: 5
          }
        },
        onInitialized: fixOwl,
        onRefreshed: fixOwl,
        onChanged: callBack
      });
      var count = 0;

      function dragecall(event) {
        $(".DragPOstion").html("Dragged", count++);
      }
      var fixOwl = function () {
        var $stage = $('.product-slider'),
          stageW = $stage.width(),
          $el = $('.owl-item'),
          elW = 0;
        $el.each(function () {
          elW += $(this).width() + +($(this).css("margin-right").slice(0, -2))
        });
        if (elW > stageW) {
          $stage.width(elW);
        };
      }

      function callBack(event) {
        if ($('.product-slider .owl-item.active.sticky').length > 0) {
          $('.product-slider-sticky').remove();
        }

        if ($('.product-slider').attr('data-event') !== 'prev') {
          var owl = $('.product-slider').data('owlCarousel');
          var i = 0;
          if (!$('.product-slider .owl-item.active').next().hasClass('sticky')) {
            i = 1;
          } else {
            i = 0;
            $('.product-slider-sticky').remove()
          }
          if (i >= 1) {
            if ($('.product-slider-sticky').length < 1) {
              var content = $('.owl-item.sticky').html();
              var newdiv = $("<div class='product-slider-sticky'>");
              newdiv.css({
                'width': jQuery('.owl-item.sticky').width(),
                'left': '0',
                // 'left': 'unset'
              }).html(content);
              $('.product-slider .owl-stage').after(newdiv);
            }

          } else {
            $('.product-slider-sticky').remove();
          }
        } else {
          if ($('.product-slider .owl-item.active').prev('.sticky').length === 1) {
            $('.product-slider-sticky').remove();
          }
          $('.product-slider').attr('data-event', '')
        }

      }


    },
    //group collection click event show product collection slider
    categoryclick: function ($this) {
      var $widget = this
      var vid = $this.attr("child-id");
      if (vid == "All" && !$("span.group-col[child-id = 'All']").hasClass("active")) {
        activecat = []
        $(".product-group-slider span").removeClass("active");
      }
      $this.toggleClass("active");
      if (vid != "All" && $this.hasClass("active")) {
        $("span.group-col[child-id = 'All']").removeClass("active")
        activecat.push(vid)
        activecat = $widget.uniquevalue(activecat);
      } else {
        activecat = jQuery.grep(activecat, function (value) {
          return value != vid;
        });
      }
      if (vid == "All") {
        var value = $widget.allcategoryfilter();
        var datavalue = $widget.slidervalue(value)
        $widget.resetslidervalue(datavalue["html"], datavalue["items"])
      } else {
        var value = $widget.activeclassvalue();
        var datavalue = $widget.slidervalue(value);

        $widget.resetslidervalue(datavalue["html"], datavalue["items"])
      }
    },
    resetslidervalue: function (prohtml, items) {
      if (prohtml) {
        $(".product-slider .owl-stage").html('');
        if (items <= 3) {
          owlproduct.trigger('replace.owl.carousel', prohtml, {
            options: {
              loop: false,
              mouseDrag: false,
              touchDrage: false
            }
          });
          owlproduct.trigger('refresh.owl.carousel');
          // $(".product-slider  .owl-controls").css("display","none")

        } else {
          owlproduct.trigger('replace.owl.carousel', prohtml, {
            options: {
              mouseDrag: false,
              loop: true,
              touchDrage: true
            }
          });
          owlproduct.trigger('refresh.owl.carousel');
          owlproduct.trigger('to.owl.carousel', 0)
          $('.pro-slider .owl-stage').addClass("stop")
          setTimeout(function () {
            $('.pro-slider .owl-stage').removeClass("stop")
          }, 250)
        }
      }
    },
    allcategoryfilter: function () {
      var gruopname = this._slidercollection(),
        result = new Array();
      var gvalue = activecat.reverse();
      var cvalue = activecol.reverse();
      if (cvalue.length <= 0) {
        var logos = this.options.logos;
        var collection = []
        $.each(logos, function (key, logos) {
          collection.push(logos.name)
        });
        collection.push("Other")
        cvalue = collection;
      }
      $.each(cvalue, function (ckey, cvalue) {
        var collectionvalue = cvalue;
        $(".product-group-slider span").each(function (key, value) {
          var gid = $(this).attr("child-id").replace("_", " ");
          if (_.contains(gruopname[collectionvalue], gid)) {
            var array = gruopname[collectionvalue][gid]
            result.push(...array);
          }
        })
      })
      return result.length > 0 ? result : this.getallproductcollectionwise()
    },
    activeclassvalue: function () {
      var gruopname = this._slidercollection(),
        result = new Array();
      var gvalue = []
      var cvalue = []
      gvalue = activecat.reverse();
      cvalue = activecol;
      if (cvalue.length <= 0) {
        var logos = this.options.logos;
        var collection = []
        $.each(logos, function (key, logos) {
          collection.push(logos.name)
        });
        collection.push("Other")
        cvalue = collection;
      }
      if (!$(".product-group-slider span").hasClass("active")) {
        gvalue = []
        $(".product-group-slider span").each(function () {
          var id = $(this).attr("child-id");
          gvalue.push(id);
        })
      }
      $.each(gvalue, function (key, value) {
        var gid = value.replace("_", " ")
        $.each(cvalue, function (ckey, cvalue) {
          var collectionvalue = cvalue;
          if (_.contains(gruopname[collectionvalue], gid)) {
            var array = gruopname[collectionvalue][gid]
            result.push(...array);
          }
        })
      })
      return result
    },
    getslidervalueusingcollection: function () {
      var data = this.getRandomList(),
        gcol = activecol.reverse(),
        result = [];
      if (gcol.length > 0) {
        $.each(gcol, function (key, colvalue) {
          _.filter(data, function (value) {
            if (value['Collection'] == colvalue) {
              result.push(value)
            }
          });
        })
      }
      return result
    },
    getslidervaluewithactivecat: function () {
      var gruopname = this._slidercollection(),
        result = new Array();
      var final_array = [];
      var gvalue = activecat.reverse();
      var cvalue = activecol.reverse();
      $.each(cvalue, function (ckey, cvalue) {
        var collectionvalue = cvalue;
        $.each(gvalue, function (key, value) {
          var gid = value.replace("_", " ")
          if (_.contains(gruopname[collectionvalue], gid)) {
            var array = gruopname[collectionvalue][gid]
            result.push(...array);
          }
        })
      })
      return result
    },
    filterbysearch: function (col, cat) {
      var gruopname = this._slidercollection(),
        result = new Array();
      var temp = [];
      temp.push(col)
      var cvalue = this.getcollectionlogo();
      $.each(cvalue, function (key, val) {
        if (!_.contains(temp, val)) {
          temp.push(val)
        }
      })
      $.each(temp, function (ckey, cvalue) {
        var collectionvalue = cvalue;
        var gid = cat.replace("_", " ");
        if (_.contains(gruopname[collectionvalue], gid)) {
          var array = gruopname[collectionvalue][gid];
          result.push(...array);
        }
      });
      // return result;

      return result;
    },
    shortothercolllection: function (res) {
      var temp = []
      $.each(res, function (key, value) {
        if (value.Collection != "Other") {
          temp.push(value)
        }
      })
      $.each(res, function (key, value) {
        if (value.Collection == "Other") {
          temp.push(value)
        }
      })
      return temp
    },
    getallproductcollectionwise: function () {
      var cvalue = this.getcollectionlogo();
      cvalue.push("Other");
      var result = new Array();
      var gruopname = this._slidercollection();
      var gvalue = this._gruopnamcollection();
      $.each(cvalue, function (ckey, cvalue) {
        var collectionvalue = cvalue;
        $.each(gvalue, function (key, value) {
          var gid = value.replace("_", " ")
          if (_.contains(gruopname[collectionvalue], gid)) {
            var array = gruopname[collectionvalue][gid]
            result.push(...array);
          }
        })
      })
      return result
    },
    getCollectionlogoUrl: function (collectionname) {
      var imageurl = ""
      _.filter(this.options.logos, function (value) {
        if (value['name'] == collectionname) {
          imageurl = value['image']
        }
      });
      return imageurl;
    },
    slidervalue: function (res) {
      res = res.filter((thing, index, self) =>
        index === self.findIndex((t) => (
          t.Style === thing.Style
        ))
      )
      var sliderhtml = ''
      var obj = {}
      var $widget = this
      var baseurl = this.options.baseurl;
      $.each(res, function (key, val) {

        var logoimage = $widget.getCollectionlogoUrl(val.Collection)
        var parentid = logoimage ? "<img src=" + logoimage + " class=" + val.Collection + ">" : val.Collection + " collection",
          ItemName = val.ShortDesc ? val.ShortDesc : '',
          placeholder = baseurl + 'pub/media/catalog/product/placeholder/default/image.jpg';
        var DisPercent = val.DisPercent;
        var imagurltag = baseurl + 'pub/media/Sttl_Customerorder/discount.png';
        if (DisPercent > 0) {
          var dtag = "<img class='disctag' src='" + imagurltag + "'>"
        } else {
          dtag = ""
        };
        $('.disctag').css({
          'width': '1px !important',
          'height': '1px !important'
        });

        sliderhtml += "<div class='item product product-item' gname=" + val.GroupName + " id=" + val.Style + ">  <a class='product-item-link'> <span class='product-image-wrapper' style='padding-bottom: 133.94495412844%; width: auto;'> <img class='img-responsive owl-lazys' src='" + (val.U_WImage1 ? val.U_WImage1 : placeholder) + "' width='218' height='292' alt=" + ItemName + " /></span> </a> <div class='product details product-item-details'> <div class='show-product-dis-box'><span>" + parentid + "</span>" + dtag + "</div><div class='show-product-dis-box-more'> <span> <lable>Style No.</lable> </span> <span>" + val.Style + "</span> </div> </div> </div>";
      });

      obj["html"] = sliderhtml
      obj["items"] = res.length
      return obj;
    },
    // <---------------------------------------- AB collection slider [End] ----------------------------------------------------------->        
    // <----------------------------------------- Product info render [Start] ---------------------------------------------------------->
    messagetimeout: function (selector) {
      setTimeout(function () {
        $(selector).fadeOut(1000);
      }, 2000)
    },

    /**
     * Add a Data to Po using Option1 and use for AJAX
     *
     * @returns {{order_id: text, : bool}}
     * @private
     */
    _OnItemChange: function ($this, $widget, data, whichclick) {
      var input = $('input#show_style'),
        child_pro = this.getProductArray(input.val(), 1),
        temp_parent_sku = input.val();

      if (child_pro.length < 1) {
        $(".style-notfound").addClass("message-error error");
        $(".style-notfound").show();
        $(".style-notfound").html("Item not found.");
        $widget.messagetimeout(".style-notfound");
      } else {
        console.log("child_pro",child_pro)
                console.log("temp_parent_sku",temp_parent_sku)
        var styleconfiguration = mageTemplate(renderProductInfo, {
          data: child_pro,
          quickview_url: "#",
          parent_color: temp_parent_sku,
          customersFlatDiscount: this.options.customersFlatDiscount,
          rptswitcher: $widget._getRPTswitcherButtons(this.options.magento, temp_parent_sku, $widget),
          baseurl: this.options.baseurl
        });
        $(".cf.catalog-product-view").html(styleconfiguration);

        /* sikcy product view*/
        var current_product = $widget.getConfigurableProduct(this.options.jsonConfig, 'Style'),
          tml_finalitems = _.filter(current_product, function (item) {
            return item.Style == temp_parent_sku;
          });

        var tempcollection = [];
        $.each(this.options.logos, function (key, val) {
          tempcollection.push(val.name)
        })
        var category_collection = tml_finalitems[0].Collection;
        var category_name = tml_finalitems[0].GroupName.replace(" ", "_");
        if (whichclick == 0) {
          var result = $widget.filterbysearch(category_collection, category_name);
          var datavalue = $widget.slidervalue(result)
          $widget.resetslidervalue(datavalue["html"], datavalue["items"])
          $(".owl-item").removeClass("active_collection");
          $("li#All").parent().addClass("active_collection");
          $("span.group-col").removeClass("active");
          $("[child-id='" + category_name + "']").addClass("active");
        }

        $(".product-slider").find(".owl-item").removeClass("sticky");
        $(".product-slider").find(".owl-item .product-item").removeClass("pro_active");
        $(".product-slider").find("#" + temp_parent_sku + "").addClass("pro_active");

        var active = temp_parent_sku;

        jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').addClass("pro_active").parent().addClass('sticky');
        if (jQuery('.product-slider .owl-stage div.product[id="' + active + '"]').length > 0) {
          $('.product-slider-sticky').css({
            'right': 'unset',
            'left': '0'
          });
          if ($('.product-slider-sticky').length < 1) {
            var content = $('.owl-item.sticky').html();
            var newdiv = $("<div class='product-slider-sticky'>");
            newdiv.css({
              'width': jQuery('.owl-item.sticky').width(),
              'right': 'unset',
              'left': '0'
            }).html(content);
            $('.product-slider .owl-stage').after(newdiv);
          } else {
            $('.product-slider-sticky').remove();
            var content = $('.owl-item.sticky').html();
            var newdiv = $("<div class='product-slider-sticky'>");
            newdiv.css({
              'width': jQuery('.owl-item.sticky').width(),
              'right': 'unset',
              'left': '0'
            }).html(content);
            $('.product-slider .owl-stage').after(newdiv);
          }

        } else {
          $('.product-slider-sticky').remove();
        }

        if ($('.owl-item.sticky.active').length > 0)
          $('.product-slider-sticky').remove();

        if ($('#po_number').val() != '')
          $widget._determineProductData($('#po_number').val(), 2);
        else
          $('html, body').animate({
            scrollTop: $($(".Collections .owl-stage-outer")).offset().top,
          }, 500, 'linear')
        if ($(".product_options").find(".core-color-section").length > 0) {
          $(".customorder-color.core-color-section .swatch-option.image").first().trigger('click');
        } else {
          $(".customorder-color.fashion-color-section .swatch-option.image").first().trigger('click');
        }
        setTimeout(function(){
            if($(".owl-item.sticky.active .pro_active").length){
                $(".product-slider-sticky").remove();
            }
        },5)
      }
    },

    _getRPTswitcherButtons: function (_magentopro, _sku, $widget) {

      var petiteSku = '';
      var tailSku = '';
      var regularSku = '';
      var currentsku = _sku;
      var check = currentsku.substr((currentsku.length) - 1, 1);

      var sapconfigdata = this.options.ConfigStyle;
      var sapconfigskus = [];
      sapconfigdata.forEach(function (item, index) {
        sapconfigskus.push(item.Style);
      });

      if (check.toUpperCase() == ('P').toUpperCase() || check.toUpperCase() == ('T').toUpperCase()) {
        regularSku = currentsku.substr(0, (currentsku.length - 1))
      } else {
        regularSku = _sku;
      }

      if (tailSku == '' && petiteSku == '') {
        tailSku = regularSku + 'T';
        petiteSku = regularSku + 'P';
      }

      var available_skus = {};


      if (petiteSku != '') {
        if (_.contains(sapconfigskus, petiteSku)) {
          available_skus["petite"] = petiteSku;
        } else {
          petiteSku = '';
        }
      }


      if (tailSku != '') {
        if (_.contains(sapconfigskus, tailSku)) {
          available_skus["tall"] = tailSku;
        } else {
          tailSku = '';
        }
      }

      if (regularSku != '') {
        if (_.contains(sapconfigskus, regularSku)) {
          available_skus["regular"] = regularSku;
        } else {
          regularSku = '';
        }
      }
      return available_skus;
    },

    setInitialPositions: function (instant = 0) {
      var timeOut = 1000;
      if (instant == 1) {
        timeOut = 0;
      }
      const popup_init_pos = {
        top: (window.innerHeight / 2) - parseInt($('.qty-add-popup').find('.modal-inner-wrap').height()) / 2,
        left: (window.innerWidth / 2) - parseInt($('.qty-add-popup').find('.modal-inner-wrap').width()) / 2
      }
      setTimeout(function () {
        document.getElementsByClassName('qty-add-popup')[0].getElementsByClassName('modal-inner-wrap')[0].style.top = popup_init_pos.top + "px";
        document.getElementsByClassName('qty-add-popup')[0].getElementsByClassName('modal-inner-wrap')[0].style.left = popup_init_pos.left + "px";
      }, timeOut)
    },


    // QUANTITY ADD POPUP START
    qtyaddpopup: function () {
      var $widget = this
      var options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        modalClass: "qty-add-popup",
        modalCloseBtn: true,

        buttons: [{
          text: $.mage.__('Done'),
          click: function (event) {
            $("body").removeClass("prevent_scrolling");
            this.closeModal(event);
            $widget.setInitialPositions();
            $("body").removeClass("overflow_hidden");
          },
          class: 'style-qty-edit',
        }],

      };

      var popup = modal(options, $('#qty-add-popup-modal'));

      var table_style_sku = [];
      var table_style_qty_name = [];
      var table_style_qty_name_data = {};
      var entered_by_keyboard = false;

      $(window).resize(function () {
        $widget.setInitialPositions(1);
      });


      $(document).on("keypress", ".qty_add_num", function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode >= 48 && keycode <= 57) {
          entered_by_keyboard = true;
        }
      });

      $(document).on("click", function (event) {
        if (!jQuery(event.target).closest(".qty_add_num, .size_navigation").length) {
          entered_by_keyboard = false;
        }
      });

      var box = document.getElementsByClassName('qty-add-popup'),
        main_select = box[0].getElementsByClassName('modal-inner-wrap'),
        draggable = main_select[0],
        yl = $(".block.mobile-block-collapsible-nav").height(),
        dd = $(".modal-inner-wrap").width(),
        isMouseDown, initX, initY, height = draggable.offsetHeight,
        width = draggable.offsetWidth;
      draggable.style.position = 'absolute';
      draggable.style.left = (window.innerWidth - dd) / 2 + 'px';

      draggable.addEventListener('touchstart', function (e) {
        isMouseDown = true;
        var touchobj = e.changedTouches[0];
        initX = touchobj.clientX - this.offsetLeft;
        initY = touchobj.clientY - this.offsetTop;
      });

      draggable.addEventListener('touchmove', function (e) {
        var touchobj = e.changedTouches[0];
        if (isMouseDown) {
          var cx = touchobj.clientX - initX,
            cy = touchobj.clientY - initY;
          if (cx < 0) {
            cx = 0;
          }
          if (cy < 0) {
            cy = 0;
          }
          if (window.innerWidth - touchobj.clientX + initX < width) {
            cx = window.innerWidth - width;
          }
          if (touchobj.clientY > window.innerHeight - height - yl + initY) {
            cy = window.innerHeight - height - yl;
          }
          draggable.style.left = cx + 'px';
          draggable.style.top = cy + 'px';
        }

      });

      draggable.addEventListener('touchend', function () {
        isMouseDown = false;
        document.body.classList.remove('modal-inner-wrap');
      });


      $(document).on('click', '.checkvalue', function () {
        var ar_key = $(this).parent().find("[name^='itemscode']").val();
        var data = $(this).parents("tbody").children(":not(:first-child)");
        $("#currenstylesku").attr('value', ar_key);
        table_style_sku = [];
        table_style_qty_name = [];
        table_style_qty_name_data = {};
        data.each(function () {
          table_style_sku.push($(this).children(":nth-child(4)").find("[name^='itemscode']").val());
          table_style_qty_name.push($(this).children(":nth-child(4)").find("[name^='qty']").attr("name"));
          table_style_qty_name_data[$(this).children(":nth-child(4)").find("[name^='qty']").attr("name")] = $(this).children(":nth-child(4)").find("[name^='qty']").val();
        });
        $("#addstyleqty").attr('value', $(this).attr("name"));
        $widget.updateqtypopupdata(table_style_qty_name_data, table_style_sku);
        $widget.setInitialPositions();
        setTimeout(function () {
          $("#qty-add-popup-modal").modal("openModal");
        }, 400)
        $("body").addClass("prevent_scrolling");
      });
      $(document).on('click', '.size_navigation span', function () {
        if (entered_by_keyboard) {
          $(".qty_add_num").focus();
        }
        table_style_qty_name_data[$("#addstyleqty").attr("value")] = $(".qty_add_num").attr("value");
        var ar_key = $("#currenstylesku").attr("value");
        var curent_element_index = $.inArray(ar_key, table_style_sku);
        if ($(this).hasClass('previous_size')) {
          curent_element_index = curent_element_index - 1;
          if ((curent_element_index + 1) > 0) {
            $("#currenstylesku").attr('value', table_style_sku[curent_element_index]);
            $("#addstyleqty").attr('value', table_style_qty_name[curent_element_index]);
            $widget.updateqtypopupdata(table_style_qty_name_data, table_style_sku);
          }
        } else if ($(this).hasClass('next_size')) {
          var last_style_index = table_style_sku.length;
          curent_element_index = curent_element_index + 1;
          if (curent_element_index < last_style_index) {
            $("#currenstylesku").attr('value', table_style_sku[curent_element_index]);
            $("#addstyleqty").attr('value', table_style_qty_name[curent_element_index]);
            $widget.updateqtypopupdata(table_style_qty_name_data, table_style_sku);
          }
        }
      });
      $('.num-in span').click(function () {
        entered_by_keyboard = false;
        var $input = $(this).siblings('input.qty_add_num');

        if ($(this).hasClass('minus')) {
          var count = parseFloat($input.val()) - 1;

          count = count < 1 ? '' : count;

          if (count < 2) {
            $(this).addClass('dis');
          } else {
            $(this).removeClass('dis');
          }
          $("input.qty_add_num").attr("value", count);
        } else {
          if ($input.val() == '') {
            $input.val(0);
          }
          count = parseFloat($input.val()) + 1
          $("input.qty_add_num").attr("value", count);
          if (count > 1) {
            $(this).parents('.num-block').find(('.minus')).removeClass('dis');
          }
        }

        $input.change();
        return false;
      });

      $(document).on('touchstart click', '.modal-footer button.style-qty-edit', function () {
        var style = $("#addstyleqty").val();
        var qty = $(".qty_add_num").val();
        table_style_qty_name_data[style] = qty;

        $widget.setqtyintable(table_style_qty_name_data);

      });


      $(".qty_add_num").on("change paste keyup input", function () {
        var style = $("#addstyleqty").val();
        var qty = $(".qty_add_num").val();
        table_style_qty_name_data[style] = qty;

        $widget.setqtyintable(table_style_qty_name_data);
      });


      $(document).on("focusin", ".qty_add_num", function () {
        var isiDevice = /ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase());
        if (isiDevice) {
          this.setSelectionRange(0, jQuery(this).val().length);
        } else {
          jQuery(this).select();
        }
        entered_by_keyboard = true;
      });

      $('.qty_add_num').on('input', function (e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value == 0) {
          this.value = '';
        }
      });
    },


    setqtyintable: function (style_data) {
      var $widget = this
      var valueexist = false;
      for (var key in style_data) {
        var currentstyle_val = style_data[key];
        var qty_selector = '[name="' + key + '"]';
        // table_data += "<span>"+key +" = "+style_data[key]+"</span><br>";
        if (currentstyle_val > 0) {
          valueexist = true
          $(qty_selector).val(currentstyle_val);
        } else {
          $(qty_selector).val('');
        }
        $widget._checkvalueUpdate(qty_selector, true, $widget);
      }
      if (valueexist) {
        jQuery(".saveData").removeClass("disabled", 1000, "easeInOutBounce");
      } else {
        jQuery(".saveData").addClass("disabled", 1000, "easeOutBounce");
      }
    },
    updateqtypopupdata: function (table_style_qty_name_data, table_style_sku) {
      var $widget = this
      var ar_key = $("#currenstylesku").attr("value");
      $widget.skudatas();
      var skudata = skusdata[ar_key];
      $(".qty_for_size").html("Qty for " + skudata.Size);
      if (skudata.ActualQty != '' && skudata.ActualQty != null) {
        var Qty = parseFloat(skudata.ActualQty);
        $(".available_qty_popup").html(Qty);
      }

      var currenstyleqty = $("#addstyleqty").attr("value");
      // console.log(table_style_qty_name_data);


      if (table_style_qty_name_data[currenstyleqty] > 0 && table_style_qty_name_data[currenstyleqty] != '') {
        $(".qty_add_num").attr("value", table_style_qty_name_data[currenstyleqty]);
      } else {
        $(".qty_add_num").attr("value", '');
        $(".qty_add_num").val('');
      }


      // var date_condition = new Date(skudata.ETA);
      var dateString = skudata.ETA;
      var date = new Date(dateString.replace(/-/g, '/'));
      if (skudata.ETA != '' && skudata.ETA != null) {
        // var date = new Date(skudata.ETA);
        // console.log(date);
        if (!isNaN(date)) {
          if (!$('.eta_info').is(":visible")) {
            $('.eta_info').fadeIn();
          }
          $('.eta_info_detailed').html((date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear());
        } else {
          $('.eta_info').fadeOut(); /*Not Available*/
          $(".modal-popup.qty-add-popup.modal-slide .modal-content").css("height", "170px");
        }
      }

      var currentskuindex = $.inArray(ar_key, table_style_sku);
      var lastindex = (table_style_sku.length) - 1;
      if (currentskuindex == 0) {
        if ((table_style_sku.length) <= 1) {
          // console.log("Condition Satisfied..");
          $(".previous_size , .next_size").fadeOut("fast");
        } else {
          $(".previous_size").fadeOut("fast");
          $(".next_size").fadeIn("fast");
        }
      } else if (currentskuindex == lastindex) {
        $(".next_size").fadeOut("fast");
        $(".previous_size").fadeIn("fast");
      } else {
        $(".previous_size, .next_size").fadeIn("fast");
      }
    },
    _checkvalueUpdate: function (obj, update, $widget) {
      var $widget = this
      var qty = $(obj).val();
      var maxQty = $(obj).attr("max");
      var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
      var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
      var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();

      if (qty != '' && qty != 0) {

        var price = qty * selectprice;
        if (parseInt(qty) > parseInt(maxQty)) {
          var backqty = parseInt(qty) - parseInt(maxQty);
          $(obj).next("span").html('Backorder ' + backqty);
        } else {
          $(obj).next("span").html('');
        }
        if (update == false) {
          var colorcode = $('input[name="colorcode[' + selectcolor + '][' + selectsize + ']"').val();
          $("#qty_change_" + colorcode.replace("/", "")).val(1);
        }

        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').val(price.toFixed(2));
        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').closest('td').find('span').html('$' + $widget.convertcurrency(price.toFixed(2)));

        var savechnagestatus = $(obj).closest('table').find('.maxqtyvaldi').text().length;
        if (savechnagestatus <= 0) {
          $('.saveChng').attr('disabled', false);
        }
      } else {
        $(obj).val('')
        $(obj).next("span").html('');
        var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
        var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
        var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();
        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').val('');
        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"]').closest('td').find('span').html('');
        $(obj).next("span").html('');
      }
      $widget.showtotal($widget);
    },
    showtotal: function ($widget) {
      var $widget = this
      var unittotals = $('.sizeTable').find('.unittotal');
      var gd_total = 0;
      $(unittotals).each(function () {
        if ($(this).val() != '') {
          var total = parseFloat($(this).val());
          gd_total = gd_total + total;
        }

      });
      var totalprice = $widget.convertcurrency(parseFloat(gd_total).toFixed(2));
      $('#hi_grandtotal').val(parseFloat(gd_total).toFixed(2));
      $('.grandtotal').html('');
      $('.grandtotal').html('$' + totalprice);
    },
    convertcurrency: function (price) {
      var x = price;
      x = x.toString();
      var afterPoint = '';
      if (x.indexOf('.') > 0)
        afterPoint = x.substring(x.indexOf('.'), x.length);
      x = Math.floor(x);
      x = x.toString();
      var lastThree = x.substring(x.length - 3);
      var otherNumbers = x.substring(0, x.length - 3);
      if (otherNumbers != '')
        lastThree = ',' + lastThree;
      return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;

    },
    // <----------------------------------------- Product info render [End] ---------------------------------------------------------->
    _ScrollAnimate: function ($this, time) {
      var scroll_top = 0;
      if ($this.attr("class").includes("collectionTabs")) {
        scroll_top = $this.position().top + 70;
      } else {
        scroll_top = $("#user-gird-conatiner").position().top + 140;
      }
      $('.new-container.column.main').animate({
          scrollTop: scroll_top,
        },
        time)
    },
    _renderLineitembeforeAJAX: function ($this, _current_options, styles = {}, click_event = '') {
      var $widget = $this,
        orderdata = '',
        allorderitems = $widget._getOrderDataItems($widget, orderdata, _current_options, styles);
      if (finalitems.length > 0) {
        var lineitem_temp = mageTemplate(lineitemrenderer, {
          finalorderrendere: allorderitems,
          currencyconvert: $.proxy(this._convertcurrency, this),
          ordersummary: $widget._getorderSummaryinfo($widget, orderdata),
          baseurl: this.options.baseurl
        });
        $(".line-item").html(lineitem_temp);
        $('span#polist').css('display','none')
        $('div#po_numberautocomplete-list').hide();
        $('input#po_number').css('pointer-events','none')
      } else {
        $(".line-item").html("");
      }
    },

    _renderLineitemAfterAJAX: function ($widget, response, current_options) {

      var allorderitems = $widget._getOrderDataItems($widget, response, current_options);
      $widget._getorderSummaryinfo($widget, response);
      if (finalitems.length > 0) {
        var lineitem_temp = mageTemplate(lineitemrenderer, {
          finalorderrendere: allorderitems,
          currencyconvert: $.proxy(this._convertcurrency, this),
          ordersummary: $widget._getorderSummaryinfo($widget, response)
        });
        $(".line-item").html(lineitem_temp);
      } else {
        $(".line-item").html("");
      }
    },

    _getorderSummaryinfo: function ($widget, _ordersummary) {
      var ordersummary = '';
      if (_ordersummary != '') {
        ordersummary = _ordersummary.line_item_render.ordersummary;
      } else {
        ordersummary = $widget._generateordertotalarray($widget);
      }
      return ordersummary;
    },
    _DatabyStyle: function () {

      var data = this.options.jsonConfig;
      var temp_databyStyle = {};

      data.forEach(function (item, index) {
        var sizegroup = item.SizeGroup;
        temp_databyStyle[sizegroup] = {};
      });

      data.forEach(function (item, index) {
        var sizegroup = item.SizeGroup;
        var sizeorder = item.SizeOrder;
        var size = item.Size;
        temp_databyStyle[sizegroup][sizeorder] = size;
      });

      return temp_databyStyle;
    },
    _stylebyInventory: function () {
      var data = this.options.jsonConfig,
        temp_items = [];

      data.forEach(function (item, index) {
        var style = item.Style;
        temp_items[style] = item;
      });

      return temp_items;
    },
    _getItemfromOrderList: function (sku) {
      var tml_finalitems = _.filter(finalitems, function (item) {
        return item.ItemCode == sku;
      });
      return tml_finalitems;
    },
    _getOrderDataItems: function ($this, _response, _current_options, styles = {}) {

      var $widget = $this,
        response = _response,
        mainresponce = {},
        style = '',
        submitcolor = '',
        viewmode = '',
        stylebyInventory = $widget._stylebyInventory(),
        data = this.options.jsonConfig;


      // var order_id = response.base64_order_id;
      if ($widget._DatabyStyle() && $widget._stylebyInventory()) {
        var databyStyle = $widget._DatabyStyle();

        var allorderdata = '';
        if (response != '') {
          allorderdata = response.line_item_render.allorderdata;
        } else {
          allorderdata = $widget._generateqtyarray(_current_options, $widget, styles);
        }

        var tmp_distinstyle = allorderdata.map(function (item) {
          return item.Style;
        });

        const uniqueArray = [...new Set(tmp_distinstyle)];

        var distinstyle = uniqueArray;
        var sizegrouparray = {};
        if (distinstyle) {
          distinstyle.forEach(function (item, index) {
            if (item in stylebyInventory) {
              var stylesize = stylebyInventory[item].SizeGroup;
              sizegrouparray[stylesize] = {};
            }
          });
          var count = 0;
          distinstyle.forEach(function (item, index) {
            if (item in stylebyInventory) {
              var stylesize = stylebyInventory[item].SizeGroup;
              sizegrouparray[stylesize][count] = stylebyInventory[item].Style;
              count++;
            }
          });
          for (var index in sizegrouparray) {

            var item_size = sizegrouparray[index];
            var groupstyle = item_size;
            var current_style = 'viewtype' + index;
            var datastyle_index = databyStyle.index;
            allorderdata.forEach(function (item, index) {
              if (item.Type != 'gift') {
                mainresponce[current_style] = {};
              }
            });

            allorderdata.forEach(function (item, index) {
              if (item.Type != 'gift') {
                for (var index_a in item_size) {
                  var stylegroup = item_size[index_a]
                  if (stylegroup == item.Style) {
                    mainresponce[current_style][stylegroup] = {};
                  }
                }
              }
            });
            allorderdata.forEach(function (item, index) {
              if (item.Type != 'gift') {
                for (var index_a in item_size) {
                  var stylegroup = item_size[index_a];
                  var colorcode = item.ColorCode;
                  if (stylegroup == item.Style) {
                    mainresponce[current_style][stylegroup][colorcode] = {};
                  }
                }
              }
            });
            var order_item_count = 0;
            allorderdata.forEach(function (item, index) {
              if (item.Type != 'gift') {
                for (var index_a in item_size) {
                  var stylegroup = item_size[index_a];
                  var colorcode = item.ColorCode;
                  if (stylegroup == item.Style) {
                    mainresponce[current_style][stylegroup][colorcode][order_item_count] = item;
                    order_item_count++;
                  }
                }
              }
            });

          }
        }

      }

      // console.log(mainresponce);

      return mainresponce;
    },

    _generateqtyarray: function (_current_options, $widget, styles = {}) {
      removedskus = [];
      var data_selector = $('.colorContainer').find('.checkvalue');
      var current_options = _current_options;
      item_edited = false;
      if (current_options == 1 && current_options != 0 && current_options != '') {
        $(data_selector).each(function () {
          var count = 0;
          if ($(this).val() != '') {
            var itemcode = $(this).attr("itemscode"),
              orderdata = $widget.getProductArray(itemcode, 0),
              selectcolor = orderdata[0].Color,
              selectsize = orderdata[0].Size,
              base_price = '',
              disprice = '',
              added_qty = $(this).val(),
              order_item = $widget._getItemfromOrderList(itemcode),
              customersFlatDiscount = $widget.options.customersFlatDiscount,
              cstomer_price_group = customersFlatDiscount[0].PriceList;

            if (cstomer_price_group == 'Institutional Price List') {
              base_price = orderdata[0].InsPrice;
              disprice = orderdata[0].InsPrice;
            } else {
              base_price = orderdata[0].UnitPrice;
              disprice = orderdata[0].DisPrice;
            }
            if (order_item.length > 0) {
              if (order_item[0].QTYOrdered !== added_qty) {
                item_edited = true;
              }
            } else {
              item_edited = true;
            }
            var pafterdiscount = parseFloat();
            var pbeforediscount = added_qty * base_price;
            pbeforediscount = parseFloat(pbeforediscount);

            if (disprice < base_price) {
              pafterdiscount = added_qty * disprice;
            } else {
              pafterdiscount = added_qty * base_price;
            }

            var tmpitem = {};

            var current_itemcode = $(this).attr("itemscode");

            tmpitem = {
              ColorCode: orderdata[0].ColorCode,
              ItemCode: itemcode,
              ColorStatus: orderdata[0].ColorStatus,
              DiscountPer: orderdata[0].DisPercent,
              DiscountPrice: orderdata[0].DisPrice,
              OrderOption: "1",
              PriceAfterDiscount: '' + pafterdiscount + '',
              QTYOrdered: added_qty,
              Size: selectsize,
              Style: orderdata[0].Style,
              StyleStatus: orderdata[0].StyleStatus,
              TotalPrice: '' + pafterdiscount + '',
              UnitPrice: base_price,
              PriceBeforeDiscount: '' + pbeforediscount + '',
              Color: selectcolor,
              Type: "normal"

            };

            var itemcodeexistinarray = false;
            var array_index = -1;

            finalitems.forEach(function (item, index) {
              if (item.ItemCode == current_itemcode && itemcodeexistinarray == false) {
                itemcodeexistinarray = true;
                array_index = index;
              }
            });

            if (itemcodeexistinarray) {
              if (added_qty > 0) {
                finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                finalitems[array_index].QTYOrdered = added_qty;
                finalitems[array_index].TotalPrice = pafterdiscount;
              } else {
                var tml_finalitems = _.filter(finalitems, function (item) {
                  if (item.ItemCode == current_itemcode) {
                    removedskus.push(current_itemcode);
                  }
                  return item.ItemCode !== current_itemcode
                });
                finalitems = tml_finalitems;
              }
            } else {
              if (added_qty > 0) {
                finalitems.push(tmpitem);
              }
            }
            $widget._checkvalueUpdate($(this), true);

          }
        });
      }
      if (current_options == 5 && current_options != 0 && current_options != '') {
        $.each(styles, function (key, value) {
          var count = 0;
          if (value.qty != '') {
            var itemcode = value.itemscode,
              orderdata = $widget.getProductArray(itemcode, 0),
              selectcolor = orderdata[0].Color,
              selectsize = orderdata[0].Size,
              base_price = '',
              disprice = orderdata[0].DisPrice,
              added_qty = value.qty,
              order_item = $widget._getItemfromOrderList(itemcode),
              customersFlatDiscount = $widget.options.customersFlatDiscount,
              cstomer_price_group = customersFlatDiscount[0].PriceList;

            if (cstomer_price_group == 'Institutional Price List') {
              base_price = orderdata[0].InsPrice;
            } else {
              base_price = orderdata[0].UnitPrice;
            }
            if (order_item.length > 0) {
              if (order_item[0].QTYOrdered !== added_qty) {
                item_edited = true;
              }
            } else {
              item_edited = true;
            }
            var pafterdiscount = parseFloat();
            var pbeforediscount = added_qty * base_price;
            pbeforediscount = parseFloat(pbeforediscount);

            if (disprice < base_price) {
              pafterdiscount = added_qty * disprice;
            } else {
              pafterdiscount = added_qty * base_price;
            }

            var tmpitem = {};

            var current_itemcode = value.itemscode;

            tmpitem = {
              ColorCode: orderdata[0].ColorCode,
              ItemCode: itemcode,
              ColorStatus: orderdata[0].ColorStatus,
              DiscountPer: orderdata[0].DisPercent,
              DiscountPrice: orderdata[0].DisPrice,
              OrderOption: "1",
              PriceAfterDiscount: '' + pafterdiscount + '',
              QTYOrdered: added_qty,
              Size: selectsize,
              Style: orderdata[0].Style,
              StyleStatus: orderdata[0].StyleStatus,
              TotalPrice: '' + pafterdiscount + '',
              UnitPrice: base_price,
              PriceBeforeDiscount: '' + pbeforediscount + '',
              Color: selectcolor,
              Type: "normal"

            };

            var itemcodeexistinarray = false;
            var array_index = -1;

            finalitems.forEach(function (item, index) {
              if (item.ItemCode == current_itemcode && itemcodeexistinarray == false) {
                itemcodeexistinarray = true;
                array_index = index;
              }
            });

            if (itemcodeexistinarray) {
              if (added_qty > 0) {
                finalitems[array_index].PriceAfterDiscount = pafterdiscount;
                finalitems[array_index].QTYOrdered = added_qty;
                finalitems[array_index].TotalPrice = pafterdiscount;
              } else {
                var tml_finalitems = _.filter(finalitems, function (item) {
                  if (item.ItemCode == current_itemcode) {
                    removedskus.push(current_itemcode);
                  }
                  return item.ItemCode !== current_itemcode
                });
                finalitems = tml_finalitems;
              }
            } else {
              if (added_qty > 0) {
                finalitems.push(tmpitem);
              }
            }
          }
        });
      }
      return finalitems;
    },
    _checkvalueUpdate: function (obj, update) {
      var $widget = this;
      var qty = $(obj).val();
      var maxQty = $(obj).attr("max");
      var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
      var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
      var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();

      if (qty != '') {

        var price = qty * selectprice;
        if (parseInt(qty) > parseInt(maxQty)) {
          var backqty = parseInt(qty) - parseInt(maxQty);
          $(obj).next("span").html('Backorder ' + backqty);
        } else {
          $(obj).next("span").html('');
        }
        if (update == false) {
          var colorcode = $('input[name="colorcode[' + selectcolor + '][' + selectsize + ']"').val();
          $("#qty_change_" + colorcode.replace("/", "")).val(1);
        }

        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"').val(price.toFixed(2));
        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"').closest('td').find('span').html('$' + $widget._convertcurrency(price.toFixed(2)));
        var savechnagestatus = $(obj).closest('table').find('.maxqtyvaldi').text().length;
        if (savechnagestatus <= 0) {
          $('.saveChng').attr('disabled', false);
        }
      } else {
        $(obj).val('')
        $(obj).next("span").html('');
        var selectprice = $(obj).closest('td').find('input[type=hidden]').val();
        var selectcolor = $(obj).closest('td').find('input[name=selectcolor]').val();
        var selectsize = $(obj).closest('td').find('input[name=selectsize]').val();
        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"').val('');
        $('input[name="inpprice[' + selectcolor + '][' + selectsize + ']"').closest('td').find('span').html('');
        $(obj).next("span").html('');
      }
      $widget._showtotal();
    },
    _showtotal: function () {
      var $widget = this;
      var unittotals = $('.sizeTable').find('.unittotal');
      var gd_total = 0;
      $(unittotals).each(function () {
        if ($(this).val() != '') {
          var total = parseFloat($(this).val());
          gd_total = gd_total + total;
        }

      });
      var totalprice = $widget._convertcurrency(parseFloat(gd_total).toFixed(2));
      $('#hi_grandtotal').val(parseFloat(gd_total).toFixed(2));
      $('.grandtotal').html('');
      $('.grandtotal').html('$' + totalprice);
    },

    _generateordertotalarray: function ($widget) {
      var data_selector = $('.colorContainer').find('.checkvalue');


      var customersFlatDiscount = $widget.options.customersFlatDiscount;
      customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;
      var beforebulkdiscount = customersFlatDiscount;
      var sellingprice = 0;
      var discountAmount = 0;

      var customrsbulkdiscount = $widget.options.customersBulcDiscount;

      var DocTotalQty = 0;

      finalitems.forEach(function (item, index) {
        sellingprice = parseFloat(sellingprice) + parseFloat(item.TotalPrice);
        DocTotalQty = parseInt(DocTotalQty) + parseInt(item.QTYOrdered);
      });


      var bulkdiscount = 0;

      if (customrsbulkdiscount.length > 0) {
        customrsbulkdiscount.forEach(function (item, index) {
          var bulkqtyfrom = item.QtyFrom;
          var bulkqtyto = item.QtyTo;
          if (DocTotalQty >= bulkqtyfrom && DocTotalQty <= bulkqtyto) {
            bulkdiscount = item.Discount;
          }
        });
      }

      var totalpriceordered = sellingprice;
      customersFlatDiscount = parseFloat(customersFlatDiscount) + parseFloat(bulkdiscount);
      if (customersFlatDiscount > 0) {
        sellingprice = sellingprice - (sellingprice * (customersFlatDiscount / 100));
        discountAmount = totalpriceordered * (customersFlatDiscount / 100);
      }

      ordertotaldata = {
        TotalBeforeDiscount: totalpriceordered,
        DiscountAmount: discountAmount,
        DiscountPer: customersFlatDiscount,
        DocTotal: sellingprice,
        TotalDiscountPer: 0,
        TotalDiscountAmount: 0,
        TotalQtyOrdered: DocTotalQty,
        FlatDiscount: beforebulkdiscount,
      }


      return ordertotaldata;
    },
    _isNormalInteger: function (str) {
      try {
        str = str.replace(/\s+/g, '');
        if (str != '') {
          str = str.toString();
          str = str.trim();
          if (!str) {
            return false;
          }
          str = str.replace(/^0+/, "") || "0";
          var n = Math.floor(Number(str));
          return String(n) === str && n >= 0;
        } else {
          return false;
        }
      } catch (err) {
        return false
      }
    },
    _convertcurrency: function (price) {
      var x = price;

      x = x.toString();
      var afterPoint = '';
      if (x.indexOf('.') > 0)
        afterPoint = x.substring(x.indexOf('.'), x.length);
      x = Math.floor(x);
      x = x.toString();
      var lastThree = x.substring(x.length - 3);
      var otherNumbers = x.substring(0, x.length - 3);
      if (otherNumbers != '')
        lastThree = ',' + lastThree;
      return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;
    },
    _changeemptyurl: function () {
      if (typeof (history.pushState) != "undefined") {
        var nexturl = this.options.baseurl + 'customerorder/customer/neworder',
          obj = {
            Title: 'orderpage',
            Url: nexturl
          };
        history.pushState(obj, obj.Title, obj.Url);
      } else
        alert("Browser does not support HTML5.");

    },
    _convertcurrencytofloat: function (price) {
      var money = price,
        firstsplit = money.split(","),
        lastinde = firstsplit[firstsplit.length - 1].split("."),
        finalmoney = '';
      for (var i = 0; i < firstsplit.length - 1; i++) {
        finalmoney = finalmoney + firstsplit[i]
      }
      finalmoney = finalmoney + lastinde[0] + '.' + lastinde[1];
      // finalmoney = parseFloat(finalmoney);
      return finalmoney;
    },
    _updatetmpOrderData: function ($widget, _response) {
      var allorderdata = _response;
      finalitems = [];

      if (allorderdata.line_item_render.allorderdata.length <= 0) {
        $widget._changeemptyurl();
        window.location.reload(true);
        return false;
      }

      var orderitemsdata = allorderdata.line_item_render.allorderdata;
      var ordersummarydata = allorderdata.line_item_render.ordersummary;

      orderitemsdata.forEach(function (item, index) {
        var tmpitem = {},
          pbeforediscount = item.QTYOrdered * item.UnitPrice,
          pbeforediscount = parseFloat(pbeforediscount),
          product_type = 'normal';

        if (item.ColorCode == '') {
          product_type = 'gift';

        }

        tmpitem = {
          ColorCode: item.ColorCode,
          ItemCode: item.ItemCode,
          ColorStatus: item.ColorStatus,
          DiscountPer: item.DiscountPer,
          DiscountPrice: item.DiscountPrice,
          OrderOption: item.OrderOption,
          PriceAfterDiscount: $widget._convertcurrencytofloat(item.PriceAfterDiscount),
          QTYOrdered: item.QTYOrdered,
          Size: item.Size,
          Style: item.Style,
          StyleStatus: item.StyleStatus,
          TotalPrice: $widget._convertcurrencytofloat(item.TotalPrice),
          UnitPrice: item.UnitPrice,
          PriceBeforeDiscount: '' + pbeforediscount + '',
          Color: item.ColorName,
          Type: product_type
        };

        finalitems.push(tmpitem);
      });

      var customersFlatDiscount = $widget.options.customersFlatDiscount;
      customersFlatDiscount = customersFlatDiscount[0].FlatDiscount;
      var beforebulkdiscount = customersFlatDiscount;

      ordertotaldata = {
        TotalBeforeDiscount: ordersummarydata.TotalBeforeDiscount,
        DiscountAmount: ordersummarydata.DiscountAmount,
        DiscountPer: ordersummarydata.DiscountPer,
        DocTotal: ordersummarydata.DocTotal,
        TotalDiscountPer: ordersummarydata.TotalDiscountPer,
        TotalDiscountAmount: ordersummarydata.TotalDiscountAmount,
        TotalQtyOrdered: ordersummarydata.TotalQTYUnits,
        FlatDiscount: beforebulkdiscount,
      }
    },
    _changeurl: function (base64_order_id, base64_ncp_id) {
      var order_id = $('#order_id').val();
      if (order_id != '') {
        if (typeof (history.pushState) != "undefined") {
          var nexturl = this.options.baseurl + 'customerorder/customer/neworder/id/' + base64_order_id + '/ncp/' + base64_ncp_id,
            obj = {
              Title: 'orderpage',
              Url: nexturl
            };
          history.pushState(obj, obj.Title, obj.Url);
        } else
          alert("Browser does not support HTML5.");
      }
    },
    _optiononeAdddata: function ($widget, current_options, Styles = {}) {
      var is_savedata = 'true',
        req_url = this.options.baseurl + 'customerorder/customer/optiontwojs',
        customerdata = JSON.stringify($widget.options.customersFlatDiscount),
        nextstep = $('#nextstep').val();
      // $(".cf.loaderAdd").children().slideDown(300);
      $widget.showsuccesspopup("Item Successfully Added To PO");
      setTimeout(function () {
        $widget.closeposuccesspopup();
      }, 2000);

      $widget._renderLineitembeforeAJAX($widget, current_options, Styles);

      // $(".cf.loaderAdd").children().slideUp(300);

      var tmp_ordertotaldata = JSON.stringify(ordertotaldata),
        tmp_finalitems = JSON.stringify(finalitems),
        po_number = $("#po_number").val(),
        order_id = $("#order_id").val(),
        base64_order_id = $("#base64_order_id").val(),
        base64_ncp_id = $("#base64_ncp_id").val();
      $(".colorContainer .checkvalue, .colorContainer .unittotal").val('');
      $(".colorContainer .showprice, .maxqtyvaldi").html('');
      $(".saveData").addClass("disabled", 1000, "easeOutBounce");
      // $('html, body').animate({scrollTop: $("#nav-tabContent").offset().top - 80, }, 700, 'linear')
      // $widget._ScrollAnimate($("#nav-tabContent"), 700)
      $.ajax({
        url: req_url,
        type: "POST",
        data: {
          po_number: po_number,
          is_savedata: is_savedata,
          order_id: order_id,
          base64_order_id: base64_order_id,
          base64_ncp_id: base64_ncp_id,
          customerdata: customerdata,
          allorderitemdata: tmp_finalitems,
          deletedsku: removedskus,
          ordersummary: tmp_ordertotaldata,
        },
        showLoader: false,
        cache: false,
        success: function (response) {
          if (response.session_distroy) {
            $('body').trigger('processStart');
            window.location.reload(true);
            return false;
          }

          if (!response.errors) {
            if (response.order_id)
              $("#order_id").val(response.order_id);

            if (response.base64_order_id)
              $("#base64_order_id").val(response.base64_order_id);

            if (response.base64_ncp_id)
              $("#base64_ncp_id").val(response.base64_ncp_id);
          }
          if (response.errors) {

            $widget._updatetmpOrderData($widget, response);
            var lineitem_temp = $widget._renderLineitemAfterAJAX($widget, response, current_options);
            $(".cf.line-item").fadeIn(300);
            $(".line-item .orderListing").html(lineitem_temp);

            if (response.message)
              $widget.adderror(response.message)
            $widget._changeurl(response.base64_order_id, response.base64_ncp_id);

            if (lineitem_temp)
              $(".cf.delOrdLink").fadeIn(300);

            $("#is_edit_order").attr("value", "0");
          }

          if (response.success) {
            if (response.base64_order_id && response.base64_ncp_id)
              $widget._changeurl(response.base64_order_id, response.base64_ncp_id);
            $("#is_edit_order").attr("value", "0");
          }
          if (nextstep == 'continuetopayment') {
            $('#nextstep').val('');
            $widget._Contopayment();
          }
          if (nextstep == 'saveasdraft') {
            $('#nextstep').val('');
            $widget._SaveasDraft();
          }

        }
      });

      return true;
    },
    qtyeditpopup: function () {
      var $widget = this;
      var options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        modalClass: "qty-popup",
        modalCloseBtn: true,
        buttons: [{
          text: $.mage.__('Done'),
          click: function (event) {
            $("body").removeClass("prevent_scrolling");
            this.closeModal(event);
          },
          class: 'line-item-edit',
        }],

      };

      var popup = modal(options, $('#popup-modal'));


      var edit_table_style_sku = [];
      var edit_table_style_qty_name = [];
      var edit_table_style_qty_name_data = {};
      var edit_table_data = {};
      var entered_by_keyboard = false;
      var non_edited_data = '';
      var active_table_index = '';

      $(document).on("keypress", ".in-num.qty_num", function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode >= 48 && keycode <= 57) {
          entered_by_keyboard = true;
        }
      });

      $(document).on("click", function (event) {
        if (!$(event.target).closest(".in-num.qty_num, .edit_size_navigation").length) {
          entered_by_keyboard = false;
        }
      });

      var box = document.getElementsByClassName('qty-popup'),
        main_select = box[0].getElementsByClassName('modal-inner-wrap'),
        draggable = main_select[0],
        yl = $(".block.mobile-block-collapsible-nav").height(),
        dd = $(".modal-inner-wrap").width(),
        isMouseDown, initX, initY, height = draggable.offsetHeight,
        width = draggable.offsetWidth;
      draggable.style.position = 'absolute';
      draggable.style.left = (window.innerWidth - dd) / 2 + 'px';

      draggable.addEventListener('touchstart', function (e) {
        isMouseDown = true;
        var touchobj = e.changedTouches[0];
        initX = touchobj.clientX - this.offsetLeft;
        initY = touchobj.clientY - this.offsetTop;
      });

      draggable.addEventListener('touchmove', function (e) {
        var touchobj = e.changedTouches[0];
        if (isMouseDown) {
          var cx = touchobj.clientX - initX,
            cy = touchobj.clientY - initY;
          if (cx < 0) {
            cx = 0;
          }
          if (cy < 0) {
            cy = 0;
          }
          if (window.innerWidth - touchobj.clientX + initX < width) {
            cx = window.innerWidth - width;
          }
          if (touchobj.clientY > window.innerHeight - height - yl + initY) {
            cy = window.innerHeight - height - yl;
          }
          draggable.style.left = cx + 'px';
          draggable.style.top = cy + 'px';
        }

      });

      draggable.addEventListener('touchend', function () {
        isMouseDown = false;
        document.body.classList.remove('modal-inner-wrap');
      });

      $(document).on('click', '.Qty_popup .size_item_edit_ic', function () {
        var checkinputval = $('.swatch-attribute-options.clearfix').find('.checkvalue'),
          valIsExists = false;

        $(checkinputval).each(function () {
          if ($(this).val() != '') {
            valIsExists = true;
          }
        });

        var is_qty_change = 0,
          prev_obj_id = $(".swatch-attribute-options.clearfix").find('.tab-pane.fade.active').attr("id");

        if (typeof prev_obj_id !== "undefined") {
          is_qty_change = $("#qty_change_" + prev_obj_id.replace("/", "")).val();
        }
        var opt = {
          autoOpen: false
        };

        if (valIsExists == 1 && is_qty_change == 1) {
          var getColorCode = '';
          if ($(".swatch-option.image.active").length > 0)
            getColorCode = $(".swatch-option.image.active").attr('option-color-code');
          $('#nextstep').val('continuetopayment');
          var theDialog = $("#removeUser").dialog(opt);
          $("#contopaymentredirect").val('1');
          theDialog.dialog("open");
          return false;
        }
        $("body").addClass("prevent_scrolling");
        $(".qty_num").attr('value', $(this).parent().attr("edit-qty"));
        $("#popup-modal #selectcolor").attr('value', $(this).parent().attr("edit-color"));
        $("#popup-modal #showprice").attr('value', $(this).parent().attr("edit-unitprice"));
        $("#popup-modal #mainprice").attr('value', $(this).parent().attr("edit-unitprice"));
        $("#popup-modal #DiscountPer").attr('value', $(this).parent().attr("edit-discountprice"));
        $("#popup-modal #selectsize").attr('value', $(this).parent().attr("edit-style"));
        $("#popup-modal #itemscode").attr('value', $(this).parent().attr("edit-itemcode"));
        $("#popup-modal #id").attr('value', $(this).parent().attr("edit-id"));
        $("#popup-modal #qty_num").attr('value', $(this).parent().attr(""));
        $("#popup-modal #selectsize").attr('value', $(this).parent().parents(".row-data-toggle").attr("row-style"));
        $("#popup-modal #colorcode").attr('value', $(this).parent().parents(".row-data-toggle").attr("row-color"));
        $("#popup-modal #in-num.size").attr('value', $(this).parent().siblings().html());


        var data = $(this).parents("tbody.row-data-toggle").children();

        edit_table_style_sku = [];

        edit_table_style_qty_name_data = {};
        edit_table_data = {};

        non_edited_data = '';
        var i = 0;
        data.each(function () {
          edit_table_style_sku.push($(this).children(":nth-child(3)").attr("edit-itemcode"));
          edit_table_style_qty_name_data[$(this).children(":nth-child(3)").attr("edit-itemcode")] = $(this).children(":nth-child(3)").attr("edit-qty");
          edit_table_data[i] = {
            is_qtyedit: true,
            DiscountPer: $(this).children(":nth-child(3)").attr("edit-discountprice"),
            colorcode: $(this).children(":nth-child(2)").html(),
            itemscode: $(this).children(":nth-child(3)").attr("edit-itemcode"),
            selectsize: $(this).children(":nth-child(3)").attr("edit-style"),
            selectcolor: $(this).children(":nth-child(3)").attr("edit-color"),
            showprice: $(this).children(":nth-child(3)").attr("edit-unitprice"),
            order_id: $("#order_id").val(),
            id: $(this).children(":nth-child(3)").attr("edit-id"),
            qty: $(this).children(":nth-child(3)").attr("edit-qty")
          }
          i++;
        });

        non_edited_data = JSON.parse(JSON.stringify(edit_table_data));;

        // $("#addstyleqty").attr('value',$(this).attr("name"));

        updateqtypopupdata();

        active_table_index = $(this).parents("[data-table-index]").attr("data-table-index");
        // console.log(active_table_index);
        $("#popup-modal").modal("openModal", function () {
          $("body").addClass("prevent_scrolling");
        });

      });

      $(document).on('click', '.edit_size_navigation span', function () {
        if (entered_by_keyboard) {
          $(".in-num.qty_num").focus();
        }
        edit_table_style_qty_name_data[$("#itemscode").attr("value")] = $(".in-num.qty_num").attr("value");
        var ar_key = $("#itemscode").attr("value");
        var curent_element_index = $.inArray(ar_key, edit_table_style_sku);
        edit_table_data[curent_element_index].qty = $(".in-num.qty_num").attr("value");
        if ($(this).hasClass('previous_size')) {
          curent_element_index = curent_element_index - 1;
          if ((curent_element_index + 1) > 0) {
            $("#itemscode").attr('value', edit_table_style_sku[curent_element_index]);
            // $("#addstyleqty").attr('value',edit_table_style_qty_name[curent_element_index]);
            updateqtypopupdata();
          }
        } else if ($(this).hasClass('next_size')) {
          var last_style_index = edit_table_style_sku.length;
          curent_element_index = curent_element_index + 1;
          if (curent_element_index < last_style_index) {
            $("#itemscode").attr('value', edit_table_style_sku[curent_element_index]);
            updateqtypopupdata();
          }
        }
      });
      $(document).ready(function () {
        $('.num-in span').click(function () {
          entered_by_keyboard = false;
          var $input = $(this).siblings('input.qty_num');
          if ($(this).hasClass('minus')) {
            var count = parseFloat($input.val()) - 1;


            if ($(".orderList.mobile.lineItemsList tbody tr#togglebutton").length <= 1) {
              if ($(".toggletable table.exp-line-item .row-data-toggle tr").length <= 1) {
                count = count < 1 ? 1 : count;
              } else {
                count = count < 1 ? 0 : count;
              }
            } else {
              count = count < 1 ? 0 : count;
            }

            if (count < 2) {
              $(this).addClass('dis');
            } else {
              $(this).removeClass('dis');
            }
            $("input.qty_num").attr("value", count);
          } else {
            var count = parseFloat($input.val()) + 1
            $("input.qty_num").attr("value", count);
            if (count > 1) {
              $(this).parents('.num-block').find(('.minus')).removeClass('dis');
            }
          }

          $input.change();
          return false;
        });

      });

      function updateqtypopupdata() {
        // console.log(edit_table_data);
        if ($(".orderList.mobile.lineItemsList tbody tr#togglebutton").length <= 1) {
          if ($(".toggletable table.exp-line-item .row-data-toggle tr").length <= 1) {
            $(".user_note_for_qty").hide();
          } else {
            $(".user_note_for_qty").show();
          }
        } else {
          $(".user_note_for_qty").show();
        }
        var ar_key = $("#itemscode").attr("value");
        $widget.skudatas();
        var skudata = skusdata[ar_key];
        // console.log(skudata);
        //POPUP SIZE LABEL START
        $(".qty_for_size").html("Qty for " + skudata.Size);
        $(".current_note_size").html(skudata.Size);
        if (skudata.ActualQty != '' && skudata.ActualQty != null) {
          var Qty = parseFloat(skudata.ActualQty);
          $(".available_qty_popup").html(Qty);
        }
        //POPUP SIZE LABEL START

        //POPUP QTY START
        var currenstyleqty = $("#itemscode").attr("value");
        if (edit_table_style_qty_name_data[currenstyleqty] > 0 && edit_table_style_qty_name_data[currenstyleqty] != '') {
          $(".in-num.qty_num").attr("value", edit_table_style_qty_name_data[currenstyleqty]);
        } else {
          $(".in-num.qty_num").attr("value", '');
          $(".in-num.qty_num").val('');
        }
        //POPUP QTY END

        //POPUP ETA DATE START
        var date_condition = new Date(skudata.ETA),
          dateString = skudata.ETA;
        var date = new Date(dateString.replace(/-/g, '/'));
        if (skudata.ETA != '' && skudata.ETA != null) {
          if (!isNaN(date)) {
            if (!$('.eta_info').is(":visible")) {
              $('.eta_info').fadeIn();
            }
            $('.eta_info_detailed').html((date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear());
          } else {
            $('.eta_info').fadeOut(); /*Not Available*/
          }
        }
        //POPUP ETA DATE END

        //HIDE OR SHOW NEXT & PREVIEOUS ICON START
        var currentskuindex = $.inArray(ar_key, edit_table_style_sku);
        var lastindex = (edit_table_style_sku.length) - 1;

        if (currentskuindex == 0) {
          if ((edit_table_style_sku.length) <= 1) {
            $(".previous_size , .next_size").fadeOut("fast");
          } else {
            $(".previous_size").fadeOut("fast");
            $(".next_size").fadeIn("fast");
          }
        } else if (currentskuindex == lastindex) {
          $(".next_size").fadeOut("fast");
          $(".previous_size").fadeIn("fast");
        } else {
          $(".previous_size, .next_size").fadeIn("fast");
        }
        //HIDE OR SHOW NEXT & PREVIEOUS ICON END
      }


      $(".action-close").click(function () {
        $("#popup-modal").modal("closeModal");
      });

      $('.in-num.qty_num').on('input', function (e) {
        // console.log(this.value);
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value == 0) {
          // console.log("aaaaaa");
          this.value = '';
        }

      });

      $(document).on("focusin", ".in-num.qty_num", function () {
        var isiDevice = /ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase());
        if (isiDevice) {
          this.setSelectionRange(0, jQuery(this).val().length);
        } else {
          jQuery(this).select();
        }
        entered_by_keyboard = true;
      });

      $(document).on('click', '.modal-footer button.line-item-edit', function () {
        var qty = $(".qty_num").val();
        var ar_key = $("#itemscode").attr("value");
        var currentskuindex = $.inArray(ar_key, edit_table_style_sku);
        edit_table_data[currentskuindex].qty = $(".in-num.qty_num").attr("value");
        // console.log(edit_table_data);
        var value_changed = false;

        for (var key in edit_table_data) {
          for (var subkey in edit_table_data[key]) {
            if (non_edited_data[key].qty != edit_table_data[key].qty) {
              value_changed = true;
            }
          }
        }

        if (value_changed) {
          // console.log(active_table_index);
          $widget.EditQty(edit_table_data, active_table_index);

        }

      });
    },
    EditQty: function (edit_table_data, active_table) {
      var $widget = this
      $widget._optiononeAdddata($widget, 5, edit_table_data);
      if (active_table) {
        var targetrow = $("[data-table-index='" + active_table + "']");
        var prevmainrow = $("[data-table-index='" + active_table + "']").prev();
        if (prevmainrow.find(".line_item_coll_icon i").hasClass("fa-caret-down")) {
          prevmainrow.find(".line_item_coll_icon i").addClass("fa-caret-up").removeClass("fa-caret-down");
        }
        targetrow.show().find('div').fadeIn(1000, function () {
          if (!$(this).is(':visible')) {
            targetrow.hide();
            targetrow.removeClass("active");
            // targetrow.find(".expandable_row").removeClass("active-tab");
          } else {
            // targetrow.find("expandable_row").addClass("active-tab");
            targetrow.addClass("active")
          }
        });
      }
    },

    posuccesspopup: function () {
      var options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        modalClass: "po-success-popup",
        modalCloseBtn: true,
      };

      var popup = modal(options, $('#posuccess-message'));
    },

    showsuccesspopup: function (message) {
      $("#posuccess-message p").html(message);
      $("#posuccess-message").modal("openModal");
    },
    closeposuccesspopup: function () {
      $("#posuccess-message").modal("closeModal");
    },
  });

  return $.mage.SwatchRenderer; //list
});