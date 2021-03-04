<?php
namespace WeltPixel\Quickview\Block;


use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Sttl\Feature\Model\Feature;

class Quickviewpopup extends \Magento\Framework\View\Element\Template {

	protected $feature;

	public function __construct(
		Context $context,
		EncoderInterface $jsonEncoder,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $procollection,
        Feature $feature,
		array $data = []
	) {
		parent::__construct($context);
		$this->jsonEncoder = $jsonEncoder;
         $this->procollection = $procollection;
         $this->swatchHelper = $swatchHelper;
		$this->feature = $feature;
	}

	public function getfeatureprocollection(){
		return $this->procollection->addAttributeToSelect('*')
					->setPageSize(6)
					->addAttributeToFilter('featured_product', array('eq' => 1))
					->setOrder('updated_at', 'DESC')
					->load();
	}

	public function getMagentoProduct() {
		$Featuredpros = $this->getfeatureprocollection();
		$simpleproductdata = [];
		$configdata = [];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		foreach ($Featuredpros as $product) {
			$product_id = $product->getId();
			$configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
			$configdata[] = [
						 'id' => $configProduct->getSku(),
	                     'name' => $configProduct->getName(),
	                     'collection' => $configProduct->getResource()->getAttribute("collecttion")->getFrontend()->getValue($configProduct),	
	                     'producturl' => $configProduct->getProductUrl(),
							];						
			$_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);
			foreach ($_children as $child){
			    $cproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($child->getID());
			    $images = $cproduct->getMediaGalleryImages();
			 	if ($images instanceof \Magento\Framework\Data\Collection) {
			 		$imagesurls =array();
			 		$i = 0;
	                foreach ($images as $image) {
	                    $imagesurls[$i] = $image->getUrl();
	                    $i++;
	                }
	            }
	            $featuredata = $this->feature->getCollection()->addFieldToFilter('option_id', ['in' => $child->getFeature()])->setOrder('sort_order','ASC')->load();
	            $hashcodeData = $this->swatchHelper->getSwatchesByOptionsId([$child->getColor(),$child->getHeather_colors(),$child->getSeasonalcolors()]);
	            $simpleproductdata[] = [
	                        'id' => $child->getSku(),
	                        'feature' => $featuredata->getData(),
	                        'parent_sku' => $cproduct->getResource()->getAttribute("parent_style")->getFrontend()->getValue($cproduct),
	                        'name' => $child->getName(),
	                        'collection' => $cproduct->getResource()->getAttribute("collecttion")->getFrontend()->getValue($cproduct),
	                        'image' => $imagesurls,
	                        'color' => $cproduct->getResource()->getAttribute("color")->getFrontend()->getValue($cproduct),
	                        'colorurl' => $hashcodeData[$child->getColor()]['value'],
	                        'heathercolor' => $cproduct->getResource()->getAttribute("heather_colors")->getFrontend()->getValue($cproduct),
	                        'heathercolorurl' => $hashcodeData[$child->getHeather_colors()]['value'],
	                        'seasonalcolor' => $cproduct->getResource()->getAttribute("seasonalcolors")->getFrontend()->getValue($cproduct),
	                        'seasonalcolorurl' => $hashcodeData[$child->getSeasonalcolors()]['value'],
	                        'size' => $cproduct->getResource()->getAttribute("size")->getFrontend()->getValue($cproduct),
	                        'size_chat' => $cproduct->getResource()->getAttribute("sizecharturl")->getFrontend()->getValue($cproduct),
	                     	'fabric_chat' => $cproduct->getResource()->getAttribute("ufabriccareurl")->getFrontend()->getValue($cproduct),
	                    ];
			}
		}
		$responces[] = ['configurationproduct' => $configdata, 'simapleproduct' => $simpleproductdata];
		return $this->jsonEncoder->encode($responces);

	}


}




