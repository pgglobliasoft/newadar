<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Adaruniforms\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;

class Sttlcreatepost extends \Magento\Customer\Controller\Account\CreatePost
{   
    public function execute()
    {
        $recaptcha = $this->getRequest()->getParam('g-recaptcha-response');
        $resultRedirect = $this->resultRedirectFactory->create();
        if(isset($recaptcha) && $recaptcha !='')
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Sttl\Adaruniforms\Helper\Sap');
        $this->formKeyValidator = $objectManager->get('Magento\Framework\Data\Form\FormKey\Validator');
        $this->accountRedirect = $objectManager->get('Magento\Customer\Model\Account\Redirect');
        $requestData = $this->getRequest()->isPost();
        $post = $this->getRequest()->getParams();
        
        $form_key_hidden = $this->getRequest()->getParam('form_key_hidden');
        $form_key = $this->getRequest()->getParam('form_key');
        $customer_number = $this->getRequest()->getParam('customer_number'); 
        $webaccess_code = $this->getRequest()->getParam('webaccess_code'); 
        $incorrectDetails = "";
        $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
        $customMessage = __('Registration error, please contact us at 718-935-1197 ext. 3.');
        if(isset($customer_number) && $customer_number != '' && isset($webaccess_code) &&  $webaccess_code != ''&& trim($this->getRequest()->getParam('firstname')) != ''&& trim($this->getRequest()->getParam('lastname')) != '')
        {
            $incorrectDetails = 0;
            $checkCustomer = $objectManager->get('Magento\Customer\Model\CustomerFactory')->create();
            $customer = $checkCustomer->getCollection()
                                ->addAttributeToSelect("*")
                                ->addFieldToFilter(
                                array(
                                    array('attribute'=>'customer_number','eq'=> $customer_number),
                                    array('attribute'=>'webaccess_code', 'eq'=> $webaccess_code)
                                ));
            $webCustomerData = $customer->getData();
            if(count($webCustomerData) > 0){
                $incorrectDetails = 1;
                $this->messageManager->addError($customMessage);
                $resultRedirect->setUrl($this->_redirect->error($url));
                return $resultRedirect;
            }else{
                $CheckSapData = "dbo.MWEB_OCRD.CardCode = '".$customer_number."'";
                $CheckSapData .= " AND dbo.MWEB_OCRD.WebAccessCode = '".$webaccess_code."'";
                $customerCollection = "";
                $helperData = $helper->checkCustomerExist($CheckSapData);
                if(count($helperData) == 0 || !isset($helperData[0])){
                    $incorrectDetails = 1;
                    $this->messageManager->addError($customMessage);
                    $resultRedirect->setUrl($this->_redirect->error($url));
                    return $resultRedirect;
                }
            }
        }else{
            $this->messageManager->addError($customMessage);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        if ($form_key_hidden != $form_key.'@d@r9876' && $incorrectDetails == "") {
            $this->messageManager->addError($customMessage);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
            $incorrectDetails = 1;
        }
        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest()) || $incorrectDetails == 1) {
            $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }
        $this->session->regenerateId();
        try {
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];

            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            $customer->setAddresses($addresses);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $this->session->getBeforeAuthUrl();

            $this->checkPasswordConfirmation($password, $confirmation);

            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __(
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $email
                    )
                );
                // @codingStandardsIgnoreEnd
                $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
                $resultRedirect->setUrl($this->_redirect->success($url));
            } else {
                $this->session->setCustomerDataAsLoggedIn($customer);
                $this->messageManager->addSuccess($this->getSuccessMessage());
                $requestedRedirect = $this->accountRedirect->getRedirectCookie();
                if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $requestedRedirect) {
                    $resultRedirect->setUrl($this->_redirect->success($requestedRedirect));
                    $this->accountRedirect->clearRedirectCookie();
                    return $resultRedirect;
                }
                $resultRedirect = $this->accountRedirect->getRedirect();
            }
            /**if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }**/

            return $resultRedirect;
        } catch (StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } catch (InputException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
        } catch (\Exception $e) {
            //$this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->messageManager->addException($e, __('We can\'t save the customer.'));
        }

        $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
        return $resultRedirect;
        }else{

            $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
            return $resultRedirect;
        }
        
    }
    
}
