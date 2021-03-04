<?php

namespace Sttl\Brand\Controller\Adminhtml\Brand;

class NewAction extends \Sttl\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Brand::edit_brand');
    }
}
