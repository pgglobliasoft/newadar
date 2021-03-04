<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class ShippingAddress extends \Magento\Framework\App\Action\Action
{
        protected $resultPageFactory;

        protected $session;

        protected $dataObjectFactory;

        protected $resultJsonFactory;

        //protected $_customerRepositoryInterface;

        public function __construct(
            context $context,
            \Magento\Customer\Model\Session $customerSession,
            PageFactory $resultPageFactory,
            \Magento\Framework\DataObjectFactory $dataObjectFactory,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Sttl\Adaruniforms\Helper\Sap $saphelper
            )
        {
            $this->session = $customerSession;
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
            $this->dataObjectFactory = $dataObjectFactory;
            $this->resultJsonFactory = $resultJsonFactory;
            $this->saphelper = $saphelper;
        }
        

        public function execute()
        {

            $resultJson = $this->resultJsonFactory->create();
            $response = '';
            if (!$this->session->isLoggedIn())
            {
                $response = [
                    'session_distroy' => true,
                    'message' => __("Customer session expired please login.")
                ];
                return $resultJson->setData($response);
            }
            else
            {               
                $post = $this->getRequest()->getParams();
                $order_number = $post['order_number'];               
                try {

                        if($order_number)
                        {
                            $address = $this->customerAddressSet($order_number);
                            $orderinfo['order_id'] = $order_id;
                                $customerData = $this->saphelper->getCustomerDetails(["CardCode", "BState", "BCountry", "BillingName", "BillingAddress", "BStateName", "BZipCode", "BCity","PaymentTerm","ShipCode","ShipType"]);            
                                $shiipintgData = $this->saphelper->getCustomerShippingAddressDetails($customerData[0]['CardCode']);
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
                                            $billingstatedata = $this->saphelper->getstateinfo($customerData[0]['BState']);
                                            $billingcountrydata = $this->saphelper->getcontryinfo($customerData[0]['BCountry']);                     
                                            $orderinfo['BillingName'] =  @$customerData[0]['BillingName'];
                                            $orderinfo['BillingAddress'] =  @$customerData[0]['BillingAddress'];
                                            $orderinfo['BillingStateCode'] =  @$customerData[0]['BState'];
                                            $orderinfo['BillingState'] =  @$customerData[0]['BStateName'];
                                             $orderinfo['BillingZip'] =  @$customerData[0]['BZipCode'];
                                            $orderinfo['BillingCountryCode'] =  @$customerData[0]['BCountry'];                
                                            $orderinfo['BillingCountry'] =  isset($billingcountrydata[0]['CountryName']) ? $billingcountrydata[0]['CountryName'] : "";
                                            $orderinfo['BillingCity'] =  @$customerData[0]['BCity'];
                                            $orderinfo['ShippingType'] =  @$customerData[0]['ShipType'];                                             
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
                                // print_r($orderinfo);
                                $data = $this->saphelper->SetBillingAddress($customerData , $orderinfo);
            
                            $response = [
                                            'errors' => false,
                                            'order_id' => $order_number,
                                            'base64_order_id' => base64_encode($order_number),
                                            'message' => __("PO saved successfully.")
                                        ];


                        }else{
                               $response = [
                                    'errors' => true,
                                    'message' => __("Please add Qty")
                                ];
                        }
                    
                        return $resultJson->setData($response);

                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $message = $e->getMessage();
                        $response = [
                            'errors' => true,
                            'message' => __($message)
                        ];
                    } catch (\Exception $e) {
                        $response = [
                            'errors' => true,
                            'message' => __($e->getMessage())
                        ];
                    }
                    return $resultJson->setData($response);
            }
        }

        public function customerAddressSet($order_id)
        {
            $orderinfo['order_id'] = $order_id;
            $customerData = $this->saphelper->getCustomerDetails(["CardCode", "BState", "BCountry", "BillingName", "BillingAddress", "BStateName", "BZipCode", "BCity","PaymentTerm","ShipCode","ShipType"]);            
            $shiipintgData = $this->saphelper->getCustomerShippingAddressDetails($customerData[0]['CardCode']);
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
                        $billingstatedata = $this->saphelper->getstateinfo($customerData[0]['BState']);
                        $billingcountrydata = $this->saphelper->getcontryinfo($customerData[0]['BCountry']);                     
                        $orderinfo['BillingName'] =  @$customerData[0]['BillingName'];
                        $orderinfo['BillingAddress'] =  @$customerData[0]['BillingAddress'];
                        $orderinfo['BillingStateCode'] =  @$customerData[0]['BState'];
                        $orderinfo['BillingState'] =  @$customerData[0]['BStateName'];
                         $orderinfo['BillingZip'] =  @$customerData[0]['BZipCode'];
                        $orderinfo['BillingCountryCode'] =  @$customerData[0]['BCountry'];                
                        $orderinfo['BillingCountry'] =  isset($billingcountrydata[0]['CountryName']) ? $billingcountrydata[0]['CountryName'] : "";
                        $orderinfo['BillingCity'] =  @$customerData[0]['BCity'];
                        $orderinfo['ShippingType'] =  @$customerData[0]['ShipType'];                                             
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
            // print_r($orderinfo);
            $data = $this->saphelper->SetBillingAddress($customerData , $orderinfo);
            return $data;

        }

}


