<?php

namespace Sttl\Feature\Block\Product;

use Magento\Catalog\Model\Product;

class Feature extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

	protected $pageLayoutBuilder;
	
	protected $options;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_featureHelper;
    protected $_feature;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sttl\Feature\Helper\Data $featureHelper,
        \Sttl\Feature\Model\Feature $feature,
        array $data = []
    )
    {
        $this->_feature = $feature;
        $this->_featureHelper = $featureHelper;
        $this->_coreRegistry = $registry;
		$this->pageConfig = $context->getPageConfig();
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    public function getFeature()
    {
        $optionId = $this->getProduct()->getMgsFeature();
        if ($optionId) {
            $collection = $this->_feature->getCollection()->addFieldToFilter('option_id', ['eq' => $optionId]);
            if (count($collection)) {
                return $collection->getFirstItem();
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_featureHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }
	
	public function getLayoutOptions($withEmpty = true)
	{
		$pageLayout = $this->getPageLayout();
		
		return $pageLayout;
	}
	
	
    /**
     * @return string
     */
    protected function getPageLayout()
    {
        return $this->pageConfig->getPageLayout() ?: $this->getLayout()->getUpdate()->getPageLayout();
    }
}
