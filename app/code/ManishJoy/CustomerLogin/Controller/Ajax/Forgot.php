<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\CustomerLogin\Controller\Ajax;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
class Forgot extends \Magento\Framework\App\Action\Action
{
	protected $customerAccountManagement;
    protected $escaper;
    protected $session;
    protected $jsonHelper;
    protected $_ajaxLoginHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        JsonHelper $jsonHelper
        
    )
    {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $result = array();
        $captchaStatus = $this->session->getResultCaptcha();
        if ($captchaStatus) {
            if (isset($captchaStatus['error'])) {
                $this->session->setResultCaptcha(null);
                $this->getResponse()->setBody($this->jsonHelper->jsonEncode($captchaStatus));
                return;
            }
            $result['imgSrc'] = $captchaStatus['imgSrc'];
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->session->setForgottenEmail($email);
                $result['error'] = __('Please correct the email address.');
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
                $result['success'] = __(
                    'We have sent a message to your email. Please check your inbox and click on the link to reset your password.'
                );
            } catch (NoSuchEntityException $e) {
                //$result['error'] = $e->getMessage();
                $result['error'] = __('We\'re unable to send the password reset email. Email address does not exist');
            } catch (SecurityViolationException $exception) {
                $result['error'] = $exception->getMessage();
            } catch (\Exception $exception) {
                 $result['error'] = $exception->getMessage();
            }
        }

        if (!empty($result['error'])) {
            $emailAdmin = 'email@admin.com';
            $htmlPopup = 'error';//$this->_ajaxLoginHelper->getErrorMessageForgotPasswordPopupHtml($emailAdmin);
            $result['html_popup'] = $htmlPopup;
        } else {
            $htmlPopup = 'done';//$this->_ajaxLoginHelper->getSuccessMessageForgotPasswordPopupHtml($email);
            $result['html_popup'] = $htmlPopup;
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}