<?php

namespace Sttl\Proupdated\Model\ResourceModel\Condition;

use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\Rule\Model\Condition\Context;
use Sttl\Proupdated\Model\ResourceModel\Condition\Product;
use Sttl\Proupdated\Model\ResourceModel\Condition\ProductFactory;
use Sttl\Proupdated\Model\ResourceModel\Condition\Product\News;
use Sttl\Proupdated\Model\ResourceModel\Condition\Product\Sale;

/**
 * Combine model
 */
class Combine extends RuleCombine {
	/**
	 * Product model factory
	 *
	 * @var \Sttl\Proupdated\Model\Rule\Condition\ProductFactory
	 */
	protected $productFactory;

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
		array $data = []
	) {
		$this->productFactory = $conditionFactory;

		parent::__construct(
			$context,
			$data
		);
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

		foreach ($productAttributes as $code => $label) {
			if ('special_price' != $code) {
				$attributes[] = [
					'value' => Product::class . '|' . $code,
					'label' => $label,
				];
			}
		}

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
