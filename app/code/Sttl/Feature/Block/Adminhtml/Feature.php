<?php

namespace Sttl\Feature\Block\Adminhtml;

class Feature extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_feature';
        $this->_blockGroup = 'Sttl_Feature';
        $this->_headerText = __('Manage Features');
        $this->_addButtonLabel = __('Add New Feature');
        parent::_construct();
    }
}
