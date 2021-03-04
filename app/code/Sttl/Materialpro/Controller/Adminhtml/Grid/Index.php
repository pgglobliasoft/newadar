<?php
namespace Sttl\Materialpro\Controller\Adminhtml\Grid;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Sttl\Materialpro\Controller\Adminhtml\Index
{

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Sttl_Materialpro::Materialpro');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketing Material Product rules'));
        return $resultPage;
    }
}
