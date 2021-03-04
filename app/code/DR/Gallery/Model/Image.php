<?php

namespace DR\Gallery\Model;

use DR\Gallery\Api\Data\ImageInterface;
use DR\Gallery\Model\Image\Source\Status;
use DR\Gallery\Model\Image\Source\Category;
use DR\Gallery\Model\ResourceModel\Image as ImageResource;
use DR\Gallery\Model\ResourceModel\Image\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Image extends AbstractModel implements ImageInterface, IdentityInterface
{
    const CACHE_TAG = 'dr_gallery_image';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string|array|bool
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'dr_gallery_image';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'image';

    /**
     * Gallery constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ImageResource $resource
     * @param StoreManagerInterface $storeManager
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ImageResource $resource,
        StoreManagerInterface $storeManager,
        Collection $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ImageResource::class);
    }

    /**
     * getAvailableStatuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            Status::ENABLED => __('Enabled'),
            Status::DISABLED => __('Disabled')
        ];
    }

    /**
     * getCategory
     *
     * @return array
     */
    public function getCustomCategory()
    {
        return [
            Category::CATLOG => __('Catalog'),
            Category::SWATCHCARD => __('Swatch Cards'),
            Category::IMAGELIBRARY => __('Image Library'),
            Category::INVENTORYANDUPCFILES => __('Inventory and UPC files'),
            Category::LISTMAPPRICINGANDDISCOUNTFILES => __('List/Map Pricing and Discount Files | Price Lists'),
            Category::AIOITEMSDATAFILES => __('AIO Items Data Files | PRODUCT DATA FILES (AIO)'),
            Category::DOCUMENTATIONPOLICIESANDMORE => __('Documentation,Policies and more'),
            Category::SIZEFITGUIDE => __('Size & Fit Guide ')
        ];
    }

    /**
     * Retrieve id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(static::ID);
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(static::NAME);
    }

    /**
     * Retrieve path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getData(static::PATH);
    }

    /**
     * Retrieve small_image
     *
     * @return string
     */
    public function getSmallImage()
    {
        return $this->getData(static::SMALLIMAGE);
    }
    
    /**
     * Retrieve download_url
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->getData(static::DOWNLOADURL);
    }


    /**
     * Retrieve custom_url
     *
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->getData(static::CUSTOMURL);
    }

    /**
     * Retrieve status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Retrieve download
     *
     * @return int
     */
    public function getDownload()
    {
        return $this->getData(static::DOWNLOAD);
    }


    /**
     * Retrieve status
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->getData(static::CATEGORY);
    }

    /**
     * Retrieve created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(static::CREATED_AT);
    }

    /**
     * Retrieve updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(static::UPDATED_AT);
    }

    /**
     * Set id
     *
     * @param int $id
     * @return ImageInterface
     */
    public function setId($id)
    {
        $this->setData(static::ID, $id);
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ImageInterface
     */
    public function setName($name)
    {
        $this->setData(static::NAME, $name);
        return $this;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return ImageInterface
     */
    public function setPath($path)
    {
        $this->setData(static::PATH, $path);
        return $this;
    }

    /**
     * Set SMALLIMAGE
     *
     * @param string $small_image
     * @return ImageInterface
     */
    public function setSmallImage($small_image)
    {
        $this->setData(static::SMALLIMAGE, $small_image);
        return $this;
    }
    
    /**
     * Set DOWNLOADURL
     *
     * @param string $download_url
     * @return ImageInterface
     */
    public function setDownloadUrl($download_url)
    {
        $this->setData(static::DOWNLOADURL, $download_url);
        return $this;
    }

    /**
     * Set CUSTOMURL
     *
     * @param string $custom_url
     * @return ImageInterface
     */
    public function setCustomUrl($custom_url)
    {
        $this->setData(static::CUSTOMURL, $custom_url);
        return $this;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return ImageInterface
     */
    public function setStatus($status)
    {
        $this->setData(static::STATUS, $status);
        return $this;
    }

     /**
     * Set download
     *
     * @param int $download
     * @return ImageInterface
     */
    public function setDownload($download)
    {
        return $this->getData(static::DOWNLOAD, $download);
    }


    /**
     * Set category
     *
     * @param int $category
     * @return ImageInterface
     */
    public function setCategoryId($category_id)
    {
        $this->setData(static::CATEGORY, $category_id);
        return $this;
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return ImageInterface
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(static::CREATED_AT, $createdAt);
        return $this;
    }


    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return ImageInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(static::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return implode('/', [
            rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/'),
            $this->getPath()
        ]);
    }
}