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
use Magento\Store\Model\StoreManagerInterface;
use ManishJoy\ChildCustomer\Model\PostFactory;

// use Sttl\Adaruniforms\Helper\Sap;

class Save extends \Magento\Framework\App\Action\Action {

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
		PageFactory $resultPageFactory,
		StoreManagerInterface $storeManager
	) {

		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->_customerFactory = $customerFactory;
		$this->_postFactory = $postFactory;
		$this->session = $customerSession;
		$this->storeManager = $storeManager;
		$this->jsonResultFactory = $jsonResultFactory;
	}

	public function execute() {

		$_pid = $this->session->getCustomer()->getId();
		$_Pemail = $this->session->getCustomer()->getEmail();
		$_PName = $this->session->getCustomer()->getName();
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

				if (!$this->customerExists($post['email']) ||
					($post['edit_hidden'] != '' && $this->customerExists($post['email']) && $post['edit'] == 'edit')) {
					$customer = $this->_customerFactory->create();
					$model = $this->_postFactory->create();
					if ($post['edit_hidden'] != '' && $post['edit'] == 'edit') {
						$child_customer = $model->load($post['edit_hidden']);
						$model->load($post['edit_hidden']);
						$customer->load($child_customer['c_id']);
						$message = $post['email'] . " is sucessfully updated";

					} else {
						$customer->setLastname('-');
						$message = $post['email'] . " is sucessfully added";

					}
					$websiteId = $this->storeManager->getWebsite()->getWebsiteId();
					$customer->setWebsiteId($websiteId);
					$customer->setEmail($post['email']);
					$customer->setFirstname($post['fullname']);
					if (strlen($post['password']) > 3 && $post['password'] !== '00000000') {
						$customer->setPassword($post['password']);
					}
					if (!array_key_exists("check_list", $post)) {
						$post['check_list'] = [];
					}
					$customerData = $customer->getDataModel();
					$customerData->setCustomAttribute('customer_number', $post['customer_number']);
					$customerData->setCustomAttribute('webaccess_code', $post['webaccess_code']);
					$customer->updateData($customerData);
					$customer->save();
					date_default_timezone_set('America/New_York');
					if ($customer->getId()) {
						$model->addData([
							"parent_id" => $_pid,
							"c_id" => $customer->getId(),
							"fullname" => $post['fullname'],
							"permission" => json_encode($post['check_list']),
							"customercode" => $post['customer_number'],
							"webscesscode" => $post['webaccess_code'],
							"status" => @$post['status'] ? $post['status'] : 0,
							"created_at" => date('m-d-Y H:i:s'),
							"updated_at" => date('m-d-Y H:i:s'),
						]);
						$saveData = $model->save();
						if ($saveData) {
							$resultPage = $this->resultPageFactory->create();
							$hmtl = $resultPage->getLayout()
								->createBlock('ManishJoy\ChildCustomer\Block\Customer')
								->setTemplate('ManishJoy_ChildCustomer::childusertable.phtml')
								->toHtml();
							// print_r($renderDataPart);die;
							$data = ['c_id' => $customer->getId(), 'fullname' => $post['fullname'], 'parent_name' => $_PName, 'with_permission' => json_encode($post['check_list'])];
							$response = [
								'errors' => false,
								'data' => $data,
								'html' => $hmtl,
								'message' => $message,
							];
						} else {
							$response = [
								'errors' => true,
								'html' => '',
								'message' => __("customer is not added... Please try after some thing.."),
							];

						}

					} else {
						$response = [
							'errors' => true,
							'html' => '',
							'message' => __("something is wrong.. customer is not added... Please conatct to us"),
						];
					}

				} else {
					$response = [
						'errors' => true,
						'html' => '',
						'message' => __("Email is alredy exists."),
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
		$this->cleanFlush();
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

	public function cleanFlush() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		try {
			$_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
			$_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
			$types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
			foreach ($types as $type) {
				$_cacheTypeList->cleanType($type);
			}
			foreach ($_cacheFrontendPool as $cacheFrontend) {
				$cacheFrontend->getBackend()->clean();
			}
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

}
