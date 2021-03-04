<?php
/**
* Declares the block for the Ebizcharge payment method - utilized in adminhtml.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Block\Form;

class Card extends \Magento\Payment\Block\Form
{
    protected $_template = 'Ebizcharge_Ebizcharge::form/card.phtml';
    private $ebizchargePaymentsCard;
	protected $_paymentConfig;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ebizcharge\Ebizcharge\Model\Config $config,
        \Magento\Payment\Helper\Data $paymentHelper,
		\Magento\Payment\Model\Config $paymentConfig,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->ebizchargePaymentsCard = $paymentHelper->getMethodInstance('ebizcharge_ebizcharge');
		$this->_paymentConfig = $paymentConfig;
    }

    public function getClientKey()
    {
        return $this->config->getSourceKey();
    }

    public function getRequestCardCodeAdmin()
    {
        if ($this->config->getRequestCardCodeAdmin() == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function saveCardEnabled()
    {
        return $this->config->saveCard();
    }
	
	public function getPaymentCctypes()
    {
        return explode(',', $this->config->getPaymentCctypes());
    }
	
	public function getCcAvailableTypes()
    {
        $applicableTypes = $this->getPaymentCctypes();
        $types = $this->_paymentConfig->getCcTypes();

        foreach (array_keys($types) as $code)
        {
            if (!in_array($code, $applicableTypes))
            {
                unset($types[$code]);
            }
        }

        return $types;
    }
	
	public function getCcMonths()
    {
        $months = $this->getData('cc_months');

        if ($months === null)
        {
            $months[0] = __('Month');
            $months = array_merge($months, $this->_paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }
	
	public function getCcYears()
    {
        $years = $this->getData('cc_years');

        if ($years === null)
        {
            $years = $this->_paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function getPaymentSavePayment()
    {
        return $this->config->getPaymentSavePayment();
    }

    public function getDeleteUrl()
    {
        return $this->config->getBaseDeleteUrl();
    }

    public function getSavedCards()
    {
        return $this->ebizchargePaymentsCard->getSavedCards();
    }
	
	public function getEbzcCustId()
	{
		return $this->ebizchargePaymentsCard->getEbzcCustId();
	}
	
	public function hasToken(){
		return $this->ebizchargePaymentsCard->hasToken();
	}
}