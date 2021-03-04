<?php

namespace Vendor\Rules\Block\Adminhtml\Example\Material\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use \Vendor\Rules\Model\Config\Source\CategoryList;
use \Vendor\Rules\Model\Config\Source\ProductList;

class Main extends Generic implements TabInterface {

	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param Registry $registry
	 * @param FormFactory $formFactory
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		Registry $registry,
		FormFactory $formFactory,
		ProductList $ProductList,
		CategoryList $CategoryList,
		array $data = []
	) {
		$this->ProductList = $ProductList;
		$this->CategoryList = $CategoryList;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTabLabel() {
		return __('Markting Information');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTabTitle() {
		return __('Markting Information');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canShowTab() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHidden() {
		return false;
	}

	/**
	 * Prepare form before rendering HTML
	 *
	 * @return Generic
	 */

	protected function _prepareForm() {
		$model = $this->_coreRegistry->registry('current_rule');

		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create();
		$form->setHtmlIdPrefix('rule_');

		$fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

		if ($model->getId()) {
			$fieldset->addField('id', 'hidden', ['name' => 'id']);
		}

		if (!$model->getId()) {
			$model->setData('is_active', '1');
		}

		$fieldset->addField(
			'item_code',
			'text',
			['name' => 'item_code', 'label' => __('Item Sku'), 'title' => __('Item SKu'), 'required' => true]
		);

		$fieldset->addField(
			'item_name',
			'textarea',
			[
				'name' => 'item_name',
				'label' => __('Item Name'),
				'title' => __('Item Name'),
				'style' => 'height: 100px;',
			]
		);

		$fieldset->addField(
			'item_url',
			'text',
			['name' => 'item_url', 'label' => __('Item Url'), 'title' => __('image Url'), 'required' => false]
		);

		$fieldset->addField(
			'file_url',
			'text',
			['name' => 'file_url', 'label' => __('File url'), 'title' => __('image file url'), 'required' => false]
		);

		$fieldset->addField(
			'category',
			'text',
			['name' => 'category', 'label' => __('Category'), 'title' => __('Category'), 'required' => false]
		);

		$fieldset->addField(
			'price',
			'text',
			array(
				'name' => 'price',
				'label' => __('Price'),
				'title' => __('Price'),
				'required' => false,
				'class' => 'validate-number',
			)
		);

		$fieldset->addField(
			'free_after',
			'text',
			array(
				'name' => 'free_after',
				'label' => __('Free after'),
				'title' => __('Free after'),
				'required' => true,
				'class' => 'validate-number',
			)
		);

		$fieldset->addField(
			'is_active',
			'select',
			[
				'label' => __('Status'),
				'title' => __('Status'),
				'name' => 'is_active',
				'required' => true,
				'options' => ['1' => __('Active'), '0' => __('Inactive'), '2' => __('Temporarily Inactive')],
			]
		);

		$fieldset->addField(
			'minimum_order_amt',
			'text',
			array(
				'name' => 'minimum_order_amt',
				'label' => __('Minimum order amount'),
				'title' => __('Minimum order amount'),
				'required' => false,
				'class' => 'validate-number',
			)
		);

		$fieldset->addField(
			'maximum_pre_order',
			'text',
			array(
				'name' => 'maximum_pre_order',
				'label' => __('Maximum per order'),
				'title' => __('Maximum per order'),
				'required' => false,
				'class' => 'validate-number',
			)
		);

		$fieldset->addField(
			'shortorder',
			'text',
			array(
				'name' => 'shortorder',
				'label' => __('Short Order'),
				'title' => __('Short Order'),
				 'value'     => null, 
				)
		);

		// $form->setValues($valuearray);

		// if ($model->isReadonly()) {
		// 	foreach ($fieldset->getElements() as $element) {
		// 		$element->setReadonly(true, true);
		// 	}
		// }
		$form->setValues($model->getData());

		$this->setForm($form);

		$this->_eventManager->dispatch('adminhtml_example_material_edit_tab_main_prepare_form', ['form' => $form]);

		return parent::_prepareForm();
	}
}