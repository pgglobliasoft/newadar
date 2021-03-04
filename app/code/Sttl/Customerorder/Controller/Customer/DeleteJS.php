<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class DeleteJS extends \Magento\Framework\App\Action\Action
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
        $this->session->setCustomRedirectUrl($this->_storeManager->getStore()->getCurrentUrl(false));
        $resultRedirect->setPath('login'); 
        return $resultRedirect;
    }
    else
    {       
    	$deleteArray = array();
    	$post = $this->getRequest()->getParams();        
        if(isset($post['customerdata'])){
            $customerdata = json_decode($post["customerdata"], true);
        }else{
            $customerdata = $this->sapHelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);
        }             
      
		if(isset($post['isorder_delete'])){ 
			$deleteArray['deleteOrd'] = $post;
			$response = [
                    'errors' => true,
                    'message' => __("Item Not Delete.")
                ];               

            // echo "<pre>";
            // print_r($deleteArray['deleteOrd']);
            // die;
            $deleteOrderItems = $this->sapHelper->deleteOrdreRecords($deleteArray['deleteOrd'],$customerdata[0]['CardCode']);            
			if(!empty($post['order_id'])){
				$order_id = $post['order_id'];

                $ordertotaldata = json_decode($post["ordersummary"], true);
                if(!empty($ordertotaldata)){
                	if(!empty($ordertotaldata['TotalQtyOrdered']) && !empty($ordertotaldata['TotalBeforeDiscount']))
                    {
                        $this->sapHelper->updateordertotal($order_id,$ordertotaldata['TotalQtyOrdered'],$ordertotaldata['TotalBeforeDiscount'],$ordertotaldata['FlatDiscount'],$ordertotaldata['DiscountAmount'],$ordertotaldata['DocTotal'],0,0,$customerdata[0]);
                    }
                    else
                    {
                        $getAllItem = $this->sapHelper->getOrderAllItems($order_id,'T');
                        if(empty($getAllItem) && count($getAllItem) == 0){
                            $this->sapHelper->updateordertotal($order_id,0,0,0,0,0,0,0,$customerdata[0]);       
                        }
                    }
                }
                
			}
			if($deleteOrderItems){
				$response = [
                    'errors' => false,
                    'success' => true,
                    'order_id' => $deleteOrderItems,
                    'message' => __("Item Deleted Successfully.")
                ];
			}
			return $resultJson->setData($response);
			
		}else if(isset($post['isholeorder_delete'])){
            if(isset($post['order_id']))
            {
                $id = base64_decode($post['order_id']);
            }
            if($id != '' && $id > 0)
            {
                $this->sapHelper->removePObyId($id);
                $response = [
                    'success' => true,
                    'message' => __("Item Deleted Successfully."),
                    'delete_order_id' => $id
                ];
                return $resultJson->setData($response);
            }
        }else{

            $back_redirect = $this->_storemanager->getStore()->getBaseUrl()."customerorder/customer/neworder";

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