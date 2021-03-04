<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Delete extends \Magento\Framework\App\Action\Action
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
        $customerdata = $this->sapHelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);              
      
		if(isset($post['isorder_delete'])){ 
			$deleteArray['deleteOrd'] = $post;
			$response = [
                    'errors' => true,
                    'message' => __("Item Not Delete.")
                ];               
            
            $deleteOrderItems = $this->sapHelper->deleteOrdreRecords($deleteArray['deleteOrd'],$customerdata[0]['CardCode']);            
			if(!empty($post['order_id'])){
				$order_id = $post['order_id'];
				$gd_total =  $this->sapHelper->getOrderSumItems($order_id);
            	$totalQty =  $this->sapHelper->getOrderSumQty($order_id);
            	if(!empty($totalQty[0]['TotalQtyOrdered']) && !empty($gd_total[0]['TotalPriceOrdered']))
                {
                    $FlatDiscount = isset($post['flatDiscount']) ? $post['flatDiscount'] : "";
                    $sellingprice = $gd_total[0]['TotalPriceOrdered'];
                    $DiscountAmount = "";
                    if($FlatDiscount > 0){
                        $sellingprice = $gd_total[0]['TotalPriceOrdered'] - ($gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100));
                        $DiscountAmount = $gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100);
                    }
                    $this->sapHelper->updateordertotal($order_id,$totalQty[0]['TotalQtyOrdered'],$gd_total[0]['TotalPriceOrdered'],$FlatDiscount,$DiscountAmount,$sellingprice,0,0,$customerdata[0]);
                }
                else
                {
                    $getAllItem = $this->sapHelper->getOrderAllItems($order_id,'T');
                    if(empty($getAllItem) && count($getAllItem) == 0){
                        $this->sapHelper->updateordertotal($order_id,0,0,0,0,0,0,0,$customerdata[0]);       
                    }
                }
                
			}
			if($deleteOrderItems){
				/**$distinstyle = $this->sapHelper->gettempOrdrstyle($deleteOrderItems); 
                $values = array_map('array_pop', $distinstyle);
                $implodedStyle = implode("','", $values);
                $distinstyle = $this->sapHelper->getsizegroup($implodedStyle); 
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
                  $renderDataByColor = $this->sapHelper->newrenderOrderItemHtml($deleteOrderItems,'','','',$groupstyle);  
                  $filnalHtml .= $renderDataByColor;
                }
                $filnalHtml .= $this->sapHelper->renderOrderItemHtmltotal($deleteOrderItems,'');**/
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
                $filnalHtml = $resultPage->getLayout()
                    ->createBlock('Sttl\Adaruniforms\Block\View')
                    ->setOrderId($deleteOrderItems)
                    ->setStyle('')
                    ->setSubmitcolor('')
                    ->setTemplate('Sttl_Customerorder::OrderTotal.phtml')
                    ->toHtml(); 
				$response = [
                    'errors' => false,
                    'order_id' => $deleteOrderItems,
                    'html' => $filnalHtml,
                    'message' => __("Item Deleted Successfully.")
                ];
			}
			return $resultJson->setData($response);
			
		}else{
            // order delete process

         

            $back_parms = base64_decode($this->getRequest()->getParam('back'));
            $back_redirect = $this->_storemanager->getStore()->getBaseUrl()."customerorder/customer/index";
            if($back_parms == 1)
            {
                $back_redirect = $this->_storemanager->getStore()->getBaseUrl()."customerorder/customer/index?q=d";
            }
			$id = 0;
			if($this->getRequest()->getParam('id') != '')
			{
				$id = base64_decode($this->getRequest()->getParam('id'));
			}
			if($id != '' && $id > 0)
			{
				$this->sapHelper->removePObyId($id);
				$this->messageManager->addSuccessMessage(__('Order removed successfully.'));                
				$resultRedirect->setUrl($back_redirect);
	        	return $resultRedirect;
			}
			else
			{
				$this->messageManager->addSuccessMessage(__('We can\'t find order to remove.'));
			}
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
	        return $resultRedirect;
	    }
    }
}

}