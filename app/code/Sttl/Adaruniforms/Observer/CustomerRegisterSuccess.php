<?php
namespace Sttl\Adaruniforms\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerRegisterSuccess implements ObserverInterface
{
    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
     protected $dataHelper;
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Sttl\Adaruniforms\Helper\Sap $dataHelper
    ) {
        $this->customerRepository = $customerRepository;
         $this->dataHelper = $dataHelper;
    }
    
    public function execute(Observer $observer)
    {
        $accountController = $observer->getAccountController();
        $customer = $observer->getCustomer();
        $request = $accountController->getRequest();
        if($request->getParam('webaccess_code') != '')
        {
            $customer_number = $request->getParam('customer_number');
            $webaccess_code = $request->getParam('webaccess_code');
            $CheckSapData = "MWEB_OCRD.CardCode = '".$customer_number."'";
            $CheckSapData .= " AND MWEB_OCRD.WebAccessCode = '".$webaccess_code."'";
            $helperData = $this->dataHelper->checkCustomerExist($CheckSapData);
            $customer->setCustomAttribute('customer_number', $customer_number);
            $customer->setCustomAttribute('webaccess_code', $webaccess_code);
            if(isset($helperData[0]['CardName']))
            {
                $customer->setCustomAttribute('sap_name', $helperData[0]['CardName']);    
            }
            $this->customerRepository->save($customer);
        }
    }
}