<?php
namespace ManishJoy\ChildCustomer\Block;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use ManishJoy\ChildCustomer\Model\PostFactory;
use ManishJoy\ChildCustomer\Model\ResourceModel\Post\CollectionFactory;
use Sttl\Adaruniforms\Helper\Sap;

class Customer extends \Magento\Framework\View\Element\Template {

	/**
	 * @var string
	 */
	protected $_session;

	protected $_postFactory;

	public function __construct(
		Context $context,
		StoreManagerInterface $storeManager,
		PostFactory $postFactory,
		CustomerFactory $customerFactory,
		Sap $saphelper,
		Session $customerSession,
		CollectionFactory $postFactoryCollection
	) {

		$this->sapHelper = $saphelper;
		$this->customerFactory = $customerFactory;
		$this->session = $customerSession;
		$this->_postFactory = $postFactory;
		$this->_storeManager = $storeManager;
		$this->_postFactoryCollection = $postFactoryCollection;
		parent::__construct($context);
	}

	public function isLoggedIn() {
		return $this->_customerSession->isLoggedIn();
	}

	public function getFromUrl() {
		return "sdfsdf";
	}

	/*
		* @return from action
	*/
	public function getPostActionUrl() {
		return $this->getBaseUrl() . 'undercustomer/index/save';
	}

	/**
	 * @return delete action url
	 */
	public function getDelActionUrl() {
		return $this->getBaseUrl() . 'undercustomer/index/delete';
	}

	/**
	 * @return reload action url
	 */
	public function getDataActionUrl() {
		return $this->getBaseUrl() . 'undercustomer/index/getchild';
	}

	/**
	 * @var array $data
	 */
	public function getCustomerDetails() {

		$customer = $this->session->getCustomer();
		return $customer->getData();

	}
	public function getCollection() {
		$post = $this->_postFactory->create();
		$collection = $post->getCollection();
		return $collection;
	}

	/**
	 * @return join customer data
	 */
	public function getCustomerAlldata() {

		$parent_id = $this->session->getCustomer()->getId();
		$collection = $this->_postFactoryCollection->create();
		$collection->setcustomerFilter($parent_id);
		return $collection;
	}

}