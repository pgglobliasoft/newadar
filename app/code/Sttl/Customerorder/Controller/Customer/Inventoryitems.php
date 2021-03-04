<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;

class Inventoryitems extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
	
	protected $helpersap;

	protected $_customerSession;



	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Customer\Model\Session $customerSession,
		Sap $helpersap
		)
	{
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $customerSession;
		$this->helpersap = $helpersap;

	}
	
	public function execute()
	{	
		$adminuser =  $this->_customerSession->getCustomerAsadmin();
		$resultJson = $this->resultJsonFactory->create();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$_scopeConfig = $objectManager->create('Magento\Store\Model\StoreManager');
		$palceholder = $_scopeConfig->getStore()->getConfig('catalog/placeholder/image_placeholder');
		$mediaUrl = $_scopeConfig->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$placeholder_image = $mediaUrl.'catalog/product/placeholder/'.$palceholder;

		$response = [];
		if ($adminuser){
			try{
				$getallitmesdata = $this->helpersap->getAllInventoryItems();

					foreach($getallitmesdata as $key => $data)
					{
						if($data["ETA"]==0){
							$data["ETA"]="-";
						}
						
						$AllInventoryItems[] = [
		                    'ItemCode'      => $data["ItemCode"],
		                    // 'ItemName'        => htmlspecialchars($data["ItemName"]),
		                    'Style'        => $data["Style"],
		                    'ColorCode'        => $data["ColorCode"],
		                    'Size'        => $data["Size"],
		                    'Available Qty'        => $data["ActualQty"],
		                    'ETA'        => $data["ETA"],
		                    'UnitPrice'        => $data["UnitPrice"],
		                    'DisPrice'        => $data["DisPrice"],
		                    'images'	=>  stripslashes($data['U_WImage1']) | $placeholder_image,
		                    'u_images'	=> $data['U_WImage1'] ? $data['U_WImage1'] : $placeholder_image
		                    // 'Committed'        => $data["Collection"],
		                ];	
					}

					// print_r($getallitmesdata);die;
	            $response = ['success' => 'true', "AllproductData" => $AllInventoryItems];
				
	        } catch (\Exception $e) {
	            $response = ['success' => 'false', 'message' => $e->getMessage()];
	        }        
		}	
		return $resultJson->setData($response);
	}	
}