define(['jquery'],
    function($) {
        'use strict';
        return { 
        	copyToClipboard: function(element){
        		var $temp = $("<input>");
				$("body").append($temp);
				$temp.val(element).select();
				document.execCommand("copy");
				$temp.remove();
        	}
        }
    }
);