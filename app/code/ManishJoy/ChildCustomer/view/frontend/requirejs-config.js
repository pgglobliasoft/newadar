var config = {
    map: {
        '*': {
            
            customerRules: 'ManishJoy_ChildCustomer/js/agent_permission',           
            swal: 'ManishJoy_ChildCustomer/js/sweetalert.min',  
            example : 'ManishJoy_ChildCustomer/js/example',
            child_customer_stores: 'ManishJoy_ChildCustomer/js/child_customer_data'            
        }
    },
    shim: {
        swal: {
            deps: ['jquery']
        }
    }
};