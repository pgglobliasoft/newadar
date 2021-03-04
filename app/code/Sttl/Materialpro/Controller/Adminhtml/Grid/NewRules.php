<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Materialpro\Controller\Adminhtml\Grid;

use Magento\Framework\Controller\ResultFactory;

class NewRules extends \Sttl\Materialpro\Controller\Adminhtml\Index
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        
        $resultPage->getConfig()->getTitle()->prepend(__('New Rules Form'));
        return $resultPage;
    }
}
