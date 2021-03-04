<?php

namespace Sttl\Feature\Model;

use Magento\Framework\DataObject\IdentityInterface;

class Feature extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    protected $_productCollectionFactory;
    protected $_storeManager;
    protected $_url;
    protected $_featureHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Sttl\Feature\Model\Resource\Feature $resource = null,
        \Sttl\Feature\Model\Resource\Feature\Collection $resourceCollection = null,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\UrlInterface $url,
        \Sttl\Feature\Helper\Data $featureHelper,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_featureHelper = $featureHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Sttl\Feature\Model\Resource\Feature');
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('feature', ['eq' => $this->getOptionId()]);
        return $collection;
    }

    public function getUrl()
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $route = $this->_featureHelper->getConfig('general_settings/route');
        return $url . $route . '/' . $this->getUrlKey();
    }

    public function getSmallImageUrl()
    {
        $url = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'sttl_feature/no_image.png';
        $thumbnail = $this->getSmallImage();
        if ($thumbnail) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $thumbnail;
        };
        return $url;
    }

    public function getImageUrl()
    {
        $url = false;
        $image = $this->getImage();
        if ($image) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $image;
        };
        return $url;
    }
}
