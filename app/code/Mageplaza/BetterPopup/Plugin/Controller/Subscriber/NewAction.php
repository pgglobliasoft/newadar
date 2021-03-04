<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterPopup
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterPopup\Plugin\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterPopup\Helper\Data;

/**
 * Class NewAction
 * @package Mageplaza\BetterPopup\Plugin\Controller\Subscriber
 */
class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Mageplaza\BetterPopup\Helper\Data
     */
    protected $_helperData;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     * @param JsonFactory $resultJsonFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        JsonFactory $resultJsonFactory,
        Data $helperData
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helperData       = $helperData;

        parent::__construct($context, $subscriberFactory, $customerSession, $storeManager, $customerUrl, $customerAccountManagement);
    }

    /**
     * @param $subject
     * @param $proceed
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function aroundExecute($subject, $proceed)
    {
        if (!$this->_helperData->isEnabled() || !$this->getRequest()->isAjax()) {
            return $proceed();
        }

        $response = [];
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This email address is already subscribed.')
                    );
                }

                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                        $response = [
                        'error' => true,
                        'msg'     => __('The confirmation request has been sent.'),
                    ];
                } else {
                       $response = [
                        'success' => true,
                        'msg'     => __('Thank you for signing up! Check your inbox soon for special offers, our latest innovation and products, and more!'),
                    ];
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = [
                    'error' => true,
                    'status' => 'ERROR',
                    'msg'     => __('There was a problem with the subscription: %1', $e->getMessage()),
                ];
            } catch (\Exception $e) {
                $response = [
                    'error' => true,
                    'status' => 'ERROR',
                    'msg'    => __('Something went wrong with the subscription: %1', $e->getMessage()),
                ];
            }
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}