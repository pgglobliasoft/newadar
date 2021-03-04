<?php
namespace Sttl\Adaruniforms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    protected $scopeConfig;
	
	protected $_categoryCollection;
	
	protected $_storeManager;

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    )
    {
		$this->_categoryCollection = $categoryCollection;
		$this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /*
     * @return bool
     */
    public function getConfigData($path)
    {
       return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	
	public function getChildCategories($id)
    {
       $collection = $this->_categoryCollection->create()
			->addAttributeToSelect('*')
			->setStore($this->_storeManager->getStore())
			->addFieldToFilter('level', ['eq' => $id])
			->addAttributeToFilter('is_active','1');
			
	   return $collection;
    }
}