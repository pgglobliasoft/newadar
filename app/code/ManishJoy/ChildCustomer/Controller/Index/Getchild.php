<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\ChildCustomer\Controller\Index;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use ManishJoy\ChildCustomer\Model\PostFactory;

// use Sttl\Adaruniforms\Helper\Sap;

class Getchild extends \Magento\Framework\App\Action\Action {

	protected $resultForwardFactory;
	protected $resultPageFactory;
	protected $saphelper;
	protected $_postFactory;
	private $jsonResultFactory;

	public function __construct(
		CustomerFactory $customerFactory,
		Context $context,
		PostFactory $postFactory,
		Session $customerSession,
		JsonFactory $jsonResultFactory,
		PageFactory $resultPageFactory
	) {

		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->_customerFactory = $customerFactory;
		$this->_postFactory = $postFactory;
		$this->session = $customerSession;
		$this->jsonResultFactory = $jsonResultFactory;
	}

	public function execute() {

		$post = $this->getRequest()->getParams();
		if (!$this->session->isLoggedIn()) {
			$response = [
				'errors' => true,
				'html' => '',
				'message' => __("Customer Session is expried."),
			];
		} else {

			try {

				$resultPage = $this->resultPageFactory->create();
				$hmtl = $resultPage->getLayout()
					->createBlock('ManishJoy\ChildCustomer\Block\Customer')
					->setTemplate('ManishJoy_ChildCustomer::childusertable.phtml')
					->toHtml();

				$response = [
					'errors' => false,
					'html' => $hmtl,

				];

			} catch (\Exception $e) {
				$this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
				$response = [
					'errors' => true,
					'html' => '',
					'message' => __($e->getMessage()),
				];

			}

		}

		$result = $this->jsonResultFactory->create();
		$result->setData($response);
		return $result;

	}

}
