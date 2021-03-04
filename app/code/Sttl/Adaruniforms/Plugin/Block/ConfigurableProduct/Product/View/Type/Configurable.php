<?php

namespace Sttl\Adaruniforms\Plugin\Block\ConfigurableProduct\Product\View\Type;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Sttl\Feature\Model\Feature;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;

class Configurable
{

    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $stockRegistry;
    protected $feature;
    protected $_product;
    protected $cache;

    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        StockRegistryInterface $stockRegistry,
        Feature $feature,
        \Magento\Framework\App\CacheInterface $cache,
        AttributeRepositoryInterface $attributeRepository,
        Product $_product
    ) {

        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->stockRegistry = $stockRegistry;
        $this->feature = $feature;
        $this->cache = $cache;
         $this->attributeRepository = $attributeRepository;
        $this->_product = $_product;

    }

    // Adding Quantitites (product=>qty)
    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        \Closure $proceed
    )
    {


        $feature = [];
        $description = [];
        $details = [];
        $fabriccontent = [];
        $fabricimageurl = [];

        $config = $proceed();
        $config = $this->jsonDecoder->decode($config);

        // $productId = $subject->getAllowProducts();
        $parentId = '';
        foreach ($subject->getAllowProducts() as $product) {
            $parentId = $product->getParentId();

        }
        
        $configrableproductdata = [];

        $ctemp_pro = $this->_product->load($parentId);
        $configproductdescription =   $ctemp_pro->getData('description');
        $configproductbulletsdetails =   $ctemp_pro->getData('bulletsdetails');

        $listValue = explode("<br>", $configproductbulletsdetails);
                $temp_detailed_rendered = "";
                if(count($listValue) > 1){
                    foreach ($listValue as $value):
                        $temp_detailed_rendered .= "<li>".$value."</li>";
                    endforeach;
                    $configproductbulletsdetails = $temp_detailed_rendered;
                }


        $configproductprofabriccontent =   $ctemp_pro->getData('fabriccontent');
        $configproductufabriccareurl =   $ctemp_pro->getData('ufabriccareurl');
        $configproductfeaturedata = $this->feature->getCollection()->addFieldToFilter('option_id', ['in' => $ctemp_pro->getData('feature')])->setOrder('sort_order','ASC')->load();

        $configproductfeaturedata = $configproductfeaturedata->getData();
      


        $featureId = $this->attributeRepository->get(Product::ENTITY, 'feature');
        $featurelable = $ctemp_pro->getResource()->getAttribute($featureId->getAttributeId())->getFrontend()->getLabel($ctemp_pro);


        $fabriccontentId = $this->attributeRepository->get(Product::ENTITY, 'fabriccontent');
        $fabriccontentlable = $ctemp_pro->getResource()->getAttribute($fabriccontentId->getAttributeId())->getFrontend()->getLabel($ctemp_pro);

         $descriptionId = $this->attributeRepository->get(Product::ENTITY, 'description');
        $descriptionlable = $ctemp_pro->getResource()->getAttribute($descriptionId->getAttributeId())->getFrontend()->getLabel($ctemp_pro);

        $bulletsdetailsId = $this->attributeRepository->get(Product::ENTITY, 'bulletsdetails');
        $bulletsdetailslable = $ctemp_pro->getResource()->getAttribute($bulletsdetailsId->getAttributeId())->getFrontend()->getLabel($ctemp_pro);

        $ufabriccareurlId = $this->attributeRepository->get(Product::ENTITY, 'ufabriccareurl');
        $ufabriccareurllable = $ctemp_pro->getResource()->getAttribute($ufabriccareurlId->getAttributeId())->getFrontend()->getLabel($ctemp_pro);

            

        $des_data['attributevalu'] = $configproductdescription;
        $des_data['label'] = $descriptionlable;
        $prodetails_data['attributevalu'] = $configproductbulletsdetails;
        $prodetails_data['label'] = $bulletsdetailslable;
        $fabriccontent_data['attributevalu'] = $configproductprofabriccontent;
        $fabriccontent_data['label'] = $fabriccontentlable;
        $profabricimageurl_data['attributevalu'] = $configproductufabriccareurl;
        $profabricimageurl_data['label'] = $ufabriccareurllable;
        $feature_data['attributevalu'] = $configproductfeaturedata;
        $feature_data['label'] = $featurelable;


        $configrableproductdata['description'] = $des_data;
        $configrableproductdata['prodetails'] = $prodetails_data;
        $configrableproductdata['fabriccontent'] = $fabriccontent_data;
        $configrableproductdata['profabricimageurl'] = $profabricimageurl_data;
        $configrableproductdata['feature'] = $feature_data;

        $configrableprodatacache = $this->cache->load("Configurableprodata".$parentId);

         
         if(!$configrableprodatacache)
         {
            foreach ($subject->getAllowProducts() as $product) 
            {
                

               
                $temp_pro = $this->_product->load($product->getId());
                //Get Feature Data
                $featuredata = $this->feature->getCollection()->addFieldToFilter('option_id', ['in' => $product->getData('feature')])->setOrder('sort_order','ASC')->load();
                $featurearray[$product->getId()] = $featuredata->getData();
                //Feature data Fetched

                //Get Description
                $description[$product->getId()] = $temp_pro->getData('description');
                // $description[$product->getId()] = $temp_pro->getCustomAttribute('description')->getValue();
                //Description Fetched

                //Get BulletsDetails
                $temp_details = $temp_pro->getData('bulletsdetails');
                $listValue = explode("<br>", $temp_details);
                $temp_detailed_rendered = "";
                if(count($listValue) > 1){
                    foreach ($listValue as $value):
                        $temp_detailed_rendered .= "<li>".$value."</li>";
                    endforeach;
                    $details[$product->getId()] = $temp_detailed_rendered;
                }
                //BulletsDetails Fetched

                //Get Fabriccontent
                $fabriccontent[$product->getId()] = $temp_pro->getData('fabriccontent');
                //Fabriccontent Fetched

                //Get FabricUrl
                $fabricimageurl[$temp_pro->getId()] = $temp_pro->getData("ufabriccareurl");
                //FabricUrl Fetched
                
                
            }
    
            $config['profeature'] = $featurearray;
            $config['prodescription'] = $description;
            $config['prodetails'] = $details;
            $config['profabriccontent'] = $fabriccontent;
            $config['profabricimageurl'] = $fabricimageurl;
            $config['configrableproductdata'] = $configrableproductdata;

            $data = json_encode($config,true);

            $this->cache->save($data, "Configurableprodata".$parentId); 
            return $this->jsonEncoder->encode($config);       

         }
         else 
         {
             return $this->jsonEncoder->encode(json_decode($configrableprodatacache,true));
         }
            
    }
}
