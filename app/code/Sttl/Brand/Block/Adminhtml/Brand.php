<?php

namespace Sttl\Brand\Block\Adminhtml;

class Brand extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_brand';
        $this->_blockGroup = 'Sttl_Brand';
        $this->_headerText = __('Manage Brands');
        $this->_addButtonLabel = __('Add New Brand');
        parent::_construct();
    }
}
