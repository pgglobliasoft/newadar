<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Orderview extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;
protected $registry;
protected $saphelper;


public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\Registry $registry,
    \Sttl\Adaruniforms\Helper\Sap $saphelper,
    \Magento\Framework\UrlInterface $urlInterface,  
    PageFactory $resultPageFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->_messageManager = $context->getMessageManager();
    $this->_registry = $registry;
    $this->saphelper = $saphelper;
    $this->_urlInterface = $urlInterface;

}
    public function execute()
    {
        // echo "string"; die;
        //$post = $this->getRequest()->getParams();
        //$post['order_id'] = '50';
        if($this->getRequest()->getParam('id') != '')
            {
                $id = base64_decode($this->getRequest()->getParam('id')); 
            }
        if (!$this->session->isLoggedIn())
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            // $resultRedirect->setPath('customer/account/login');
            $this->session->setCustomRedirectUrl($this->_urlInterface->getCurrentUrl());
            $resultRedirect->setPath('login'); 
            return $resultRedirect;
        }else if(!empty($id) && isset($id) && $id != '')
        {
            // echo "string"; dies;
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Adar Uniforms - Order Details'));
            $this->_registry->register('orderview',$id); 
            return $resultPage;
        }
        else
        {
            $this->_messageManager->addError('Something went wrong.');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customerorder/customer/neworder');
            return $resultRedirect;
        }
    }

}