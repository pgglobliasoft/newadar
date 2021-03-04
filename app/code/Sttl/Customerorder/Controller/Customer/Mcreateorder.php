<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Mcreateorder extends \Magento\Framework\App\Action\Action
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
                $customerDdta['CardCode'] = $this->session->getCustomer()->getData('customer_number');
                $post = $this->getRequest()->getParams();
                $po_number = $post['po_number'];
                $style = isset($post['style']) ? $post['style'] : "";
                $is_chcheckpo = isset($post['is_chcheckpo']) ? 1 : 0;
                $is_stylenumber = isset($post['is_stylenumber']) ? 1 : 0;
                $is_searchdatabycolor = isset($post['is_searchdatabycolor']) ? 1 : 0;
                $showValue = (isset($post['showValue']) && !empty($post['showValue'])) ? 1 : 0;
                $is_savedata = isset($post['is_savedata']) ? 1 : 0;
                $getColorCode = isset($post['getColorCode']) ? $post['getColorCode'] : "";
                $order_id = isset($post['order_id']) ? $post['order_id'] : "";
                $submitcolor = isset($post['submitcolor']) ? $post['submitcolor'] : "";
                $customdata = ''; 
                $success = "false";
                $message = '';
                $enty_id = '';
                $passNext = true;
                $checkponumber = '';
                try {

                    $customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);
                    
                        if($is_chcheckpo == 1){
                            $checkponumber = $this->saphelper->checkponumber($customerDdta,$po_number);
                            $response = [
                                    'errors' => false,
                                    'message' => __("")
                                ];

                             if (!preg_match("/^[^-\s][a-zA-Z0-9!%*@#$&()\\-`.+,\-\s=\"]{3,}$/", $po_number)) {
                                $passNext = false;
                                 $response = [
                                    'errors' => true,
                                    'message' => __("PO Number must be at least 4 characters long and cannot start with a space")
                                ];
                                return $resultJson->setData($response);
                            }
                            if(!empty($checkponumber) && $checkponumber!= '' && isset($checkponumber[0]))
                            {
                                $passNext = false;
                                 $response = [
                                    'errors' => true,
                                    'message' => __("PO Number already exists.")
                                ];
                                return $resultJson->setData($response);

                            }


                        }else{
                            $parent_color_data = $this->saphelper->getStyleInventoryStatus($style);
                        }
                        if(empty($checkponumber) && $is_chcheckpo != 1){
                            if($style == ""){
                                 $response = [
                                    'errors' => true,
                                    'message' => __("Please Enter Style Number.")
                                ];
                            }
                            if($is_stylenumber == 1){
                                $renderDataPart = '';
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                                $productFactory = $objectManager->get('Magento\Catalog\Model\ProductFactory');
                                $product = $productFactory->create();
                                $product_collection_data = $product->loadByAttribute('sku', $style);
                                if(!empty($parent_color_data) && !empty($product_collection_data))
                                {
                                    $resultPage = $this->resultPageFactory->create();
                                    $renderDataPart = $resultPage->getLayout()
                                            ->createBlock('Sttl\Adaruniforms\Block\View')
                                            ->setParentStyle($style)
                                            ->setParentColorData($parent_color_data)
                                            ->setProductCollectionData($product_collection_data)
                                            ->setCustomerData($customerdata)
                                            ->setTemplate('Magento_Catalog::product/view/product_options_order.phtml')
                                            ->toHtml(); 
                                }
                               
                                if(empty($renderDataPart)){
                                    $response = [
                                        'errors' => true,
                                        'message' => __("Item not found.")
                                    ];
                                return $resultJson->setData($response);    
                                }
                                if(isset($resultPage) && !empty($resultPage))
                                {
                                   $response = [
                                        'errors' => false,
                                        'html'   => $renderDataPart,
                                        'base64_order_id' => base64_encode($order_id),
                                        'base64_ncp_id' => base64_encode($po_number),
                                        //'sizeTable'   => $renderDataByColorFirst,
                                        'message' => __("Success.")
                                    ]; 
                                }
                                
                            }
                        }
                        if(empty($checkponumber) && $is_savedata == 1)
                        {
                            // var_dump($post; die;
                            $cnt = 0;
                            if (isset($post["qty"]) && !empty($post["qty"])) 
                            {
                                // echo $order_id; die;
                                if(empty($order_id)){
                                    $orderinfo = [];
                                    $order_id = $this->saphelper->insertdataordr($customerdata[0],$post['po_number'],'B2B');
                                    // $Address = $this->customerAddressSet($order_id);   
                                     // echo 'test'.$order_id; die;
                                }
                                if(!empty($order_id) && $order_id !='')
                                {
                                    $colorCondation = " AND MWEB_Temp_RDR1.ColorCode = '".$submitcolor."'";
                                    $totalQty = 0;
                                    $gd_total = 0;
                                    $mainTableUPdate = false;
                                    $delete_skus = array();
                                    $gettempitemsData = $this->saphelper->getOrderAllItems($order_id,'T');
                                    foreach($post["qty"] as $color => $size) 
                                    {
                                        if (!empty($size)) 
                                        {

                                            foreach($size as $sizeKey => $qty) 
                                            {
                                                
                                                if($qty != '' && (int)$qty == 0)
                                                {
                                                    $delete_skus[] = $post["itemscode"]["$color"]["$sizeKey"];
                                                }
                                                if($qty > 0)
                                                {   
                                                    //$tempitemsData = $this->saphelper->getItemsData($order_id,$post["itemscode"]["$color"]["$sizeKey"]);
                                                    $tempitemsData = array();
                                                    if(!empty($gettempitemsData))
                                                    {
                                                        $tempgetkeydata =  $this->searchForId($post["itemscode"]["$color"]["$sizeKey"],$gettempitemsData);
                                                        if(isset($gettempitemsData[$tempgetkeydata]))
                                                        {
                                                          $tempitemsData = $gettempitemsData[$tempgetkeydata];  
                                                        }
                                                    }

                                                if(!empty($tempitemsData)) 
                                                    {
                                                        $totalQty = (int)$totalQty + (int)$qty;
                                                        $gd_total = $gd_total + $qty * $post["showprice"]["$color"]["$sizeKey"];
                                                        $itemPerPrice = $qty * $post["showprice"]["$color"]["$sizeKey"];

                                                        $itemdata = array();
                                                    
                                                        $itemdata['PriceAfterDiscount'] = !empty($post["inpprice"]["$color"]["$sizeKey"]) ? $post["inpprice"]["$color"]["$sizeKey"] : $itemPerPrice;
                                                        $itemdata['TotalPrice'] = !empty($post["inpprice"]["$color"]["$sizeKey"]) ? $post["inpprice"]["$color"]["$sizeKey"] : $itemPerPrice;
                                                        $itemdata['QTYOrdered'] = $qty;
                                                        $itemdata['BaseDoc'] = $order_id;
                                                        $itemdata['DisPrice'] = $post["showprice"]["$color"]["$sizeKey"];
                                                        $itemdata['UnitPrice'] = $post["mainprice"]["$color"]["$sizeKey"];
                                                        $itemdata['DiscountPer'] =  number_format((float)$post["DiscountPer"]["$color"]["$sizeKey"], 2, '.', '');
                                                        //$itemdata['ColorCode'] =$post["style"] 
                                                        $itemdata['itemscode'] = $post["itemscode"]["$color"]["$sizeKey"];
                                                        $updateorderdata = $this->saphelper->updateDataOrderItems($itemdata,$tempitemsData['Id']);
                                                        $cnt++;
                                                        $mainTableUPdate = true;
                                                    } else {
                                                        $totalQty = (int)$totalQty + (int)$qty;
                                                        $gd_total = $gd_total + $qty * $post["showprice"]["$color"]["$sizeKey"];
                                                        $itemPerPrice = $qty * $post["showprice"]["$color"]["$sizeKey"];

                                                        $itemdata = array();
                                                    
                                                        $itemdata['Style'] = $post["style"];
                                                        $itemdata['ColorName'] = $color;
                                                        $itemdata['Size'] = $sizeKey;
                                                        $itemdata['BaseDoc'] = $order_id;
                                                        $itemdata['PriceAfterDiscount'] = !empty($post["inpprice"]["$color"]["$sizeKey"]) ? $post["inpprice"]["$color"]["$sizeKey"] : $itemPerPrice;
                                                        $itemdata['TotalPrice'] = !empty($post["inpprice"]["$color"]["$sizeKey"]) ? $post["inpprice"]["$color"]["$sizeKey"] : $itemPerPrice;
                                                        //$itemdata['DeliveredQTY'] = $qty;
                                                        $itemdata['QTYOrdered'] = $qty;
                                                        $itemdata['DisPrice'] = $post["showprice"]["$color"]["$sizeKey"];
                                                        $itemdata['UnitPrice'] = $post["mainprice"]["$color"]["$sizeKey"];
                                                        $itemdata['itemscode'] = $post["itemscode"]["$color"]["$sizeKey"];
                                                        $itemdata['colorcode'] = $post["colorcode"]["$color"]["$sizeKey"];
                                                        $itemdata['DiscountPer'] = $post["DiscountPer"]["$color"]["$sizeKey"];
                                                        //$itemdata['ColorCode'] =$post["style"] 
                                                       $itemdata['OrderOption'] = '1';
                                                       $getkeydata =  $this->searchForId($itemdata['itemscode'],$parent_color_data);
                                                       if(isset($parent_color_data[$getkeydata]))
                                                       {
                                                            $keytempitemsData = $parent_color_data[$getkeydata];
                                                       }else{
                                                            $keytempitemsData = array();
                                                       }
                                                       $insertorderdata = $this->saphelper->insertdataordritems($itemdata,$keytempitemsData);
                                                        $cnt++;
                                                        $mainTableUPdate = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if(!empty($delete_skus))
                                    {
                                        //echo "<pre>";print_R($delete_skus);exit;
                                         $this->saphelper->removePObyItems($order_id,$delete_skus);
                                    }
                                    if($mainTableUPdate){
                                      $gd_total =  $this->saphelper->getOrderSumItems($order_id);
                                      $totalQty =  $this->saphelper->getOrderSumQty($order_id);
                                    }
                                    if(!empty($totalQty[0]['TotalQtyOrdered']) && !empty($gd_total[0]['TotalPriceOrdered']))
                                    {
                                        $FlatDiscount = $post['flatDiscount'];
                                        $sellingprice = $gd_total[0]['TotalPriceOrdered'];
                                        $DiscountAmount = "";
                                        if($FlatDiscount > 0){
                                            $sellingprice = $gd_total[0]['TotalPriceOrdered'] - ($gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100));
                                            $DiscountAmount = $gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100);
                                        }

                                        $this->saphelper->updateordertotal($order_id,$totalQty[0]['TotalQtyOrdered'],$gd_total[0]['TotalPriceOrdered'],$FlatDiscount,$DiscountAmount,$sellingprice,0,0,$customerdata[0]);
                                    }
                                }
                                    //if ($cnt > 0) {
                                        //$this->cart->save();  
                                        /**$distinstyle = $this->saphelper->gettempOrdrstyle($order_id); 
                                        $values = array_map('array_pop', $distinstyle);
                                        $implodedStyle = implode("','", $values);
                                        $distinstyle = $this->saphelper->getsizegroup($implodedStyle); 
                                        $sizegrouparray = array();
                                        foreach($distinstyle as $key => $data)
                                        {
                                            $sizegrouparray[$data['SizeGroup']][] = $data['Style'];
                                        }
                                        $filnalHtml ='';
                                        foreach($sizegrouparray as $key => $value)
                                        {
                                          $renderDataByColor = '';
                                          $groupstyle =implode("','", $value);
                                          $renderDataByColor = $this->saphelper->newrenderOrderItemHtml($order_id,$style,$submitcolor,'',$groupstyle);  
                                          $filnalHtml .= $renderDataByColor;
                                        } 
                                        $filnalHtml .= $this->saphelper->renderOrderItemHtmltotal($order_id,'');
                                        **/
                                        
                                         $resultPage = $this->resultPageFactory->create();
                                         $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
                                         $filnalHtml = $resultPage->getLayout()->createBlock('Sttl\Adaruniforms\Block\View')
                                                        ->setOrderId($order_id)
                                                        ->setStyle($style)
                                                        ->setSubmitcolor($submitcolor)
                                                        ->setTemplate('Sttl_Customerorder::OrderTotal.phtml')->toHtml();
                                                        // echo $filnalHtml; die;
                                        $response = [
                                            'errors' => false,
                                            'order_id' => $order_id,
                                            'base64_order_id' => base64_encode($order_id),
                                            'base64_ncp_id' => base64_encode($po_number),
                                            'html' => $filnalHtml,
                                            'message' => __("PO saved successfully.")
                                        ];

                                   // }
                            }
                            else 
                            {
                                $response = [
                                    'errors' => true,
                                    'message' => __("Please add Qty")
                                ];
                            }
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

        public function searchForId($id, $array) {
            foreach ($array as $key => $val) {
               if ($val['ItemCode'] == $id) {
                   return $key;
               }
            }
           return null;
        }

}


