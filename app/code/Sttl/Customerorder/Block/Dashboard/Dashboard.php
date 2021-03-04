<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Customerorder\Block\Dashboard;

use Magento\Customer\Model\Session;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Api\CustomerRepositoryInterface;
/**
 * Dashboard Customer Info
 *
 * @api
 * @since 100.0.2
 */
class Dashboard extends \Magento\Framework\View\Element\Template {

	public $session;

	protected $sapHelper;

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
		Session $customerSession,
		Sap $saphelper,
		CustomerRepositoryInterface $CustomerRepositoryInterface,
		\Magento\Framework\View\Element\Template\Context $context,
		\Sttl\Adaruniforms\Helper\Data $configData,
		\ManishJoy\ChildCustomer\Model\PostFactory $postFactory,
		array $data = []
	) {
		$this->sapHelper = $saphelper;
		$this->postFactory = $postFactory;
		$this->session = $customerSession;
		$this->CustomerRepositoryInterface = $CustomerRepositoryInterface;
		$this->customerSession_number = $this->CustomerRepositoryInterface->getById($this->session->getCustomer()->getId());
		parent::__construct($context, $data);
		$this->configData = $configData;
	}

	public function getALLOrdersListDashboard($status = '', $po_number = '', $q = '') {
		$customer_number = $this->customerSession_number->getCustomAttribute('customer_number')->getValue();
		$orders = $this->sapHelper->getRecentOrderStatus($customer_number, $po_number, $status, $q);

		return $orders;
	}

	public function getInvData() {
		$inv = $this->sapHelper->getLimitedInventoryItems();
		return $inv;
	}
	
	public function getCustomerDetails() {
		$data = $this->sapHelper->getCustomerDetails(["CardCode", "Active", "Phone1", "BCity", "BState", "AccountBalance", "CardName", "Program", "Tier", "OpenOrder", "PaymentTerm","BulkDiscount","FlatDiscount","LifeTimeSales","LastYearSale","YTDSALE","CreditLine","AvailCredit","PastDueAmount"]);
		if (isset($data[0]) && !empty($data[0])) {
			$data = $data[0];
		}
		return $data;
	}

	public function getCustomerPriceListType() {
		$data = $this->sapHelper->getCustomerDetails(["CardCode", "PriceList"]);
		if (isset($data[0]) && !empty($data[0])) {
			$data = $data[0];
		}
		return $data;
	}

	public function getCustomerId() {
		return $this->session->getCustomer()->getId();
	}
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

	public function isValidforSearch()
    {   
        $orderLimitData = json_decode($this->configData->getConfigData("Adaruniforms/recentorder_range/ranges"),true);
        $order_imit = 0;
        $CardCode = $this->session->getCustomer()->getData('customer_number');
        $limit_customer = 0;
        foreach ($orderLimitData as $key => $value) {
            if($value['cardcode'] == $CardCode){
                $limit_customer = 1;
            }
        }
        return $limit_customer;
    }
}
