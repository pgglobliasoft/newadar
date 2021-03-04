<?php

namespace Sttl\Adaruniforms\Controller\Account;

class Accountdatarender extends \Magento\Framework\App\Action\Action {

	protected $resultForwardFactory;

	protected $sapHelper;

	protected $resultJsonFactory;

	private $CountrysId = 'countrysid';

    private $StatesId = 'statesid';

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Sttl\Adaruniforms\Helper\Ebizcharge $ebizHelper,
		\Sttl\Adaruniforms\Helper\Sap $saphelper,
		\Magento\Customer\Model\Session $Session,
        \Magento\Framework\App\CacheInterface $cache
	) {
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->sapHelper = $saphelper;
		$this->session = $Session;
		$this->ebizHelper = $ebizHelper;
        $this->cache = $cache;
	}

	public function execute() {

		$contrylist = $this->getCountryList();
		$statuslist = $this->getStateList();
		$savecard = $this->getSaveCardDetails();
		$shippingaddress = $this->getCustomerShippingAddressDetails();
		if( $contrylist && $statuslist && $savecard && $shippingaddress ){
			$response = [
					'error' => false,
					'contrylist' => $contrylist,
					'statuslist' => $statuslist,
					'savecard' => $savecard,
					'shippingaddress' => $shippingaddress,
				];
		}else{
			$response = [
					'error' => true
				];
		}
		return $this->resultJsonFactory->create()->setData($response);
	}

	public function getCustomerShippingAddressDetails() {
        return $this->sapHelper->getCustomerShippingAddressDetails();
    }

	public function getCountryList() {
        $data = $this->cache->load($this->CountrysId);
        if($data){
            return json_decode($data,true);
        }else{
            $data = $this->sapHelper->getCountryList();
            $this->cache->save(json_encode($data,true),$this->CountrysId);
            return $data;
        }
    }

    public function getStateList() {
        $data = $this->cache->load($this->StatesId);
        if($data){
            return json_decode($data,true);
        }else{
            $data = $this->sapHelper->getStateList();
            $this->cache->save(json_encode($data,true),$this->StatesId);
            return $data;
        }
    }

    /*
            * login customer phone number
    */
    public function getCustomerCustomerNumber() {
        return $this->session->getCustomer()->getData('customer_number');
    }
    public function getCustomerDetails() {
        $data = $this->sapHelper->getCustomerDetails(["CardCode"]);
        if (isset($data[0]) && !empty($data[0])) {
            $data = $data[0];
        }
        return $data;
    }
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

        return ["savecard" =>$saved_cards,"custnum" => $custNum];
    }
}
