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
use Magento\Framework\Registry;
use ManishJoy\ChildCustomer\Model\PostFactory;

// use Sttl\Adaruniforms\Helper\Sap;

class Delete extends \Magento\Framework\App\Action\Action {

	protected $resultForwardFactory;
	protected $saphelper;
	protected $_postFactory;
	private $jsonResultFactory;

	public function __construct(
		CustomerFactory $customerFactory,
		Context $context,
		PostFactory $postFactory,
		Session $customerSession,
		JsonFactory $jsonResultFactory,
		Registry $registry
	) {

		parent::__construct($context);
		$this->_customerFactory = $customerFactory;
		$this->_postFactory = $postFactory;
		$this->session = $customerSession;
		$this->registry = $registry;
		$this->jsonResultFactory = $jsonResultFactory;
	}

	public function execute() {

		$_pid = $this->session->getCustomer()->getId();
		$_Pemail = $this->session->getCustomer()->getEmail();
		$post = $this->getRequest()->getParams();
		if (!$this->session->isLoggedIn()) {
			$response = [
				'errors' => true,
				'html' => '',
				'message' => __("Customer Session is expried."),
			];
		} elseif (!$post && empty($post) || md5($_Pemail) !== $post['form_key_hidden']) {
			$response = [
				'errors' => true,
				'html' => '',
				'message' => __("It's look like you some data are wrong or missing."),
			];
		} else {

			try {

				if ($this->customerExists($post['email'])) {

					$this->registry->register('isSecureArea', true);
					$model = $this->_postFactory->create()->load($post['edit_hidden']);
					if ($model->getId() && $model['c_id']) {
						$model->delete();
						$customer = $this->_customerFactory->create()->setId($model['c_id']);
						$customeri = $customer->delete();
					}

					$response = [
						'errors' => false,
						'message' => __('sucessfully deleted '),
					];

				} else {
					$response = [
						'errors' => true,
						'html' => '',
						'message' => __("Email is not exists."),
					];
				}

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

	public function customerExists($email, $websiteId = 1) {

		$customerinfo = $this->_customerFactory->create();
		if ($websiteId > 0) {
			$customerinfo->setWebsiteId($websiteId);
		}
		$customerdata = $customerinfo->loadByEmail($email);
		if ($customerdata->getId() > 0) {
			return true;
		}
		return false;
	}

}
