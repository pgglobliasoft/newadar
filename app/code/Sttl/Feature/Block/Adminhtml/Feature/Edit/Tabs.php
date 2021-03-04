<?php

namespace Sttl\Feature\Block\Adminhtml\Feature\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('feature_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Feature'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'product_section',
            [
                'label' => __('Products'),
                'url' => $this->getUrl('feature/feature/product', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return parent::_beforeToHtml();
    }
}
