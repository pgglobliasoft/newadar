<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Inquiries\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Sttl\Inquiries\Controller\Index
{
    /**
     * Show Groups page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
