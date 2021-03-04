<?php

namespace Globala\Customapi\Model\Api;

use Sttl\Feature\Model\Feature;

class ProductApi implements \Globala\Customapi\Api\ProductApiInterface
{

    protected $productRepository;
    protected $feature;
    protected $_objectManager;
    protected $importproductbrand;

    public function __construct(
       \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $procollection,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product $Product,
        Feature $feature,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Sttl\Importproductbrand\Model\Importproductbrand $importproductbrand

    )
    {
        $this->productRepository = $productRepository;
        $this->procollection = $procollection;
        $this->feature = $feature;
        $this->_objectManager = $objectmanager;
        $this->swatchHelper = $swatchHelper;
        $this->products = $Product;
        $this->_productFactory = $productFactory;
        $this->cache = $cache;
        $this->importproductbrand = $importproductbrand;
    }

    /**
     * get test Api data.
     *
     * @api
     *
     * @param string $id
     *
     * @return \Globala\Customapi\Model\Api
     */
    public function getApiData($id)
    {
        $skuss = explode(",", $id);
        $tempsimpleproductdata = [];
        $tempconfigdata = [];
        $responces = [];
        foreach ($skuss as $key => $value) {
        $data = $this->cache->load("cache".$value);
            if(!$data){
                    $simpleproductdata = [];
                    $configdata = [];
                    $configimagesurls =array();
                    $product =  $this->productRepository->get($value);

                    $productBrandUrl = $this->getproductBrandUrl($product);

                    $images = $this->getProductImages($product->getId());
                    if ($images instanceof \Magento\Framework\Data\Collection) {
                                $i = 0;
                                foreach ($images as $image) {
                                    $configimagesurls =array();
                                    $i = 0;
                                    foreach ($images as $image) {
                                        if($image->getMedia_type() === 'image'){
                                            $configimagesurls[$i] = $image->getUrl();
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
                             'productimages' => $configimagesurls,
                             'productBrandUrl' => $productBrandUrl,
                             'fabric_chat' => ''
                            ];  
                    $_children = $product->getTypeInstance()->getUsedProducts($product);
                    $tempcolor =array();
                    foreach ($_children as $child){
                        $cproduct = $this->products->load($child->getID());
                         $imagesurls =array();
                            // if (!in_array($child->getColor(), $tempcolor) || !in_array($child->getSeasonalcolors(), $tempcolor) || !in_array($child->getHeather_colors(), $tempcolor))
                            // {
                                $images = $this->getProductImages($child->getID());
                                if(count($images) > 0){
                                    array_push($tempcolor,$child->getColor());
                                }
                                if ($images instanceof \Magento\Framework\Data\Collection) {
                                    $i = 0;
                                    foreach ($images as $image) {
                                        $imagesurls =array();
                                        $i = 0;
                                        foreach ($images as $image) {
                                            if($image->getMedia_type() === 'image'){
                                                $imagesurls[$i] = $image->getUrl();
                                                $i++;
                                            }
                                        }
                                    }
                                }
                            // }
                        $featuredata = $this->feature->getCollection()->addFieldToFilter('option_id', ['in' => $child->getFeature()])->setOrder('sort_order','ASC')->load();
                        $hashcodeData = $this->swatchHelper->getSwatchesByOptionsId([$child->getColor(),$child->getHeather_colors(),$child->getSeasonalcolors()]);

                        $simpleproductdata[] = [
                                        'id' => $child->getSku(),
                                        'feature' => $featuredata->getData(),
                                        'parent_sku' => $value,
                                        'name' => $child->getName(),
                                        'collection' => $cproduct->getResource()->getAttribute("collecttion")->getFrontend()->getValue($cproduct),
                                        'image' => $imagesurls,
                                        'color' => $cproduct->getResource()->getAttribute("color")->getFrontend()->getValue($cproduct),
                                        'colorurl' => @$hashcodeData[$child->getColor()]['value'],   
                                        'heathercolor' => ($cproduct->getResource()->getAttribute("heather_colors")->getFrontend()->getValue($cproduct) == 'No') ? null : $cproduct->getResource()->getAttribute("heather_colors")->getFrontend()->getValue($cproduct),
                                        'heathercolorurl' => @$hashcodeData[$child->getHeather_colors()]['value'],
                                        'seasonalcolor' => $cproduct->getResource()->getAttribute("seasonalcolors")->getFrontend()->getValue($cproduct),
                                        'seasonalcolorurl' => @$hashcodeData[$child->getSeasonalcolors()]['value'],
                                        'size' => $child->getSize(),
                                        'size_chat' => $cproduct->getResource()->getAttribute("sizecharturl")->getFrontend()->getValue($cproduct),
                                        'fabric_chat' => $cproduct->getResource()->getAttribute("ufabriccareurl")->getFrontend()->getValue($cproduct),
                                    ];
                    }
                    $cachedata[$value] = ['configurationproduct' => $configdata, 'simapleproduct' => $simpleproductdata];
                    $dataaaa = json_encode($cachedata,true);
                    $this->cache->save($dataaaa,"cache".$value);


                    $tempsimpleproductdata = array_merge($tempsimpleproductdata,$simpleproductdata);
                    $tempconfigdata = array_merge($tempconfigdata,$configdata);
                    
            }else{
                $dataa = json_decode($data,true);
                $tempsimpleproductdata = array_merge($tempsimpleproductdata,$dataa[$value]['simapleproduct']);
                $tempconfigdata = array_merge($tempconfigdata,$dataa[$value]['configurationproduct']);
            }        

        }

        $responces[] = ['configurationproduct' => $tempconfigdata, 'simapleproduct' => $tempsimpleproductdata];
        return $responces;
    }
    public function getProductImages($productId) {
        $_product = $this->_productFactory->create()->load($productId);
        $productImages = $_product->getMediaGalleryImages();
        return $productImages;
    }
    public function getproductBrandUrl($product)
    {
        
        // $_product = $this->getProductDetails();
        $brands_product_urls =  $this->importproductbrand
                                ->getCollection()
                                ->addFieldToFilter('brand_id', '1')
                                ->addFieldToFilter('sku', array('eq' => $product->getSku()));
        $brand_product_url = $brands_product_urls->getData();       
        return $brand_product_url;
    }

}