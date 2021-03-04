var config = {
    map: {
        '*': {
            snapGallery: 'DR_Gallery/js/snapGallery',
            fancyboxjs: 'DR_Gallery/js/fancyboxjs',
			lazyload : 'DR_Gallery/js/jquery.lazyload',
			lazyloadspinner : 'DR_Gallery/js/lazyloadspinner',
            lazyloder : 'DR_Gallery/js/lazyloader',
            filesave : 'DR_Gallery/js/filesaver'
        }
    },
	shim: {
        'lazyload': {
            'deps': ['jquery']
        },
		'lazyloadspinner': {
            'deps': ['jquery']
        },
        'fancyboxjs': {
            'deps': ['jquery']
        }
    }
};
