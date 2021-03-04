<?php
namespace Sttl\Adaruniforms\Block;

use Sttl\Adaruniforms\Helper\Data;

class Store extends \Magento\Framework\View\Element\Template
{
	/**
     * @var Data
     */
    protected $helper;
	
	protected $logo;
	
	protected $_regionCollectionFactory;
	
	protected $_countryCollectionFactory;
	
	protected $_categoryCollection;
	
	protected $_storeManager;
	
	protected $_categoryHelper;
	
	public function __construct(\Magento\Framework\View\Element\Template\Context $context, Data $helper,
		\Magento\Theme\Block\Html\Header\Logo $logo,
		\Magento\Directory\Model\RegionFactory $regionCollectionFactory,
        \Magento\Directory\Model\Country $countryCollectionFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
		\Magento\Catalog\Helper\Category $categoryHelper,
		array $data = [])
	{
		$this->helper = $helper;
		$this->logo = $logo;
		$this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
		$this->_categoryCollection = $categoryCollection;
		$this->_storeManager = $storeManager;
		parent::__construct($context);
	}

	public function getStreetAddress()
	{
		return $this->helper->getConfigData("general/store_information/street_line1");
	}
	
	public function getStreetAddressLineOne()
	{
		return $this->helper->getConfigData("general/store_information/street_line2");
	}
	
	public function getCity()
	{
		return $this->helper->getConfigData("general/store_information/city");
	}
	
	public function getPostCode()
	{
		return $this->helper->getConfigData("general/store_information/postcode");
	}
	
	public function getCountryName()
	{
		$country_id = $this->helper->getConfigData("general/store_information/country_id");
		$country_obj = $this->_countryCollectionFactory->loadByCode($country_id);
		return $country_obj->getName();
	}
	
	public function getRegionName()
	{
		$region_id = $this->helper->getConfigData("general/store_information/region_id");
		$region = $this->_regionCollectionFactory->create();
        $region->load($region_id);
		return $region->getCode();
	}
	
	public function getStorePhoneNumber()
	{
		return $this->helper->getConfigData("general/store_information/phone");
	}
	
	public function getStoreEmail()
	{
		return $this->helper->getConfigData("trans_email/ident_general/email");
	}
	
	public function getFacebookUrl()
	{
		//return "asdasd";
		return $this->helper->getConfigData("Adaruniforms/social_media/facebook_url");
	}
	
	public function getTwitterUrl()
	{
		return $this->helper->getConfigData("Adaruniforms/social_media/twitter_url");
	}
	
	public function getLinkdinUrl()
	{
		return $this->helper->getConfigData("Adaruniforms/social_media/linkdin_url");
	}
	
	public function getGooglePlusUrl()
	{
		return $this->helper->getConfigData("Adaruniforms/social_media/google_plus_url");
	}
	
	/**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {    
        return $this->logo->getLogoSrc();
    }
    
    /**
     * Get logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {    
        return $this->logo->getLogoAlt();
    }
    
    /**
     * Get logo width
     *
     * @return int
     */
    public function getLogoWidth()
    {    
        return $this->logo->getLogoWidth();
    }
    
    /**
     * Get logo height
     *
     * @return int
     */
    public function getLogoHeight()
    {    
        return $this->logo->getLogoHeight();
    }
	
	public function getCategoryCollection()
	{
		$collection = $this->_categoryCollection->create()
			->addAttributeToSelect('*')
			->setStore($this->_storeManager->getStore())
			->addFieldToFilter('level', ['eq' => 2])
			->addAttributeToFilter('is_active','1');
			
	   return $collection;
	}
	
	public function getStoreName()
	{
		return $this->helper->getConfigData("general/store_information/name");
	}
	
	public function getStoreFaxNo()
	{
		return $this->helper->getConfigData("Adaruniforms/contact_us/fax_number");
	}
	
	public function getStoreTollFreeNo()
	{
		return $this->helper->getConfigData("Adaruniforms/contact_us/toll_free");
	}
	
	public function getServerIp()
	{
		return $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/server_ip");
	}
	public function getUsername()
	{
		return $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/username");
	}
	public function getPassword()
	{
		return $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/password");
	}
	public function getDbname()
	{
		return $this->helper->getConfigData("Adaruniforms/sap_server_onfiguration/db_name");
	}
}