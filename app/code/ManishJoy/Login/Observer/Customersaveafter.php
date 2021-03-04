<?php
/**
* Copyright � 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

namespace ManishJoy\Login\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class Customersaveafter implements ObserverInterface
{
    protected $_request;
    protected $_layout;
    protected $_objectManager = null;
    protected $_customerGroup;

    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_layout = $context->getLayout();
        $this->_request = $context->getRequest();
        $this->_objectManager = $objectManager;
        $this->customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
    }

    /**
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(EventObserver $observer)
    {
     
       
        // echo '<pre>'; print_r($this->_request->getPost()); die;        
        $post = $this->_request->getPost('customer123');    

        $fieldpost = $post['newmenu'];    
        $fieldpost['account_id'] = array_filter($fieldpost['account_id']);
        $customer123 = $observer->getEvent()->getCustomer();       
       
        $customer = $this->customerRepository->getById($customer123->getId()); 
        $customer->setCustomAttribute('allow_custom',@$post['allow_custom']);                 
        $customer->setCustomAttribute('admin_custom',@$post['admin_custom']);
        $customer->setCustomAttribute('admin_all_custom',@$fieldpost['admin_all_custom']);
        if(!@$fieldpost['admin_all_custom'] ){             
           
                $customer->setCustomAttribute('account_id',implode(',',@$fieldpost['account_id']));
        }else{        
             $customer->setCustomAttribute('account_id','');
        }               
        try
        {                
            $customer = $this->customerRepository->save($customer);
        }
        catch (Exception $e)
        {
           
            echo $e->getMessage(); die;
        }
        // echo($customer->getCustomAttribute('admin_custom')->getValue()).'<br>'; 
        // echo($customer->getCustomAttribute('admin_all_custom')->getValue()).'<br>';
        // echo($customer->getCustomAttribute('account_id')->getValue()).'<br>';
        // echo 'set'; die;
        // }

    }
}