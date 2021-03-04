define(['jquery', 'Manadev_LayeredNavigation/js/vars/actionHelper',
    'Manadev_Core/js/functions/requestAnimationFrame'],
function($, actionHelper, requestAnimationFrame) {

    $(document).on('mana-layered-navigation-action', function(event, action) {
        actionHelper.forEachElement('.filter-current li a, .m-applied-filters li a', action, function(action) {
            var $a = $(this);
            var $li = $a.parent();

            if (action.op == '-' || !action.op && action.clear) {
                $li.hide();
            }
        });
    });

    return function(config, element) {
        $(element).on('click', '.filter-current li a, .filter-clear, .m-applied-filters li a', function() {
            $(document).trigger('mana-layered-navigation-action', [$(this).data('action')]);
        });

        $(element).on('click', '.filter-title', function() {
            if($('.mana-filter-block.active').length > 0)
            {
                $('.filter-options').show();
            }else{
                $('.filter-options').hide();
            }
			//$(document).trigger('mana-after-show', [element]);
        });
		$(element).on('click', '.mobile_sortby', function() {
            if($('.sidebar-main .sorter:visible').length == 0)
            {
                    $('.mobile_sortby').addClass('active');
                    $('body').addClass('mobile-sortby-body');
                    $('.sidebar-main .sorter').fadeIn(1400);
            }else{
                $('.sidebar-main .sorter').fadeOut(1400);
                setTimeout(function(){
                  $('.mobile_sortby').removeClass('active');
                  $('body').removeClass('mobile-sortby-body');
                },1400)
            }
            //$(document).trigger('mana-after-show', [element]);
        });
    };
});
