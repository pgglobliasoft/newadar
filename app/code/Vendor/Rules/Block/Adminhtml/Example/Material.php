<?php

namespace Vendor\Rules\Block\Adminhtml\Example;

class Material extends \Magento\Backend\Block\Widget\Grid\Container {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_controller = 'example_material';
		$this->_headerText = __('Marketing product Grid');
		$this->_addButtonLabel = __('Add New Marketing product');
		parent::_construct();
	}
}