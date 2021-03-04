<?php
namespace Sttl\Customerorder\Controller\Customer;

class Productdataweb extends \Magento\Framework\App\Action\Action {
	protected $_productCollectionFactory;
	protected $resultJsonFactory;
	protected $_productVisibility;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\Product\Visibility $productVisibility,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	) {
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_productVisibility = $productVisibility;
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
	}

	public function execute() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$proHelper = $objectManager->create('Sttl\Adaruniforms\Helper\Sap');
		$data = $proHelper->simpleproductsku();
		$resultJson = $this->resultJsonFactory->create();
		$collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToSelect(['name', 'parent_style'])->addAttributeToSort('sku', 'ASC');
		$result = [];
		$sresult = [];
		foreach ($data as $product) {
			$string = htmlentities(preg_replace('/\W\w+\s*(\W*)$/', '$1', $product['ItemName']), ENT_QUOTES | ENT_IGNORE, "UTF-8");
			$sresult[] = [
				'sku' => $product['style'],
				'name' => $string,
				'sap' => true,
			];
		}
		// foreach ($collection as $product) {
		// 	if ($product->getTypeId() == "configurable") {
		// 		$result[] = [
		// 			'sku' => $product->getSku(),
		// 			'name' => $product->getName(),
		// 		];
		// 	}
		// }
		// $C = array_merge($result, $sresult);
		// echo count($result);
		// echo "<br>";
		// echo count($sresult);die;
		return $resultJson->setData($sresult);
	}

}
