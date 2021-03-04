<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\Login\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\session;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use ManishJoy\Login\Helper\Customer;

class SetCustomer extends \Magento\Framework\App\Action\Action
{
  
    protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $saphelper;
    protected $resultJsonFactory;
    protected $objectManager;
    protected $allcustomerCollection;

    public function __construct(            
            Context $context,  
            CustomerFactory $customerFactory,
            JsonFactory $resultJsonFactory,       
            Customer $Customer,
            Session $customerSession,
            \Magento\Customer\Model\ResourceModel\Customer\Collection $allcustomerCollection,
            CustomerRepositoryInterface $customerRepositoryInterface
        )
    {
        
        parent::__construct($context);        
        $this->_customerHelper = $Customer;
        $this->resultJsonFactory = $resultJsonFactory;       
        $this->_customerFactory = $customerFactory;
        $this->session = $customerSession;  
        $this->allcustomerCollection = $allcustomerCollection;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
          
    }

    public function execute()
    {
        // echo "test";
        // die;

        $pastnew = time()-1;
        setcookie("PHPSESSID", "as ", $pastnew, '/', '.adaruniforms.com', true );
        
        // die;
        
        $post = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();
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
            // $data = $this->getCustomerData($post['CardName']); 
            
            $customerBycode =$this->getFilteredCustomerCollection($post['CardCode']); 
            $errors = false; $html = '';
            if(!$customerBycode)
            {
                $dynamicCustomerId = 1;
                $errors = $this->_customerHelper->setCustomerData123($post['CardCode'], $dynamicCustomerId); 
                $customer = $this->_customerFactory->create()->load($dynamicCustomerId);
                

                $html = 'Customer inserted succesfully ';  
                $this->session->setCustomerAsLoggedIn($customer); 
                $this->session->setCustomerEnroller($post['CardName']);            
            }else{
                
                $html =  'admin customer session successfully';        
                $id = $customerBycode;        
                $customer = $this->_customerFactory->create()->load($id);
                $this->session->setCustomerAsLoggedIn($customer);
                $this->session->setCustomerEnroller($post['CardName']);
            }

                $customerId = $this->session->getCustomeradminId();
                $sap_customername = $this->session->getCustomerEnroller();

                $admincustomer = $this->_customerFactory->create()->load($customerId)->getDataModel();
                $admincustomer->setCustomAttribute('you_are_viewing', $sap_customername);
                $this->_customerRepositoryInterface->save($admincustomer);
                $this->session->setCustomerCardNme($post['CardName']);
                $response = [
                                'errors' => $errors,
                                'html'   => $html,
                                'message' => __("Customer Session is set.")
                            ];
        }
        return $resultJson->setData($response);

    }

    public function getFilteredCustomerCollection($CardCode) {
        $customer = $this->allcustomerCollection->addAttributeToSelect('*')
                ->addAttributeToFilter('customer_number',$CardCode)
                ->load();
        if($customer->getSize()){
            $data = $customer->getFirstItem();
            return  $data->getId();             
        }else{
            return  0;
        }
    }

}
