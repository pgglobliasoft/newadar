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

		// echo "<pre>";
		// print_r($post);
		// die;

		try {
			$customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);
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
						// $this->saphelper->deleteordritems($enty_id,$post["style"]);
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
										$itemdata['DisPrice'] = $post["showprice"]["$color"]["$sizeKey"];
										$itemdata['UnitPrice'] = $post["mainprice"]["$color"]["$sizeKey"];
										$itemdata['itemscode'] = $post["itemscode"]["$color"]["$sizeKey"];
										$itemdata['colorcode'] = $post["colorcode"]["$color"]["$sizeKey"];
										$itemdata['DiscountPer'] =  number_format((float)$post["DiscountPer"]["$color"]["$sizeKey"], 2, '.', '');
										//$itemdata['ColorCode'] =$post["style"]
										$itemdata['OrderOption'] = '4';
										$this->saphelper->insertdataordritems($itemdata);
										$cnt++;
									}
								}
							}
						}
						if(!empty($totalQty) && !empty($gd_total))
						{
							$gd_total =  $this->saphelper->getOrderSumItems($enty_id);
                            $totalQty =  $this->saphelper->getOrderSumQty($enty_id);
							$descountPer = '';
							$discountAmount = '';
							  if(!empty($totalQty[0]['TotalQtyOrdered']) && !empty($gd_total[0]['TotalPriceOrdered']))
                            {
                            	$getLoginCUstomerData = $this->saphelper->getCustomerDetails();
								$FlatDiscount = number_format($getLoginCUstomerData[0]['FlatDiscount'],2);
								$FlatDic = explode('.',number_format($FlatDiscount,2));
								if(isset($FlatDic[1]) && $FlatDic[1] == 00){
									$FlatDiscount = $FlatDic[0];
								}
                                $sellingprice = $gd_total[0]['TotalPriceOrdered'];
                                $DiscountAmount = "";
                                if($FlatDiscount > 0){
                                    $sellingprice = $gd_total[0]['TotalPriceOrdered'] - ($gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100));
                                    $DiscountAmount = $gd_total[0]['TotalPriceOrdered'] * ($FlatDiscount / 100);
                                }
                                $this->saphelper->updateordertotal($enty_id,$totalQty[0]['TotalQtyOrdered'],$gd_total[0]['TotalPriceOrdered'],$FlatDiscount,$DiscountAmount,$sellingprice,0,0,$customerdata[0]);
                            }
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
				$message = "Customer Session Expired Please Login And Shop Now.";
			}
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$message = $e->getMessage();
			} catch (\Exception $e) {
			// echo  $e->getMessage();exit;
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
