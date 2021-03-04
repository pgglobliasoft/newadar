<?php

namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Createorderjs extends \Magento\Framework\App\Action\Action
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
    ) {
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
        if (!$this->session->isLoggedIn()) {
            $response = [
                'session_distroy' => true,
                'message' => __("Customer session expired please login.")
            ];
            return $resultJson->setData($response);
        } else {
            $customerDdta['CardCode'] = $this->session->getCustomer()->getData('customer_number');
            $post = $this->getRequest()->getParams();


            echo "<pre>";
            print_r($post);


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

                $customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount", "CardName", "CardCode", "Program", "Tier", "BulkDiscount"]);
                
                $parent_color_data = $this->saphelper->getStyleInventoryStatus($style);
            


                if (empty($checkponumber) && $is_savedata == 1) {
                    $cnt = 0;
                    if (isset($post["qty"]) && !empty($post["qty"])) {

                        if (empty($order_id)) {
                            $order_id = $this->saphelper->insertdataordr($customerdata[0], $post['po_number'], 'B2B');
                        }
                        
                        if (!empty($order_id) && $order_id != '') {
                            $colorCondation = " AND MWEB_Temp_RDR1.ColorCode = '" . $submitcolor . "'";
                            $totalQty = 0;
                            $gd_total = 0;
                            $mainTableUPdate = false;
                            $delete_skus = array();
                            $gettempitemsData = $this->saphelper->getOrderAllItems($order_id, 'T');
                            foreach ($post["qty"] as $color => $size) {
                                if (!empty($size)) {

                                    foreach ($size as $sizeKey => $qty) {

                                        if ($qty != '' && (int) $qty == 0) {
                                            $delete_skus[] = $post["itemscode"]["$color"]["$sizeKey"];
                                        }
                                        if ($qty > 0) {
                                            //$tempitemsData = $this->saphelper->getItemsData($order_id,$post["itemscode"]["$color"]["$sizeKey"]);
                                            $tempitemsData = array();
                                            if (!empty($gettempitemsData)) {
                                                $tempgetkeydata =  $this->searchForId($post["itemscode"]["$color"]["$sizeKey"], $gettempitemsData);
                                                if (isset($gettempitemsData[$tempgetkeydata])) {
                                                    $tempitemsData = $gettempitemsData[$tempgetkeydata];
                                                }
                                            }

                                            if (!empty($tempitemsData)) {
                                                $totalQty = (int) $totalQty + (int) $qty;
                                                $gd_total = $gd_total + $qty * $post["showprice"]["$color"]["$sizeKey"];
                                                $itemPerPrice = $qty * $post["showprice"]["$color"]["$sizeKey"];

                                                $itemdata = array();

                                                $itemdata['PriceAfterDiscount'] = !empty($post["inpprice"]["$color"]["$sizeKey"]) ? $post["inpprice"]["$color"]["$sizeKey"] : $itemPerPrice;
                                                $itemdata['TotalPrice'] = !empty($post["inpprice"]["$color"]["$sizeKey"]) ? $post["inpprice"]["$color"]["$sizeKey"] : $itemPerPrice;
                                                $itemdata['QTYOrdered'] = $qty;
                                                $itemdata['BaseDoc'] = $order_id;
                                                $itemdata['DisPrice'] = $post["showprice"]["$color"]["$sizeKey"];
                                                $itemdata['UnitPrice'] = $post["mainprice"]["$color"]["$sizeKey"];
                                                $itemdata['DiscountPer'] =  number_format((float) $post["DiscountPer"]["$color"]["$sizeKey"], 2, '.', '');
                                                //$itemdata['ColorCode'] =$post["style"] 
                                                $itemdata['itemscode'] = $post["itemscode"]["$color"]["$sizeKey"];
                                                $updateorderdata = $this->saphelper->updateDataOrderItems($itemdata, $tempitemsData['Id']);
                                                $cnt++;
                                                $mainTableUPdate = true;
                                            } else {
                                                $totalQty = (int) $totalQty + (int) $qty;
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
                                                $getkeydata =  $this->searchForId($itemdata['itemscode'], $parent_color_data);
                                                if (isset($parent_color_data[$getkeydata])) {
                                                    $keytempitemsData = $parent_color_data[$getkeydata];
                                                } else {
                                                    $keytempitemsData = array();
                                                }
                                                $insertorderdata = $this->saphelper->insertdataordritems($itemdata, $keytempitemsData);
                                                $cnt++;
                                                $mainTableUPdate = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if (!empty($delete_skus)) {
                                //echo "<pre>";print_R($delete_skus);exit;
                                $this->saphelper->removePObyItems($order_id, $delete_skus);
                            }
                            if ($mainTableUPdate) {
                                $gd_total =  $this->saphelper->getOrderSumItems($order_id);
                                $totalQty =  $this->saphelper->getOrderSumQty($order_id);
                            }
                            if (!empty($totalQty[0]['TotalQtyOrdered']) && !empty($gd_total[0]['TotalPriceOrdered'])) {
                                $FlatDiscount = $post['flatDiscount'];
                                $sellingprice = $gd_total[0]['TotalPriceOrdered'];
                                $DiscountAmount = "";
                                if ($FlatDiscount > 0) {
                                    $sellingprice = $gd_total[0]['TotalPriceOrdered'] - ($gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100));
                                    $DiscountAmount = $gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100);
                                }

                                $this->saphelper->updateordertotal($order_id, $totalQty[0]['TotalQtyOrdered'], $gd_total[0]['TotalPriceOrdered'], $FlatDiscount, $DiscountAmount, $sellingprice, 0, 0, $customerdata[0]);
                            }
                        }

                        $resultPage = $this->resultPageFactory->create();
                        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
                        

                        $filnalHtml = $resultPage->getLayout()
                                ->createBlock('Sttl\Adaruniforms\Block\View')
                                ->setOrderId($order_id)
                                ->setStyle($style)
                                ->setSubmitcolor($submitcolor)
                                ->setTemplate('Sttl_Customerorder::OrderTotal.phtml')
                                ->toHtml();


                        $response = [
                            'errors' => false,
                            'order_id' => $order_id,
                            'base64_order_id' => base64_encode($order_id),
                            'base64_ncp_id' => base64_encode($po_number),
                            'html' => $filnalHtml,
                            'message' => __("PO saved successfully.")
                        ];

                        // }
                    } else {
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
    public function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['ItemCode'] == $id) {
                return $key;
            }
        }
        return null;
    }
}
