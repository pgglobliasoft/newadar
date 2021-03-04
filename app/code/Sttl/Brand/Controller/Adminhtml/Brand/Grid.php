<?php

namespace Sttl\Brand\Controller\Adminhtml\Brand;

class Grid extends \Sttl\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
