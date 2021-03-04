<?php
/**
* Accesses data to pass to the 'Manage My Payment Method' pages.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;

class Cards extends Template
{
    private $customerTokenManagement;
    protected $_mage_cust_id;
    protected $_ebzc_cust_id;
    protected $_customerSession;
    protected $_tran;
    protected $_paymentConfig;
    protected $_myConfig;

    public function __construct(
        Template\Context $context,
        Token $customerTokenManagement,
        TranApi $tranApi,
        Session $session,
        \Magento\Payment\Model\Config $paymentConfig,
        \Ebizcharge\Ebizcharge\Model\Config $config,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
        $this->_tran = $tranApi;
        $this->_customerSession = $session;
        $this->_paymentConfig = $paymentConfig;
        $this->_myConfig = $config;
        
        $customer = $this->_customerSession->getCustomer();
        $this->_mage_cust_id = $customer->getId();
        $this->_ebzc_cust_id = $this->customerTokenManagement->getCollection()->addFieldToFilter('mage_cust_id', $customer->getId())
            ->getFirstItem()
            ->getEbzcCustId();
        
        $isSandBox = $this->_myConfig->isSandboxMode();
        
        if ($isSandBox)
        {
            $this->_tran->usesandbox = true;
        }
        
        $this->_tran->key = $this->_myConfig->getSourceKey();
		$this->_tran->userid = $this->_myConfig->getSourceId();
        $this->_tran->pin = $this->_myConfig->getSourcePin();
        $this->_tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
    }

    public function getEbzcCustId()
    {
        return $this->_ebzc_cust_id;
    }

    public function getMageCustId()
    {
        return $this->_mage_cust_id;
    }

    public function getEbzcMethodId()
    {
        $mid = $this->getRequest()->getParam('mid');

        return $mid;
    }

    public function getMethodName()
    {
        $method = $this->getRequest()->getParam('method');

        return urldecode($method);
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getAddCardUrl()
    {
        return $this->getUrl('ebizcharge/cards/addaction/');
    }

    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('ebizcharge/cards/saveaction', ['_secure' => true]);
    }    
    
    public function getPaymentCards()
    {
        $collection = $this->customerTokenManagement->getCollection()
            ->addFieldToFilter('mage_cust_id', $this->_customerSession->getCustomerId());

        return $collection;
    }
    
    public function getConfig($path)
    {
        return $this->_myConfig->getConfig($path);
    }
	
	public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }
	// for user account payment methods listing
    public function getPaymentMethods()
    {
        if ($this->_ebzc_cust_id != "")
        {
            $wsdl = $this->_tran->_getWsdlUrl();
            $ueSecurityToken = $this->_tran->_getUeSecurityToken();
			$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());
			try {
				$methodProfiles = $client->getCustomerPaymentMethodProfiles(
					array(
					'securityToken' => $ueSecurityToken,
					'customerToken' => $this->_ebzc_cust_id
				));
//				if(count($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile) > 1) 
//				{
//					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
//				} 
//				else 
//				{
//					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult;
//				}
				
				if (!isset ($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile))
				{
					$paymentMethods = null;
				}
				elseif(count($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile) > 1) 
				{
					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
				} 
				else 
				{
					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult;
				}
				return $paymentMethods;
				
			} catch (Exception $ex) {
				return NULL;
				//throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: ' . $ex->getMessage()));
			}
			
        }
        return [];
    }
    
    public function getCustomerPaymentMethod()
    {
        $mid = $this->getRequest()->getParam('mid');
        $cid = $this->getRequest()->getParam('cid');
		
        $wsdl = $this->_tran->_getWsdlUrl();
        $ueSecurityToken = $this->_tran->_getUeSecurityToken();
		$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());
		
		try {
			$methodProfiles = $client->GetCustomerPaymentMethodProfile(
				array(
				'securityToken' => $ueSecurityToken,
				'customerToken' => $cid,
				'paymentMethodId' => $mid
			));

//			if(count($methodProfiles->GetCustomerPaymentMethodProfileResult->PaymentMethodProfile) > 1) {
//				$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfileResult->PaymentMethodProfile;
//			} else {
//				$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfileResult;
//			}
			$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfileResult;
			
			return $paymentMethods;
		} catch (Exception $ex) {
			return NULL;
			//throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: ' . $ex->getMessage()));
		}
		
    }
    
}