<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Childskus\Block\Adminhtml\Excel;

/**
 * @api
 * @since 100.0.2
 */
class Import extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Sttl_Childskus::import.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }
}
