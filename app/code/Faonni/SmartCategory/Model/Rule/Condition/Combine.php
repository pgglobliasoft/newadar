<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Rule\Condition;

use Faonni\SmartCategory\Model\Rule\Condition\Product;
use Faonni\SmartCategory\Model\Rule\Condition\ProductFactory;
use Faonni\SmartCategory\Model\Rule\Condition\Product\News;
use Faonni\SmartCategory\Model\Rule\Condition\Product\Sale;
use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\Rule\Model\Condition\Context;

/**
 * Combine model
 */
class Combine extends RuleCombine {
	/**
	 * Product model factory
	 *
	 * @var \Faonni\SmartCategory\Model\Rule\Condition\ProductFactory
	 */
	protected $productFactory;

	protected $_attributeFactory;

	/**
	 * Initialize combine
	 *
	 * @param Context $context
	 * @param ProductFactory $conditionFactory
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		ProductFactory $conditionFactory,
		\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
		array $data = []
	) {
		$this->productFactory = $conditionFactory;

		parent::__construct(
			$context,
			$data
		);
		$this->_attributeFactory = $attributeFactory;
		$this->setType(self::class);
	}

	/**
	 * Get inherited conditions selectors
	 *
	 * @return array
	 */
	public function getNewChildSelectOptions() {
		$productAttributes = $this->productFactory->create()
			->loadAttributeOptions()
			->getAttributeOption();

		$attributeInfo = $this->_attributeFactory->getCollection()
			->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
		$attributes = [
			[
				'value' => Sale::class,
				'label' => __('Special Price'),
			],
			[
				'value' => News::class,
				'label' => __('New'),
			],

		];
		// echo '<pre>';
		// print_r($productAttributes); die;

		foreach ($productAttributes as $code => $label) {
			if ('special_price' != $code) {
				$attributes[] = [
					'value' => Product::class . '|' . $code,
					'label' => $label,
				];
			}
		}

		// foreach($attributeInfo as $attributes1)
		// {
		//     $code = $attributes1['attribute_code'];
		//     $label = $attributes1['frontend_label'];
		//     $attributes[] = [
		//             'value' => Product::class . '|' . $code,
		//             'label' => $label,
		//         ];
		// }

		$conditions = parent::getNewChildSelectOptions();
		$conditions = array_merge_recursive(
			$conditions,
			[
				[
					'value' => self::class,
					'label' => __('Conditions Combination'),
				],
				['label' => __('Product Attribute'), 'value' => $attributes],
			]
		);
		return $conditions;
	}

	/**
	 * Collect validated attributes
	 *
	 * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
	 * @return $this
	 */
	public function collectValidatedAttributes($productCollection) {
		foreach ($this->getConditions() as $condition) {
			$condition->collectValidatedAttributes($productCollection);
		}
		return $this;
	}
}
