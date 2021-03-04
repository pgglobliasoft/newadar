/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    paths: {            
		'functions': "Magento_Theme/js/functions",
		"jquery.bootstrap": "https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min"
		//'general': "Magento_Theme/js/general"
        },   
    shim: {
        'functions': {
            deps: ['jquery']
        },
		'jquery.bootstrap': {
            'deps': ['jquery']
        }
    },
	map: {
        "*": {
            theAdarValidationMethod: "js/theAdarValidationMethod"
        }
    }
};