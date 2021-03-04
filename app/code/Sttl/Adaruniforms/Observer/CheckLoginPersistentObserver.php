<?php
namespace Sttl\Adaruniforms\Observer;
use Magento\Framework\Event\ObserverInterface;


class CheckLoginPersistentObserver implements ObserverInterface
{
         /**
         * @var \Magento\Framework\App\Response\RedirectInterface
         */
        protected $redirect;

        /**
         * Customer session
         *
         * @var \Magento\Customer\Model\Session
         */
        protected $_customerSession;

        public function __construct(
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\App\Response\RedirectInterface $redirect,
             \Magento\Framework\UrlInterface $url

        ) {

            $this->_customerSession = $customerSession;
            $this->redirect = $redirect;
            $this->url = $url;

        }

        public function execute(\Magento\Framework\Event\Observer $observer)
        {
        	$controller = $observer->getControllerAction();
            if(!$this->_customerSession->isLoggedIn()) {            	
               $observer->getControllerAction()->getResponse()->setRedirect($this->url->getBaseUrl().'login');
            }

        }

}
