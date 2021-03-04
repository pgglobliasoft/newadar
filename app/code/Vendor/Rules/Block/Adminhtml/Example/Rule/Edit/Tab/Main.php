<?php

namespace Vendor\Rules\Block\Adminhtml\Example\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use \Vendor\Rules\Model\Config\Source\ProductList;
use \Vendor\Rules\Model\Config\Source\CategoryList;

class Main extends Generic implements TabInterface
{

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
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     */

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height: 100px;'
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $fieldset->addField('sort_order', 'text', ['name' => 'sort_order', 'label' => __('Priority')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('From'),
                'title' => __('From'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );
        $fieldset->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('To'),
                'title' => __('To'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

         $fieldset->addField(
                    'products',
                    'multiselect',
                    [
                        'label' => __('Products'),
                        'title' => __('Products'),
                        'name' => 'products',
                        // 'required' => true,
                        'values' => $this->ProductList->toOptionArray(),
                        'note'      => __('Please select the products for the product which one you want to give as a free after all condition meets')
                    ]
         
                );

         $fieldset->addField(
                    'Categorys',
                    'select',
                    [
                        'label' => __('Category'),
                        'title' => __('Category'),
                        'name' => 'Categorys',
                        // 'required' => true,
                        'values' => $this->CategoryList->toOptionArray(),
                        'note'      => __('Please select the category which one you want apply rule')
                    ]
                );

        $fieldset->addField('free_qty', 'text', ['name' => 'free_qty', 'label' => __('Free Qty'),'note'      => __('Please Enter Qty which you want to give free')]);
            
             // print_r(json_decode($model->getProducts(),true));die;
        $valuearray = $model->getData();
        $valuearray['products'] = json_decode($model->getProducts(),true);

        // $valuearray['Categorys'] = json_decode($valuearray['Categorys'],true);

        $form->setValues($valuearray);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_example_rule_edit_tab_main_prepare_form', ['form' => $form]);

        return parent::_prepareForm();
    }
}