<?php
namespace Sttl\Customerorder\Block;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
class Revieworder extends \Magento\Framework\View\Element\Template
{
	protected $sapHelper;
	protected $CustomerRepositoryInterface;
	protected $Session;
	protected $customerSession;
	protected $_registry;
	public function __construct(Context $context, Sap $sapHelper, CustomerRepositoryInterface $CustomerRepositoryInterface, Session $Session,\Magento\Framework\Registry $registry,array $data = [])
	{
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->Session = $Session;
		$this->_registry = $registry;
		
	}
	 public function getRegisterData()
    {         
        return $this->_registry->registry('revieworder');    
    }
    public function getcustomerdata()
    {
    	$customer = $this->Session->getCustomer();
		return $customer->getData();
    }
    public function getCustomerDetails() {
		$data = $this->sapHelper->getCustomerDetails();
		if (isset($data[0]) && !empty($data[0])) {
			$data = $data[0];
		}
		return $data;
	}
	public function getOrderDataDetails($order_id, $data_from,$customer) {

		/*et MWEB_Temp_ORDR data*/
		$orderdata = $this->sapHelper->getidbyorderdata($order_id);

		if (empty($orderdata)) {
			return array();
		}
		return $orderdata[0];
	}
}