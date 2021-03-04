<?php

namespace Sttl\Brand\Controller\Adminhtml\Brand;

class Index extends \Sttl\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sttl_Brand::manage_brand');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Brands'));
        $resultPage->addBreadcrumb(__('Shop By Brand'), __('Shop By Brand'));
        $resultPage->addBreadcrumb(__('Manage Brands'), __('Manage Brands'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Brand::manage_brand');
    }
}
