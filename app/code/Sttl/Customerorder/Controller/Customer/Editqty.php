<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Editqty extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $sapHelper;

protected $session;

protected $storemanager;

protected $resultJsonFactory;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
	\Sttl\Adaruniforms\Helper\Sap $sapHelper,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	\Magento\Store\Model\StoreManagerInterface $storemanager
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
	$this->sapHelper = $sapHelper;
	$this->_storemanager = $storemanager;
	$this->resultJsonFactory = $resultJsonFactory;
}
public function execute()
{

	$resultJson = $this->resultJsonFactory->create();

	$resultRedirect = $this->resultRedirectFactory->create();
    if (!$this->session->isLoggedIn())
    {
        // $resultRedirect->setPath($this->_storemanager->getStore()->getBaseUrl());
        $this->session->setCustomRedirectUrl($this->_storeManager->getStore()->getCurrentUrl(false));
        $resultRedirect->setPath('login');
        return $resultRedirect;
    }
    else
    {
    	$deleteArray = array();
    	$post = $this->getRequest()->getParams();
      $edit_data = $post['line_data'];

      $edited_item_array = array();

      $order_id = '';
    if(isset($post['line_data'])){

        $itemdata = array();
        // $itemdata = array();
        $response = [
                    'errors' => true,
                    'message' => __("Item Not Edit.")
                ];

        foreach ($edit_data as $key => $value) {
      			if(!empty($value['itemscode'])){
                  $order_id = $value['order_id'];
                  $qty = $value['qty'];
                  $UnitPrice = $value['showprice'];
                  $DiscountPer = $value['DiscountPer'];
                  $itemscode = $value['itemscode'];
                  // $id = $value['id'];

                  $TotalPrice = $qty * $UnitPrice;


                      $itemdata["TotalPrice"] = $TotalPrice;
                      $itemdata["PriceAfterDiscount"] = $TotalPrice ;
                      $itemdata["QTYOrdered"] = $value['qty'];
                      $itemdata["BaseDoc"] = $value['order_id'];
                      $itemdata["DisPrice"] = $value['showprice'];
                      $itemdata["UnitPrice"] = $value['showprice'];
                      $itemdata["DiscountPer"] = number_format((float)$value["DiscountPer"], 2, '.', '');;
                      $itemdata["itemscode"] = $value['itemscode'];

                      $delete_skus = array();

                      $order_poid = $itemdata["BaseDoc"];
                      $gd_total =  $this->sapHelper->getOrderSumItems($order_id);
                      $totalQty =  $this->sapHelper->getOrderSumQty($order_id);

                      if(!empty($totalQty[0]['TotalQtyOrdered']) && !empty($gd_total[0]['TotalPriceOrdered']))
                      {

                          $result = $this->sapHelper->updateDataOrderItems($itemdata,$itemscode);
                          $gd_total =  $this->sapHelper->getOrderSumItems($order_id);
                          $totalQty =  $this->sapHelper->getOrderSumQty($order_id);

                          $getLoginCUstomerData = $this->sapHelper->getCustomerDetails();
                          $customerdata["FlatDiscount"] = $getLoginCUstomerData[0]["FlatDiscount"];
                          $customerdata["CardName"] = $getLoginCUstomerData[0]["CardName"];
                          $customerdata["CardCode"] = $getLoginCUstomerData[0]["CardCode"];
                          $customerdata["Program"] = $getLoginCUstomerData[0]["Program"];
                          $customerdata["Tier"] = $getLoginCUstomerData[0]["Tier"];
                          $customerdata["BulkDiscount"] = $getLoginCUstomerData[0]["BulkDiscount"];
                          // print_r($getLoginCUstomerData);die;
                          $FlatDiscount = $getLoginCUstomerData[0]['FlatDiscount'];
                          // print_r($FlatDiscount);die;
                          $sellingprice = $gd_total[0]['TotalPriceOrdered'];
                          $DiscountAmount = "";
                          if($FlatDiscount > 0){
                              $sellingprice = $gd_total[0]['TotalPriceOrdered'] - ($gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100));
                              $DiscountAmount = $gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100);
                          }

                          if($itemdata["QTYOrdered"] == 0){
                              $delete_skus[] = $itemdata["itemscode"];
                          }

                          if(!empty($delete_skus)){
                             $this->sapHelper->removePObyItems($order_poid,$delete_skus);
                          }

                          $this->sapHelper->updateordertotal($order_id,$totalQty[0]['TotalQtyOrdered'],$gd_total[0]['TotalPriceOrdered'],$FlatDiscount,$DiscountAmount,$sellingprice,0,0,$customerdata);


                          if($result == 0){
                            $edited_item_array[$value["colorcode"]] = $qty;
                          }

                      }


      			}
          }

          // echo $result;
          // die;

			if($result == 0){
                $distinstyle = $this->sapHelper->gettempOrdrstyle($order_id);
                $filnalHtml= "";

                  if($order_id > 0)
                  {
                    $style = '';
                    $submitcolor = '';

                        $values = array_map('array_pop', $distinstyle);
                                      $implodedStyle = implode("','", $values);
                                      $distinstyle = $this->sapHelper->getsizegroup($implodedStyle);
                                      $sizegrouparray = array();
                                      foreach($distinstyle as $key => $data)
                                      {
                                          $sizegrouparray[$data['SizeGroup']][] = $data['Style'];
                                      }
                                      $ItemStyles[] = array();
                                      // print_r($sizegrouparray);
                                    foreach ($distinstyle as $key => $data) {
                                        // if(array_key_exists($value, $styleInventory))
                                        // {
                                                $ItemStyles[0][] = $data['Style'];
                                        // }
                                    }
                                    // print_r($ItemStyles);die;
                                      $filnalHtml ='';
                                      foreach($ItemStyles as $key => $value)
                                      {
                                        $renderDataByColor = '';
                                        $groupstyle =implode("','", $value);
                                        $renderDataByColor = $this->sapHelper->newrenderMOrderItemHtml($order_id,'','','',$groupstyle);
                                        $filnalHtml .= $renderDataByColor;
                                      }
                                      $filnalHtml .= $this->sapHelper->renderMOrderItemHtmltotal($order_id,'');

                        // echo $filnalHtml;die;
                    }
                  }



                $resultPage = $this->resultPageFactory->create();
                $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
				$response = [
                    'errors' => false,
                    'limetable' => $filnalHtml,
                    'message' => __("Qty Successfully edited"),
                    // 'itemcode' => $itemdata["itemscode"],
                    'editedqty' => $edited_item_array
                ];
			}
			return $resultJson->setData($response);
		}
    }
}
