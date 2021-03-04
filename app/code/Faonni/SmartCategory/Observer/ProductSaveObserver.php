<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Faonni\SmartCategory\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Product save observer
 */
class ProductSaveObserver implements ObserverInterface {
	/**
	 * Product rule processor
	 *
	 * @var ProductRuleProcessor
	 */
	protected $productRuleProcessor;

	/**
	 * Intialize observer
	 *
	 * @param ProductRuleProcessor $objectManager
	 */
	public function __construct(
		ProductRuleProcessor $productRuleProcessor,
		\Sttl\Childskus\Model\PostFactory $gridFactory
	) {
		$this->productRuleProcessor = $productRuleProcessor;
		$this->gridFactory = $gridFactory;
	}

	/**
	 * Apply smart category rules after product model save
	 *
	 * @param Observer $observer
	 * @return void
	 */
	public function execute(Observer $observer) {
		$product = $observer->getEvent()->getProduct();

		$categories = $product->getCategoryIds();
		$categories[] = 86;

		$rowData = $this->gridFactory->create();
		$data = $rowData->load($product['sku'],"parantsku")->getId();
		if($data){
			$rowData->load($data);
		}
		$childsku = $product['child_sku'] ? $product['child_sku'] : '';
		 $rowData->addData([
            "parantsku" => $product['sku'],
            "childsku1" => $childsku
            ]);
		$rowData->save();
		if ($product['listing_page_image'] != '' && $product['listing_page_image'] > '0') {
			$product->setCategoryIds($categories);
			$product->save();

		}

	}
}
