define([
		'jquery',
		'jquery/ui',
		'jquery/validate',
		'mage/translate'
	], function($){
		'use strict';
	 
	return function() {
		$.validator.addMethod(
			"adar_cc_exp",
			function(value, element) {
				var filter = new RegExp("(0[123456789]|10|11|12)([/])([0-9][0-9])");
				if(filter.test(value)) {
					var data = value.split("/");
					var d = new Date();
					var month = d.getMonth();
					month = month + 1;
					var year = d.getFullYear();
					year= (year+'').slice(-2);
					if (data[1] > year) {
						return true;
					} else if (data[1] == year) {
						if (data[0] >= month) {
							return true;
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			},
			$.mage.__("Expiration date should be in MM/YY format and not expired.")
		);
		
		$.validator.addMethod(
			"adar_cc_cvv",
			function(value, element) {
				var filter = new RegExp("[0-9]{3,4}");
				if(filter.test(value)) {
					return true;
				} else {
					return false;
				}
			},
			$.mage.__("Please provide valid security code.")
		);
	}
});