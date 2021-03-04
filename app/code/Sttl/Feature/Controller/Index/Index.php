<?php

namespace Sttl\Feature\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_featureHelper;
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Sttl\Feature\Helper\Data $featureHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    )
    {
        $this->_featureHelper = $featureHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_featureHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($this->_featureHelper->getConfig('list_page_settings/template')) {
            $resultPage->getConfig()->setPageLayout($this->_featureHelper->getConfig('list_page_settings/template'));
        }
        return $resultPage;
    }
}