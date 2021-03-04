<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Neworder extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
     \Magento\Framework\UrlInterface $urlInterface,  
    PageFactory $resultPageFactory
    )
{
    $this->session = $customerSession;
    $this->_urlInterface = $urlInterface;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
}
public function execute()
{
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Adar Uniforms - New Order'));
        return $resultPage;
    }
}

}