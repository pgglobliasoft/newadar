<?php

namespace Sttl\Feature\Controller\Feature;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Sttl\Feature\Model\Layer\Resolver;

class View extends \Magento\Framework\App\Action\Action
{
    protected $_request;
    protected $_response;
    protected $resultRedirectFactory;
    protected $resultFactory;
    protected $_featureModel;
    protected $_coreRegistry = null;
    private $layerResolver;
    protected $resultForwardFactory;
    protected $_featureHelper;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Sttl\Feature\Model\Feature $featureModel,
        \Magento\Framework\Registry $coreRegistry,
        Resolver $layerResolver,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Sttl\Feature\Helper\Data $featureHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_featureModel = $featureModel;
        $this->layerResolver = $layerResolver;
        $this->_coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_featureHelper = $featureHelper;
    }

    public function _initFeature()
    {
        $featureId = (int)$this->getRequest()->getParam('feature_id', false);
        if (!$featureId) {
            return false;
        }
        try {
            $feature = $this->_featureModel->load($featureId);
        } catch (\Exception $e) {
            return false;
        }
        $this->_coreRegistry->register('current_feature', $feature);
        return $feature;
    }

    public function execute()
    {
        if (!$this->_featureHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $feature = $this->_initFeature();
        if ($feature) {
            $this->layerResolver->create('feature');
            $page = $this->resultPageFactory->create();
            if ($this->_featureHelper->getConfig('view_page_settings/template')) {
                $page->getConfig()->setPageLayout($this->_featureHelper->getConfig('view_page_settings/template'));
            }
            $page->addHandle(['type' => 'Sttl_BRAND_' . $feature->getId()]);
            if (($layoutUpdate = $feature->getLayoutUpdateXml()) && trim($layoutUpdate) != '') {
                $page->addUpdate($layoutUpdate);
            }
            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('feature-' . $feature->getUrlKey());
            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}