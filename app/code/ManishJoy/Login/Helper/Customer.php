<?php
namespace ManishJoy\Login\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Registry;
use Magento\Customer\Model\session;

class Customer extends AbstractHelper
{  

	protected $_storeManager;
	 
	protected $_session; 
	
	protected $_customerRepositoryInterface;

    protected $_customerAddressFactory;

    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        Sap $saphelper,
        Registry $registry,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory
    )
    {		

        $this->_customerFactory = $customerFactory;
        $this->storeManager     = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->_customerAddressFactory = $customerAddressFactory;
        $this->sapHelper = $saphelper;
        $this->_registry =$registry;
        parent::__construct($context);        
	
    }
    
    public function getCustomerByEmail($email = '') {

        if ($email) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->get('ManishJoy\CustomerLogin\Model\Cookie\Custom')->set("access_data", "custom_data", 36000);
            $customer = $this->_customerFactory->create()->setWebsiteId('1')->loadByEmail($email);
            return $customer;
        } else {
            return 0;
        }

    }
    public function CustomerloadById($id) {
        $customer = $this->_customerFactory->create()->load(1);
        return $customer;
    }

    public function setCustomerData123($CardCode, $dynamicCustomerId = '') {
        $dynamicCustomerId = ($dynamicCustomerId != '') ? $dynamicCustomerId : 1;
        $customerdata = $this->sapHelper->getCustomerByCode($CardCode);
        $customerdata[0]['SapName'] = $customerdata[0]['CardName'];
        $customerdata[0]['Email'] = 'not_enrolled@gmail.com';
        $customerdata[0]['CardName'] = 'Not';
        $customer = $this->_customerFactory->create()->load($dynamicCustomerId);
        if (!$customer->getId()) {
            try {
                $this->addNewCustomer($customerdata, $dynamicCustomerId);
                // $this->cleanFlush();
            } catch (Exception $e) {
                return true;
            }
        }else {
            $customer->setEmail($customerdata[0]['Email']);
            $customer->setFirstname($customerdata[0]['CardName']);
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('customer_number',$customerdata[0]['CardCode']);
            $customerData->setCustomAttribute('webaccess_code',$customerdata[0]['WebAccessCode']);
            $customerData->setCustomAttribute('sap_name',$customerdata[0]['SapName']);
            $customer->updateData($customerData);
            try {
                $customer->save();
            } catch (Exception $e) {
                return true;
            }
        }
        $this->cleanFlush();
        return false;

    }

    public function addNewCustomer($customerdata, $dynamicCustomerId)
    { 
    $address = array(
            'customer_address_id' => '',
            'prefix' => '',
            'firstname' => 'Not_',
            'middlename' => '',
            'lastname' => 'Enrolled',
            'suffix' => '',
            'company' => '',
            'street' => array(
                '0' => 'Customer Address 1', // this is mandatory
                '1' => 'Customer Address 2', // this is optional
            ),
            'city' => 'New York',
            'country_id' => 'US', // two letters country code
            'region' => 'New York', // can be empty '' if no region
            'region_id' => '43', // can be empty '' if no region_id
            'postcode' => '10450',
            'telephone' => '000-000-0000',
            'fax' => '',
            'save_in_address_book' => 1,
        );
        $customer = $this->_customerFactory->create();
        $customer->setEntityId($dynamicCustomerId);
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customer->setEmail($customerdata[0]['Email']);
        $customer->setFirstname('Not ');
        $customer->setLastname('Enrolled');
        $customer->setPassword('Not_Enrolled');
        $customerData = $customer->getDataModel();
        $customerData->setCustomAttribute('customer_number',$customerdata[0]['CardCode']);
        $customerData->setCustomAttribute('webaccess_code',$customerdata[0]['WebAccessCode']);
        $customerData->setCustomAttribute('sap_name',$customerdata[0]['SapName']);
        $customer->updateData($customerData);
        $customer->setConfirmation(null);
        $customer->save();
        // Set New Customer Data End
        // Set Customer Addredd Data by Id Start
        $customAddress = $this->_customerAddressFactory->create();
        $customAddress->setData($address)->setCustomerId(1)->setIsDefaultBilling('1')->setIsDefaultShipping('1')->setSaveInAddressBook('1');
        $customAddress->save();
        // Set Customer Addredd Data by Id End
        return 0;

    }


    public function cleanFlush()
    {       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try{
            $_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
            $_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
            $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
            foreach ($types as $type) {
                $_cacheTypeList->cleanType($type);
            }
            foreach ($_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }catch(Exception $e){
            return $e->getMessage();
        }
    }



    public function deleteCustomer($deleteCustomerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $customerSession->setCustomerEnroller('');
        $this->_registry->register('isSecureArea', true);
        $customerData = $this->_customerFactory->create()->load($deleteCustomerId);
        try
        {
           $customerData->delete();           
           
        } catch (\Exception $e) {
           echo $e->getMessage();
        }
       
    }

 
}	
