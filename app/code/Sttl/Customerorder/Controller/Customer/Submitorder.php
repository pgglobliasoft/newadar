<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Submitorder extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;
protected $registry;
protected $saphelper;
protected $resultJsonFactory; 

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\Registry $registry,
    \Sttl\Adaruniforms\Helper\Sap $saphelper,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\UrlInterface $urlInterface,  
    PageFactory $resultPageFactory
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->_messageManager = $context->getMessageManager();
    $this->_registry = $registry;
    $this->_urlInterface = $urlInterface;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->saphelper = $saphelper;

}
    public function execute()
    {

        $resultJson = $this->resultJsonFactory->create();
        $post = $this->getRequest()->getParams();
        if (!$this->session->isLoggedIn())
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            // $resultRedirect->setPath('customer/account/login');
            $this->session->setCustomRedirectUrl($this->_urlInterface->getCurrentUrl());
            $resultRedirect->setPath('login'); 
            return $resultRedirect;
            
        }else if(!empty($post) && isset($post) && $post != '')
        {
            
              $order_id =  $post['payment']['order_id'];
              if($order_id){
                   $orderStatus = $this->saphelper->checktOrderStatus($order_id);
                   if($orderStatus != 'Draft'){
                        $response = [
                            'errors' => true,
                            'orderStatus' => __($orderStatus),
                            'orderId' => $order_id,
                            'isOrderSubmitted' => ($orderStatus == 'Submitted') ? true : false,
                            'message' => __("This Order is already submitted.")
                        ];
                        return $resultJson->setData($response);
                    }
                }


            $orderinfo = array();
            $orderinfo['order_id'] = $post['payment']['order_id'];
            $orderinfo['po_number'] = $post['payment']['po_number'];
            if(isset($post['payment']['coupon_code']))
            {
                $orderinfo['coupon_code'] = $post['payment']['coupon_code'];    
            }else{
                $orderinfo['coupon_code'] = '';
            }
            //$orderinfo['style'] = $post['payment']['style'];
            $orderinfo['DeliveryNotes'] = isset($post['payment']['delivery_note']) ? $post['payment']['delivery_note'] : "";
            $orderinfo['ebiz_customer_number'] = isset($post['payment']['ebiz_customer_number']) ? $post['payment']['ebiz_customer_number'] : "";
            $orderinfo['CardID'] = '';
            if(isset($post['payment']['selectcard_id']))
            {
                $orderinfo['CardID'] = $post['payment']['selectcard_id'];
            }
            
            $orderinfo['ShippingType'] =isset($post['payment']['shiiping_method']) ? $post['payment']['shiiping_method'] : "";
             $cc_expiry_hidden = '';
            $orderinfo['Comments'] = '';
        if(isset($post['payment']['cc_expiry_hidden']) && isset($post['payment']['cc_no_hidden']))
        {
            $cc_expiry_hidden = $post['payment']['cc_expiry_hidden']; 
            $orderinfo['Comments'] = $post['payment']['cc_no_hidden'].','.$cc_expiry_hidden;
            if(isset($post['payment']['cc_attrMethodName_hidden']))
                {
                    $orderinfo['Comments'].=','.$post['payment']['cc_attrMethodName_hidden']; 
                } 
        }
            $shiipintgData = $this->saphelper->getCustomerShippingAddressDetails();
            if(isset($post['shiipingform']) && !empty($post['shiipingform']) && $orderinfo['ShippingType'] != '4')
            {
                //print_r($post['shiipingform']['hidden_fullname_shiiping']);exit;
                $orderinfo['ShippingId'] =  $post['shiipingform']['hidden_fullname_shiiping'];
                /*if(isset($post['shiipingform']['address2']) && $post['shiipingform']['address1'])
                {
                    $orderinfo['ShippingAddress'] = $post['shiipingform']['hidden_address1'];
                    
                }else{*/
                    $orderinfo['ShippingStreetNo'] = $post['shiipingform']['hidden_AddStreetNo'];
                    $orderinfo['ShippingAddress'] = $post['shiipingform']['hidden_address2'];
                    
                //}
                $countrydataShip = $this->saphelper->getcontryinfo(!empty($post['shiipingform']['hidden_country']) ? $post['shiipingform']['hidden_country'] : 'US');
                $CountryCodeShip = $CountryNameShip = "";
                if(count($countrydataShip) > 0){
                   $CountryCodeShip =  $countrydataShip[0]['CountryCode'];
                   $CountryNameShip = $countrydataShip[0]['CountryName'];
                }

                $statedataShip = $this->saphelper->getstateinfo(!empty($post['shiipingform']['hidden_state']) ? $post['shiipingform']['hidden_state'] : "");
                $StateCodeShip = $StateNameShip =  "";
                if(count($statedataShip) > 0){
                   $StateCodeShip =  $statedataShip[0]['StateCode'];
                   $StateNameShip = $statedataShip[0]['StateName'];
                }

                $orderinfo['ShippingCity'] = $post['shiipingform']['hidden_city'];
                $orderinfo['ShippingZip'] = $post['shiipingform']['hidden_zipcode'];
                $orderinfo['ShippingCountryCode'] = $post['shiipingform']['hidden_country'];
                $orderinfo['ShippingCountry'] = $CountryNameShip;
                $orderinfo['ShippingStateCode'] = $post['shiipingform']['hidden_state'];
                $orderinfo['ShippingState'] = $StateNameShip;
                $orderinfo['BlindDropship'] = $post['shiipingform']['hidden_blindDropship'];
                //$orderinfo['DefaultAdd'] = $post['shiipingform']['fullname_shiiping'];
            }else{
                $orderinfo['ShippingId'] =  '';
                $orderinfo['ShippingAddress'] = '';
                $orderinfo['ShippingCity'] = '';
                $orderinfo['ShippingZip'] = '';
                $orderinfo['ShippingCountryCode'] = '';
                $orderinfo['ShippingCountry'] = '';
                $orderinfo['ShippingStateCode'] = '';
                $orderinfo['ShippingState'] = '';
                $orderinfo['ShippingStreetNo'] = '';
                $orderinfo['BlindDropship'] = '';
            }
            $customerData = $this->saphelper->getCustomerDetails(["BState", "BStateName", "BillingAddress", "BCity", "BCountry", "BillingName"]);
            if(isset($post['billingform']) && $post['billingform'] != '')
            {
                $statedata = $this->saphelper->getstateinfo(!empty($post['billingform']['BState']) ? $post['billingform']['BState'] : $customerData[0]['BState']);
                $StateCode = $StateName =  "";
                if(count($statedata) > 0){
                   $StateCode =  $statedata[0]['StateCode'];
                   $StateName = $statedata[0]['StateName'];
                }
                $countrydata = $this->saphelper->getcontryinfo(!empty($post['billingform']['BCountry']) ? $post['billingform']['BCountry'] : $customerData[0]['BCountry']);
                $CountryCode = $CountryName = "";
                if(count($countrydata) > 0){
                   $CountryCode =  $countrydata[0]['CountryCode'];
                   $CountryName = $countrydata[0]['CountryName'];
                }
                $orderinfo['BillingName'] = isset($post['billingform']['BillingName']) ? $post['billingform']['BillingName'] : $customerData[0]['BillingName'];
                $orderinfo['BillingAddress'] = !empty($post['billingform']['billaddress1']) ? $post['billingform']['billaddress1'] : $customerData[0]['BillingAddress'];
                $orderinfo['BillingState'] =  !empty($customerData[0]['BStateName']) ? $customerData[0]['BStateName'] : "";
                $orderinfo['BillingStateCode'] = !empty($customerData[0]['BState']) ? $customerData[0]['BState'] : "";
                $orderinfo['BillingZip'] = !empty($post['billingform']['BZipCode']) ? $post['billingform']['BZipCode'] : $customerData[0]['BillingAddress'];
                $orderinfo['BillingCountryCode'] = !empty($CountryCode) ? $CountryCode : "US"; 
                $orderinfo['BillingCountry'] =  !empty($CountryName) ? $CountryName : "USA";
                $orderinfo['BillingCity'] = !empty($post['billingform']['BCity']) ? $post['billingform']['BCity'] : $customerData[0]['BCity'];
                //$orderinfo['BState'] = !empty($post['billingform']['BState']) ? $post['billingform']['BState'] : $customerData[0]['BState'];
                //$orderinfo['BStatename'] = $post['billingform']['BStatename'];
                //$orderinfo['BillingCountry'] = $post['billingform']['billCountry'];
            }
            $updatecustomerData = $this->saphelper->updateordershipbil($orderinfo);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Order Submit'));
            $this->_registry->register('submitorder',$orderinfo);
            $response = [
                            'errors' => 'false',
                            'message' => __("Success.")
                        ];
            return $resultJson->setData($response); 
            //return $resultPage;
        }
        else
        {
            $this->_messageManager->addError("Opps Something went wrong.");
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customerorder/customer/neworder');
            return $resultRedirect;
        }
    }

}