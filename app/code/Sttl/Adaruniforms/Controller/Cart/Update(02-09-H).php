<?php
namespace Sttl\Adaruniforms\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;
class Update extends \Magento\Framework\App\Action\Action
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
		$po_number = $this->getRequest()->getPostValue('po_number');
		$style_id = $this->getRequest()->getPostValue('style_id');
		$success = "false";
		$message = '';
		$enty_id = '';
		$data = [];
		try {
			//$customerdata = $this->saphelper->getCustomerDetails();
			if($this->customerSession->isLoggedIn())
			{
				$cnt = 0;
				$enty_id = '';
				$data = $this->saphelper->getOrderItems($po_number, $style_id);
				
				if (!empty($data)) {
					$success = 'true';
					$message = __('PO saved successfully.');
				} else {
					$success = "false";
					$message = "Invalid P.O. Number and Style";
				}
				
				
			}else{
				$success = "false";
				$message = "customer session expriex plz login and shop now";	
			}
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$message = $e->getMessage();
			} catch (\Exception $e) {
				$message = "error";
				$success = "false";
			}
		
		$result = new DataObject();
		if($enty_id !='')
		{
			$result->setData('enty_id', $enty_id);	
		}
		$result->setData('success', $success);
		$result->setData('data', $data); 
        //$result->setData('error', $fail);
		$result->setData('type', ($success) ? 'mage-success' : 'mage-error');
		$result->setData('messages',$message);
		return $this->resultJsonFactory->create()->setData($result->getData());
	}
}	