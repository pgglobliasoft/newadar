<?php

namespace Vendor\Rules\Block\Adminhtml\Example\Material\Grid\Renderer;

class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
	protected $_storeManager;

	public function __construct(
		\Magento\Backend\Block\Context $context,
		array $data = []
	) {
		parent::__construct($context, $data);
	}

	public function render(\Magento\Framework\DataObject $row) {
		$price = '<b>$</b>' . $this->_getValue($row);
		return $price;
	}
}