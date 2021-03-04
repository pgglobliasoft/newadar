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
    PageFactory $resultPageFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->_registry = $registry;
    $this->resultPageFactory = $resultPageFactory;
}
public function execute()
{
    $docnumber = base64_decode($this->getRequest()->getParam('docnum'));    
    if($docnumber != '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Sttl\Adaruniforms\Helper\Sap');
        $invoicesData = $helper->getInvoicesDetails($docnumber,$this->session->getCustomer()->getData('customer_number'));
    }
    
    if (!$this->session->isLoggedIn())
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }
    else
    {
        if(empty($invoicesData))
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/index/');
            return $resultRedirect;
        }
        $resultPage = $this->resultPageFactory->create();
        $this->_registry->register('invoicesData',$invoicesData);
        $resultPage->getConfig()->getTitle()->set(__(''));
        return $resultPage;
    }
}

}