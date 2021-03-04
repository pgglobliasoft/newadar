<?php
namespace Sttl\Customerinvoices\Block;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
class View extends \Magento\Framework\View\Element\Template
{
	protected $sapHelper;
	protected $CustomerRepositoryInterface;
	protected $Session;
	protected $customerSession;
	protected $_registry;

	public function __construct(Context $context, Sap $sapHelper,  Session $Session, \Magento\Framework\Registry $registry,array $data = [])
	{
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->Session = $Session;
		$this->_registry = $registry;
	}
	 public function getRegisterData()
    {         
        return $this->_registry->registry('invoicesData');    
    }
}