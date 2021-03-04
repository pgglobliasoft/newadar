require(['jquery', 'Magento_Ui/js/lib/validation/validator', ], function($, validator) {
    validator.addRule('custom-validation', function(value) {
        var val = $('input[name="customer123[newmenu][admin_all_custom]"]').val();
        var admin = $('input[name="customer123[admin_custom]"]').val();        
        if(admin < 1){ return true;}      //not valiation check  
        if (val > 0) {
            return true;
        } 
        else {
            if (value.length < 1) {
                return false;
            } else {
                return true;
            }
        }
    }, $.mage.__('Select customer Bp number'));
});