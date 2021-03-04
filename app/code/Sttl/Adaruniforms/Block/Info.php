<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Adaruniforms\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Sttl\Adaruniforms\Helper\Sap;
use Sttl\Proupdated\Model\ResourceModel\Post\Collection;
use Sttl\Proupdated\Model\ReadFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Dashboard Customer Info
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Framework\View\Element\Template {
	/**
	 * Cached subscription object
	 *
	 * @var \Magento\Newsletter\Model\Subscriber
	 */
	protected $_subscription;

	/**
	 * @var \Magento\Newsletter\Model\SubscriberFactory
	 */
	protected $_subscriberFactory;

	/**
	 * @var \Magento\Customer\Helper\View
	 */
	protected $_helperView;

	/**
	 * @var \Magento\Customer\Helper\Session\CurrentCustomer
	 */
	protected $currentCustomer;

	public $session;

	protected $sapHelper;

	protected $ebizHelper;

	protected $request;

	protected $DownloadLibrary;

	protected $directoryList;

	protected $filesystem;

	protected $httpContext;

	protected $PostCollection;

	protected $filterProvider;

	protected $customerSession_number;

	protected $CustomerRepositoryInterface;

	protected $invoiceBlock;

	private $catalogcacheId = 'catalogcacheid';

	private $annoucecacheId = 'annoucecacheid';

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
	 * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
	 * @param \Magento\Customer\Helper\View $helperView
	 * @param array $data
	 */
	public function __construct(
		\Magento\Cms\Model\Template\FilterProvider $filterProvider, 
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\App\Http\Context $httpContext,
		\Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
		\Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
		\Magento\Customer\Helper\View $helperView,
		\Magento\Framework\App\Request\Http $request,
		\Sttl\Adaruniforms\Helper\Ebizcharge $ebizHelper,
		\ManishJoy\ChildCustomer\Model\PostFactory $postFactory,
		\Sttl\Adaruniforms\Helper\DownloadLibrary $DownloadLibrary,
		\Magento\Framework\Filesystem $filesystem,
		\Magestore\Bannerslider\Helper\Data $bannersliderHelper,
		Collection $PostCollection,
		EncoderInterface $jsonEncoder,
		Session $customerSession,
		Sap $saphelper,
		ReadFactory $ReadFactory,
		\Magestore\Bannerslider\Model\BannerFactory $bannerFactory,
		CustomerRepositoryInterface $CustomerRepositoryInterface,
		\Sttl\Customerinvoices\Block\Index $invoiceBlock,
		\Magento\Store\Model\StoreManagerInterface $store,
		\Magento\Catalog\Model\ResourceModel\Product\Collection $procollection,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\App\CacheInterface $cache,
		\Vendor\Rules\Model\GridFactory $factoryCategory,
		array $data = []
	) {
		$this->filterProvider = $filterProvider;
		$this->currentCustomer = $currentCustomer;
		$this->_subscriberFactory = $subscriberFactory;
		$this->sapHelper = $saphelper;
		$this->jsonEncoder = $jsonEncoder;
		$this->postFactory = $postFactory;
		$this->DownloadLibrary = $DownloadLibrary;
		$this->session = $customerSession;
		$this->_helperView = $helperView;
		$this->ebizHelper = $ebizHelper;
		$this->request = $request;
		$this->filesystem = $filesystem;
		$this->_bannerFactory = $bannerFactory;
		$this->_bannersliderHelper = $bannersliderHelper;
		$this->Collection = $PostCollection;
		$this->_ReadFactory = $ReadFactory;
		$this->_invoiceBlock = $invoiceBlock;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->procollection = $procollection;
		$this->store = $store;
		$this->resource = $resource;
		$this->cache = $cache;
		$this->factoryCategory = $factoryCategory;
		$this->customerSession_number = $this->CustomerRepositoryInterface->getById($this->session->getCustomer()->getId());
		parent::__construct($context, $data);
	}

	// public function getTrackingInfoAll() {
	// 	$data = $this->sapHelper->getTrackingInfoAll();
	// 	return $data;
	// }

	  public function getfeatureprocollectioncol(){
        return $this->factoryCategory->create()->getCollection()->addFieldToSelect('sku')->addFieldToFilter('is_active',array('in' => array(1,2)))->setOrder('sort_order', 'ASC')->setPageSize(6)->getData();
    }

	public function getAccountBalance() {
		$data = $this->sapHelper->getCustomerDetails(["AccountBalance","PastDueAmount"]);
		if (isset($data[0]) && !empty($data[0])) {
			$data = $data[0];
		}
		return $data;
	}
	public function getInvoice() {
		$customer_number = $this->getCustomerCustomerNumber();
		$invoice = $this->sapHelper->getInvoicesData($customer_number,"Open","","",0,6,"DueDate","ASC");
		// $invoice1 = $this->sapHelper->getInvoicesData($customer_number,"Open","","",0,6,"DueDate","ASC");
		if(count($invoice) < 5)
		{
			$count=(5 - count($invoice));
			
			$invoice2 = $this->sapHelper->getInvoicesData($customer_number,"Paid","","",0,$count+1,"CreateDate","DESC");
			// echo "<pre>";
			// print_r($invoice2);
			// die;
			$invoice = array_merge($invoice,$invoice2);
		    // $invoice = $this->sapHelper->getInvoicesData($customer_number,"","","",0,6,"DueDate","ASC");
		}
	
		return $invoice;
	}
	public function getCatalogsliderData(){

		$data = $this->cache->load($this->catalogcacheId);
		if(!$data){
			$connection = $this->resource->getConnection();
			$tableName = $this->resource->getTableName('dr_gallery_image');
			$sql = $connection->select()->from(["tn" => $tableName])->where('category_id=?', 1)->where('status=?',1);
			$result = $connection->fetchAll($sql);
			$data = json_encode($result,true);
        	$this->cache->save($data,$this->catalogcacheId);
	    	return $result;
		}else{
			return json_decode($data,true);
		}

	}

	public function getCustomersFlatDiscount() {
        $flatdiscount = $this->sapHelper->getCustomerDetails(['FlatDiscount','BulkDiscount','Program','Tier','CardName','CardCode','PriceList']);
        return $this->jsonEncoder->encode($flatdiscount);
    }

    	public function getCustomersBulkDiscount() {
		$bulkDiscount = '';
		$logincustomerdata = $this->getCustomersFlatDiscount();

		$logincustomerdata = json_decode($logincustomerdata, true);

		if(isset($logincustomerdata) && isset($logincustomerdata[0]['BulkDiscount']) == true){
			$Program = $logincustomerdata[0]['Program'];
			$Tier = $logincustomerdata[0]['Tier'];
			$bulkDiscount = $this->sapHelper->getBulkDiscountInfoofCustomer($Program,$Tier);              
			return $this->jsonEncoder->encode($bulkDiscount);
		}

	}
   

	public function getBuldDiscountByProgram() {
		$bulkDiscountData = '';
		$logincustomerdata = $this->getCustomerDetails();
		if(isset($logincustomerdata)){
			$Program = $logincustomerdata['Program'];
			$sort_order = [];
			$tmp_cat_coll = [];
			$bulkDiscountData = $this->sapHelper->getBuldDiscountByProgram($Program);
			foreach ($bulkDiscountData as $key => $value) {
				if($value['Tier'] == 'WHITE'){
					$value['sortOrder'] = 1;
				}elseif ($value['Tier'] == 'BRONZE') {
					$value['sortOrder'] = 2;
				}elseif ($value['Tier'] == 'SILVER') {
					$value['sortOrder'] = 3;
				}elseif ($value['Tier'] == 'GOLD') {
					$value['sortOrder'] = 4;
				}elseif ($value['Tier'] == 'PLATINUM') {
					$value['sortOrder'] = 5;
				}else{
					$value['sortOrder'] = 6;
				}
				array_push($tmp_cat_coll,$value);
    			$sort_order[$key] = $value['sortOrder'];
			}
			array_multisort($sort_order, SORT_ASC, $tmp_cat_coll);
			return $tmp_cat_coll;
		}
	}
	
	public function getproductBaseUrl(){
		return $this->store->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
	public function getBaseUrl(){
		return $this->store->getStore()->getBaseUrl();
	}
	public function getfeatureprocollection(){
		return $this->procollection->addAttributeToSelect('*')
					->setPageSize(6)
					->addAttributeToFilter('featured_product', array('eq' => 1))
					->setOrder('updated_at', 'DESC')
					->load();
	}

	public function gethtmlcontent($html){
		return $this->filterProvider->getPageFilter()->filter($html);
	}

	public function getInvoiceData(){
		$invoiceData = $this->_invoiceBlock->getInvoicesData();

			// $current_balance = 0;
			// foreach ($invoiceData as $key => $value) {
				
			// }

		return $invoiceData;
	}

	public function getALLOrdersListDashboard($status = '', $po_number = '', $q = '') {
		$customer_number = $this->customerSession_number->getCustomAttribute('customer_number')->getValue();
		$orders = $this->sapHelper->getRecentOrderStatus($customer_number, $po_number, $status, $q);

		return $this->jsonEncoder->encode($orders);
	}

	public function getInvData() {
		$inv = $this->sapHelper->getLimitedInventoryItems();
		return $inv;
	}

	public function getReadNoJson()
    {
		$data =  $this->_ReadFactory->create()->getCollection()->addFieldToSelect('read_json')->addFieldToFilter('customer_id', $this->getCustomerId())->getData();
		if(count($data) > 0){
			return $data;
		}else{
			return [];
		}
    }
	 public function getBaseUrlMedia($img)
    {
    	return $this->_bannersliderHelper->getBaseUrlMedia($img);	
    }
	public function getBannerData($bannerid) {

		$banner = $this->_bannerFactory->create()->setStoreViewId(1)->load($bannerid);
		return $banner->getData();
	}
	public function getPostCollection() {

		$data = $this->cache->load($this->annoucecacheId);
		if(!$data){
			$collection = $this->Collection->addFieldToFilter('status', ['eq' => 1]);
			$data = json_encode($collection->getData(),true);
        	$this->cache->save($data,$this->annoucecacheId);
			return $collection->getData();
	    	
		}else{
			return json_decode($data,true);
		}
	}
	public function getFullActionName() {
		try {
			return $this->request->getFullActionName();
		} catch (NoSuchEntityException $e) {
			return null;
		}
	}
	/*
		    * admin customer details
	*/
	public function getAdminCustomer() {
		try {
			return $this->session->getCustomerAsadmin();
		} catch (NoSuchEntityException $e) {
			return null;
		}
	}
	/*
		    * login customer phone number
	*/
	public function getCustomerCustomerNumber() {
		return $this->session->getCustomer()->getData('customer_number');
	}

	/*
		    * custmoer id
	*/
	public function getCustomerId() {
		return $this->session->getCustomer()->getId();
	}
	public function getCustomerInfo() {
		return $this->session->getCustomer();
	}
	/*
		    * permission Json
	*/
	public function getPermissionJson() {
		$c_id = $this->getCustomerId();
		$post = $this->postFactory->create();
		$collection = $post->getCollection()->addFieldToSelect('permission')->addFieldToFilter('c_id', $c_id);
		$permission = $collection->getData();
		$permissionarray = '';
		if ($permission) {
			$permissionarray = $permission[0]['permission'];
		}

		return $permissionarray;
	}

	/*
		    * login customer details
	*/
	public function getCustomerDetails() {
		$data = $this->sapHelper->getCustomerDetails(["CardCode", "Active", "Phone1", "BCity", "BState", "AccountBalance", "CardName", "Program", "Tier", "OpenOrder", "PaymentTerm","BulkDiscount","FlatDiscount","LifeTimeSales","LastYearSale","YTDSALE","CreditLine","AvailCredit","PastDueAmount"]);
		if (isset($data[0]) && !empty($data[0])) {
			$data = $data[0];
		}
		return $data;
	}

	public function getCustomersDiscountData() {
		$bulkDiscount = '';
		$logincustomerdata = $this->getCustomerDetails();

		if(isset($logincustomerdata)){
			$Program = $logincustomerdata['Program'];
			$Tier = $logincustomerdata['Tier'];
			$bulkDiscount = $this->sapHelper->getBulkDiscountInfoofCustomer($Program,$Tier);
			return $bulkDiscount;
		}

	}

	public function getImage_library_directory($path) {
		return $this->DownloadLibrary->listFolderFiles($path);
	}

	public function getDirectorypath() {
		return $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)->getAbsolutePath('ftp_images' . DIRECTORY_SEPARATOR);
	}

	/*
		    * login customer phone number with format
	*/
	public function getCustomerPhoneNo() {
		$customer_phone = 'N/A';
		$data = $this->getCustomerDetails();
		if (isset($data) && !isset($data['errors'])) {
			if (isset($data["Phone1"]) && !empty($data["Phone1"])) {
				$tmpPhone = preg_replace("/[^0-9]/", "", $data["Phone1"]);
				$lenPhone = strlen($tmpPhone);
				if ($lenPhone == 10) {
					$customer_phone = substr($tmpPhone, 0, 3) . '-' . substr($tmpPhone, 3, 3) . '-' . substr($tmpPhone, 6);
				} else {
					$customer_phone = str_replace([" ", "."], "-", $data["Phone1"]);
				}
			}
		}
		return $customer_phone;
	}

	/*
		    * shipping details
	*/
	public function getCustomerShippingAddressDetails() {
		return $this->sapHelper->getCustomerShippingAddressDetails();
	}
	/*
		    * ebiz customer details
	*/
	public function getEbizcustomerDetails() {
		$objCustomers = '';
		$data = $this->getCustomerDetails();
		if (isset($data) && !isset($data['errors'])) {
			$customer_number = $this->getCustomerCustomerNumber();
			$search_query = array(
				array(
					'Field' => 'CustomerID',
					'Type' => 'eq',
					'Value' => $customer_number),
			);
			if (isset($customer_number) && $customer_number != '') {
				$objCustomers = $this->ebizHelper->searchCustomerByParams($search_query, true, 0, 1);
			}
		}
		return $objCustomers;
	}

	/*
		    * ebiz customer details
	*/
	public function getSaveCardDetails() {
		$saved_cards = '';
		$data = $this->getCustomerDetails();
		if (isset($data) && !isset($data['errors'])) {
			$custNum = '';
			$objCustomers = $this->getEbizcustomerDetails();
			if (isset($objCustomers->Customers) && count($objCustomers->Customers) > 0) {
				$objCustomer = $objCustomers->Customers;
				$objCustomer = $objCustomer[0];
				if (isset($objCustomer->CustNum)) {
					$custNum = $objCustomer->CustNum;
				}
			}
			$saved_cards = (isset($objCustomer->PaymentMethods) && count($objCustomer->PaymentMethods) > 0) ? $objCustomer->PaymentMethods : array();
		}

		return $saved_cards;
	}

	public function getCountryList() {
		return $this->sapHelper->getCountryList();
	}

	public function getStateList() {
		return $this->sapHelper->getStateList();
	}

	public function getSliderHtml() {
		
		return $this->getLayout()->createBlock("Magestore\Bannerslider\Block\SliderItem")->setSliderId(6)->toHtml();
	}

	public function getnotificationsection() {
		return $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId(61)->toHtml();
	}

	/**
	 * Returns the Magento Customer Model for this block
	 *
	 * @return \Magento\Customer\Api\Data\CustomerInterface|null
	 */
	public function getCustomer() {
		try {
			return $this->currentCustomer->getCustomer();
		} catch (NoSuchEntityException $e) {
			return null;
		}
	}

	/**
	 * Get the full name of a customer
	 *
	 * @return string full name
	 */
	public function getName() {
		return $this->_helperView->getCustomerName($this->getCustomer());
	}

	/**
	 * @return string
	 */
	public function getChangePasswordUrl() {
		return $this->_urlBuilder->getUrl('customer/account/edit/changepass/1');
	}

	/**
	 * Get Customer Subscription Object Information
	 *
	 * @return \Magento\Newsletter\Model\Subscriber
	 */
	public function getSubscriptionObject() {
		if (!$this->_subscription) {
			$this->_subscription = $this->_createSubscriber();
			$customer = $this->getCustomer();
			if ($customer) {
				$this->_subscription->loadByCustomerId($customer->getId());
			}
		}
		return $this->_subscription;
	}

	/**
	 * Gets Customer subscription status
	 *
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
	 */
	public function getIsSubscribed() {
		return $this->getSubscriptionObject()->isSubscribed();
	}

	/**
	 * Newsletter module availability
	 *
	 * @return bool
	 */
	public function isNewsletterEnabled() {
		return $this->getLayout()
			->getBlockSingleton(\Magento\Customer\Block\Form\Register::class)
			->isNewsletterEnabled();
	}

	/**
	 * @return \Magento\Newsletter\Model\Subscriber
	 */
	protected function _createSubscriber() {
		return $this->_subscriberFactory->create();
	}

	/**
	 * @return string
	 */
	protected function _toHtml() {
		return $this->currentCustomer->getCustomerId() ? parent::_toHtml() : '';
	}

	public function getTrackingInfoAll() {
		$CardCode = $this->session->getCustomer()->getData('customer_number');
		$allTrakingInfo = $this->sapHelper->getTrackingInfoAll($CardCode);
		return $this->jsonEncoder->encode($allTrakingInfo);
	}
}
