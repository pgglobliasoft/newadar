<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Removelineitem extends \Magento\Framework\App\Action\Action
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

            $response = [
                        'errors' => true,
                        'message' => __("Item Not Delete.")
                    ];                 
    		$id = 0;
    		if($post['baseorder_id'] != '')
    		{
    			$id = base64_decode($post['baseorder_id']);
    		}
    		if($id != '' && $id > 0)
    		{
    			$this->sapHelper->removePObyId($id);
                $this->session->setPonumber($post['po_number']);
    		} 
    		else
    		{
    			$this->messageManager->addSuccessMessage(__('We can\'t find order to remove.'));
    		}
            $response = [
                'errors' => false,
                'current_ponumber' => $post['po_number'],
                'message' => __("Item Deleted Successfully.")
            ];
            return $resultJson->setData($response);

    }
}

}