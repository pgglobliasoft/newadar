<?php

namespace Vendor\Rules\Model\Rule\Condition\Product;

/**
 * Class Totalsales
 */
class Amount extends \Magento\Rule\Model\Condition\AbstractCondition {

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
			'DocTotal' => __('Total Amount'),
			'TotalQtyOrdered' => __('Total Qty'),
			'qty' => __('Style Qty'),
			'Style' => __('Browser Product'),
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

	/**
	 * Get value element type
	 * @return string
	 */
	public function getValueElementType() {
		return 'text';
	}

	/**
	 * Get value select options
	 * @return array|mixed
	 */
	public function getValueSelectOptions() {
		$opt = [];
		if ($this->hasValueOption()) {
			foreach ((array) $this->getValueOption() as $key => $value) {
				$opt[] = ['value' => $key, 'label' => $value];
			}
		}
		return $opt;
	}

}