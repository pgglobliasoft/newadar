<?php

namespace ManishJoy\CustomerLogin\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use \Magento\Framework\UrlInterface;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
      
        $this->session = ObjectManager::getInstance()->get(Session::class);
        $this->_urlInterface = ObjectManager::getInstance()->get(UrlInterface::class);
        echo $this->session->isLoggedIn();  
        if (!$this->session->isLoggedIn())
        { 
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->session->setCustomRedirectUrl($this->_urlInterface->getCurrentUrl());
            $resultRedirect->setPath('login'); 
            return $resultRedirect;
        }       
        return $this->resultPageFactory->create();
    }
}
