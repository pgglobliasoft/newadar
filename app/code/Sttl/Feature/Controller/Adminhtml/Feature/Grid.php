<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

class Grid extends \Sttl\Feature\Controller\Adminhtml\Feature
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
