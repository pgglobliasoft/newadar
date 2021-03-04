<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Sttl\Adaruniforms\Helper\Sap;

class Testtwo extends \Magento\Framework\App\Action\Action {

	protected $session;

	protected $resultJsonFactory;

	public function __construct(
		context $context,
		Session $customerSession,
		JsonFactory $resultJsonFactory,
		Sap $saphelper
	) {

		$this->session = $customerSession;
		$this->saphelper = $saphelper;
		$this->resultJsonFactory = $resultJsonFactory;
		parent::__construct($context);

	}

	public function execute() {
		$result['test'] = 'test';
		$resultJson = $this->resultJsonFactory->create();
		if (!$this->session->isLoggedIn()) {
			$response = [
				'session_distroy' => true,
				'message' => __("Customer session expired please login."),
			];
			return $resultJson->setData($response);
		} else {

			$po_number = 10408;
			$customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount", "CardName", "CardCode", "Program", "Tier", "BulkDiscount"]);
			$orderData = $this->saphelper->getSapOrdersDataByID($customerdata[0], $po_number);

		}
		return $resultJson->setData(['success' => $orderData]);
	}

}