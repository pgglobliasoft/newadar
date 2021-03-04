<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Adaruniforms\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Sttl\Adaruniforms\Helper\Sap;

/**
 * Dashboard Customer Info
 *
 * @api
 * @since 100.0.2
 */
class Accountinfo extends \Magento\Framework\View\Element\Template {
    

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    public $session;

    protected $sapHelper;

    protected $ebizHelper;

    protected $invoiceBlock;

    private $CountrysId = 'countrysid';

    private $StatesId = 'statesid';

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \ManishJoy\ChildCustomer\Model\PostFactory $postFactory,
        Session $customerSession,
        Sap $saphelper,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->sapHelper = $saphelper;
        $this->postFactory = $postFactory;
        $this->session = $customerSession;
        parent::__construct($context, $data);
    }

    public function customerloged(){
        if($this->session->isLoggedIn()) {
           return true;
        }else{
            return false;
        }
    }


    public function getAccountBalance() {
        $data = $this->sapHelper->getCustomerDetails(["AccountBalance","PastDueAmount"]);
        if (isset($data[0]) && !empty($data[0])) {
            $data = $data[0];
        }
        return $data;
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
     * @return string
     */
    public function getChangePasswordUrl() {
        return $this->_urlBuilder->getUrl('customer/account/edit/changepass/1');
    }

}
