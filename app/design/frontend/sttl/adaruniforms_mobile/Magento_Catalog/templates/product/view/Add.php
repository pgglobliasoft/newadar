<?php
namespace Sttl\Adaruniforms\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;
class Add extends \Magento\Framework\App\Action\Action
{
	protected $customerSession;
	protected $resultJsonFactory;
	protected $messageManager;
	protected $formKey;   
	protected $cart;
	protected $product;
	protected $preferredBasketModel;
	protected $productRepository;
	protected $dataObjectFactory;
	
	public function __construct(
	Context $context,
	\Magento\Customer\Model\Session $customerSession,
	\Magento\Framework\Message\ManagerInterface $messageManager,
	\Magento\Framework\Data\Form\FormKey $formKey,
	\Magento\Checkout\Model\Cart $cart,
	\Magento\Catalog\Model\Product $product,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
	\Magento\Framework\DataObjectFactory $dataObjectFactory,
	\Sttl\Adaruniforms\Helper\Sap $saphelper
	) {
		
		$this->resultJsonFactory = $resultJsonFactory;
		$this->customerSession = $customerSession;
		$this->messageManager = $messageManager;
		$this->formKey = $formKey;
		$this->cart = $cart;
		$this->product = $product;
		$this->productRepository = $productRepository;
		parent::__construct($context);
		$this->dataObjectFactory = $dataObjectFactory;
		$this->saphelper = $saphelper;
		
		
	}
	
	public function execute()
	{
		$post = $this->getRequest()->getParams();
		$success = "false";
		$message = '';
		$enty_id = '';
		try {
			$customerdata = $this->saphelper->getCustomerDetails();
			if(!empty($customerdata) && $customerdata!= '' && isset($customerdata[0]))
			{
				$cnt = 0;
				$enty_id = '';
				if (isset($post["qty"]) && !empty($post["qty"])) 
				{
					//$enty_id =  $this->saphelper->checkponumber($customerdata[0],$post['po_number']);
					if(empty($post["sap_ponumber_id"]) || $post["sap_ponumber_id"] == '')
					{
						$enty_id = $this->saphelper->insertdataordr($customerdata[0],strtolower($post['po_number']));
					}else{
						$enty_id = $post["sap_ponumber_id"]	;
					}
					
					if(!empty($enty_id) && $enty_id !='')
					{
						$this->saphelper->deleteordritems($enty_id,$post["style"]);
						$totalQty = 0;
						$gd_total = 0;
						foreach($post["qty"] as $color => $size) 
						{
							if (!empty($size)) 
							{
								
								foreach($size as $sizeKey => $qty) 
								{

									$totalQty= (int)$totalQty + (int)$qty;
									$itmdata = array();
									if($qty > 0)
									{
										$gd_total= $gd_total + $qty * $post["showprice"]["$color"]["$sizeKey"];
										$itemdata['Style'] = $post["style"];
										$itemdata['ColorName'] = $color;
										$itemdata['Size'] = $sizeKey;
										$itemdata['BaseDoc'] = $enty_id;
										$itemdata['PriceAfterDiscount'] = $post["inpprice"]["$color"]["$sizeKey"];
										$itemdata['TotalPrice'] = $post["inpprice"]["$color"]["$sizeKey"];
										//$itemdata['DeliveredQTY'] = $qty;
										$itemdata['QTYOrdered'] = $qty;
										$itemdata['UnitPrice'] = $post["showprice"]["$color"]["$sizeKey"];
										$itemdata['itemscode'] = $post["itemscode"]["$color"]["$sizeKey"];
										$itemdata['colorcode'] = $post["colorcode"]["$color"]["$sizeKey"];
										
										//$itemdata['ColorCode'] =$post["style"] 
										$customerdata = $this->saphelper->insertdataordritems($itemdata);
										$cnt++;
									}
								}
							}
						}
						if(!empty($totalQty) && !empty($gd_total))
						{
							$this->saphelper->updateordertotal($enty_id,$totalQty,$gd_total);
						}
					}
						if ($cnt > 0) {
							$this->cart->save();						
							$success = 'true';
							$message = __('PO saved successfully.');
						}
				} 
				else 
				{
						$success = "false";
						$message = "Please add Qty";
				} 
			}else{
				$success = "false";
				$message = "customer session expriex plz login and shop now";	
			}
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$message = $e->getMessage();
			} catch (\Exception $e) {
			echo  $e->getMessage();exit;
				$message = "error";
				$success = "false";
			}
		
		$result = new DataObject();
		if($enty_id !='')
		{
			$result->setData('enty_id', $enty_id);	
			$result->setData('base64_enty_id', base64_encode($enty_id));
			$result->setData('base64_po_number', base64_encode($post['po_number']));
				
		}
		$result->setData('success', $success);
        //$result->setData('error', $fail);
		$result->setData('type', ($success) ? 'mage-success' : 'mage-error');
		$result->setData('messages',$message);
		return $this->resultJsonFactory->create()->setData($result->getData());
	}
}	