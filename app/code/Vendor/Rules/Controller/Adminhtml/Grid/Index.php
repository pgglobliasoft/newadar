<?php

namespace Vendor\Rules\Controller\Adminhtml\Grid;

class Index extends \Magento\Backend\App\Action
{

    private $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vendor_Rules::grid_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Featured Product List'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_Rules::grid_list');
    }
}
