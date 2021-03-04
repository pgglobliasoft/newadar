<?php

namespace ManishJoy\CustomerLogin\Block;

use Magento\Framework\View\Element\Template;

class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
	protected $_registry;
	
	protected $saphelper;
	
	private $registry_key_name = 'customer_data';
	
    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
		\Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
		\Magento\Framework\Registry $registry,
		\Sttl\Adaruniforms\Helper\Sap $saphelper,
        array $data = []
    ) {
		parent::__construct($context,$currentCustomer, $subscriberFactory,$helperView, $data);
		$this->_registry = $registry;
		$this->saphelper = $saphelper;
    }

    public function getCustomerData()
    {
		$data = $this->getCustomVariable($this->registry_key_name);
		if (empty($data)) {
			$data = $this->saphelper->getCustomerDetails(["Active", "Phone1", "CardCode", "BCity", "BState", "AccountBalance", "CardName", "Program", "Tier", "OpenOrder", "PaymentTerm"]);
			$this->setCustomVariable($this->registry_key_name, $data);
		}
        return $data;
    }
	
	public function setCustomVariable($key = '', $value = '')
	{
		 $this->_registry->register($key, $value);
	}
	 
	/**
	 * Retrieving custom variable from registry
	 * @return string
	 */
	public function getCustomVariable($key = '')
	{
		 return $this->_registry->registry($key);
	}
	
	protected function _toHtml()
    {
        $this->setModuleName($this->extractModuleName('Magento\Customer\Block\Account\Dashboard\Info'));
        return parent::_toHtml();
    }
}