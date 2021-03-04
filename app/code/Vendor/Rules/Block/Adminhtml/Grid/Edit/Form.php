<?php

namespace Vendor\Rules\Block\Adminhtml\Grid\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Vendor\Rules\Model\Status $options,
        \Vendor\Rules\Model\Configurable $option,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_option = $option;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );

        $form->setHtmlIdPrefix('wkgrid_');
        if ($model->getEntityId()) {
            // echo "<pre>";
            // print_r($model->getSortOrder());
            // print_r($model->getSku());
            // print_r($model->getName());
            // print_r($model->getDetail());
            // die;
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Product Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add New Product'), 'class' => 'fieldset-wide']
            );
        }



        $fieldset->addField(
            'detail',
            'select',
            [
                'name' => 'detail',
                'label' => __('Detail'),
                'id' => 'detail',
                'title' => __('Detail'),
                'values' => $this->_option->getOptionArray(),
                'class' => 'detail',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Status'),
                'id' => 'is_active',
                'title' => __('Status'),
                'values' => $this->_options->getOptionArray(),
                'class' => 'status',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'id' => 'sort_order',
                'title' => __('Sort Order'),
                'class' => 'required-entry',
                // 'placeholder' => $model->getEntityId() ? __($model->getSortOrder()) : __(""),
                'value' => $model->getEntityId() ? __($model->getSortOrder()) : __(""),
                'required' => true,
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
