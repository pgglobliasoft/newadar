<?php
namespace Vendor\Rules\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\Rule\Model\Condition\Context;
use Vendor\Rules\Model\Rule\Condition\Product;
use Vendor\Rules\Model\Rule\Condition\ProductFactory;
use Vendor\Rules\Model\Rule\Condition\Product\Amount;
use Vendor\Rules\Model\Rule\Condition\Product\Customer;

// use Magento\CatalogRule\Model\Rule\Condition\Product;
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
		$attributes = [
			[
				'value' => Amount::class,
				'label' => __('Total Amount'),
			],
			[
				'value' => Amount::class . '|TotalQtyOrdered',
				'label' => __('Total Qty'),
			],
			[
				'value' => Amount::class . '|qty',
				'label' => __('Style Qty'),
			],
			[
				'value' => Customer::class,
				'label' => __('Sku Product'),
			],

		];

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
