<?php
namespace Vendor\Rules\Model\Rule\Condition\Product;

use Sttl\Adaruniforms\Helper\Sap;

/**
 * Class Customer
 */
class Customer extends \Magento\Rule\Model\Condition\AbstractCondition {
	/**
	 * @var \Magento\Config\Model\Config\Source\Yesno
	 */
	protected $sourceYesno;

	/**
	 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
	 */
	protected $orderFactory;

	/**
	 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
	 */
	protected $saphelper;
	/**
	 * Constructor
	 * @param \Magento\Rule\Model\Condition\Context $context
	 * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
	 * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Rule\Model\Condition\Context $context,
		\Amasty\Base\Model\Source\Frequency $sourceYesno,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
		Sap $saphelper,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->sourceYesno = $sourceYesno;
		$this->sapHelper = $saphelper;
		$this->orderFactory = $orderFactory;
	}

	/**
	 * Load attribute options
	 * @return $this
	 */
	public function loadAttributeOptions() {
		$this->setAttributeOption([
			'product_sap' => __('Line item have product'),
		]);
		return $this;
	}

	/**
	 * Get input type
	 * @return string
	 */
	public function getInputType() {
		return 'select';
	}

	/**
	 * Get value element type
	 * @return string
	 */
	public function getValueElementType() {
		return 'multiselect';
	}

	/**
	 * Get value select options
	 * @return array|mixed
	 */
	public function getValueSelectOptions() {
		if (!$this->hasData('value_select_options')) {
			$this->setData(
				'value_select_options',
				$this->toOptionArray()
			);
		}
		return $this->getData('value_select_options');
	}

	public function loadOperatorOptions() {
		$this->setOperatorOption([
			'{}' => __('Proudct contain'),
			'!{}' => __('Proudct not contain'),
		]);
		return $this;
	}

	/**
	 * @return array
	 */
	public function toOptionArray() {

		$data = $this->sapHelper->getproductsku();
		foreach ($data as $item) {
			$options[] = array(
				'value' => $item['style'],
				'label' => __($item['style']),
			);
		}

		return $options;
	}

}