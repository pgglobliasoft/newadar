<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Childskus\Controller\Adminhtml;

    
use Sttl\Childskus\Controller\RegistryConstants;

/**
 * Adminhtml tax rate controller
 */
abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    // const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    protected $resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

}
