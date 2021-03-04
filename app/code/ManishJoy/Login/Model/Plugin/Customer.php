<?php

namespace ManishJoy\Login\Model\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\ExtensionAttributesInterface\Config;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class Customer
{
    protected $_responseFactory;
    protected $_url;

    public function __construct(
        \Magento\Framework\App\ResponseFactory $responseFactory,            
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_responseFactory = $responseFactory;
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
    }

    // public function aroundexecute(\Magento\Customer\Controller\Adminhtml\Index\Save $save)
    // {
    //     $post = $save->getRequest()->getPostValue();
    //     // $customer = $observer->getCustomer();
    //     // print_r($customer->getData());
    //     $post['customer']['admin_custom'] =  $post['customer123']['admin_custom'];
    //     $post['customer']['admin_all_custom'] =  $post['customer123']['admin_all_custom'];
    //     $post['customer']['account_id'] =  $post['customer123']['account_id'];
    //     return 
      
    // }
    public function afterexecute(\Magento\Customer\Controller\Adminhtml\Index\Save $save , $result)
    {
        // $post = $save->getRequest()->getPostValue();
        // var_dump($result->getCustomer()); die;
        // $post['customer']['admin_custom'] =  $post['customer123']['admin_custom'];
        // $post['customer']['admin_all_custom'] =  $post['customer123']['admin_all_custom'];
        // $post['customer']['account_id'] =  $post['customer123']['account_id'];
        // return 
      
    }
}