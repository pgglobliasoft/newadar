/* 
var config = {
    "waitSeconds": 0,
     "map": {
        "*": {}
	},
	"shim":{

	},
	deps:[
		'Vsourz_Imagegallery/js/masonry-effect'
	]
}; */
var config = {
	"waitSeconds": 0,
    paths: {            
		'mediaimage': "Vsourz_Imagegallery/js/custom-media-image",
		'freewalljs': "Vsourz_Imagegallery/js/freewall-js"
        },   
    shim: {
        'mediaimage': {
            deps: ['jquery']
        },
		'freewalljs': {
            deps: ['jquery']
        }
    }
};
