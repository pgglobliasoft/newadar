<?php

namespace Sttl\Customerorder\Controller\Customer;

class OrderList extends \Magento\Framework\App\Action\Action {

	protected $resultForwardFactory;

	protected $saphelper;

	protected $resultJsonFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Sttl\Adaruniforms\Helper\Sap $saphelper,
		\Magento\Customer\Model\Session $Session

	) {
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->saphelper = $saphelper;
		$this->session = $Session;
	}

	public function execute() {

		$post = $this->getRequest()->getParams();
		if (!$this->session->isLoggedIn() || empty($post)) {
			$response = [
				'session_distroy' => true,
				'message' => __("Customer session expired please login."),
			];

		} else {

			$CardCode = $this->session->getCustomer()->getData('customer_number');
			$data = $this->saphelper->getAllSapOrderspage($CardCode);
			if ($post['po_number'] != '') {
				$data = $this->saphelper->getAllSapOrderspage($CardCode, $post['po_number']);
			}
			if ($post['q'] != '') {
				$data = $this->saphelper->getAllSapOrderspage($CardCode, '', 'Draft', 'd');
			}
			$response = [
				'session_distroy' => true,
				'order' => json_encode($data),
				'message' => __("Customer session expired please login."),
			];

		}
		return $this->resultJsonFactory->create()->setData($response);
	}
}
