<?php
namespace DR\Gallery\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;


public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
    StoreManagerInterface $storeManager
 
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->_storeManager = $storeManager;

}
public function execute()
{
    if (!$this->session->isLoggedIn())
    {
         $resultRedirect = $this->resultRedirectFactory->create();
        $this->session->setCustomRedirectUrl($this->_storeManager->getStore()->getCurrentUrl(false));
        $resultRedirect->setPath('login');
        return $resultRedirect;
    }
    else
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Adar Uniforms - Download Library'));
        return $resultPage;
    }
}

}