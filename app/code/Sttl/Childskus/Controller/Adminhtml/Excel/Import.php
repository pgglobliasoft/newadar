<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Childskus\Controller\Adminhtml\Excel;

// use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Import extends \Sttl\Childskus\Controller\Adminhtml\Excel
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    // const ADMIN_RESOURCE = 'Magento_TaxImportExport::import_export';

    /**
     * Import and export Page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Sttl_Childskus::Childskus');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Sttl\Childskus\Block\Adminhtml\Excel\ImportHeader::class
            )
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Sttl\Childskus\Block\Adminhtml\Excel\Import::class)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Import Skus Data from Excel'));
        return $resultPage;
    }
}
