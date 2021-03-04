<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Feature\Model\Feature;



class Quickview extends \Magento\Framework\App\Action\Action {

    protected $feature;

    protected $galleryReadHandler;

    protected $collectionFactory;

    private $cacheId = 'quickviewpopup';

    protected $productRepository;

    public function __construct(
        Context $context,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $procollection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Feature $feature,
        \Magento\Catalog\Model\Product $Product,
        \Sttl\Customerorder\Helper\Galleryhelper $galleryhelper,
        \Vendor\Rules\Model\GridFactory $factoryCategory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Config $optionsvalue,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []

    ) {
        parent::__construct($context);
        $this->procollection = $procollection;
        $this->swatchHelper = $swatchHelper;
        $this->feature = $feature;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->galleryhelper = $galleryhelper;
        $this->factoryCategory = $factoryCategory;
        $this->_productFactory = $productFactory;
        $this->optionsvalue = $optionsvalue;
        $this->products = $Product;
        $this->cache = $cache;
        $this->productRepository = $productRepository;
    }


    public function getfeatureprocollection(){
        return $this->factoryCategory->create()->getCollection()->addFieldToSelect('sku')->addFieldToFilter('is_active',array('in' => array(1,2)))->setOrder('sort_order', 'ASC')->setPageSize(6)->getData();



    }

    public function getProductImages($productId) {
        $_product = $this->_productFactory->create()->load($productId);
        $productImages = $_product->getMediaGalleryImages();
        return $productImages;
    }
    public function execute() {

        $data = $this->cache->load($this->cacheId);
        $resultJson = $this->resultJsonFactory->create();  
    if (!$data) {
        $Featuredpros = $this->getfeatureprocollection();

        // foreach ($Featuredpros as $key => $value) {
        //     print_r($value);
        // }die;
        $attribute = $this->optionsvalue->getAttribute('catalog_product', 'size');
        $options = $attribute->getSource()->getAllOptions();
        $simpleproductdata = [];
        $configdata = [];
        foreach ($Featuredpros as $key => $value) {
        $product =  $this->productRepository->get($value['sku']);   
            
            $configimagesurls =array();
            $images = $this->getProductImages($product->getId());
            if ($images instanceof \Magento\Framework\Data\Collection) {
                        $i = 0;
                        foreach ($images as $image) {
                            $configimagesurls =array();
                            $i = 0;
                            foreach ($images as $image) {
                                $configimagesurls[$i] = $image->getUrl();
                                $i++;
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
                            $imagesurls =array();
                            $i = 0;
                            foreach ($images as $image) {
                                $imagesurls[$i] = $image->getUrl();
                                $i++;
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

        
        $responces[] = ['configurationproduct' => $configdata, 'simapleproduct' => $simpleproductdata, 'sizeoption'=> $options];

        $data = json_encode($responces,true);
        $this->cache->save($data,$this->cacheId);
    }
        if ($data) {
        $data = json_decode($data,true);
            return $resultJson->setData($data);
        }else{
            return $resultJson->setData($responces);
        }
    }
}