<?php
namespace Globala\Customapi\Controller\Api;

class CallApi extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$post = $this->getRequest()->getParams();
		$resultJson = $this->resultJsonFactory->create();
		$sku = $post['sku'];
		$products = [];
		if($sku){
			$productUrl='https://dev.adaruniforms.com/rest/V1/ProductApi?id=A6104P';
	        $ch = curl_init($productUrl);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	            "Content-Type: application/json"
	            )
	        );
	        $productList = curl_exec($ch);
	        $err      = curl_error($ch);
	        $products = json_decode($productList);
	        curl_close($ch);
		}
		return $resultJson->setData($products);
	}
}