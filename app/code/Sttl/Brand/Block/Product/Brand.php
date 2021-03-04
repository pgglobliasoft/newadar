<?php

namespace Sttl\Brand\Block\Product;

use Magento\Catalog\Model\Product;

class Brand extends \Magento\Framework\View\Element\Template
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
    protected $_brandHelper;
    protected $_brand;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sttl\Brand\Helper\Data $brandHelper,
        \Sttl\Brand\Model\Brand $brand,
        array $data = []
    )
    {
        $this->_brand = $brand;
        $this->_brandHelper = $brandHelper;
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

    public function getBrand()
    {
        $optionId = $this->getProduct()->getMgsBrand();
        if ($optionId) {
            $collection = $this->_brand->getCollection()->addFieldToFilter('option_id', ['eq' => $optionId]);
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
        $result = $this->_brandHelper->getConfig($key);
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
