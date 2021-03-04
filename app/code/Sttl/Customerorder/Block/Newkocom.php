<?php
namespace Sttl\Customerorder\Block;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
class Newkocom extends \Magento\Framework\View\Element\Template
{
	protected $sapHelper;
	protected $CustomerRepositoryInterface;
	protected $Session;
	protected $customerSession;
	
	public function __construct(Context $context, Sap $sapHelper, CustomerRepositoryInterface $CustomerRepositoryInterface, Session $Session,array $data = [])
	{
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->Session = $Session;
		
		$this->customerSession = $this->CustomerRepositoryInterface->getById($this->Session->getCustomer()->getId());		
	}

	public function getPageTitle()
	{
		return "New Order";
	}


}