var config = {
    paths: {            
        "mobile-scroll": "js/moblie/mobile-scroll.js"
        },   
    shim: {
        'mobile-scroll': {
            'deps': ['jquery']
        }
    },
    map: {
        "*": {
            "mage/menu": "js/menu"
        }
    }
};