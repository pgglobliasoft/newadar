<?php

namespace ManishJoy\ChildCustomer\Controller\Adminhtml\Admin;

// use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    public function execute()

    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('ManishJoy_ChildCustomer::admin_customer_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('All Admin Customers'));
        return $resultPage;
    }
}
