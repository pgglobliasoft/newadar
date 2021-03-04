<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

class NewAction extends \Sttl\Feature\Controller\Adminhtml\Feature
{
    public function execute()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Feature::edit_feature');
    }
}
