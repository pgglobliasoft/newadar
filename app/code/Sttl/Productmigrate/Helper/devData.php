<?php
namespace Sttl\Productmigrate\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use \Magento\Eav\Model\Entity\Attribute;
use \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use \Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\Framework\Filesystem\Io\File;

class Data extends AbstractHelper
{
    const ATTRIBUTE_SET_ID = '4';
    const STATUS = '1';
    const TAXCLASSID = '0';
    const USE_CONFIG_MANAGE_STOCK = '0';
    const MANAGE_STOCK = '1';
    const IS_IN_STOCK = '1';
    const VISIBILITY = '1';
    const CATALOG_PRODUCT = 'catalog_product';
    const LOG_FILE_NAME = 'productimport.log';

    protected $getColorAttibuteOption;
    protected $configrebleAttribute;
    protected $directoryList;
    protected $file;
    protected $_productCollectionFactory;
    protected $swatchHelper;
    public function __construct(
        Context $context,
        Attribute $attribute,
        AttributeOptionManagementInterface $AttributeOptionManagementInterface,
        AttributeOptionInterfaceFactory $AttributeOptionInterfaceFactory,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $configrebleAttribute,
        DirectoryList $directoryList,
        \Psr\Log\LoggerInterface $logger,
        File $file,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Swatches\Helper\Media $swatchHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Sttl\Productmigrate\Model\Product\Gallery\Video\Processor $videoGalleryProcessor

    )
    {
        $this->directoryList = $directoryList;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->product = $product;
        $this->attribute = $attribute;
        $this->AttributeOptionInterfaceFactory = $AttributeOptionInterfaceFactory;
        $this->AttributeOptionManagementInterface = $AttributeOptionManagementInterface;
        $this->eavConfig = $eavConfig;
        $this->configrebleAttribute = $configrebleAttribute;
        $this->file = $file;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
        $this->swatchHelper = $swatchHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->directoryList->getRoot().'/var/log/'.self::LOG_FILE_NAME));
        $this->videoGalleryProcessor = $videoGalleryProcessor;
    }
    public function getcategoryId($productitem)
    {
        $categoryId = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactorydata = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        if($productitem['Collection'] != '')
        {
            $collectiondata = $this->_categoryFactory->create()->addAttributeToFilter('name',trim($productitem['Collection']));
                     if($collectiondata->getSize()) {
                       foreach($collectiondata as $categoryData)
                       {
                            $category = $categoryFactorydata->create()->load($categoryData->getId());
                            foreach($category->getChildrenCategories() as $childcategory)
                             {
                                if(strtolower($childcategory->getName()) == strtolower(trim($productitem['Items_Catageory'])))
                                {
                                   $categoryId[0]= $childcategory->getEntityId();
                                }
                             }
                             $categoryId[1] = $categoryData->getId();
                       }
                    }
        }
        if($productitem['Gender'] != '')
        {
            $collectioninfo = $this->_categoryFactory->create()->addAttributeToFilter('name',trim($productitem['Gender']));
                     if ($collectioninfo->getSize()) {
                       foreach($collectioninfo as $categoryData)
                       {
                            $category = $categoryFactorydata->create()->load($categoryData->getId());
                            foreach($category->getChildrenCategories() as $childcategory)
                             {
                                if(strtolower($childcategory->getName()) == strtolower(trim($productitem['Items_Catageory'])))
                                {
                                   $categoryId[2]= $childcategory->getEntityId();
                                }
                             }
                              $categoryId[3] = $categoryData->getId();
                       }
                    }
        }
        return $categoryId;
    }

    public function ImportSimpleProduct($productArray,$adar_magento_obj, $fp_log)
	{
       $this->logger->info('=============== Simple Product Import =====================');
	   $UpdateStatus = '';
	   $i = 0;
	   $ParentChange = '';
	   foreach($productArray as $productitem)
       {
       	   if(trim($productitem['Gender']) != '')
			{

				if(strtolower(trim($productitem['Gender'])) == 'w')
				{
					$productitem['Gender'] = 'Womens';
				}
				elseif(strtolower(trim($productitem['Gender'])) == 'm')
				{
					$productitem['Gender'] = 'Mens';
				}
				elseif(strtolower(trim($productitem['Gender'])) == 'u')
				{
					$productitem['Gender'] = 'Unisex';
				}
			}
			if(trim($productitem['Parent_Style']) != '')
			{
				if($ParentChange == '' || $ParentChange != trim($productitem['Parent_Style']))
                {
                    $ParentChange = trim($productitem['Parent_Style']);
                    $i = 0;
                } 
				if(isset($productitem['Short_Description']) && trim($productitem['Short_Description']) !='')
				{

				$category_id = array();
				$category_id = $this->getcategoryId($productitem);
				$superAttirbuteids=[];

				/* Solid Colors attrribute 93*/
				$getColorAttibuteOption = $this->getAttibuteValueData('color');
				$superAttirbuteids [] = $getColorAttibuteOption['attributeData']['attribute_id'];

				/**seasonalcolors attribute 152*/
				$getSeasonalColorsAttibuteOption = $this->getAttibuteValueData('seasonalcolors');
				$superAttirbuteids[]= $getSeasonalColorsAttibuteOption['attributeData']['attribute_id'];

				/* heather_colors attribute 227*/
				$getHealtherColorsAttibuteOption = $this->getAttibuteValueData('heather_colors');
				$superAttirbuteids[]= $getHealtherColorsAttibuteOption['attributeData']['attribute_id'];


				$getSizeAttibuteOption = $this->getAttibuteValueData('size');
				$superAttirbuteids[]= $getSizeAttibuteOption['attributeData']['attribute_id'];

				$getCollecttionAttibuteOption = $this->getAttibuteValueData('collecttion');
				$getFeatureAttibuteOption = $this->getAttibuteValueData('feature');
				$getParenColorAttibuteOption = $this->getAttibuteValueData('maincolor');
				$getGenderAttibuteOption = $this->getAttibuteValueData('gender');

				$getCategoryAttibuteOption = $this->getAttibuteValueData('filter_category');
				$getFitAttibuteOption = $this->getAttibuteValueData('fit');
				$getBrandAttibuteOption = $this->getAttibuteValueData('sttl_brand');
				if(isset($productitem['Bullets']) && $productitem['Bullets'] != '')
				{
					$bulltesArray =explode("*",ltrim($productitem['Bullets'],"*"));
					$bulletString = implode('<br>', $bulltesArray);
					$productitem['Bullets'] = str_replace('�', "'", $bulletString);

				}	
			


			
				$getColorValue = $this->getColorAttibutevalue($getColorAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log,$productitem['IsImgUpdate']);

				$getSeasonalColorsValue = $this->getColorAttibutevalue($getSeasonalColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log,$productitem['IsImgUpdate']); //Seasonal_Colors

				// Heather Color
				$getHealtherColorsValue = $this->getColorAttibutevalue($getHealtherColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log,$productitem['IsImgUpdate']); 

		// 	echo "<pre>";
		// print_r($getHealtherColorsAttibuteOption); die;

				$getSizeValue = $this->getAttibutevalue($getSizeAttibuteOption,trim($productitem['Size']));
				if(isset($productitem['Collection']) && $productitem['Collection'] !='')
				{
					if($productitem['Collection'] == 'Universal' || strtolower($productitem['Collection']) == strtolower('Universal by Adar'))
					{
						$productitem['Collection'] = 'Universal by Adar'; //Universal by adar
					} 
					$getCollectionValue = $this->getAttibutevalue($getCollecttionAttibuteOption,trim($productitem['Collection']));
				}
				$FeaturesValueArray[] = '';
				if($productitem['Fabric_Features'] != '')
				{
					$FeaturesArray = explode(",",ltrim($productitem['Fabric_Features'],","));
					$FeaturesValueArray = array();
					foreach($FeaturesArray as $FeaturesLable)
					{
						$getFeatureValue = $this->getAttibutevalue($getFeatureAttibuteOption,trim($FeaturesLable));
						$FeaturesValueArray[] = $getFeatureValue['value'];
					}
				}
				if($productitem['Parent_Color'] !='')
				{
					$getParenColorValue = $this->getAttibutevalue($getParenColorAttibuteOption,trim($productitem['Parent_Color']));
				}

				$getGenderValue = $this->getAttibutevalue($getGenderAttibuteOption,trim($productitem['Gender']));
				$getCatageoryValue = $this->getAttibutevalue($getCategoryAttibuteOption,trim($productitem['Items_Catageory']));
				if(isset($productitem['Brand']) && $productitem['Brand'] != '')
				{
					$getBrandValue = $this->getAttibutevalue($getBrandAttibuteOption,trim($productitem['Brand']));
				}
				if(isset($productitem['Fit']) && trim($productitem['Fit']) != '')
				{
					$getFitValue = $this->getAttibutevalue($getFitAttibuteOption,trim($productitem['Fit']));
				}

				$discription = str_replace('�', "'", $productitem['Long_Description']);
				$productitem['Qty_Available'] = trim(str_replace("+", "", $productitem['Qty_Available']));
				$allImagePath = array();
				if(isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
				{
					if($productitem['Web_Image1'] !='')
					{
						$allImagePath[]= $this->imgurlupdate($productitem['Web_Image1']);
					}
					else
					{
						$allImagePath[]=  $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
					}
					if($productitem['Web_Image2'] !='')
					{
						$allImagePath[]= $this->imgurlupdate($productitem['Web_Image2']);
					}
					if($productitem['Web_Image3'] !='')
					{
						$allImagePath[]= $this->imgurlupdate($productitem['Web_Image3']);
					}
					if($productitem['Web_Image4'] !='')
					{
						$allImagePath[]= $this->imgurlupdate($productitem['Web_Image4']);
					}
					if($productitem['Web_Image5'] !='')
					{
						$allImagePath[]= $this->imgurlupdate($productitem['Web_Image5']);
					}
					if($productitem['Web_Image6'] !='')
					{
						$allImagePath[]= $this->imgurlupdate($productitem['Web_Image6']);
					}
				}
				$getProductId = $this->product->getIdBySku($productitem['SKU_Number']);
				$update_prd = false;
				if($getProductId && $getProductId != '')
				{
					$product = $this->product->load($getProductId);
					$update_prd = true;
				}
				else
				{
					$product = $this->productFactory->create();
					$product->setTypeId('simple');
					$product->setSku($productitem['SKU_Number']);
					$product->setAttributeSetId(self::ATTRIBUTE_SET_ID);
					$product->setVisibility(self::VISIBILITY);
					if( strtolower(trim($productitem['Color_Type'])) == 'seasonal')
					{
						$product->setSeasonalcolors($getSeasonalColorsValue['value']);
					}
					// heather color condition
					elseif( strtolower(trim($productitem['Color_Type'])) == 'heather')
					{
						$product->setHeatherColors($getHealtherColorsValue['value']);
					}
					else
					{
						$product->setSeasonalcolors(231);
						$product->setHeatherColors(26291);
					}
					$product->setColor($getColorValue['value']);
					$product->setSize($getSizeValue['value']);
					$product->setCreatedAt(strtotime('now'));
				}
				$product->setWebsiteIds(array(1));
				$product->setStoreId(0);

				if( strtolower(trim($productitem['Color_Type'])) == 'seasonal')
				{
					$product->setSeasonalcolors($getSeasonalColorsValue['value']);
				}
				// heather color condition
				elseif( strtolower(trim($productitem['Color_Type'])) == 'heather')
				{
					$product->setHeatherColors($getHealtherColorsValue['value']);
				}
				else
				{
					$product->setSeasonalcolors(231);
					$product->setHeatherColors(26291);
				}
				$product->setName(str_replace('�', "'", $productitem['Short_Description'].' '.$productitem['Color_Name'].' '.$productitem['Size']));
				$product->setShortDescription(str_replace('�', "'", $productitem['Short_Description']));
				$product->setDescription($discription);
				$product->setBulletsdetails(str_replace('�', "'", $productitem['Bullets']));
				$product->setFabriccontent($productitem['Fabric_Content']);

				$product->setCategoryIds($category_id);
				$product->setFilterCategory($getCatageoryValue['value']);
				if(isset($productitem['ISActive']) && $productitem['ISActive'] ==  'N')
				{
				  $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
				}
				if(isset($productitem['ISActive']) && $productitem['ISActive'] == 'Y')
				{
					$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
				}
				$product->setTaxClassId(self::TAXCLASSID);
				$product->setWeight($productitem['Weight']);
				$product->setParentStyle(trim($productitem['Parent_Style']));

				$product->setGender($getGenderValue['value']);
				$product->setFit($getFitValue['value']);
				if(isset($getCollectionValue['value']) && trim($getCollectionValue['value']) != '')
				{
					$product->setcollecttion(array($getCollectionValue['value']));
				}
				if(isset($getBrandValue['value']) && $getBrandValue['value'] !='')
				{
					$product->setSttlBrand($getBrandValue['value']);
				}
               	$product->setFeature($FeaturesValueArray);
               	if(isset($getParenColorValue['value']) && trim($getParenColorValue['value']) != '')
               	{
               		$product->setMaincolor(array($getParenColorValue['value']));
               	}
				$product->setPrice(round($productitem['Items_Price_list']));
				if($productitem['Qty_Available'] <= 0) {
					$productitem['Qty_Available'] = 1;
				}
				$product->setStockData( array(
							'use_config_manage_stock' => 1,
							'manage_stock' => 1,
							'is_in_stock' => 1,
							'qty' => $productitem['Qty_Available']
							)
				);
				$product->setSizecharturl($this->imgurlupdate($productitem['Website_Size_Chart']));
				$product->setModelWears($productitem['Model_Wears']);
				if(!empty($allImagePath) && isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
				{
					if($getProductId)
					{
						$this->RemoveImagesProduct($getProductId);
					}
					$this->imapgeupload($product,$allImagePath, $fp_log);
				}
				try
				{
					$product->save();
					$LastupdateQuery = '';
					if($productitem['Default_Color'] == 'N')
					{
						$date = 'Sucess - Updated on '.date("m/d/Y H:i:s");
						$LastupdateQuery = 'update "dbo".ItemAIO set MagentoField = \'1\', ISImgUpdate = \'N\', LastUpdated = \''.$date.'\'  where id ='.$productitem['ID'];
						$data = $adar_magento_obj->execute($LastupdateQuery);
					}
					echo PHP_EOL;
					echo $productitem['SKU_Number'];
					echo PHP_EOL;
					echo "=={";
					print_r($product->getId());
					echo "}==";
					echo PHP_EOL;
					fwrite($fp_log, $productitem['SKU_Number'].' Updated product sucess.'."\n");
				}
				catch (Exception $e)
				{
					fwrite($fp_log, $e->getMessage()."\n");
				}
					$i++;
				}
				else{
					echo $productitem['SKU_Number']." Short Description Null not create a products.";
					echo PHP_EOL;
					fwrite($fp_log, $productitem['SKU_Number'].' Short Description Null not create a products.'."\n");
					$date = 'Sucess - Updated on '.date("m/d/Y H:i:s");
					$LastupdateQuery = 'update "dbo".ItemAIO set MagentoField = \'1\', ISImgUpdate = \'N\', LastUpdated = \''.$date.'\'  where id ='.$productitem['ID'];
						$data = $adar_magento_obj->execute($LastupdateQuery);
				}
			}
	   }
	   //$UpdateQuery = $adar_magento_obj->execute($UpdateStatus);
	}

    public function ImportConfigProduct($productitem,$adar_magento_obj, $fp_log)
	{
		$parent_style_array = array();
		$productitem['IsImgUpdate'] = 'Y';
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productitem['Short_Description'] .= ' new collecttion';
		if(isset($productitem['Short_Description']) && trim($productitem['Short_Description']) !='')
		{

		}
		else
		{
			echo $productitem['Parent_Style']." Short Description Null not create a products.";
			echo PHP_EOL;
			fwrite($fp_log, $productitem['Parent_Style'].' Short Description Null not create a products.'."\n");
		}



		if(trim($productitem['Gender']) != '')
		{
			if(strtolower(trim($productitem['Gender'])) == 'w')
			{
				$productitem['Gender'] = 'Womens';
			}
			elseif(strtolower(trim($productitem['Gender'])) == 'm')
			{
				$productitem['Gender'] = 'Mens';
			}
			elseif(strtolower(trim($productitem['Gender'])) == 'u')
			{
				$productitem['Gender'] = 'Unisex';
			}
		}
		$category_id = array();
		$category_id = $this->getcategoryId($productitem);
		$superAttirbuteids=[];

		/*Soli color attribite get 93*/
		$getColorAttibuteOption = $this->getAttibuteValueData('color');
		$superAttirbuteids [] = $getColorAttibuteOption['attributeData']['attribute_id'];

		/* seasonalcolors attrbiute get data 152*/
		$getSeasonalColorsAttibuteOption = $this->getAttibuteValueData('seasonalcolors');
		$superAttirbuteids[]= $getSeasonalColorsAttibuteOption['attributeData']['attribute_id'];

		/* heather_colors attrinute 227*/
		$getHealtherColorsAttibuteOption = $this->getAttibuteValueData('heather_colors');
		$superAttirbuteids[]= $getHealtherColorsAttibuteOption['attributeData']['attribute_id'];


		$getSizeAttibuteOption = $this->getAttibuteValueData('size');
		$superAttirbuteids[]= $getSizeAttibuteOption['attributeData']['attribute_id'];
		$getCollecttionAttibuteOption = $this->getAttibuteValueData('collecttion');
		$getFeatureAttibuteOption = $this->getAttibuteValueData('feature');
		$getParenColorAttibuteOption = $this->getAttibuteValueData('maincolor');
		$getGenderAttibuteOption = $this->getAttibuteValueData('gender');

		$getCategoryAttibuteOption = $this->getAttibuteValueData('filter_category');
		$getFitAttibuteOption = $this->getAttibuteValueData('fit');
		$getBrandAttibuteOption = $this->getAttibuteValueData('sttl_brand');

		$bulltesArray =explode("*",ltrim($productitem['Bullets'],"*"));
		$bulletString = implode('<br>', $bulltesArray);
		$productitem['Bullets'] = str_replace('�', "'", $bulletString);


		$getColorValue = $this->getColorAttibutevalue($getColorAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log,$productitem['IsImgUpdate']);
		$getSeasonalColorsValue = $this->getColorAttibutevalue($getSeasonalColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log,$productitem['IsImgUpdate']); //Seasonal_Colors

		// Heather color
		$getHealtherColorsValue = $this->getColorAttibutevalue($getHealtherColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log,$productitem['IsImgUpdate']); 


		$getSizeValue = $this->getAttibutevalue($getSizeAttibuteOption,trim($productitem['Size']));
		if(isset($productitem['Collection']) && trim($productitem['Collection'])!='')
		{
			if($productitem['Collection'] == 'Universal')
			{
				$productitem['Collection'] = 'Universal by adar';
			}
			$getCollectionValue = $this->getAttibutevalue($getCollecttionAttibuteOption,trim($productitem['Collection']));
		}

		$FeaturesValueArray[] = '';
		if($productitem['Fabric_Features'] != '')
		{
			$FeaturesArray = explode(",",ltrim($productitem['Fabric_Features'],","));
			$FeaturesValueArray = array();
			foreach($FeaturesArray as $FeaturesLable)
			{
				$getFeatureValue = $this->getAttibutevalue($getFeatureAttibuteOption,trim($FeaturesLable));
				$FeaturesValueArray[] = $getFeatureValue['value'];
			}
		}
		if(isset($productitem['Parent_Color']) && trim($productitem['Parent_Color']) !='')
		{
			$getParenColorValue = $this->getAttibutevalue($getParenColorAttibuteOption,trim($productitem['Parent_Color']));
		}

		$getGenderValue = $this->getAttibutevalue($getGenderAttibuteOption,trim($productitem['Gender']));
		$getCatageoryValue = $this->getAttibutevalue($getCategoryAttibuteOption,trim($productitem['Items_Catageory']));

		if(isset($productitem['Brand']) && $productitem['Brand'] != '')
		{

			$getBrandValue = $this->getAttibutevalue($getBrandAttibuteOption,trim($productitem['Brand']));
		}
		$getFitValue = $this->getAttibutevalue($getFitAttibuteOption,trim($productitem['Fit']));
		$discription = str_replace('�', "'", $productitem['Long_Description']);
		$productitem['Qty_Available'] = trim(str_replace("+", "", $productitem['Qty_Available']));
		$allImagePath = array();

		if(isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
		{
			if($productitem['Web_Image1'] !='')
			{
				$allImagePath[]= $this->imgurlupdate($productitem['Web_Image1']);
			}
			else
			{
				$allImagePath[]=  $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
			}
			if($productitem['Web_Image2'] !='')
			{
				$allImagePath[]= $this->imgurlupdate($productitem['Web_Image2']);
			}
			if($productitem['Web_Image3'] !='')
			{
			$allImagePath[]= $this->imgurlupdate($productitem['Web_Image3']);
			}
			if($productitem['Web_Image4'] !='')
			{
			$allImagePath[]= $this->imgurlupdate($productitem['Web_Image4']);
			}
			if($productitem['Video'] !='' && isset($productitem['Video']))
			{
				$allImagePath[]['videos']= $this->imgurlupdate($productitem['Video']);
			}
			if($productitem['Web_Image5'] !='')
			{
			$allImagePath[]= $this->imgurlupdate($productitem['Web_Image5']);
			}
			if($productitem['Web_Image6'] !='')
			{
			$allImagePath[]= $this->imgurlupdate($productitem['Web_Image6']);
			}
		}
		$getConfigProductId = $this->product->getIdBySku($productitem['Parent_Style']);

		if($getConfigProductId && $getConfigProductId != '')
		{
			$Configrebleproduct = '';
			$Configrebleproduct = $this->product->load($getConfigProductId);

			if(trim($Configrebleproduct->getTypeId()) == 'simple')
			{
				
				$objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
				$deleteProductRepository = $objectManager->create('\Magento\Catalog\Model\ProductRepository');
				
				$deleteProductRepository->delete($Configrebleproduct);
				
				$Configrebleproduct = $this->productFactory->create();
				$Configrebleproduct->setTypeId('configurable');
				$Configrebleproduct->setSku($productitem['Parent_Style']);
				$Configrebleproduct->setAttributeSetId(self::ATTRIBUTE_SET_ID);
				$Configrebleproduct->setVisibility(4);
				$Configrebleproduct->setCreatedAt(strtotime('now'));

			}else{
				$old_cat_ids = $Configrebleproduct->getCategoryIds();
				if(!empty($old_cat_ids))
				{
					$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
					$connection = $resource->getConnection();
					$tableName = $resource->getTableName('catalog_category_product');
					$sql_delete = "Delete FROM " . $tableName." Where product_id = '".$getConfigProductId."'";
					$connection->query($sql_delete);

					$tableName = $resource->getTableName('catalog_category_product_index_store1');
					$sql_delete = "Delete FROM " . $tableName." Where product_id = '".$getConfigProductId."'";
					$connection->query($sql_delete);
				}
				$Configrebleproduct = $this->product->load($getConfigProductId);
			}
			
		}
		else
		{
			$Configrebleproduct = $this->productFactory->create();
			$Configrebleproduct->setTypeId('configurable');
			$Configrebleproduct->setSku($productitem['Parent_Style']);
			$Configrebleproduct->setAttributeSetId(self::ATTRIBUTE_SET_ID);
			$Configrebleproduct->setVisibility(4);
			$Configrebleproduct->setCreatedAt(strtotime('now'));
		}
		$Configrebleproduct->setPetite('');
		$Configrebleproduct->setTail('');
		$Configrebleproduct->setRegular('');
		$check  = '';
		$mainstyle  = '';
		$check = substr($productitem['Parent_Style'], -1);
		$mainstyle = substr($productitem['Parent_Style'], 0,-1);
		if(strtoupper($check) == strtoupper(trim('P')) && strtoupper($check) == strtoupper(trim($productitem['SizeVar'])))
		{
		
			$Configrebleproduct->setPetite($productitem['Parent_Style']);
			$Configrebleproduct->setTail($mainstyle.'T');
			$Configrebleproduct->setRegular($mainstyle);
		}
		if(strtoupper($check) == strtoupper(trim('T')) && strtoupper($check) == strtoupper(trim($productitem['SizeVar'])))
		{
		
			$Configrebleproduct->setTail($productitem['Parent_Style']);
			$Configrebleproduct->setPetite($mainstyle.'P');
			$Configrebleproduct->setRegular($mainstyle);
		}
		$Configrebleproduct->setWebsiteIds(array(1));
		$Configrebleproduct->setStoreId(0);

		$Configrebleproduct->setCategoryIds($category_id);
		$Configrebleproduct->setName(str_replace('�', "'", $productitem['Short_Description']));
		$Configrebleproduct->setShortDescription(str_replace('�', "'", $productitem['Short_Description']));

		$Configrebleproduct->setPrice(0);
		$Configrebleproduct->setDescription($discription);

		if(isset($productitem['Bullets']) && $productitem['Bullets'] != '')
		{
			$Configrebleproduct->setBulletsdetails($productitem['Bullets']);
		}
		if(isset($productitem['Fabric_Content']) && $productitem['Fabric_Content'] != '')
		{
			$Configrebleproduct->setFabriccontent($productitem['Fabric_Content']);
		}
		if(isset($productitem['ISActive']) && $productitem['ISActive'] ==  'N')
		{
		  $Configrebleproduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
		}
		if(isset($productitem['ISActive']) && $productitem['ISActive'] == 'Y')
		{
			$Configrebleproduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		}
		$Configrebleproduct->setTaxClassId(self::TAXCLASSID);
		$Configrebleproduct->setWeight($productitem['Weight']);
		if(isset($getGenderValue['value']) && $getGenderValue['value'] !='')
		{
			$Configrebleproduct->setGender($getGenderValue['value']);
		}
		if(isset($getFitValue['value']) && $getFitValue['value'] != '')
		{
		$Configrebleproduct->setFit($getFitValue['value']);
		}
		if(isset($getCatageoryValue['value']) && $getCatageoryValue['value'] != '')
		{
			$Configrebleproduct->setFilterCategory($getCatageoryValue['value']);
		}
		if(isset($getBrandValue['value']) && $getBrandValue['value'] !='')
		{
			$Configrebleproduct->setSttlBrand($getBrandValue['value']);
		}
		if(isset($getCollectionValue['value']) && trim($getCollectionValue['value']) !='')
		{
			$Configrebleproduct->setCollecttion(array($getCollectionValue['value']));
		}

		$Configrebleproduct->setFeature($FeaturesValueArray);
		if(!empty($getParenColorValue['value']) && trim($getParenColorValue['value']) !='')
		{
			$Configrebleproduct->setMaincolor(array($getParenColorValue['value']));
		}
		$Configrebleproduct->setStockData( array(
				'use_config_manage_stock' => 1,
				'manage_stock' => self::MANAGE_STOCK,
				'is_in_stock' => self::IS_IN_STOCK
				)
			);
		if($productitem['Website_Size_Chart'] != '')
		{
		   $Configrebleproduct->setSizecharturl($this->imgurlupdate($productitem['Website_Size_Chart']));
		}else{
			fwrite($fp_log, 'Sizechart image not exist for '. $productitem['Parent_Style'] . "\n");
			$this->logger->info('This parent Style not exsting sizechart image'. $productitem['Parent_Style']);
		}
		if($productitem['Model_Wears'] != '')
		{
			$Configrebleproduct->setModelWears($productitem['Model_Wears']);
		}
		
		if(!empty($allImagePath) && isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
		{
			if($getConfigProductId)
			{
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('catalog_product_entity_media_gallery');
                $imageProcessor = $objectManager->create('\Magento\Catalog\Model\Product\Gallery\Processor');
                $images = $Configrebleproduct->getMediaGallery();
                foreach($images['images'] as $key => $child){
                    if($child['file'])
                    {
                        $filename = $child['file'];
                        $sql = "Delete FROM " . $tableName." Where value = '".$filename."'";
                        $connection->query($sql);
                        $values_id = $child['value_id'];
                        $sqldelte = "Delete FROM au_catalog_product_entity_media_gallery_value Where value_id = '".$values_id."'";
                        $connection->query($sqldelte);

                    }
                }
			}
			$this->imapgeupload($Configrebleproduct,$allImagePath, $fp_log);
		}

		try
		{

			$prd_link_data = $this->removePrdLinks($getConfigProductId, 'delete');
			$Configrebleproduct->save();			
			$this->simpleproductsettoconfigreble($Configrebleproduct->getId(),$productitem['Parent_Style'], $superAttirbuteids, $fp_log);
			$prd_link_data = $this->removePrdLinks($getConfigProductId, 'insert', $prd_link_data);
			if(isset($productitem['RecomProduct']) && trim($productitem['RecomProduct']) != '')
			{
					$linkDataAll = [];
					$Configrebleproducttemp = $this->product->load($Configrebleproduct->getId());
					$skuLinks = explode(",",$productitem['RecomProduct']);
					if(count($skuLinks) > 0 )
					{
						
						foreach($skuLinks as $n => $skuLink)
						{
							$skuLink = trim($skuLink);
							$getProductId = $this->product->getIdBySku($skuLink)
							;
							// print_r($getProductId);die;
							if($getProductId != '')
							{
								try {
								$linkedProduct = $this->product->get(trim($skuLink));
								} catch (Exception $e){
									fwrite($fp_log, trim($skuLink).' Not Exsting.'."\n");
									$linkedProduct = false;
								}
								if($linkedProduct && trim($skuLink) != $Configrebleproduct->getSku())
								{
									$productLinks = $objectManager->create('Magento\Catalog\Api\Data\ProductLinkInterface');
									$linkData = $productLinks->setSku($Configrebleproduct->getSku())
										->setLinkedProductSku(trim($skuLink))
										->setLinkType('related')
										->setPosition($n + 1);
									$linkDataAll[] = $linkData;
								}
							}else{
								fwrite($fp_log, $productitem['Parent_Style'] .' Product is not available in website.'.$skuLink."\n");
							}
						}
					}
					if($linkDataAll) {
						$Configrebleproducttemp->setProductLinks($linkDataAll);
					}
				$Configrebleproducttemp->save();
				$this->productRepository->save($Configrebleproducttemp);
			}
			echo PHP_EOL;
			echo "={";
			print_r($Configrebleproduct->getId());
			echo "}=";
			echo PHP_EOL;

			fwrite($fp_log, $productitem['Parent_Style'].' update product sucess.'."\n");
			$this->logger->info('Add Configreble Product : Product Id=='.$Configrebleproduct->getId());

		}
		catch (Exception $e)
		{
			fwrite($fp_log, $e->getMessage()."\n");
			$this->logger->info($e->getMessage());
		}

	}
	public function removePrdLinks($getConfigProductId = 0, $flag = '', $prd_link_data = array())
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('catalog_product_link');

		if($flag == 'delete')
		{
			$sql_select = "Select * FROM " . $tableName." Where product_id = '".$getConfigProductId."'";
			$prd_link_data = $connection->fetchAll($sql_select);
			if(!empty($prd_link_data))
			{
				$sql_delete = "Delete FROM " . $tableName." Where product_id = '".$getConfigProductId."'";
				$connection->query($sql_delete);
				return $prd_link_data;
			}
			return array();
		}

		if($flag == 'insert' && !empty($prd_link_data))
		{
			foreach($prd_link_data as $prd_link)
			{
				$sql_insert = "Insert into " . $tableName." (product_id, linked_product_id,link_type_id) VALUES ('".$prd_link['product_id']."', '".$prd_link['linked_product_id']."', '".$prd_link['link_type_id']."') ";
				$connection->query($sql_insert);
			}
		}
	}

	public function simpleproductsettoconfigreble($id, $parent_style, $superAttirbuteids, $fp_log,$con_prd_update = false)
    {

    	try {
    			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    			$getConfigProductData = $objectManager->create('\Magento\Catalog\Model\Product')->load($id);
    			echo "<pre>";
    			print_r($superAttirbuteids);
				//$getConfigProductData = $this->product->load($id);
				$associatedProductIds = array();
				$prodCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
				$collection = $prodCollection->create()
					->addAttributeToSelect('entity_id')
					->addAttributeToFilter('parent_style',$parent_style)
					->addAttributeToFilter(array(array('attribute'=>'type_id','eq' => 'simple')))
					->load();
				foreach($collection as $simple_item)
				{
					$associatedProductIds[] = $simple_item->getId();
				}
				var_dump($associatedProductIds);
				$attributes = $superAttirbuteids;
				$getConfigProductData->setCanSaveConfigurableAttributes(true);
				$getConfigProductData->setCanSaveCustomOptions(true);
				$getConfigProductData->getTypeInstance()->setUsedProductAttributeIds($attributes, $getConfigProductData);
				$configurableAttributesData = $getConfigProductData->getTypeInstance()->getConfigurableAttributesAsArray($getConfigProductData);
				$getConfigProductData->setCanSaveConfigurableAttributes(true);
				$getConfigProductData->setConfigurableAttributesData($configurableAttributesData);
				$configurableProductsData = array();
				$getConfigProductData->setConfigurableProductsData($configurableProductsData);
				$getConfigProductData->setAssociatedProductIds($associatedProductIds);

				$getConfigProductData->save();
        }
        catch (Exception $e) {
            fwrite($fp_log, $e->getMessage()."\n");
            $this->logger->info('there is some issue in simple product set to configurable for ' . $productimportArray['configreble_id']);
        }
        return true;

    }
	public function videosUpload($product,$videsArray)
    {
        $videoData = [
            'video_id' => $product->getName(), //set your video id
            'video_title' => $product->getName(), //set your video title
            'video_description' => $product->getDescription(), //set your video description
            'thumbnail' => $videsArray['images'], //set your video thumbnail path.
            'video_provider' => "youtube",
            'video_metadata' => null,
            'video_url' =>  $videsArray['url'], //set your youtube video url
            'media_type' => \Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
        ];

        //download thumbnail image and save locally under pub/media
        $videoData['file'] = $videsArray['images'];

        // Add video to the product

        if ($product->hasGalleryAttribute()) {
            $this->videoGalleryProcessor->addVideo($product,$videoData,'',false,false);
        }
        return $product;

    }
    public function imapgeupload($product,$allImagePath,$fp_log)
    {
            $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            $i = 0;
            $filenameArray = array();
            foreach ($allImagePath as $key => $imagePath) {
                if(isset($imagePath['videos']) && $imagePath['videos'] != '')
                {
                	$productVideos =array();
            		$productVideos['url']= $imagePath['videos'];
					$productVideos['images']=  $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
		            $this->videosUpload($product,$productVideos);
                }
                else
                {

					$image_info = @getimagesize($imagePath);
					fwrite($fp_log, 'Image  URL:'.$imagePath."\n");
					// echo $imagePath.'<br>';
					if(!empty(trim($imagePath)) && !empty($image_info))
					{
						$imagePath = str_replace("https:/w", "https://w", $imagePath);
						
						fwrite($fp_log, 'Image  URL:'.$imagePath."\n");
						
					 if($this->is_image_exist($imagePath))
					 {
						
						$basenameArray = explode('.', basename($imagePath));
						$filename = $basenameArray[0];
						$newFileName = $tmpDir . baseName($imagePath);
						if(in_array($filename, $filenameArray))
						{
							$rename = $filename.'_'.$i;
							$newFileName = str_replace($filename,$rename,$newFileName);
							$filenameArray[] = $filename;
						}else{
							$filenameArray[] = $filename;
						}
						$result = $this->file->read($imagePath, $newFileName);
					 }else{
						$this->logger->info('SUK number == '.$product->getSku().' images not exist '. $imagePath);
						fwrite($fp_log, 'Image not exist for '.$product->getSku().' URL:'.$imagePath."\n");
						 $noimagePath = $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
						$basenameArray = explode('.', basename($imagePath));
						$filename = $basenameArray[0];
						$newFileName = $tmpDir . baseName($imagePath);
						if(in_array($filename, $filenameArray))
						{
							$rename = $filename.'_'.$i;
							$newFileName = str_replace($filename,$rename,$newFileName);
							$filenameArray[] = $filename;
						}else{
							$filenameArray[] = $filename;
						}
						if($filename == 'image-placeholder')
						{
							$rename = $filename.'_'.$i;
							$newFileName = str_replace($filename,$rename,$newFileName);
							$filenameArray[] = $filename;
						}
						$result = $this->file->read($noimagePath, $newFileName);
					  }

						if ($result)
						{

							if($i == 0)
							{
								try {
										$product->addImageToMediaGallery($newFileName,array('image', 'small_image', 'thumbnail'), false, false);
									}
									catch (Exception $e) {
										fwrite($fp_log, 'Image not updated '.$product->getSku().' -- '.$e->getMessage()."\n");
									}
							}else{
								try {
										$product->addImageToMediaGallery($newFileName,array(), false, false);
									}
									catch (Exception $e) {
										fwrite($fp_log, 'Image not updated '.$product->getSku().' -- '.$e->getMessage()."\n");
									}

							}
						}
					}
					else
					{
						fwrite($fp_log, 'Image url is not valid or not an image.'.$product->getSku().' URL:'.$imagePath."\n");
					}
            	}
            	       $i++;

            }
           return $product;
    }

    public function getAttibuteValueData($attributeName)
    {
           try{
                $attributeCode = $attributeName;
                $attributeData = $this->attribute->loadByCode(self::CATALOG_PRODUCT, $attributeCode);
                $attributeId = $attributeData->getId();
                $attributedata = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
                $attoptionArray = $attributedata->getSource()->getAllOptions();
                $result['AttributeOptionData'] = $attoptionArray;
                $result['attributeData'] = $attributeData->getData();
            }catch (\Exception $e) {
                $result['error'] = $e;
                $this->logger->info($e);
            }
        return $result;
    }
    public function getColorAttibutevalue($AttributeOptionData,$attibuteoptionname,$fileUrl = '',$fp_log,$isImgUpdate = ''){
      $key = array_search($attibuteoptionname, array_column($AttributeOptionData['AttributeOptionData'], 'label'));
      if($key != false)
      {
      	$fileUrl = $this->imgurlupdate($fileUrl);
		$lastoptionData = $AttributeOptionData['AttributeOptionData'][$key];
		 $attributedata = $AttributeOptionData['attributeData'];
          if($fileUrl != '' && $isImgUpdate == 'Y' && !empty($isImgUpdate))
          {
            if($this->is_image_exist($fileUrl))
            {
                 $lastoptionData['url'] = $fileUrl;
                 $fileUrlBasename = baseName($fileUrl);
            }else{
                $this->logger->info(' images not exist '. $fileUrl);
                fwrite($fp_log, 'Image not exist '.$fileUrl."\n");
                $noimagePath = $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder-color.jpg';
                $fileUrlBasename = baseName($fileUrl);
                $lastoptionData['url'] = $noimagePath;
                $fileUrl = $noimagePath;
            }
            $addoptionattributedata = $this->eavConfig->getAttribute('catalog_product', $attributedata['attribute_code']);
            $finalProductData = $lastoptionData;
            $lastoptionvalue = $lastoptionData['value'];
            $object_Manager = \Magento\Framework\App\ObjectManager::getInstance();
            $filesystem = $object_Manager->create('\Magento\Framework\Filesystem');
            $mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $productMediaConfig = $object_Manager->create('\Magento\Catalog\Model\Product\Media\Config');
            $tmpMediaPath = $productMediaConfig->getBaseTmpMediaPath();
            $fullTmpMediaPath = $mediaDirectory->getAbsolutePath($tmpMediaPath);
            $this->driverFile->createDirectory($fullTmpMediaPath);
            $this->driverFile->copy($fileUrl, $fullTmpMediaPath.'/'.$fileUrlBasename);
   //          $contextOptions = array(
			// 	"ssl" => array(
			// 		"verify_peer"      => false,
			// 		"verify_peer_name" => false,
			// 	),
			// );
   //          copy($fileUrl, $fullTmpMediaPath.'/'.$fileUrlBasename , stream_context_create( $contextOptions ) );

            $newFile =  $this->swatchHelper->moveImageFromTmp($fileUrlBasename);
            if (substr($newFile, 0, 1) == '.') {
                $newFile = substr($newFile, 1); // Fix generating swatch variations for files beginning with ".".
            }
            $this->swatchHelper->generateSwatchVariations($newFile);
            $data = array();
            $data['swatchvisual']['value'][$lastoptionvalue] = $newFile;
            $addoptionattributedata->addData($data)->save();
          }
          return $AttributeOptionData['AttributeOptionData'][$key];
      }else{
          $attributedata = $AttributeOptionData['attributeData'];
          $option = $this->AttributeOptionInterfaceFactory->create();
          $option->setLabel($attibuteoptionname);
          $data = $this->AttributeOptionManagementInterface->add(self::CATALOG_PRODUCT, $attributedata['attribute_code'], $option);
          $returnreturn =  $this->getAttibuteValueData($attributedata['attribute_code']);
          $key = array_search($attibuteoptionname, array_column($returnreturn['AttributeOptionData'], 'label'));
          $lastoptionData = $returnreturn['AttributeOptionData'][$key];
          if($fileUrl != '')
          {
            if($this->is_image_exist($fileUrl))
            {
                 $lastoptionData['url'] = $fileUrl;
                 $fileUrlBasename = baseName($fileUrl);
            }else{
                $this->logger->info(' images not exist '. $fileUrl);
                fwrite($fp_log, 'Image not exist '.$fileUrl."\n");
                $noimagePath = $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder-color.jpg';
                $fileUrlBasename = baseName($fileUrl);
                $lastoptionData['url'] = $noimagePath;
                $fileUrl = $noimagePath;
            }
            $addoptionattributedata = $this->eavConfig->getAttribute('catalog_product', $attributedata['attribute_code']);
            $finalProductData = $lastoptionData;
            $lastoptionvalue = $lastoptionData['value'];
            $object_Manager = \Magento\Framework\App\ObjectManager::getInstance();
            $filesystem = $object_Manager->create('\Magento\Framework\Filesystem');
            $mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $productMediaConfig = $object_Manager->create('\Magento\Catalog\Model\Product\Media\Config');
            $tmpMediaPath = $productMediaConfig->getBaseTmpMediaPath();
            $fullTmpMediaPath = $mediaDirectory->getAbsolutePath($tmpMediaPath);
            $this->driverFile->createDirectory($fullTmpMediaPath);
            $this->driverFile->copy($fileUrl, $fullTmpMediaPath.'/'.$fileUrlBasename);
   //          $contextOptions = array(
			// 	"ssl" => array(
			// 		"verify_peer"      => false,
			// 		"verify_peer_name" => false,
			// 	),
			// );
   //          copy($fileUrl, $fullTmpMediaPath.'/'.$fileUrlBasename , stream_context_create( $contextOptions ) );

            $newFile =  $this->swatchHelper->moveImageFromTmp($fileUrlBasename);
            if (substr($newFile, 0, 1) == '.') {
                $newFile = substr($newFile, 1); // Fix generating swatch variations for files beginning with ".".
            }
            $this->swatchHelper->generateSwatchVariations($newFile);
            $data = array();
            $data['swatchvisual']['value'][$lastoptionvalue] = $newFile;
            $addoptionattributedata->addData($data)->save();
          }
          $fileUrl = '';
            return $this->getColorAttibutevalue($returnreturn,$attibuteoptionname,$fileUrl,$fp_log);
          }
      }


    public function getAttibutevalue($AttributeOptionData,$attibuteoptionname){
        $key = array_search($attibuteoptionname, array_column($AttributeOptionData['AttributeOptionData'], 'label'));
        if($key != false)
        {

             return $AttributeOptionData['AttributeOptionData'][$key];
        }else{

            $attributedata = $AttributeOptionData['attributeData'];
            $option = $this->AttributeOptionInterfaceFactory->create();
            $option->setLabel($attibuteoptionname);
            $data = $this->AttributeOptionManagementInterface->add(self::CATALOG_PRODUCT, $attributedata['attribute_code'], $option);
            $returnreturn =  $this->getAttibuteValueData($attributedata['attribute_code']);
              //$this->resetAttibuteOptionArray($returnreturn,$attributedata['attribute_code']);
               return $this->getAttibutevalue($returnreturn,$attibuteoptionname);
            }
            return '';
        }
    public function resetAttibuteOptionArray($attributeArray,$attributeCode)
    {
        if($attributeCode == 'color')
        {
            $getColorAttibuteOption = '';
            $getColorAttibuteOption = $this->getAttibuteValueData('color');
        }
        return;
    }
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;
    }



    public function RemoveImagesProduct($getProductId)
    {
                if($getProductId  && $getProductId != '')
                  {
                        $product = $this->product->load($getProductId);
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName('catalog_product_entity_media_gallery');
                        $imageProcessor = $objectManager->create('\Magento\Catalog\Model\Product\Gallery\Processor');
                        $images = $product->getMediaGallery();
                        foreach($images['images'] as $key => $child){

                            if($child['file'])
                            {
                                $filename = $child['file'];
                                $sql = "Delete FROM " . $tableName." Where value = '".$filename."'";
                                $connection->query($sql);
                                $values_id = $child['value_id'];
                                $sqldelte = "Delete FROM au_catalog_product_entity_media_gallery_value Where value_id = '".$values_id."'";
                                $connection->query($sqldelte);

                            }
                        }
                  }
                  return true;
    }
    
    public function is_image_exist($url)
    {
        //$url = str_replace("https:/w", "https://w", $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //$code = curl_getinfo($ch);
        if($code == 200){
           $status = true;
        }else{

          $status = false;
        }
        curl_close($ch);
       return $status;
    }
    public function imgurlupdate($imgurl)
    {
    	// $imgurl = preg_replace('/^https(?=:\/\/)/i','http',$imgurl);
      if (strpos($imgurl, 'adarmedicaluniforms.com') !== false) {
      	  return str_replace('adarmedicaluniforms.com', 'adaruniforms.com', $imgurl);
      }else{
        return $imgurl;
      } 


    }
}
