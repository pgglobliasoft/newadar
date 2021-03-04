<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\CustomerLogin\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

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
        \Magento\Framework\Json\Helper\Data $helper,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Get account redirect.
     * For release backward compatibility.
     *
     * @deprecated
     * @return AccountRedirect
     */
    protected function getAccountRedirect()
    {
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
    public function setAccountRedirect($value)
    {
        $this->accountRedirect = $value;
    }

    /**
     * @deprecated
     * @return ScopeConfigInterface
     */
    protected function getScopeConfig()
    {
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
    public function setScopeConfig($value)
    {
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
    public function execute()
    {        
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = [
                                'username' => $this->getRequest()->getPost('username'),
                                'password' => $this->getRequest()->getPost('password')
                            ];
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'redirect' =>  $this->customerSession->getCustomRedirectUrl(),
            'message' => __('Login successful.')
        ];
        try {
            $checkMobileView = !empty($this->getRequest()->getPost('checkMobileView')) ? $this->getRequest()->getPost('checkMobileView') : false;
            if($checkMobileView == 'true')
            {
                $response = [
                        'errors' => true,                        
                        'message' => 'For an optimal experience please use the B2B portal on a desktop device.'
                    ];
            }
            else
            {

                $customer = $this->customerAccountManagement->authenticate(
                    $credentials['username'],
                    $credentials['password']
                );
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory'); 
                $customerinfo=$customerFactory->create();
                $customerinfo->setWebsiteId('1');
                $customerdata = $customerinfo->loadByEmail($credentials['username']);     
                // echo 'sting' ;      var_dump($customerdata->getData())    ;die;
                if(!empty($customerdata) && !$customerdata['admin_custom'])
                {
                    $helper = $objectManager->get('\Sttl\Adaruniforms\Helper\Sap');
                    $CheckSapData = "MWEB_OCRD.CardCode = '".$customerdata->getCustomerNumber()."'";
                    $CheckSapData .= " AND MWEB_OCRD.WebAccessCode = '".$customerdata->getWebaccessCode()."'";
                        $helperData = $helper->checkCustomerExist($CheckSapData);
                        // echo '<pre>'; print_r( $helperData ); 
                        if(!empty($helperData)&& isset($helperData[0]))
                        {
                            $this->customerSession->setCustomerDataAsLoggedIn($customer);
                            $this->customerSession->regenerateId();
                            // echo 'smdfsk';
                            $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                            if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                                $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                                $this->getAccountRedirect()->clearRedirectCookie();
                            }  
                        }else{
                                    $response = [
                                'errors' => true,
                                'message' => ' You are not authorised to login Customer Code is not exists, please contact us at 718-935-1197 or user registration.'
                            ]; 
                        }
                    
                    
                }else if($customerdata['admin_custom']){
                	$this->customerSession->setCustomerDataAsLoggedIn($customer);
                    $this->customerSession->setCustomerAsadmin(base64_encode('admin_custom'));
                    $this->customerSession->regenerateId();
                    $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                        $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                        $this->getAccountRedirect()->clearRedirectCookie();
                    }                           
                }
                else{
                   $response = [
                    'errors' => true,
                    'message' => 'Your Email is Not Existing.'
                 ]; 
                }
            }
            
        } catch (EmailNotConfirmedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid lsdsdsdsogin or password.').$e->getMessage()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
