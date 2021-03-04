var config = {
    map: {
        '*': {
            customer: 'Sttl_Customerorder/js/customer',
            quickline: 'Sttl_Customerorder/js/quickline',
            neworder: 'Sttl_Customerorder/js/neworder',
            'dshightlight' : 'Sttl_Customerorder/js/demohightlight',
            datatables: 'Sttl_Customerorder/js/dataTables',
            'orderList': 'Sttl_Customerorder/js/orderview',
            'datatables.net' : 'Sttl_Customerorder/js/dataTables.min',
            'datatables.buttons.html5' : 'Sttl_Customerorder/js/buttons.html5.min',
            'datatables.net-buttons' :  'Sttl_Customerorder/js/dataTables.buttons.min',
            'datatables.hightlight' : 'Sttl_Customerorder/js/dataTables.searchHighlight.min',
            'ionrange' : 'Sttl_Customerorder/js/ion.rangeSlider',
            'momentjs' : 'Sttl_Customerorder/js/moment.min',
            'FileSaver' : 'Sttl_Customerorder/js/FileSaver',
            'excel_jszip' : 'Sttl_Customerorder/js/excel_jszip',
            'myexcel' : 'Sttl_Customerorder/js/myexcel',

        }
    },
    shim: {
        "datatables.net-buttons": ['jquery','datatables.net'],
        "dshightlight":['datatables.hightlight'],
        "ionrange":['jquery'],
        "momentjs":['jquery']
    }
};