<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Feature\Model\Feature;
use Magento\Framework\App\Filesystem\DirectoryList;

class MageProJson extends \Magento\Framework\App\Action\Action {

    protected $feature;

    protected $galleryReadHandler;

    protected $collectionFactory;

    private $cacheId = 'quickviewpopup';

    protected $productRepository;

    public function __construct(
        Context $context,
        Feature $feature,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Catalog\Model\Product $Product,
        \Magento\Eav\Model\Config $optionsvalue,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Block\Product\Context $imagecontext,
        array $data = []

    ) {
        parent::__construct($context);
        $this->feature = $feature;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->_productFactory = $productFactory;
        $this->swatchHelper = $swatchHelper;
        $this->products = $Product;
        $this->optionsvalue = $optionsvalue;
        $this->_filesystem = $filesystem;
        $this->_imageHelper = $imagecontext->getImageHelper();
		$this->_directory =  $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT); 
    }

    public function getProductImages($productId) {
        $_product = $this->_productFactory->create()->load($productId);
        $productImages = $_product->getMediaGalleryImages();
        return $productImages;
    }

    public function execute() {

    	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);

        $attribute = $this->optionsvalue->getAttribute('catalog_product', 'size');
        $options = $attribute->getSource()->getAllOptions();
        $simpleproductdata = [];
        $configdata = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name')->addAttributeToSort('sku', 'ASC');
        foreach ($collection as $product) {
	        if($product->getTypeId() == "configurable"){
	            $configimagesurls =array();
	            $images = $this->getProductImages($product->getId());
	            if ($images instanceof \Magento\Framework\Data\Collection) {
	                        $i = 0;
	                        foreach ($images as $image) {
				                $image->setData(
				                    'medium_image_url',
				                    $this->_imageHelper->init($product, 'product_page_image_medium_no_frame')
				                        ->setImageFile($image->getFile())
				                        ->getUrl()
				                );
	                            $configimagesurls =array();
	                            $i = 0;
	                            foreach ($images as $image) {
	                            	if($image->getMedia_type() == 'image'){
		                                $configimagesurls[$i] = $image->getMedium_image_url();
		                                $i++;
	                            	}
	                            }
	                        }
	                    }
	                    
	            $configdata[] = [
	                         'id' => $product->getSku(),
	                         'name' => $product->getName(),
	                         'collection' => $product->getResource()->getAttribute("collecttion")->getFrontend()->getValue($product),
	                         'producturl' => $product->getProductUrl(),
	                         'productimgurl' => $product->getImage(),
	                         'productimages' => $configimagesurls,
	                            ];   

	            $_children = $product->getTypeInstance()->getUsedProducts($product);

	                $tempcolor =array();
	            foreach ($_children as $child){
	                $cproduct = $this->products->load($child->getID());
	                $imagesurls =array();
	                if (!in_array($child->getColor(), $tempcolor) || !in_array($child->getSeasonalcolors(), $tempcolor) || !in_array($child->getHeather_colors(), $tempcolor))
	                {
	                    array_push($tempcolor,$child->getColor());
	                    $images = $this->getProductImages($child->getID());
	                    if ($images instanceof \Magento\Framework\Data\Collection) {
	                        $i = 0;
	                        foreach ($images as $image) {
	                        	$image->setData(
				                    'medium_image_url',
				                    $this->_imageHelper->init($child, 'product_page_image_medium_no_frame')
				                        ->setImageFile($image->getFile())
				                        ->getUrl()
				                );
	                            $imagesurls =array();
	                            $i = 0;
	                            foreach ($images as $image) {
	                            	if($image->getMedia_type() == 'image'){
	                                	$imagesurls[$i] = $image->getMedium_image_url();
	                                	$i++;
	                            	}
	                            }
	                        }
	                    }
	                }
	                
	                $featuredata = $this->feature->getCollection()->addFieldToFilter('option_id', ['in' => $child->getFeature()])->setOrder('sort_order','ASC')->load();
	                $hashcodeData = $this->swatchHelper->getSwatchesByOptionsId([$child->getColor(),$child->getHeather_colors(),$child->getSeasonalcolors()]);
	                $simpleproductdata[] = [
	                            'id' => $child->getSku(),
	                            'feature' => $featuredata->getData(),
	                            'parent_sku' => $product->getSku(),
	                            'name' => $child->getName(),
	                            'collection' => $cproduct->getResource()->getAttribute("collecttion")->getFrontend()->getValue($cproduct),
	                            'image' => $imagesurls,
	                            'color' => $cproduct->getResource()->getAttribute("color")->getFrontend()->getValue($cproduct),
	                            'colorurl' => @$hashcodeData[$child->getColor()]['value'],   
	                            'heathercolor' => ($cproduct->getResource()->getAttribute("heather_colors")->getFrontend()->getValue($cproduct) == 'No') ? null : $cproduct->getResource()->getAttribute("heather_colors")->getFrontend()->getValue($cproduct),
	                            'heathercolorurl' => @$hashcodeData[$child->getHeather_colors()]['value'],
	                            'seasonalcolor' => ($cproduct->getResource()->getAttribute("seasonalcolors")->getFrontend()->getValue($cproduct) == 'No') ? null : $cproduct->getResource()->getAttribute("seasonalcolors")->getFrontend()->getValue($cproduct),
	                            'seasonalcolorurl' => @$hashcodeData[$child->getSeasonalcolors()]['value'],
	                            'size' => $child->getSize(),
	                            'size_chat' => $cproduct->getResource()->getAttribute("sizecharturl")->getFrontend()->getValue($cproduct),
	                            'fabric_chat' => $cproduct->getResource()->getAttribute("ufabriccareurl")->getFrontend()->getValue($cproduct),
	                        ];
	            }
	        }
    	}
        $responces[] = ['configurationproduct' => $configdata, 'simapleproduct' => $simpleproductdata, 'sizeoption'=> $options];
        $Json = json_encode($responces,true);
        try {
		    $media = $this->_filesystem->getDirectoryWrite(DirectoryList::APP);
		    $media1 = $this->_filesystem->getDirectoryWrite(DirectoryList::PUB);
		    try {
		    	$media->writeFile("code/Sttl/Customerorder/view/frontend/web/template/MageProJson.json",$Json);
		    	$media1->writeFile("static/frontend/sttl/adaruniforms/en_US/Sttl_Customerorder/template/MageProJson.json",$Json);
	        }
	        catch(Exception $e) {
	        	$logger->info($e->getMessage());
	    	}
	        finally {
	            $logger->info("Inventory Json File is Gendered");
	        }
	    } catch(Exception $e) {
	        $logger->info($e->getMessage());
	    }
    }
}