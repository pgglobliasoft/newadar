<?php

namespace Sttl\Feature\Block\Feature;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_catalogLayer;
    protected $_featureHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Sttl\Feature\Helper\Data $featureHelper,
        array $data = []
    )
    {
        $this->_featureHelper = $featureHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $featureRoute = $this->_featureHelper->getConfig('general_settings/route');
        $pageTitle = $this->_featureHelper->getConfig('list_page_settings/title');
        $feature = $this->getCurrentFeature();
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbs->addCrumb(
            'sttl_feature',
            [
                'label' => $pageTitle,
                'title' => $pageTitle,
                'link' => $baseUrl . $featureRoute
            ]
        );
        $breadcrumbs->addCrumb(
            'feature',
            [
                'label' => $feature->getName(),
                'title' => $feature->getName(),
                'link' => ''
            ]
        );
    }

    public function getCurrentFeature()
    {
        $feature = $this->_coreRegistry->registry('current_feature');
        if ($feature) {
            $this->setData('current_feature', $feature);
        }
        return $feature;
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_featureHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    protected function _prepareLayout()
    {
        $feature = $this->getCurrentFeature();
        $pageTitle = $feature->getName();
        $metaKeywords = $feature->getMetaKeywords();
        $metaDescription = $feature->getMetaDescription();
        $this->_addBreadcrumbs();
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaKeywords) {
            $this->pageConfig->setKeywords($metaKeywords);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}