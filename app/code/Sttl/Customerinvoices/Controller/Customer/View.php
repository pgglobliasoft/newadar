<?php
namespace Sttl\Customerinvoices\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;
protected $registry;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\UrlInterface $urlInterface,  
    PageFactory $resultPageFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->_registry = $registry;
    $this->resultPageFactory = $resultPageFactory;
    $this->_urlInterface = $urlInterface;
}
    public function execute()
    {
        $docnumber = base64_decode($this->getRequest()->getParam('docnum'));
        if (!$this->session->isLoggedIn())
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            // $resultRedirect->setPath('customer/account/login');
            $this->session->setCustomRedirectUrl($this->_urlInterface->getCurrentUrl());
            $resultRedirect->setPath('login'); 
            return $resultRedirect;
        }
        else
        {
            if($docnumber != '')
            {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $helper = $objectManager->get('Sttl\Adaruniforms\Helper\Sap');
                $invoicesData = $helper->getInvoicesDetails($docnumber,$this->session->getCustomer()->getData('customer_number'));
            }
            if(empty($invoicesData))
            {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/index/');
                return $resultRedirect;
            }
            $resultPage = $this->resultPageFactory->create();
            $this->_registry->register('invoicesData',$invoicesData);
            $resultPage->getConfig()->getTitle()->set(__('Adar Uniforms - Invoice'));
            return $resultPage;
        }
    }

}