<?php
namespace Sttl\Customerorder\Controller\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Sttl\Adaruniforms\Helper\Sap;

class Infojs extends \Magento\Framework\App\Action\Action {
	// protected $_resultPageFactory;
	protected $helpersap;
	protected $CustomerRepositoryInterface;
	protected $customerSession;
	protected $Session;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		Sap $helpersap,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		Session $Session,
		CustomerRepositoryInterface $CustomerRepositoryInterface
	) {

		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->helpersap = $helpersap;
		$this->Session = $Session;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->customerSession = $this->CustomerRepositoryInterface->getById($this->Session->getCustomer()->getId());
		return parent::__construct($context);
	}

	public function execute() {
		$po_number = $this->getRequest()->getParam('po_number');
		$customer_number = $this->customerSession->getCustomAttribute('customer_number')->getValue();
		$totalrecords = $this->helpersap->getAllSapOrderspage($customer_number, $po_number, '', '');
		return $this->resultJsonFactory->create()->setData($totalrecords);

	}

}