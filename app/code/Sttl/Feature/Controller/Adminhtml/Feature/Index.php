<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

class Index extends \Sttl\Feature\Controller\Adminhtml\Feature
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sttl_Feature::manage_feature');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Features'));
        $resultPage->addBreadcrumb(__('Shop By Feature'), __('Shop By Feature'));
        $resultPage->addBreadcrumb(__('Manage Features'), __('Manage Features'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Feature::manage_feature');
    }
}
