<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\Rules\Controller\Adminhtml\Excel;

// use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Import extends \Vendor\Rules\Controller\Adminhtml\Excel
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
        $resultPage->setActiveMenu('Vendor_Rules::Marketing_Product_import');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Vendor\Rules\Block\Adminhtml\Excel\ImportHeader::class
            )
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Vendor\Rules\Block\Adminhtml\Excel\Import::class)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Import Marketing Product Data from Excel'));
        return $resultPage;
    }
}
