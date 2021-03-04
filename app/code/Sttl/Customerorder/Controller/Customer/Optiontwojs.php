<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;

class Optiontwojs extends \Magento\Framework\App\Action\Action
{
    protected $session;

    protected $dataObjectFactory;

    protected $resultJsonFactory;

    public function __construct(
        context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Sttl\Adaruniforms\Helper\Sap $saphelper
    )
    {
        $this->session = $customerSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->saphelper = $saphelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
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

            $po_number = $post['po_number'];
            $base_order_id = isset($post['base_order_id']) ? $post['base_order_id'] : '';
            $is_option1 = isset($post['is_savedata']) ? 1 : 0;
            $is_option2 = isset($post['activeoption']) ? 1 : 0;
            if($is_option2){
                if($post['activeoption'] == ''){
                    $is_option2 = 0;
                }
            }

            $message = '';
            $backorder = '';
            $backorderQty  = '';
            $responseData = '';
            $order_id = false;
            $responsebutton = false;
            $response = array();
            $neworder_id = '';
            $bse64_neworder_id = '';
            $order_id_for_option1 = '';
            $order_id_for_option2 = '';
            try {   
                    if($is_option1 || $is_option2){
                        
                        if(isset($post['customerdata'])){
                            $customerdata = json_decode($post["customerdata"], true);
                        }else{
                            $customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);
                        }
                        if(isset($customerdata[0])){
                            $order_id = isset($post['order_id']) ? $post['order_id'] : "";
                            $submitcolor = isset($post['submitcolor']) ? $post['submitcolor'] : "";
                            $success = "false";
                            $message = '';
                            $enty_id = '';
                            $passNext = true;
                            
                            $deleteskus = isset($post['deletedsku']) ? $post['deletedsku'] : "";

                            $all_local_orderitemdata = json_decode($post["allorderitemdata"], true);
                            
                            $tmp_alllocalorderitems = array();
                            foreach ($all_local_orderitemdata as $key => $local_order_item) {
                                $tmp_alllocalorderitems[$local_order_item['ItemCode']] = $local_order_item;
                            }

                            if (empty($order_id)) {
                                $order_id = $this->saphelper->insertdataordr($customerdata[0], $post['po_number'], 'B2B');
                                $order_id_for_option1 = $order_id;
                                $bse64_neworder_id = base64_encode($order_id_for_option1);
                            }


                            $order_id_for_option1 = $order_id;

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

                            // echo "Test";
                            // die;
                            
                            // echo "<pre>";
                            if (!empty($order_id) && $order_id != '') {
                                $gettempitemsData = $this->saphelper->getOrderAllItems($order_id, 'T');
                                $tmp_allsaporderitems = array();
                                foreach ($gettempitemsData as $key => $sap_order_item) {
                                    $tmp_allsaporderitems[$sap_order_item['ItemCode']] = $sap_order_item;
                                }
                                $itemexe_count = 0;
                                $itemnotexe_count = 0;
                                
                                
                                foreach ($tmp_alllocalorderitems as $key => $order_item_data) {
                                    if(array_key_exists($key, $tmp_allsaporderitems)){
                                        // Update Order Item
                                        $saporderitemqty = $tmp_allsaporderitems[$key]['QTYOrdered'];
                                        if($order_item_data['QTYOrdered'] != '' && $order_item_data['QTYOrdered'] > 0){
                                            $itemdata = array();
                                            $itemdata['PriceAfterDiscount'] = number_format($order_item_data['PriceAfterDiscount'],2);
                                            $itemdata['TotalPrice'] = number_format($order_item_data['TotalPrice'],2);
                                            $itemdata['QTYOrdered'] = (int) $order_item_data['QTYOrdered'];
                                            $itemdata['BaseDoc'] = $order_id;
                                            $itemdata['DisPrice'] = number_format($order_item_data['DiscountPrice'],2);
                                            $itemdata['UnitPrice'] = number_format($order_item_data['UnitPrice'],2);
                                            $itemdata['DiscountPer'] = (int) $order_item_data['DiscountPer'];
                                            $itemdata['itemscode'] = $order_item_data['ItemCode'];
                                            // echo "New function to Update a Order Item";
                                            // print_r($itemdata);
                                            $updateorderdata = $this->saphelper->updateDataOrderItems($itemdata, $tmp_allsaporderitems[$key]['Id']);
                                            $mainTableUPdate = true;
                                        }
                                    }else{
                                        // Insert Order Item
                                        if($order_item_data['QTYOrdered'] != ''){
                                            $itemdata = array();
                                            $itemdata['Style'] = $order_item_data['Style'];
                                            $itemdata['ColorName'] = $order_item_data['Color'];
                                            $itemdata['Size'] = $order_item_data['Size'];
                                            $itemdata['BaseDoc'] = $order_id;
                                            $itemdata['PriceAfterDiscount'] = number_format($order_item_data['PriceAfterDiscount'],2);
                                            $itemdata['TotalPrice'] = number_format($order_item_data['TotalPrice'],2);
                                            $itemdata['QTYOrdered'] = (int) $order_item_data['QTYOrdered'];
                                            $itemdata['DisPrice'] = number_format($order_item_data['DiscountPrice'],2);
                                            $itemdata['UnitPrice'] = number_format($order_item_data['UnitPrice'],2);
                                            $itemdata['DiscountPer'] = (int) $order_item_data['DiscountPer'];
                                            $itemdata['itemscode'] = $order_item_data['ItemCode'];
                                            $itemdata['colorcode'] = $order_item_data['ColorCode'];
                                            $itemdata['OrderOption'] = '1';
                                            $keytempitemsData = array();
                                            // echo "New function to Insert a Order Item";
                                            // print_r($itemdata);
                                            // die;
                                            $insertorderdata = $this->saphelper->insertdataordritems($itemdata, $keytempitemsData);
                                            $mainTableUPdate = true;
                                        }
                                    }
                                }

                                // die;

                                if (!empty($deleteskus)) {
                                    // echo "<pre>";
                                    // print_r($deleteskus);
                                    $this->saphelper->removePObyItemsfromJS($order_id, $deleteskus);
                                }

                                $ordertotaldata = json_decode($post["ordersummary"], true);

                                $this->saphelper->updateordertotal($order_id_for_option1, $ordertotaldata['TotalQtyOrdered'], $ordertotaldata['TotalBeforeDiscount'], $ordertotaldata['FlatDiscount'], $ordertotaldata['DiscountAmount'], $ordertotaldata['DocTotal'], 0, 0, $customerdata[0]);
                                
                                $response = ['success' => true ];
                                // $response = ['errors' => true ];
                            }

                            if(isset($response['success']))
                            {
                                if($is_option1 == 1){
                                    $order_id = $order_id_for_option1;
                                    // echo $order_id;
                                }else if($base_order_id != '' && $base_order_id){
                                    $order_id = $base_order_id;
                                }else{
                                    $order_id = $order_id_for_option1;
                                }

                                $responsebutton = true;                        
                                if($is_option1 == 1){
                                    if($order_id)
                                    {
                                        $response['order_id'] = $order_id;
                                        $response['base64_order_id'] = base64_encode($order_id_for_option1);
                                        $response['base64_ncp_id'] = base64_encode($po_number);
                                        $response['message'] = __("PO saved successfully.");
                                    }
                                }else{
                                    if($responsebutton) 
                                    {
                                        $response['orderBtnRow'] = true;
                                    }
                                    if($order_id)
                                    {
                                        $response['order_id'] = $order_id;
                                        $response['base64_order_id'] = base64_encode($order_id_for_option1);
                                        $response['base64_ncp_id'] = base64_encode($po_number);
                                        $response['message'] = __("Item Added successfully.");
                                    }
                                }
                            }

                        }else{
                            $response = [
                            'errors' => true,
                            'message' => __('customer not existing')
                            ];
                        }
                    }

                    if($base_order_id != '' && $base_order_id){

                        $isfetchOrder = isset($post['get_order_data']) ? 1 : 0;
                        if($isfetchOrder){
                            $orderStatus = $this->saphelper->checktOrderStatus($base_order_id);
                            if($orderStatus != 'Draft'){
                                $response = [
                                    'errors' => true,
                                    'orderStatus' => __($orderStatus),
                                    'orderId' => $base_order_id,
                                    'isOrderSubmitted' => ($orderStatus == 'Submitted') ? true : false,
                                    'message' => __("This Order is already submitted.")
                                ];
                                return $resultJson->setData($response);
                            }
                        }


                        $orderdata = $this->getDatafromOrderId($base_order_id);

                        $response['line_item_render'] = $orderdata;
                        $response['order_id'] = $base_order_id;
                        $response['base64_order_id'] = base64_encode($base_order_id);
                        $response['base64_ncp_id'] = base64_encode($po_number);
                        $response['message'] = __("Line Item rendered successfully.");
                        $response['success'] = true;
                    }

                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $message = $e->getMessage();
                    $response = [
                        'errors' => true,
                        'message' => __($message)
                    ];
                    echo $e;
                    die;

                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    $response = [
                        'errors' => true,
                        'message' => __($message)
                    ];
                    echo $e;
                    die;
                }

                if($order_id_for_option1 && $order_id_for_option1 != ''){
                    if(isset($response['errors'])){
                        $orderdata = $this->getDatafromOrderId($order_id_for_option1);

                        $response['line_item_render'] = $orderdata;
                        $response['order_id'] = $order_id_for_option1;
                        $response['base64_order_id'] = base64_encode($order_id_for_option1);
                        $response['base64_ncp_id'] = base64_encode($po_number);
                        $response['message'] = __("Something went wrong please try again.");
                    }
                }

                return $resultJson->setData($response);
        }
    }


    public function getDatafromOrderId($order_id_tmp){
        $style = '';
        $submitcolor = '';
        $viewmode = '';

        $tmp_order_id = $order_id_tmp;

        $finalLineitemrewspons = array();
        $tempordersummary  =array();

        $finalLineitemrewspons['allorderdata'] = $this->saphelper->getOrderAllItems($tmp_order_id,'T');


        $tempordersummary = $this->saphelper->renderOrderItemHtmltotalJS($tmp_order_id,$viewmode);
        foreach($tempordersummary as $key => $data){
            $finalLineitemrewspons['ordersummary'] = $data;
        }
        return $finalLineitemrewspons;  
    }
}