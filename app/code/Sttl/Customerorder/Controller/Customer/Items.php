<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\Json\EncoderInterface;

class Items extends \Magento\Framework\App\Action\Action {

	protected $resultJsonFactory;

	protected $helpersap;

	protected $_customerSession;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		EncoderInterface $jsonEncoder,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Customer\Model\Session $customerSession,
		Sap $helpersap
	) {
		parent::__construct($context);
		$this->jsonEncoder = $jsonEncoder;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helpersap = $helpersap;

	}

	public function execute() {
		$resultJson = $this->resultJsonFactory->create();
		$getallitmesdata = $this->helpersap->getJsAllInventoryItemshalf();
		return $resultJson->setData($this->jsonEncoder->encode($getallitmesdata));
	}
}