<?php
namespace ManishJoy\CustomerLogin\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use ManishJoy\ChildCustomer\Helper\Customer as ChildCustomer;
use ManishJoy\Login\Helper\Customer;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;


/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends \Magento\Framework\App\Action\Action {
	/**
	 * @var \Magento\Framework\Session\Generic
	 */
	protected $session;

	/**
	 * @var AccountManagementInterface
	 */
	protected $customerAccountManagement;
	/**
	 * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
	 */
	private $cookieManager;

	/**
	 * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
	 */
	private $cookieMetadataFactory;

	/**
	 * @var \Magento\Framework\Json\Helper\Data $helper
	 */
	protected $helper;

	/**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	protected $resultJsonFactory;

	/**
	 * @var \Magento\Framework\Controller\Result\RawFactory
	 */
	protected $resultRawFactory;

	/**
	 * @var AccountRedirect
	 */
	protected $accountRedirect;

	/**
	 * @var ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * Initialize Login controller
	 *
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Framework\Json\Helper\Data $helper
	 * @param AccountManagementInterface $customerAccountManagement
	 * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	 * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		Sap $helper,
		AccountManagementInterface $customerAccountManagement,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
		Customer $Customer,
		ChildCustomer $ChildCustomer,
		CustomerFactory $customerFactory,
		CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Framework\Session\SessionManagerInterface $session,
		\Magento\Framework\App\ResourceConnection $resourceConnection

	) {
		parent::__construct($context);
		$this->customerSession = $customerSession;
		$this->_Saphelper = $helper;
		$this->session = $session;
		$this->_customer = $Customer;
		$this->customerAccountManagement = $customerAccountManagement;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->_ChildCustomer = $ChildCustomer;
		$this->resultRawFactory = $resultRawFactory;
		$this->_customerFactory = $customerFactory;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->resource = $resourceConnection;
	}

	/**
	 * Get account redirect.
	 * For release backward compatibility.
	 *
	 * @deprecated
	 * @return AccountRedirect
	 */
	protected function getAccountRedirect() {
		if (!is_object($this->accountRedirect)) {
			$this->accountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);
		}
		return $this->accountRedirect;
	}

	/**
	 * Account redirect setter for unit tests.
	 *
	 * @deprecated
	 * @param AccountRedirect $value
	 * @return void
	 */
	public function setAccountRedirect($value) {
		$this->accountRedirect = $value;
	}

	/**
	 * @deprecated
	 * @return ScopeConfigInterface
	 */
	protected function getScopeConfig() {
		if (!is_object($this->scopeConfig)) {
			$this->scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
		}
		return $this->scopeConfig;
	}

	/**
	 * @deprecated
	 * @param ScopeConfigInterface $value
	 * @return void
	 */
	public function setScopeConfig($value) {
		$this->scopeConfig = $value;
	}

	/**
	 * Login registered users and initiate a session.
	 *
	 * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function execute() {


		$credentials = null;
		$httpBadRequestCode = 400;

		/** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
		$resultRaw = $this->resultRawFactory->create();
		try {
			$credentials = [
				'username' => $this->getRequest()->getPost('username'),
				'password' => $this->getRequest()->getPost('password'),
			];
			
		} catch (\Exception $e) {
			return $resultRaw->setHttpResponseCode($httpBadRequestCode);
		}


		if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
			return $resultRaw->setHttpResponseCode($httpBadRequestCode);
		}
		
		try {


			$customer = $this->customerAccountManagement->authenticate(
				$credentials['username'],
				$credentials['password']
			);
			

			$customerdata = $this->_customer->getCustomerByEmail($credentials['username']);
			// echo "string";
			// echo "<pre>";
			// print_r($customerdata);
			// die;
			$ChildCustomer = $this->_ChildCustomer->getChildCustomerdata($customerdata->getId());
			if (!empty($customerdata)) {

				// echo "string";
			// echo "<pre>";
			// print_r($customerdata);
			// die;

				if (empty($ChildCustomer) || (!empty($ChildCustomer) && $ChildCustomer['status'] < 1)) {
					if (!empty($ChildCustomer && $ChildCustomer->getId() > 1)) {
						$this->_ChildCustomer->setChildCustomerActive($ChildCustomer->getId(), 1);
						$this->customerSession->setChildCustomer($ChildCustomer);
					}

					if (!$customerdata['admin_custom']) {
						$session = $this->SetCustomerSession($customer, $customerdata);
						$redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
						if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
							$response['redirectUrl'] = $this->_redirect->success($redirectRoute);
							$this->getAccountRedirect()->clearRedirectCookie();
						}

					} else {

						if ($customerdata->getCustomerNumber()) {
							$cardcode = "'" . $customerdata->getCustomerNumber() . "'";
							$ocrdCustomer = $this->_Saphelper->getfirstCustomerWithIds(0, $cardcode);
							if (@$ocrdCustomer['errors'] == true) {
								$response = [
									'errors' => true,
									'message' => __($ocrdCustomer['message']),
								];
								$resultJson = $this->resultJsonFactory->create();
								return $resultJson->setData($response);
							}
						} else {
							$admin = $customerdata->getData();
							echo "<pre>";
							print_r($admin);
							die;
							$firstId = explode(',', $customerdata->getAccountId());
							$ocrdCustomer = $this->_Saphelper->getfirstCustomerWithIds($admin['admin_all_custom'], $firstId[0]);
							if (@$ocrdCustomer['errors'] == true) {
								$response = [
									'errors' => true,
									'message' => __($ocrdCustomer['message']),
								];
								$resultJson = $this->resultJsonFactory->create();
								return $resultJson->setData($response);
							}
							$this->_customer->setCustomerData123($ocrdCustomer[0]['CardCode']);
							$firstadmincustomer = $this->_customer->CustomerloadById(1);
							$customer = $this->customerAccountManagement->authenticate(
								$firstadmincustomer['email'],
								'Not_Enrolled'
							);

						}

						$this->customerSession->setCustomerEnroller($ocrdCustomer[0]['CardName']);
						$this->customerSession->setCustomerDataAsLoggedIn($customer);
						$this->adminCustomersession($customerdata);
						
						$redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
						if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
							$response['redirectUrl'] = $this->_redirect->success($redirectRoute);
							$this->getAccountRedirect()->clearRedirectCookie();
						}
					}
					$response = [
						'errors' => false,
						'redirect' => $this->customerSession->getCustomRedirectUrl(),
						'message' => __('Login successful.'),
					];
					$this->setLoggedBrowser($customerdata->getId());
					
					
				} else {

					$response = [
						'errors' => true,
						'redirect' => $this->customerSession->getCustomRedirectUrl(),
						'message' => __('You don\'t have access to login. Please contact to your admin'),
					];
				}

			} else {
				$response = [
					'errors' => true,
					'message' => 'Your Email is Not Existing.',
				];
			}

		} 



		catch (EmailNotConfirmedException $e) {
			$response = [
				'errors' => true,
				'message' => $e->getMessage(),
			];
		} catch (InvalidEmailOrPasswordException $e) {
			$response = [
				'errors' => true,
				'message' => $e->getMessage(),
			];
		} catch (LocalizedException $e) {
			$response = [
				'errors' => true,
				'message' => $e->getMessage(),
			];
		}



		

			 catch (\Exception $e) {
		
			$response = [
				'errors' => true,
				'message' => __('Invalid login or password.') . $e->getMessage(),
			];
		}

		/** @var \Magento\Framework\Controller\Result\Json $resultJson */
		$resultJson = $this->resultJsonFactory->create();

		return $resultJson->setData($response);

	}

	/*
		* Admin customer login session set
	*/
	public function adminCustomersession($customerdata) {
		$this->customerSession->setCustomerAsadmin($customerdata['firstname']);
		$this->customerSession->setCustomeradminId($customerdata->getId());
		$this->customerSession->setAdminCustomer($customerdata->getData());
		$this->customerSession->setAdmincokkietime(time());
		$this->customerSession->setCustomerPermission($customerdata['allow_custom']);
		$this->customerSession->regenerateId();
	}

	public function setLoggedBrowser($id){

		$browser = get_browser(null, true);
		// $browsername = $this->_customerFactory->create()->load($id)->getDataModel();
  //       $browsername->setCustomAttribute('login_browser', $browser['browser']);
  //       $this->_customerRepositoryInterface->save($browsername);

        $sessiondata = $this->session->getVisitorData();
        $connection = $this->resource->getConnection();
		$tableName = $this->resource->getTableName('au_customer_visitor');
        $sql = "UPDATE " . $tableName . " SET  browsername = '".$browser['browser']."' WHERE visitor_id = " . $sessiondata['visitor_id'] ;
        
		$connection->query($sql);

	}
	/**
	 * @var session
	 */
	public function SetCustomerSession($customer, $customerdata) {
		$this->customerSession->setCustomerDataAsLoggedIn($customer);
		$this->customerSession->setAdmincokkietime(time());
		$this->customerSession->setCustomerPermission(@$customerdata['allow_custom']);
		$this->customerSession->regenerateId();
	}

}
