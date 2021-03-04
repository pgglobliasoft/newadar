<?php
namespace ManishJoy\Login\Block;
  
class Login extends \Magento\Framework\View\Element\Template
{   
	protected $_session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
    	$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function isLoggedIn()
    {
    	return $this->_customerSession->isLoggedIn();
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }


}