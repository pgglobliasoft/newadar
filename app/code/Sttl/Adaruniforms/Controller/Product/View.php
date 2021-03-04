<?php

namespace Sttl\Adaruniforms\Controller\Product;


use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Sttl\Feature\Model\Feature;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;

class View extends \Magento\Framework\App\Action\Action
{

	protected $jsonEncoder;
    protected $jsonDecoder;
    protected $stockRegistry;
    protected $feature;
    protected $_product;
    protected $cache;
	protected $resultJsonFactory;
	protected $productRepository;

	public function __construct(
		 EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        StockRegistryInterface $stockRegistry,
        Feature $feature,
        \Magento\Framework\App\CacheInterface $cache,
        AttributeRepositoryInterface $attributeRepository,
    	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Product $_product,
        ProductRepository $productRepository,
		\Magento\Framework\App\Action\Context $context
	){

		$this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->stockRegistry = $stockRegistry;
        $this->feature = $feature;
        $this->cache = $cache;
        $this->attributeRepository = $attributeRepository;
    	$this->resultJsonFactory = $resultJsonFactory;
        $this->_product = $_product;
        $this->productRepository = $productRepository;
		parent::__construct($context);

	}

	public function execute()
    {
   
   		$post = $this->getRequest()->getParams();
    	$productid = $post['productid'];
    	$resultJson = $this->resultJsonFactory->create();
    	$product = $this->_product->load($productid);


    	$config = [];

    	$cacheconfigdata = $this->cache->load("oneconfigrabledata".$productid);

    	if(!$cacheconfigdata){

    		$petiteSku = $this->_product->getPetite();
			$tailSku = $this->_product->getTail();
			$regularSku = $this->_product->getRegular();
			$currentsku = $this->_product->getSku();
			$check = substr($currentsku, -1);
			if(strtoupper($check) == strtoupper(trim('P')) || strtoupper($check) == strtoupper(trim('T')))
			{
			    $regularSku = substr($currentsku, 0,-1);
			}else{
			    $regularSku = $this->_product->getSku();
			}
			if($tailSku == '' && $petiteSku == '')
			{
			    $tailSku = $regularSku.'T';
			    $petiteSku = $regularSku.'P';
			}
			
			$regula = [];
			$tail = [];
			$petite = [];
			
			$petiteUrl = '';
			if($petiteSku != '')
			{
				try 
				{
			        $petiteproductdata = $this->productRepository->get($petiteSku);
			        $petiteId = $petiteproductdata->getId();
			        $petiteSku = $petiteproductdata->getSku();
			        $petiteUrl = $petiteproductdata->getProductUrl();

			        $petite['id'] = $petiteId;
					$petite['sku'] = $petiteSku;
					$petite['url'] = $petiteUrl;

			    } 
			    catch (\Magento\Framework\Exception\NoSuchEntityException $e)
			    {
			        $petiteSku = '';
			    }
			}
			$tailUrl = '';
			if($tailSku != '')
			{

			    try
			    {
			        $tailproductdata = $this->productRepository->get($tailSku);
			        $tailId = $tailproductdata->getId();
			        $tailSku = $tailproductdata->getSku();
			        $tailUrl = $tailproductdata->getProductUrl();

			        $tail['id'] = $tailId;
					$tail['sku'] = $tailSku;
					$tail['url'] = $tailUrl;


			    } 
			    catch (\Magento\Framework\Exception\NoSuchEntityException $e)
			    {
			        $tailSku = '';
			    }
			}
			$regularUrl = '';
			if($regularSku != '')
			{
			    $regularproductdata = $this->productRepository->get($regularSku);
			    $regularId = $regularproductdata->getId();
			    $regularSku = $regularproductdata->getSku();
			    $regularUrl = $regularproductdata->getProductUrl();
				
				$regula['id'] = $regularId;
				$regula['sku'] = $regularSku;
				$regula['url'] = $regularUrl;
			}


			$topactionbuttondata['regula'] = (@$regula)?$regula : '';
			$topactionbuttondata['tail'] = (@$tail) ? $tail : '';
			$topactionbuttondata['petite'] = (@$petite) ? $petite : '';

	        $featureId = $this->attributeRepository->get(Product::ENTITY, 'feature');
	        $featurelable = $product->getResource()->getAttribute($featureId->getAttributeId())->getFrontend()->getLabel($product);


	        $fabriccontentId = $this->attributeRepository->get(Product::ENTITY, 'fabriccontent');
	        $fabriccontentlable = $product->getResource()->getAttribute($fabriccontentId->getAttributeId())->getFrontend()->getLabel($product);

	         $descriptionId = $this->attributeRepository->get(Product::ENTITY, 'description');
	        $descriptionlable = $product->getResource()->getAttribute($descriptionId->getAttributeId())->getFrontend()->getLabel($product);

	        $bulletsdetailsId = $this->attributeRepository->get(Product::ENTITY, 'bulletsdetails');
	        $bulletsdetailslable = $product->getResource()->getAttribute($bulletsdetailsId->getAttributeId())->getFrontend()->getLabel($product);

	        $ufabriccareurlId = $this->attributeRepository->get(Product::ENTITY, 'ufabriccareurl');
	        $ufabriccareurllable = $product->getResource()->getAttribute($ufabriccareurlId->getAttributeId())->getFrontend()->getLabel($product);

	        $product_label = [];
	           
	       	$product_label['profeature'] = (@$featurelable) ? $featurelable : '' ;
			$product_label['prodescription'] = (@$descriptionlable) ? $descriptionlable : '' ;
			$product_label['prodetails'] = (@$bulletsdetailslable) ? $bulletsdetailslable : '';
			$product_label['profabriccontent'] = (@$fabriccontentlable) ? $fabriccontentlable : '';
			$product_label['profabricimageurl'] = (@$ufabriccareurllable) ? $ufabriccareurllable : '';

			$featuredata = $this->feature->getCollection()->addFieldToFilter('option_id', ['in' => $product->getData('feature')])->setOrder('sort_order','ASC')->load();
			$featurearray = $featuredata->getData();

			$description = $product->getData('description');

			$temp_details = $product->getData('bulletsdetails');
			$listValue = explode("<br>", $temp_details);
			$temp_detailed_rendered = "";
			
			if(count($listValue) > 1){
					foreach ($listValue as $value):
						$temp_detailed_rendered .= "<li>".$value."</li>";
					endforeach;
				$details = $temp_detailed_rendered;
			}

			$fabriccontent = $product->getData('fabriccontent');
			$fabricimageurl = $product->getData("ufabriccareurl");

			$config['profeature'] = (@$featurearray) ? $featurearray : '';
			$config['prodescription'] = (@$description) ? $description : '';
			$config['prodetails'] = (@$details) ? $details : '';
			$config['profabriccontent'] = (@$fabriccontent) ? $fabriccontent : '';
			$config['profabricimageurl'] = (@$fabricimageurl) ? $fabricimageurl : '';
			$config['lable'] = (@$product_label) ? $product_label : '';
			$config['topactionbuttondata'] = (@$topactionbuttondata) ? $topactionbuttondata : '';
			$data = json_encode($config,true);

			$this->cache->save($data, "oneconfigrabledata".$productid); 
			return $resultJson->setData($config);

        }
        else
        {
        	return $resultJson->setData(json_decode($cacheconfigdata,true));        	
        }
    }
}


?>