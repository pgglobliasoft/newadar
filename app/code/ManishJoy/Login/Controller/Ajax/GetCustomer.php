<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\Login\Controller\Ajax;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;


class GetCustomer extends \Magento\Framework\App\Action\Action
{
  
    protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $saphelper;
    protected $resultJsonFactory;

    public function __construct(
            PageFactory $resultPageFactory,
            CustomerFactory $customerFactory,
            Context $context,  
            ForwardFactory $resultForwardFactory,
            JsonFactory $resultJsonFactory,
            Sap $saphelper,
            Session $customerSession       
        )
    {
        
        parent::__construct($context);     
        $this->_customerFactory = $customerFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sapHelper = $saphelper;       
        $this->session = $customerSession;   
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        if (!$this->session->isLoggedIn())
        {   
            $response = [
                            'errors' => true,
                            'html'   => '',
                            'message' => __("Customer Session is expried.")
                        ];
        }
        else
        {

            $adminCustomer = $this->session->getCustomeradminId();
            $customer = $this->_customerFactory->create()->load($adminCustomer);

            // echo '<pre>'; 
            // print_r($customer->getData());
            
            $admin_all_custom = @$customer['admin_all_custom'] ;
            $account_id = @$customer['account_id']; 
            $Customer_data = $this->sapHelper->getallCustomerWithIds($admin_all_custom, $account_id);

            // echo "<pre>";
            // print_r($customer->getData());

            $response = [
                            'errors' => false,
                            'customer_list'   => $Customer_data,
                            'message' => __("Success.")
                        ];
        }
        return $resultJson->setData($response);

    }

}
