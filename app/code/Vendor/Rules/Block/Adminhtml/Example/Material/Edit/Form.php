<?php

namespace Vendor\Rules\Block\Adminhtml\Example\Material\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->setId('example_material_form');
		$this->setTitle(__('Marketing Information'));
	}

	/**
	 * Prepare form before rendering HTML
	 *
	 * @return \Magento\Backend\Block\Widget\Form\Generic
	 */
	protected function _prepareForm() {
		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create(
			[
				'data' => [
					'id' => 'edit_form',
					'action' => $this->getUrl('vendor_rules/example_material/save'),
					'method' => 'post',
				],
			]
		);
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}