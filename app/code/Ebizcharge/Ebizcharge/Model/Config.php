<?php
/**
* Provides access to the admin settings for Ebizcharge.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Model;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $appState;
    protected $storeManager;
    protected $urlBuilder;
	
	const KEY_ACTIVE = 'active';
	
    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Backend\Model\Session\Quote $sessionQuote,
    \Magento\Framework\App\State $appState,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->_scopeConfigInterface = $configInterface;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }
	
	/**
     * @return bool
     */
    public function isActive()
    {
        return (bool)(int)$this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/active');
    }

    public function isSandboxMode()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sandbox');
    }
	
	public function getPaymentCctypes()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/cctypes');
    }
	
	public function getPaymentCurrency()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/currency');
    }
	
	public function getPaymentSavePayment()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/save_payment');
    }
	
	public function getPaymentMinordertotal()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/min_order_total');
    }
	
	public function getPaymentMaxordertotal()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/max_order_total');
    }

    public function isAuthorizeOnly()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/payment_action') == 'authorize';
    }

    public function saveCard()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/save_card') && ($this->customerSession->isLoggedIn() || $this->sessionQuote->getCustomerId());
    }
	
    public function getSourceKey()
    {
		return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sourcekey');
    }
	
	public function getSourceId()
    {
		return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sourceid');
    }

    public function getSourcePin()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sourcepin');
    }

    public function getPaymentDescription()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/description');
    }

    public function debugMode($code)
    {
        //return !!$this->_scopeConfigInterface->getValue('payment/'. $code .'/debug');
		//return $this->_scopeConfigInterface->getValue('payment/'. $code .'/debug');
    }

    public function getRequestCardCode()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/request_card_code');
    }

    public function getRequestCardCodeAdmin()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/request_card_code_admin');
    }

	public function getCustreceipt()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/custreceipt');
    }
	
	public function getPaymentAction()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/payment_action');
    }
	
	public function getCustreceiptTemplate()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/custreceipt_template');
    }

    public function getArea()
    {
        return $this->appState->getAreaCode();
    }

    public function getDeleteURL()
    {
        return $this->urlBuilder->getUrl('ebizcharge/cards/inlineaction');
		//$this->_storeManager->getStore()->getUrl('ebizcharge/cards/inlineaction');
    }
	
    public function getBaseDeleteURL()
    {
        return $this->urlBuilder->getBaseUrl();
    }

    public function getConfig($path)
    {
        return $this->_scopeConfigInterface->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}