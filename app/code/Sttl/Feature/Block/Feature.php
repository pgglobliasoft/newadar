<?php

namespace Sttl\Feature\Block;

use Magento\Customer\Model\Context as CustomerContext;

class Features extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_featureHelper;
    protected $_feature;
    protected $httpContext;
    protected $_catalogProductVisibility;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sttl\Feature\Helper\Data $featureHelper,
        \Sttl\Feature\Model\Feature $feature,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        array $data = []
    )
    {
        $this->_feature = $feature;
        $this->_coreRegistry = $registry;
        $this->_featureHelper = $featureHelper;
        $this->httpContext = $httpContext;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();        
        $feature = $this->_feature;
        $featureCollection = $feature->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('sort_order', 'ASC');
        $params = $this->getRequest()->getParams();
        if (isset($params['keyword']) && $params['keyword'] != '') {
            $featureCollection->addFieldToFilter('name', ['like' => '%' . $params['keyword'] . '%']);
        }
        if (isset($params['char']) && $params['char'] != '' && $params['char'] != '0-9') {
            $featureCollection->addFieldToFilter('name', ['like' => $params['char'] . '%']);
        }
        $this->setCollection($featureCollection);
    }    

    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $pageTitle = $this->_featureHelper->getConfig('list_page_settings/title');
        $breadcrumbsBlock->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbsBlock->addCrumb(
            'feature',
            [
                'label' => $pageTitle,
                'title' => $pageTitle,
                'link' => ''
            ]
        );
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
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
        $pageTitle = $this->getConfig('list_page_settings/title');
        $metaKeywords = $this->getConfig('list_page_settings/meta_keywords');
        $metaDescription = $this->getConfig('list_page_settings/meta_description');
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('feature-list');
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

    public function getProductCount(\Sttl\Feature\Model\Feature $feature)
    {
        $collection = $feature->getProductCollection();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        return count($collection);
    }

}
