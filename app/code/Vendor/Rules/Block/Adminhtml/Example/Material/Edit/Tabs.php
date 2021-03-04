<?php

namespace Vendor\Rules\Block\Adminhtml\Example\Material\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->setId('material_edit_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(__('marketing'));
	}
}