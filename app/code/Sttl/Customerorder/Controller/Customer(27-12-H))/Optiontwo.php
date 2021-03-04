<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Optiontwo extends \Magento\Framework\App\Action\Action
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
    
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
    $this->mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();    
}
public function writeProgress($total = 0, $po_number, $first_time ='')
{
    $filename = $this->mediaPath . "option2". DIRECTORY_SEPARATOR . $po_number . ".txt";
    $lines = @file( $filename , FILE_IGNORE_NEW_LINES );
    if($first_time =='first')
    {
        $lines[0] = 0;
    }else{
        $lines[0] = (!isset($lines[0]) && empty($lines[0])) ? 0 : $lines[0]+1;
    }
    if($total > 0)
    {
        $lines[1] = $total;
    }
    @file_put_contents( $filename , implode( "\n", $lines ) );
}
public function getColumn($f_pointer){
    //return array();   
$flag = true;
$f_pointer = fopen($f_pointer, "r");
while(!feof($f_pointer)){
    $ar=fgetcsv($f_pointer);
    if($flag){$flag = false; continue; }
        $skuarrays[]= $ar[0];
    }
  return $skuarrays;   
}
public function geFileData($files, $po_number)
{
    $response['error'] = true; 
    if(!empty($files["files_upload"]["type"])){
        $fileName = time().'_'.$files['files_upload']['name'];
        $valid_extensions = 'csv';
        $temporary = explode(".", $files["files_upload"]["name"]);
        $file_extension = end($temporary);
        if($file_extension == $valid_extensions)
        {
            $sourcePath = $files['files_upload']['tmp_name'];
            $this->writeProgress(count(file($sourcePath)), $po_number, 'first');
            $targetPath = "uploads/".$fileName;

            $file = fopen($sourcePath, "r");
            $skuarrays = $this->getColumn($files['files_upload']['tmp_name']);
            $response['error'] = false; 
            $response['skuarrays'] = $skuarrays; 
            $response['data'] = $file; 
        }else{
            $response['data'] = 'Please upload valid CSV file.'; 
        }
    }else{
        $response['data'] = 'Please upload valid CSV file.';
    }
    return $response;
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
        $customerDdta['CardCode'] = $this->session->getCustomer()->getData('customer_number');
        $post = $this->getRequest()->getParams();
        $po_number = $post['po_number'];
        $message = '';
        $backorder = '';
        $backorderQty  = '';
        $responseData = '';
        $order_id = false;
        $responsebutton = false;
        $response = array();
        $filename = $this->mediaPath . "option2". DIRECTORY_SEPARATOR . $po_number . ".txt";
        try {
                $customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);
                if(isset($customerdata[0]))
                {
                    $customerdata = $customerdata[0];
                    $orderData = $this->saphelper->getSapOrdersData($customerdata,$po_number);
                    if(empty($orderData[0]))
                    {
                        $inserorderData = $this->saphelper->insertdataordr($customerdata,$po_number,$OrderMethod = "B2B");
                        $orderData = $this->saphelper->getSapOrdersData($customerdata,$po_number);
                        $neworder_id = $inserorderData;
                        $bse64_neworder_id = base64_encode($neworder_id);
                        $order_id = true;
                    }
                    $orderData = $orderData[0];
                    $responsebutton = false;
                    $gettempitemsData = $this->saphelper->getOrderAllItems($orderData['Id'],'T');
                    if($post['activeoption'] == 'option3')
                    {
                        $filesData = $this->geFileData($_FILES, $po_number);
                        if(!$filesData['error'])
                        {
                            $flag = true;
                            $row = 1;
                            $error_row_array = array();
                            $backorder_row_array = array();
                            $noqty_error_row_array = array();
                            $errorhtml = '';
                            $excledataArray = array();
                            $skuarrays = array_unique(array_filter($filesData['skuarrays']));
                            $mwebinvdata = $this->saphelper->getwebinv($skuarrays);
                            while (($data = fgetcsv($filesData['data'], 10000, ",")) !== FALSE) 
                            {

                                if(!file_exists($filename))
                                {
                                    echo 'Ops!, Please try again.';exit;
                                }
                                $this->writeProgress(0, $po_number, ''); 
                                if(isset($data['1']) && isset($data['0']))
                                {
                                    $data['0'] = filter_var($data['0'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                        if($flag) { $row ++; $flag = false; continue; }
                                        if($data['0'])
                                        {
                                            if(0 < (int)$data['1'])
                                            {
                                               $sku = $data['0'];
                                               $qty = $data['1'];
                                               $key = array_search($sku, array_column($excledataArray, 'sku'));
                                               $keys = array_keys(array_combine(array_keys($excledataArray), array_column($excledataArray, 'sku')),$sku);
                                                if(!empty($keys))
                                                {
                                                    foreach($keys as $key => $oldkey)
                                                    {
                                                        $qty = $qty + $excledataArray[$oldkey]['qty'];
                                                    }
                                                }
                                                /**if($key !='')
                                                {
                                                    $qty = $excledataArray[$key]['qty'] + $data['1'];

                                                }**/
                                               $response = $this->savedata($customerdata,$orderData,$qty,$sku,$gettempitemsData,$mwebinvdata,'3');
                                               $excledataArray[$row]['sku'] =  $sku;
                                               $excledataArray[$row]['qty'] =  $data['1'];
                                        
                                            }else{
                                                $noqty_error_row_array[$row] = 'line '.$row .' SKU: '.trim( str_replace('�', "", $data['0'])).', Qty is not valid';

                                            }
                                        }
                                        if(isset($response['errors']))
                                        {
                                            $error_row_array[$row] ='line '.$row .' SKU: '.trim(str_replace('�', "", $sku)).', Item not found';
                                        }
                                        if(isset($response['backorder']) && $response['backorderQty'] != '')
                                        {
                                           $backorder_row_array[$row] = 'line '.$row .' Item added successfully. SKU: '.trim(str_replace('�', "", $sku)).', '.$response['backorderQty'];

                                        }
                                        if(isset($response['success']))
                                        {
                                            $responsebutton = true;
                                        }
                                        $row++;
                                    }
                                }
                                if($row <= 2)
                                {
                                     $response = [
                                            'errors' => true,
                                            'message' => __('No items found.')
                                        ];
                                }
                                if(!empty($error_row_array))
                                {
                                    $errorhtml .='<span id="file_close" class="fa fa-close close_op3" ></span><ul><li>The following lines have errors </li>';
                                    
                                    foreach($error_row_array as $key => $error_row)
                                    {
                                        $errorhtml .='<li>'.$error_row.' </li>';
                                    }
                                    
                                    $row_line = implode(",",$error_row_array);
                                    if($row_line)
                                    {
                                        $response = [
                                            'errors' => true,
                                            'message' => __($errorhtml)
                                        ];
                                        //"The following lines have errors ".$row_line." Item(s) in csv are not valid or improper, please correct and upload again."
                                    }
                                    
                                }
                                if(!empty($backorder_row_array))
                                {
                                    foreach($backorder_row_array as $key => $backorder_row_array)
                                    {
                                        if($backorder_row_array !=='')
                                        {
                                            $errorhtml .='<li>'.$backorder_row_array.'</li>';
                                        }
                                         $response = [
                                            'errors' => true,
                                            'message' => __($errorhtml)
                                        ];
                                    }
                                }
                                if(!empty($noqty_error_row_array))
                                {
                                    foreach($noqty_error_row_array as $key => $error_row_array)
                                    {
                                        if($error_row_array !=='')
                                        {
                                            $errorhtml .='<li>'.$error_row_array.'</li>';
                                        }
                                         $response = [
                                            'errors' => true,
                                            'message' => __($errorhtml)
                                        ];
                                    }
                                }
                                
                                if($errorhtml !=='')
                                {
                                    $errorhtml .='</ul>';    
                                }
                                $resultPage = $this->resultPageFactory->create();
                                $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
                                $filnalHtml = $resultPage->getLayout()
                                    ->createBlock('Sttl\Adaruniforms\Block\View')
                                    ->setOrderId($orderData['Id'])
                                    ->setStyle('')
                                    ->setSubmitcolor('')
                                    ->setTemplate('Sttl_Customerorder::OrderTotal.phtml')
                                    ->toHtml(); 
                               /** $distinstyle = $this->saphelper->gettempOrdrstyle($orderData['Id']); 
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
                                  $renderDataByColor = $this->saphelper->newrenderOrderItemHtml($orderData['Id'],'','','',$groupstyle);  
                                  $filnalHtml .= $renderDataByColor;
                                }
                                $filnalHtml .= $this->saphelper->renderOrderItemHtmltotal($orderData['Id'],'');**/
                                $response['html'] = $filnalHtml;
                                
                        }else{
                            $response = [
                                            'errors' => true,
                                            'message' => __('Please upload valid CSV file.')
                                        ];
                        }
                    }else{
                        $ItemSku = trim($post['opt_two_sku']);
                        $skuarrays = array($ItemSku);
                        $mwebinvdata = $this->saphelper->getwebinv($skuarrays);
                        $qty = trim($post['opt_two_qty']);
                        $response = $this->savedata($customerdata,$orderData,$qty,$ItemSku,$gettempitemsData,$mwebinvdata,'2');
                    }
                }else{
                    $response = [
                    'errors' => true,
                    'message' => __('customer not exstng')
                    ];
                }
                if(isset($response['success']))
                {
                    /**$distinstyle = $this->saphelper->gettempOrdrstyle($orderData['Id']); 
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
                      $renderDataByColor = $this->saphelper->newrenderOrderItemHtml($orderData['Id'],'','','',$groupstyle);  
                      $filnalHtml .= $renderDataByColor;
                    }
                    $filnalHtml .= $this->saphelper->renderOrderItemHtmltotal($orderData['Id'],'');
                    //$OrderInfoGrid = $this->saphelper->renderOrderItemHtml($orderData['Id']);**/
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
                    $filnalHtml = $resultPage->getLayout()
                        ->createBlock('Sttl\Adaruniforms\Block\View')
                        ->setOrderId($orderData['Id'])
                        ->setStyle('')
                        ->setSubmitcolor('')
                        ->setTemplate('Sttl_Customerorder::OrderTotal.phtml')
                        ->toHtml(); 
                    $response['html'] = $filnalHtml;
                    $responsebutton = true;
                }
                if($responsebutton) 
                {
                    $response['orderBtnRow'] = true;
                }
                if($order_id)
                {
                    $response['order_id'] = $neworder_id;
                    $response['base64_order_id'] = $bse64_neworder_id;
                    $response['base64_ncp_id'] = base64_encode($po_number);
                }
                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = $e->getMessage();
                if($post['activeoption'] == 'option3')
                {
                    $message = 'Upload sheet is corrupt, please try creating a new one';
                }
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            } catch (\Exception $e) {
                $message = $e->getMessage();
                //$message = 'something went wrong please try again';
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            }
            @unlink($filename);
            return $resultJson->setData($response);
    }
}
    public function searchForId($id, $array) {
      foreach ($array as $key => $val) {
           if ($val['ItemCode'] == $id) {
               return $key;
           }
       }
       return '';
    }
    public function savedata($customerdata,$orderData,$qty,$ItemSku,$gettempitemsData,$mwebinvdata = array(),$option = '')
    {
        
        $message = '';
        $backorder = '';
        $backorderQty  = '';
        $responseData = '';
        $backorderQty = '';
        $ItemData = array();
        $updateordertotal = false;
        $webinvkey = $this->searchForId($ItemSku,$mwebinvdata);
        if(isset($mwebinvdata[$webinvkey]))
        {
            $ItemData = $mwebinvdata[$webinvkey];//$this->saphelper->getDatabyItemCode($ItemSku);    
        }
        if(!empty($ItemData) && !empty($orderData) && isset($orderData))
        {   
            if((int)$qty > (int)$ItemData['ActualQty'])
            {
                $backorder = true;
                $backorderQty  = 'On backorder: '.((int)$qty - (int)$ItemData['ActualQty']);
                if($ItemData['ETA'] != '' & $ItemData['ETA'] != NUll)
                {
                    $backorderQty .=' for '. date("m/d/y", strtotime($ItemData['ETA']));
                }
            }
            //$tempitemsData = $this->saphelper->getItemsData($orderData['Id'],$ItemSku);
            $tempitemsData = array();
            if(!empty($gettempitemsData))
            {
                $tempgetkeydata =  $this->searchForId($ItemSku,$gettempitemsData);
                if(isset($gettempitemsData[$tempgetkeydata]))
                {
                    $tempitemsData = $gettempitemsData[$tempgetkeydata];    
                }
                
            }
            
                                        
            $inserttemsData['ItemCode'] = $ItemSku;
            $inserttemsData['QTYOrdered'] = $qty;
            $inserttemsData['UnitPrice'] =number_format((float)$ItemData['UnitPrice'], 2, '.', '');
            $inserttemsData['PriceAfterDiscount'] = number_format((float)$ItemData['DisPrice']  * $qty, 2, '.', '');
            $inserttemsData['TotalPrice']=  number_format((float)$ItemData['DisPrice']  * $qty, 2, '.', '');
            $inserttemsData['Style'] = $ItemData['Style']; 
            $inserttemsData['ColorCode'] = $ItemData['ColorCode'];
            $inserttemsData['ColorName'] = $ItemData['Color'];
            $inserttemsData['Size'] = $ItemData['Size'];
            $inserttemsData['DisPrice'] = $ItemData['DisPrice'];
            /**if($ItemData['DisPercent'] == '.00')
            {
                $ItemData['DisPercent'] = 0.00;
            }**/
            $inserttemsData['DiscountPer'] = number_format((float)$ItemData['DisPercent'], 2, '.', '');
            
            $inserttemsData['OrderOption'] = $option;
            if(!empty($tempitemsData))
            {
                $inserttemsData['id'] = $tempitemsData['Id'];
                 $this->saphelper->UpdateItemsData($inserttemsData);
                $responseData = true;   
            }else{
                $inserttemsData['BaseDoc'] = $orderData['Id'];
                $this->saphelper->InsertItemsData($inserttemsData,$ItemData);
                $responseData = true;
            }
            if($responseData)
            {
                $gd_total =  $this->saphelper->getOrderSumItems($orderData['Id']);
                $totalQty =  $this->saphelper->getOrderSumQty($orderData['Id']);
                if((!empty($totalQty[0]['TotalQtyOrdered']) && !empty($gd_total[0]['TotalPriceOrdered'])) || ($totalQty[0]['TotalQtyOrdered'] > 0 && $gd_total[0]['TotalPriceOrdered'] >= 0 ))
                            {

                                //$getLoginCUstomerData = $customerdata;//$this->saphelper->getCustomerDetails();
                                $FlatDiscount = number_format($customerdata['FlatDiscount'],2);
                                $FlatDic = explode('.',number_format($FlatDiscount,2));
                                if(isset($FlatDic[1]) && $FlatDic[1] == 00){
                                    $FlatDiscount = $FlatDic[0];
                                }
                                $sellingprice = $gd_total[0]['TotalPriceOrdered'];
                                $DiscountAmount = "";
                                if($FlatDiscount > 0){
                                    $sellingprice = $gd_total[0]['TotalPriceOrdered'] - ($gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100));
                                    $DiscountAmount = $gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100);
                                    if($DiscountAmount < 0)
                                    {
                                       $DiscountAmount = 0; 
                                    }
                                }

                                $this->saphelper->updateordertotal($orderData['Id'],$totalQty[0]['TotalQtyOrdered'],$gd_total[0]['TotalPriceOrdered'],$FlatDiscount,$DiscountAmount,$sellingprice,0,0,$customerdata);
                                 $updateordertotal = true;
                            }
            }
            if($updateordertotal && $responseData)
            {
                $message = "Item added successfully.";
                if($backorder)
                {
                    $message = "Item added successfully. ".$backorderQty;
                }
                 $response = [
                    'success' => true,
                    'orderBtnRow' => true,
                    'backorder' => $backorder,
                    'backorderQty' => $backorderQty,
                    'message' => __($message) 
                ];

            }
        }
        else
        {
             $response = [
                    'errors' => true,
                    'message' => __("Item not found.")
                ];  
        }
        return $response;
    } 

}