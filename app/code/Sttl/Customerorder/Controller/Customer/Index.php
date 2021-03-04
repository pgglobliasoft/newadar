<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $sapHelper;

    protected $session;

    protected $storemanager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
    	\Sttl\Adaruniforms\Helper\Sap $sapHelper,    
    	\Magento\Store\Model\StoreManagerInterface $storemanager
        )
    {
        $this->session = $customerSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    	$this->sapHelper = $sapHelper;
    	$this->_storemanager = $storemanager;
    }
    public function execute()
    {
        if (!$this->session->isLoggedIn())
        {         
       
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->session->setCustomRedirectUrl($this->_storemanager->getStore()->getCurrentUrl(false));
            // $resultRedirect->setPath($this->_storemanager->getStore()->getBaseUrl());
            $resultRedirect->setPath('login');
            return $resultRedirect;
        }
        else
        {
            $titleName = $this->getRequest()->getParam('q');
            $resultPage = $this->resultPageFactory->create();
            if(!$titleName){
                $resultPage->getConfig()->getTitle()->set(__('Adar Uniforms - Order Status'));
                return $resultPage;
            }else{
                $resultPage->getConfig()->getTitle()->set(__('Adar Uniforms - Purchase Order Drafts'));
                return $resultPage;
            }
        }
    }

}   