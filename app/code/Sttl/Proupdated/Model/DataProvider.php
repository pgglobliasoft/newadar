<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Created By : Rohan Hapani
 */
namespace Sttl\Proupdated\Model;
use Sttl\Proupdated\Model\ResourceModel\Post\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider {
	/**
	 * @var array
	 */
	protected $loadedData;
	// @codingStandardsIgnoreStart
	public function __construct(
		$name,
		$primaryFieldName,
		$requestFieldName,
		CollectionFactory $blogCollectionFactory,
		array $meta = [],
		array $data = []
	) {
		$this->collection = $blogCollectionFactory->create();
		parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
	}
	// @codingStandardsIgnoreEnd
	public function getData() {
		if (isset($this->loadedData)) {
			return $this->loadedData;
		}
		$items = $this->collection->getItems();
		foreach ($items as $blog) {
			$itemData = $blog->getData();
             if (isset($itemData['banners'])) {
                 $itemData['banners'] = json_decode($itemData["banners"],true);
                }
                // print_r($itemData['banners']);die;
			$this->loadedData[$blog->getId()]['customer'] = $itemData;
		}
		return $this->loadedData;
	}
}