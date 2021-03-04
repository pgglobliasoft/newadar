<?php

namespace Vendor\Rules\Model\Rule\Condition\Product;

/**
 * Class Totalsales
 */
class Style extends \Magento\Rule\Model\Condition\AbstractCondition {

	/**
	 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
	 */
	protected $orderFactory;

	/**
	 * Constructor
	 * @param \Magento\Rule\Model\Condition\Context $context
	 * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Rule\Model\Condition\Context $context,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
		array $data = []
	) {
		$this->orderFactory = $orderFactory;
		parent::__construct($context, $data);
	}

	/**
	 * Load attribute options
	 * @return $this
	 */
	public function loadAttributeOptions() {
		$this->setAttributeOption([
			'Style' => __('Line item Product'),
			'sku' => __('Product'),

		]);
		return $this;
	}

	/**
	 * Get input type
	 * @return string
	 */
	public function getInputType() {
		return 'string';
	}

	// public function loadAttributeOptions22() {
	// 	$this->setAttributeOption([
	// 		'customer_first_order' => __('Customer first order'),
	// 	]);
	// 	return $this;
	// }
	/**
	 * Get value element type
	 * @return string
	 */
	public function getValueElementType() {
		return 'select';
	}

	/**
	 * Get value select options
	 * @return array|mixed
	 */
	/**
	 * Prepare operator select options
	 *
	 * @return \Faonni\SmartCategory\Model\Rule\Condition\Product\News
	 */
	public function loadOperatorOptions() {
		$this->setOperatorOption([
			'{}' => __('Proudct contain'),
			'!{}' => __('Proudct not contain'),
		]);
		return $this;
	}

}