<?php
namespace Sttl\Customerorder\Helper;

use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class Galleryhelper extends \Magento\Framework\App\Helper\AbstractHelper {
	protected $galleryReadHandler;
    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    public function __construct(
    GalleryReadHandler $galleryReadHandler,  \Magento\Framework\App\Helper\Context $context,\Magento\Catalog\Helper\Image $imageHelper)
    {
        $this->imageHelper = $imageHelper;
        $this->galleryReadHandler = $galleryReadHandler;
        parent::__construct($context);
    }

   /** Add image gallery to $product */
    public function addGallery($product) {
        $this->galleryReadHandler->execute($product);
    }

    public function getGalleryImages(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        return $images;
    }
}