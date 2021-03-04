<?php
namespace Globalia\Quickcheckout\Controller\Index;

use Magento\Framework\App\Action\Context;

class Paymentpopup extends \Magento\Framework\App\Action\Action
{
		protected $session;
		protected $saphelper;
		protected $eBizCharge;
		protected $resultJsonFactory;
		 
		public function __construct(
                \Magento\Framework\App\Action\Context $context,
                \Magento\Customer\Model\Session $customerSession,
                \Sttl\Adaruniforms\Helper\Sap $saphelper,
                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
                \Sttl\Adaruniforms\Helper\Ebizcharge $eBizCharge
		    )
		    {
                $this->session = $customerSession;
                parent::__construct($context);
                $this->saphelper = $saphelper;
                $this->resultJsonFactory = $resultJsonFactory;
                $this->eBizCharge = $eBizCharge;

		    }

		public function execute()
    	{
    		$resultJson = $this->resultJsonFactory->create();
    		 $order_id = '';
	        $po_number = '';
	        //$style  ='';
	        $savedata = true;
	        $post = $this->getRequest()->getParams();
	        $post['fromCreatOrder'] = "web";


	        if(isset($post['back_order_id']))
	        {
	            $order_id = base64_decode($post['back_order_id']);

	        }
	        if(isset($post['back_po_number']))
	        {
	            $po_number = $post['back_po_number'];
	        }

    		if (!$this->session->isLoggedIn())
	        {	            
	        	$data['error'] = true;
	        	$data['message'] = "Customer Session Expired.";
	        	$data['orderId'] = '';
	            return $resultJson->setData($data);
	        }
	        else
	        {

                if($order_id){
                   $orderStatus = $this->saphelper->checktOrderStatus($order_id);
                   if($orderStatus != 'Draft'){
                        $response = [
                            'error' => true,
                            'orderStatus' => __($orderStatus),
                            'orderId' => $order_id,
                            'isOrderSubmitted' => ($orderStatus == 'Submitted') ? true : false,
                            'message' => __("This Order is already submitted.")
                        ];
                        return $resultJson->setData($response);
                    }
                }
	        	$orderinfo = array();

            		$post['action'] = 'payment';
	                if($order_id != '')
	                {
	                    $post['order_id'] = $order_id ;
	                    $orderinfo['order_id'] = $post['order_id'];
	                    $post['action'] = 'back';
	                }else{
	                    $orderinfo['order_id'] = isset($post['order_id']) ? $post['order_id'] : "";
	                }
	                if($po_number != '')
	                {
	                    $post['po_number'] = $po_number ;
	                    $orderinfo['po_number'] = $post['po_number'];
	                }else{
	                    $orderinfo['po_number'] = isset($post['po_number']) ? $post['po_number'] : "";
	                }
	    		$customerData = $this->saphelper->getCustomerDetails(["CardCode", "BState", "BCountry", "BillingName", "BillingAddress", "BStateName", "BZipCode", "BCity","PaymentTerm","ShipCode","ShipType"]);
	    		
	    		$search_query=array(
                        array(
                            'Field'=>'CustomerID',  
                            'Type'=>'eq',
                            'Value'=>$customerData[0]['CardCode'])
                );
                if($customerData[0]['PaymentTerm'] == 'Credit Card Auto' && $post['back_order_id'] != '') {
                    $eBizChargedata = $this->eBizCharge->searchCustomerByParams($search_query,true,0,1);
                
                }
                else
                {
                	$eBizChargedata = '';
                }
                if(isset($customerData) && isset($customerData['errors'])) 
                {

                    $data['error'] = true;
                    $data['message'] = $customerData['message'];
                    $data['orderId'] = '';
                    return $resultJson->setData($data);
                }

                $orderdata = $this->saphelper->getSapOrders($customerData[0]['CardCode'],$orderinfo['po_number'],$order_id,'Draft');

    			if($post['back_order_id'] == ''  && !isset($orderdata[0]['CardCode']))
                {
                    $data['error'] = true;
    	        	$data['message'] = "PO Number does not existing.Places create new PO.";
    	        	$data['orderId'] = '';
    	            return $resultJson->setData($data);

                }
                $ShippingId = $ShippingAddress = "";
                
                if($orderdata > 0) {
                    $ShippingId = isset($orderdata[0]['ShippingId']) ? $orderdata[0]['ShippingId'] : '';
                    $ShippingAddress = isset($orderdata[0]['ShippingAddress']) ? $orderdata[0]['ShippingAddress'] : '';
                }
                $shiipintgData = $this->saphelper->getCustomerShippingAddressDetails($customerData[0]['CardCode']);
                if($savedata  && $ShippingAddress == "")
                {
                	$orderinfo['CardID'] = '';
                    $orderinfo['DeliveryNotes'] = '';
                    if(isset($post['payment']['coupon_code']))
                    {
                        $orderinfo['coupon_code'] = $post['payment']['coupon_code'];    
                    }else{
                        $orderinfo['coupon_code'] = '';
                    }   
                    if(isset($orderdata[0]) && !empty($orderdata[0]))
                    {
                        $orderinfo['ShippingType'] =  isset($orderdata[0]['ShippingType']) ? $orderdata[0]['ShippingType'] : '';   
                    }else{
                        $orderinfo['ShippingType'] =  '';
                    }
                    $orderinfo['Comments'] = '';
                    $orderinfo['action'] = $post['action'];
                    $countrydata = [];
                    foreach ($shiipintgData as $key => $shiipintg) {
                        if($shiipintg['DefaultAdd'] == 'Y')
                        {
                            if(isset($shiipintgData[$key]) && $shiipintgData[$key] != '')
                            {
                                $statedata = $this->saphelper->getstateinfo($shiipintgData[$key]['State']);
                                $countrydata = $this->saphelper->getcontryinfo($shiipintgData[$key]['Country']);
                                $orderinfo['ShippingId'] = $shiipintgData[$key]['AddressID'];
                                $orderinfo['ShippingStreetNo'] = $shiipintgData[$key]['AddStreetNo'];
                                $orderinfo['ShippingAddress'] = $shiipintgData[$key]['Address2'];
                                //$orderinfo['ShippingState'] = $shiipintgData[$key]['State'];
                                $orderinfo['ShippingStateCode'] = isset($statedata[0]['StateCode']) ? $statedata[0]['StateCode'] : "";
                                $orderinfo['ShippingState'] = isset($statedata[0]['StateName']) ? $statedata[0]['StateName'] : ""; 
                                $orderinfo['ShippingCity'] = $shiipintgData[$key]['City'];
                                $orderinfo['ShippingZip'] = $shiipintgData[$key]['ZipCode'];
                                $orderinfo['ShippingCountry'] = isset($countrydata[0]['CountryName']) ? $countrydata[0]['CountryName'] : "";
                                $orderinfo['ShippingCountryCode'] = isset($countrydata[0]['CountryCode']) ? $countrydata[0]['CountryCode'] : ""; 
                                $orderinfo['DefaultAdd'] = $shiipintgData[$key]['DefaultAdd'];
                                if(strtolower($shiipintgData[$key]['BlindDropship']) == 'no')
                                {
                                    $shiipintgData[$key]['BlindDropship'] = 0;
                                }
                                if(strtolower($shiipintgData[$key]['BlindDropship']) == 'yes')
                                {
                                    $shiipintgData[$key]['BlindDropship'] = 1;
                                }
                                $orderinfo['BlindDropship'] = $shiipintgData[$key]['BlindDropship'];
                            }
                        }
                	}
                }
                if(!isset($orderinfo['ShippingId']))
                {
                    $orderinfo['ShippingId'] = '';
                    $orderinfo['ShippingStreetNo'] = '';
                    $orderinfo['ShippingAddress'] = '';
                    $orderinfo['ShippingStateCode'] = '';
                    $orderinfo['ShippingState'] = '';
                    $orderinfo['ShippingCity'] = '';
                    $orderinfo['ShippingZip'] = '';
                    $orderinfo['ShippingCountry'] = '';
                    $orderinfo['ShippingCountryCode'] = '';
                    $orderinfo['DefaultAdd'] = '';
                    $orderinfo['BlindDropship'] = '';
                }
                if(isset($customerData[0]) && $customerData[0] != '')
                { 
                    // $statedata = $this->saphelper->getstateinfo(!empty($customerData[0]['BState']) ? $customerData[0]['BState'] : "");
                    // $StateCode = $StateName =  "";
                    // if(count($statedata) > 0){
                    //    $StateCode =  $statedata[0]['StateCode'];
                    //    $StateName = $statedata[0]['StateName'];
                    // }
                    $countrydata = $this->saphelper->getcontryinfo(!empty($customerData[0]['BCountry']) ? $customerData[0]['BCountry'] : "");
                    $CountryCode = $CountryName = "";
                    if(count($countrydata) > 0){
                       $CountryCode =  $countrydata[0]['CountryCode'];
                       $CountryName = $countrydata[0]['CountryName'];
                    }
                    $orderinfo['BillingName'] = isset($customerData[0]['BillingName']) ? $customerData[0]['BillingName'] : "";
                    $orderinfo['BillingAddress'] = !empty($customerData[0]['BillingAddress']) ? $customerData[0]['BillingAddress'] : "";
                    $orderinfo['BillingState'] = !empty($customerData[0]['BStateName']) ? $customerData[0]['BStateName'] : "";
                    $orderinfo['BillingStateCode'] = !empty($customerData[0]['BState']) ? $customerData[0]['BState'] : "";
                    $orderinfo['BillingZip'] = !empty($customerData[0]['BZipCode']) ? $customerData[0]['BZipCode'] : "";
                    $orderinfo['BillingCountryCode'] = !empty($CountryCode) ? $CountryCode : "US"; 
                    $orderinfo['BillingCountry'] =  !empty($CountryName) ? $CountryName : "USA";
                    $orderinfo['BillingCity'] = !empty($customerData[0]['BCity']) ? $customerData[0]['BCity'] : "";
                    //$orderinfo['BillingAddresId'] = $customerData[0]['AddressID'];
                }
                if($savedata  && $ShippingAddress == ""){
                    $updatecustomerData = $this->saphelper->updateordershipbil($orderinfo);
                    $orderdata = $this->saphelper->getSapOrders($customerData[0]['CardCode'],$orderinfo['po_number'],$order_id,'Draft');
                }
                // $shiipintgData = $this->saphelper->getCustomerShippingAddressDetails($customerData[0]['CardCode']);
                if(count($orderdata) > 0){
                    $orderdatalineitem = $this->saphelper->getOrderAllItems($orderdata[0]['Id'],$orderdata[0]['dataFrom']);
                    $getOrdersData = $this->saphelper->getOrdersData($orderdata[0]['Id'],$orderdata[0]['dataFrom'],$customerData[0]['CardCode']);
                }
                else{
                	$orderdatalineitem=[];
                	$getOrdersData=[];
                }

                $data = [];
                $data['customerdata'] = $customerData;
                $data['orderdata'] = $orderdata;
                $data['shippingdata'] = $shiipintgData;
      			$data['eBizChargedata'] = $eBizChargedata;
      			$data['orderdatalineitem'] = $orderdatalineitem;
      			$data['getOrdersData'] = $getOrdersData;
      			$data['error'] = false;
                return $resultJson->setData($data);
    	    }
    	}
}