<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Payment extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;
protected $registry;
protected $saphelper;
protected $eBizCharge;
 
public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\Registry $registry,
    \Sttl\Adaruniforms\Helper\Sap $saphelper,
    PageFactory $resultPageFactory,
    \Sttl\Adaruniforms\Helper\Ebizcharge $eBizCharge
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
     $this->_messageManager = $context->getMessageManager();
    $this->_registry = $registry;
    $this->saphelper = $saphelper;
    $this->eBizCharge = $eBizCharge;

}
    public function execute()
    {
        $order_id = '';
        $po_number = '';
        //$style  ='';
        $savedata = true;
        $post = $this->getRequest()->getParams();
        if($this->getRequest()->getParam('back_order_id') != '' && !isset($post['fromCreatOrder']))
        {
            $savedata = false;
            $order_id = base64_decode($this->getRequest()->getParam('back_order_id'));

        }
        if($this->getRequest()->getParam('back_po_number') != '')
        {
            $po_number = base64_decode($this->getRequest()->getParam('back_po_number'));
        }
        /**if($this->getRequest()->getParam('back_style') != '')
        {
            $style = base64_decode($this->getRequest()->getParam('back_style'));
        }**/
        if (!$this->session->isLoggedIn())
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }else if((!empty($post) && isset($post) && $post != '') || ($order_id !='' && $po_number !=''))
        {
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
            /**if($style != '')
            {
                $post['style'] = $style ;
                $orderinfo['style'] = $post['style'];
            }else{
               $orderinfo['style'] = $post['style'];

            }**/
            $customerData = $this->saphelper->getCustomerDetails(["CardCode", "BState", "BCountry", "BillingName", "BillingAddress", "BStateName", "BZipCode", "BCity","PaymentTerm","ShipCode","ShipType"]);
            $search_query=array(
            array(
                'Field'=>'CustomerID',  
                'Type'=>'eq',
                'Value'=>$customerData[0]['CardCode'])
            );
            if($customerData[0]['PaymentTerm'] == 'Credit Card Auto' && $this->getRequest()->getParams('back_order_id') != '') {
                $eBizChargedata = $this->eBizCharge->searchCustomerByParams($search_query,true,0,1);
            
            }
            if(isset($customerData) && isset($customerData['errors'])) 
            {
                $this->_messageManager->addError($customerData['message']);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customerorder/customer/neworder');
                return $resultRedirect;
            }
            $orderdata = $this->saphelper->getSapOrders($customerData[0]['CardCode'],$orderinfo['po_number'],$order_id,'Draft');
            if($this->getRequest()->getParam('back_order_id') != '' && !isset($post['fromCreatOrder']) && !isset($orderdata[0]['CardCode']))
            {
                $this->_messageManager->addError('PO Number does not existing.Places create new PO.');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customerorder/customer/neworder');
                return $resultRedirect;

            }
            $ShippingId = $ShippingAddress = "";
            
            if($orderdata > 0) {
                $ShippingId = isset($orderdata[0]['ShippingId']) ? $orderdata[0]['ShippingId'] : '';
                $ShippingAddress = isset($orderdata[0]['ShippingAddress']) ? $orderdata[0]['ShippingAddress'] : '';
            }
            $shiipintgData = $this->saphelper->getCustomerShippingAddressDetails($customerData[0]['CardCode']);
            if($savedata && $ShippingId == "" && $ShippingAddress == "")
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
                foreach ($shiipintgData as $key => $shiipintg) {
                    if($shiipintg['DefaultAdd'] == 'Y')
                    {
                        if(isset($shiipintgData[$key]) && $shiipintgData[$key] != '')
                        {
                            //echo "<pre>";print_R($shiipintgData[$key]);exit;
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
            //$customerData = $this->saphelper->getCustomerDetails();
            if(isset($customerData[0]) && $customerData[0] != '')
            { 
                $statedata = $this->saphelper->getstateinfo(!empty($customerData[0]['BState']) ? $customerData[0]['BState'] : "");
                $StateCode = $StateName =  "";
                if(count($statedata) > 0){
                   $StateCode =  $statedata[0]['StateCode'];
                   $StateName = $statedata[0]['StateName'];
                }
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
            $updatecustomerData = $this->saphelper->updateordershipbil($orderinfo);
        }
        $orderdata = $this->saphelper->getSapOrders($customerData[0]['CardCode'],$orderinfo['po_number'],$order_id,'Draft');
        if(!empty($orderdata))
        {
            $ragisterdata['customerdata'] = $customerData;
            $ragisterdata['orderdata'] = $orderdata;
            if(!empty($shiipintgData))
            {
                $ragisterdata['shiipintgData'] = $shiipintgData;
            }
            if(!empty($eBizChargedata))
            {
                $ragisterdata['eBizChargedata'] = $eBizChargedata;        
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__(''));
            $this->_registry->register('payemntdata',$ragisterdata);
            return $resultPage;
        }else{
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customerorder/customer/index');
            return $resultRedirect;
        }
            
        }
        else
        {
            $this->_messageManager->addError('Something went wrong.');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customerorder/customer/neworder');
            return $resultRedirect;
        }
    }

}