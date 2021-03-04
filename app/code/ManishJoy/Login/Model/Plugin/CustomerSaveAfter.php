<?php

namespace ManishJoy\Login\Model\Plugin;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerSaveAfter implements ObserverInterface
{
    protected $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
        $this->customerRepository = $customerRepository;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        echo "string"; die;
        // $customer = $observer->getEvent()->getCustomer();
        // $data = $this->_request->getParams();
        // //Write your save logic here
        // $customAttribute = $data['custom_attribute'];
        // $customer->setCustomAttribute($customAttribute);
        // $this->customerRepository->save($customer);
        // echo '<pre>';print_r($data); die;
    }
}