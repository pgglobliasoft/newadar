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
    /**public function getcategoryId($categotuname)
    {
        $collection = $this->_categoryFactory
                ->create()
                ->addAttributeToFilter('name',$categotuname);
        if ($collection->getSize()) {
           foreach($collection as $categoryData)
           {
            $categoryId [] = $categoryData->getId();
           }

        }
        return $categoryId;
    }**/
    public function getcategoryId($productitem)
    {
        //$productitem  = '';
       // $productitem = $productitemData;
        //$categoryId = '';
        $categoryId = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $categoryFactorydata = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');  
        if($productitem['Collection'] != '')
        {
            $collectiondata = $this->_categoryFactory->create()->addAttributeToFilter('name',trim($productitem['Collection']));
                     if ($collectiondata->getSize()) {
                       foreach($collectiondata as $categoryData)
                       {
                            $category = $categoryFactorydata->create()->load($categoryData->getId());
                            foreach($category->getChildrenCategories() as $childcategory)
                             {
                                if($childcategory->getName() == trim($productitem['Items_Catageory']))
                                {
                                   $categoryId[0]= $childcategory->getEntityId(); 
                                   
                                   
                                }
                             }
                             $categoryId[1] = $categoryData->getId();
                            //$categoryId= $categoryData->getParentId();
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
                                if($childcategory->getName() == trim($productitem['Items_Catageory']))
                                {
                                   $categoryId[2]= $childcategory->getEntityId();   
                                  
                                }
                             }
                              $categoryId[3] = $categoryData->getId();
                            //$categoryId= $categoryData->getParentId();
                            
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
				$superAttirbuteids=[];
				$getColorAttibuteOption = $this->getAttibuteValueData('color');
				$superAttirbuteids [] = $getColorAttibuteOption['attributeData']['attribute_id'];
				$getSeasonalColorsAttibuteOption = $this->getAttibuteValueData('seasonalcolors');
				$superAttirbuteids[]= $getSeasonalColorsAttibuteOption['attributeData']['attribute_id'];
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
				$productitem['Bullets'] = str_replace('�', "'", $bulletString); //str_replace("*","<br/>",$productitem['Bullets']);
				$getColorValue = $this->getColorAttibutevalue($getColorAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log);
				$getSeasonalColorsValue = $this->getColorAttibutevalue($getSeasonalColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log); //Seasonal_Colors

				$getSizeValue = $this->getAttibutevalue($getSizeAttibuteOption,trim($productitem['Size']));
				$getCollectionValue = $this->getAttibutevalue($getCollecttionAttibuteOption,trim($productitem['Collection']));
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
				$getFitValue = $this->getAttibutevalue($getFitAttibuteOption,trim($productitem['Fit']));
				$discription = str_replace('�', "'", $productitem['Long_Description']);
				$category_id = array();
				//$category_id = '';
				$category_id = $this->getcategoryId($productitem);

				//$categoryid = $this->getcategoryId($productitem['Items_Catageory']);
				//$category_id = $categoryid;
				$productitem['Qty_Available'] = trim(str_replace("+", "", $productitem['Qty_Available']));
				$allImagePath = array();
                if(isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
				{
					if($productitem['Sketch_Image'] != '')
					{
						//$allImagePath[]= $productitem['Sketch_Image'];    
					}
					if($productitem['Web_Image1'] !='')
					{
						$allImagePath[]= $productitem['Web_Image1'];    
					}
					else
					{
						$allImagePath[]=  $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
					}
					if($productitem['Web_Image2'] !='')
					{
						$allImagePath[]= $productitem['Web_Image2'];    
					}
					if($productitem['Web_Image3'] !='')
					{
						$allImagePath[]= $productitem['Web_Image3'];    
					}
					if($productitem['Web_Image4'] !='')
					{
						$allImagePath[]= $productitem['Web_Image4'];    
					}
					if($productitem['Web_Image5'] !='')
					{
						$allImagePath[]= $productitem['Web_Image5'];    
					}
					if($productitem['Web_Image6'] !='')
					{
						$allImagePath[]= $productitem['Web_Image6'];    
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
					if( strtolower(trim($productitem['Color_Status'])) == 'Seasonal')
					{
						$product->setSeasonalcolors($getSeasonalColorsValue['value']);
					}
					else
					{
						$product->setSeasonalcolors(231);
					}
					$product->setColor($getColorValue['value']);   
					$product->setSize($getSizeValue['value']);
					$product->setCreatedAt(strtotime('now'));
					$product->setWebsiteIds(array(1,0));
					$product->setStoreId(0);
				}
					
				if( strtolower(trim($productitem['Color_Status'])) == 'Seasonal')
				{
					$product->setSeasonalcolors($getSeasonalColorsValue['value']);
				}
				else
				{
					$product->setSeasonalcolors(231);
				}
				/* $product->setColor($getColorValue['value']);   
				$product->setSize($getSizeValue['value']);
				$product->setCreatedAt(strtotime('now'));
				$product->setWebsiteId(0);
				$product->setStoreId(0); */				 
				
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
				//$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
				$product->setTaxClassId(self::TAXCLASSID);
				$product->setWeight($productitem['Weight']);
				$product->setParentStyle(trim($productitem['Parent_Style']));   
				
				$product->setGender($getGenderValue['value']);
				$product->setFit($getFitValue['value']);
				$product->setcollecttion(array($getCollectionValue['value']));
				if(isset($getBrandValue['value']) && $getBrandValue['value'] !='')
				{
					$product->setSttlBrand($getBrandValue['value']);
				}
                   
                    
				$product->setFeature($FeaturesValueArray);
				$product->setMaincolor(array($getParenColorValue['value']));
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
				$product->setSizecharturl($productitem['Website_Size_Chart']);
				$product->setModelWears($productitem['Model_Wears']);
				if(!empty($allImagePath) && isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
				{
					if($getProductId)
					{
						$this->RemoveImagesProduct($getProductId);
					}
					$this->imapgeupload($product,$allImagePath, $fp_log);
					if($i > 0)
					{
						$UpdateStatus .= 'update "dbo".ItemAIO set ISImgUpdate = \'N\' where id = '.$productitem['ID'].';';	
					}					
					echo PHP_EOL;
				}
				
				try 
				{
					$product->save();
					//echo PHP_EOL;
					//echo "=={";
					//print_r($product->getId());
					//echo "}==";
					echo PHP_EOL; 
					fwrite($fp_log, $productitem['SKU_Number'].' Updated product sucess.'."\n");
				}
				catch (Exception $e) 
				{
					fwrite($fp_log, $e->getMessage()."\n");
				}
				$i++;
			}
	   }
	   $UpdateQuery = $adar_magento_obj->query($UpdateStatus);
	}
	
    public function ImportConfigProduct($productArray,$adar_magento_obj, $fp_log)
	{
		$this->logger->info('=============== Simple Product Import =====================');
		$parent_style_array = array();
		$UpdateStatus = '';
		foreach($productArray as $productitem)
        {
			if (trim($productitem['Parent_Style']) != '' && !in_array(trim($productitem['Parent_Style']), $parent_style_array))
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
				
				$superAttirbuteids=[];
				$getColorAttibuteOption = $this->getAttibuteValueData('color');
				$superAttirbuteids [] = $getColorAttibuteOption['attributeData']['attribute_id'];
				$getSeasonalColorsAttibuteOption = $this->getAttibuteValueData('seasonalcolors');
				$superAttirbuteids[]= $getSeasonalColorsAttibuteOption['attributeData']['attribute_id'];
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
				$productitem['Bullets'] = str_replace('�', "'", $bulletString); //str_replace("*","<br/>",$productitem['Bullets']);
				$getColorValue = $this->getColorAttibutevalue($getColorAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log);
				$getSeasonalColorsValue = $this->getColorAttibutevalue($getSeasonalColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log); //Seasonal_Colors
				
				$getSizeValue = $this->getAttibutevalue($getSizeAttibuteOption,trim($productitem['Size']));
				$getCollectionValue = $this->getAttibutevalue($getCollecttionAttibuteOption,trim($productitem['Collection']));
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
				$getFitValue = $this->getAttibutevalue($getFitAttibuteOption,trim($productitem['Fit']));
				$discription = str_replace('�', "'", $productitem['Long_Description']);
				$category_id = array();
				//$category_id = '';
				$category_id = $this->getcategoryId($productitem);
				
				//$categoryid = $this->getcategoryId($productitem['Items_Catageory']);
				//$category_id = $categoryid;
				$productitem['Qty_Available'] = trim(str_replace("+", "", $productitem['Qty_Available']));
				$allImagePath = array();
                    
				if(isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
				{
					if($productitem['Sketch_Image'] != '')
					{
						//$allImagePath[]= $productitem['Sketch_Image'];    
					}
					if($productitem['Web_Image1'] !='')
					{
						$allImagePath[]= $productitem['Web_Image1'];    
					}
					else
					{
						$allImagePath[]=  $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
					}
					if($productitem['Web_Image2'] !='')
					{
						$allImagePath[]= $productitem['Web_Image2'];    
					}
					if($productitem['Web_Image3'] !='')
					{
					$allImagePath[]= $productitem['Web_Image3'];    
					}
					if($productitem['Web_Image4'] !='')
					{
					$allImagePath[]= $productitem['Web_Image4'];    
					}
					if($productitem['Web_Image5'] !='')
					{
					$allImagePath[]= $productitem['Web_Image5'];    
					}
					if($productitem['Web_Image6'] !='')
					{
					$allImagePath[]= $productitem['Web_Image6'];    
					}
				}
				
				$getConfigProductId = $this->product->getIdBySku($productitem['Parent_Style']);
				$con_prd_update = false;
				if($getConfigProductId && $getConfigProductId != '')
				{
					$Configrebleproduct = $this->product->load($getConfigProductId);
					$con_prd_update = true;
				}
				else
				{
					$Configrebleproduct = $this->productFactory->create();
					$Configrebleproduct->setTypeId('configurable');
					$Configrebleproduct->setSku($productitem['Parent_Style']);
					$Configrebleproduct->setAttributeSetId(self::ATTRIBUTE_SET_ID);
					$Configrebleproduct->setVisibility(4);
					$Configrebleproduct->setCreatedAt(strtotime('now'));
					$Configrebleproduct->setWebsiteIds(array(1));
					$Configrebleproduct->setStoreId(0);
				}
				//$Configrebleproduct->setStoreId(0);
				if(isset($productitem['Short_Description']) && $productitem['Short_Description'] != '')
				{
					$Configrebleproduct->setName(str_replace('�', "'", $productitem['Short_Description'])); 
					$Configrebleproduct->setShortDescription(str_replace('�', "'", $productitem['Short_Description']));
				}
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
				$Configrebleproduct->setCategoryIds($category_id);
				$Configrebleproduct->setStatus(self::STATUS);
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
				
				$Configrebleproduct->setCollecttion(array($getCollectionValue['value']));
				$Configrebleproduct->setFeature($FeaturesValueArray);
				$Configrebleproduct->setMaincolor(array($getParenColorValue['value']));
				$Configrebleproduct->setStockData( array(
						'use_config_manage_stock' => 1,
						'manage_stock' => self::MANAGE_STOCK,
						'is_in_stock' => self::IS_IN_STOCK
						)
					);
				if($productitem['Website_Size_Chart'] != '')
				{
				   $Configrebleproduct->setSizecharturl($productitem['Website_Size_Chart']);
					 
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
						$this->RemoveImagesProduct($getConfigProductId);
					}
					$this->imapgeupload($Configrebleproduct,$allImagePath, $fp_log);
					$UpdateStatus .= 'update "dbo".ItemAIO set ISImgUpdate = \'N\' where id = '.$productitem['ID'].';';	
				}
				try 
				{
					$Configrebleproduct->save();
					$parent_style_array[] = trim($productitem['Parent_Style']);
					$this->simpleproductsettoconfigreble($Configrebleproduct->getId(),$productitem['Parent_Style'], $superAttirbuteids, $fp_log, $con_prd_update);
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
			
			$MagentoUpdateStatus = 'update "dbo".ItemAIO set MagentoField = 1 where id ='.$productitem['ID'];
			//echo PHP_EOL;
			$adar_magento_obj->query($MagentoUpdateStatus);
		}
		$UpdateQuery = $adar_magento_obj->query($UpdateStatus);
	}
	
	public function simpleproductsettoconfigreble($id, $parent_style, $superAttirbuteids, $fp_log,$con_prd_update = false)
    {

        try {
        		$getConfigProductData = $this->product->load($id);
				$associatedProductIds = array();
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$prodCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
				$collection = $prodCollection->create()
					->addAttributeToSelect('entity_id')
					->addAttributeToFilter('parent_style',$parent_style)
					->addAttributeToFilter(array(array('attribute'=>'type_id','eq' => 'simple')))
					->load();
					
				foreach($collection as $simple_item)
				{
					$associatedProductIds[] = $simple_item->getId();
					//print_r($simple_item->getId());
					//echo PHP_EOL;
				}
				//print_R($associatedProductIds);exit;
				$attributes = $superAttirbuteids;
				$getConfigProductData->setCanSaveConfigurableAttributes(true);
				$getConfigProductData->setCanSaveCustomOptions(true);
				$getConfigProductData->getTypeInstance()->setUsedProductAttributeIds($attributes, $getConfigProductData);
				$configurableAttributesData = $getConfigProductData->getTypeInstance()->getConfigurableAttributesAsArray($getConfigProductData);
				$getConfigProductData->setCanSaveConfigurableAttributes(true);
				$getConfigProductData->setConfigurableAttributesData($configurableAttributesData);
				$configurableProductsData = array();
				$getConfigProductData->setConfigurableProductsData($configurableProductsData);
				
				/* $extensionConfigurableAttributes = $getConfigProductData->getExtensionAttributes();
				$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
				$getConfigProductData->setExtensionAttributes($extensionConfigurableAttributes); */
				
				$getConfigProductData->setAssociatedProductIds($associatedProductIds);
				
				$getConfigProductData->save();
        }
        catch (Exception $e) {
        	fwrite($fp_log, $e->getMessage()."\n");
            $this->logger->info('there is some issue in simple product set to configurable for ' . $productimportArray['configreble_id']);
        }

    }
    public function ImportRelatedProduct($productArray,$adar_magento_obj, $fp_log)
	{
		$this->logger->info('=============== Related Product Import =====================');
		$parent_style_array = array();
		$UpdateStatus = '';
		foreach($productArray as $productitem)
        {
			if (trim($productitem['Parent_Style']) != '' && !in_array(trim($productitem['Parent_Style']), $parent_style_array))
			{
				try 
				{
					$getConfigProductId = $this->product->getIdBySku($productitem['Parent_Style']);
					if($getConfigProductId && $getConfigProductId != '' && $productitem['RecomProduct'] != '' && isset($productitem['RecomProduct']))
					{
						$Configrebleproduct = $this->product->load($getConfigProductId);
						$linkDataAll = [];
						$productitem['RecomProduct'] = "R6008,R6004,R6002,R6000";
						$skuLinks = explode(",",$productitem['RecomProduct']);
						foreach($skuLinks as $n => $skuLink) 
						{
                			try {
								$linkedProduct = $this->product->get($skuLink);
			                } catch (Exception $e){
			                	fwrite($fp_log, $skuLink.' Not Exsting.'."\n");
			                    $linkedProduct = false;
			                }
			                if($linkedProduct && $skuLink != $Configrebleproduct->getSku()) 
			                {
			                  	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
			                    $productLinks = $objectManager->create('Magento\Catalog\Api\Data\ProductLinkInterface');
			                    $linkData = $productLinks->setSku($Configrebleproduct->getSku())
			                        ->setLinkedProductSku($skuLink)
			                        ->setLinkType('related')
			                        ->setPosition($n + 1);
			                    $linkDataAll[] = $linkData;
			                }

			        	}
			            if($linkDataAll) {
			                $Configrebleproduct->setProductLinks($linkDataAll);
			            }
			       	}
            		$Configrebleproduct->save();
            		$this->productRepository->save($Configrebleproduct);
					$parent_style_array[] = trim($productitem['Parent_Style']);
					echo "=={";
					print_r($Configrebleproduct->getSku());
					echo "}==";
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
		}

	}
    public function ImportProduct($productArray,$adar_magento_obj, $fp_log)
    {
       $this->logger->info('===============Product Import =====================');
       $productImportArray = [];
        try{
                $ParentChange = '';
                $i = 0;
                $cnt = 0;
                $undelteConfigrebleIds = array();
                $configurable_product_id = 0;
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
                    $superAttirbuteids=[];
                    $getColorAttibuteOption = $this->getAttibuteValueData('color');
                    $superAttirbuteids [] = $getColorAttibuteOption['attributeData']['attribute_id'];
                    $getSeasonalColorsAttibuteOption = $this->getAttibuteValueData('seasonalcolors');
                    $superAttirbuteids[]= $getSeasonalColorsAttibuteOption['attributeData']['attribute_id'];
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
                    $productitem['Bullets'] = str_replace('�', "'", $bulletString); //str_replace("*","<br/>",$productitem['Bullets']);
                    $getColorValue = $this->getColorAttibutevalue($getColorAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log);
                    $getSeasonalColorsValue = $this->getColorAttibutevalue($getSeasonalColorsAttibuteOption,trim($productitem['Color_Name']),$productitem['Color_Swatch'],$fp_log); //Seasonal_Colors
                    
                    $getSizeValue = $this->getAttibutevalue($getSizeAttibuteOption,trim($productitem['Size']));
                    $getCollectionValue = $this->getAttibutevalue($getCollecttionAttibuteOption,trim($productitem['Collection']));
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
                    $getFitValue = $this->getAttibutevalue($getFitAttibuteOption,trim($productitem['Fit']));
                    $discription = str_replace('�', "'", $productitem['Long_Description']);
                    $category_id = array();
                    //$category_id = '';
                    $category_id = $this->getcategoryId($productitem);
                    //$categoryid = $this->getcategoryId($productitem['Items_Catageory']);
                    //$category_id = $categoryid;
                    $productitem['Qty_Available'] = trim(str_replace("+", "", $productitem['Qty_Available']));
                    $allImagePath = array();
                    
                    if(isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
                    {
                        if($productitem['Sketch_Image'] != '')
                        {
                            //$allImagePath[]= $productitem['Sketch_Image'];    
                        }
                        if($productitem['Web_Image1'] !='')
                        {
                            $allImagePath[]= $productitem['Web_Image1'];    
                        }
                        if($productitem['Web_Image2'] !='')
                        {
                            $allImagePath[]= $productitem['Web_Image2'];    
                        }
                        if($productitem['Web_Image3'] !='')
                        {
                        $allImagePath[]= $productitem['Web_Image3'];    
                        }
                        if($productitem['Web_Image4'] !='')
                        {
                        $allImagePath[]= $productitem['Web_Image4'];    
                        }
                        if($productitem['Web_Image5'] !='')
                        {
                        $allImagePath[]= $productitem['Web_Image5'];    
                        }
                        if($productitem['Web_Image6'] !='')
                        {
                        $allImagePath[]= $productitem['Web_Image6'];    
                        }
                    }
					
                    if($i == 0)
                    {
                        $getConfigProductId = $this->product->getIdBySku($productitem['Parent_Style']);
						$con_prd_update = false;
                        if($getConfigProductId && $getConfigProductId != '')
                        {
                            $Configrebleproduct = $this->product->load($getConfigProductId);
							$con_prd_update = true;
                        }
						else
						{
                                $Configrebleproduct = $this->productFactory->create();
								$Configrebleproduct->setTypeId('configurable');
								$Configrebleproduct->setSku($productitem['Parent_Style']);
								$Configrebleproduct->setAttributeSetId(self::ATTRIBUTE_SET_ID);
								$Configrebleproduct->setVisibility(4);
                                $Configrebleproduct->setCreatedAt(strtotime('now'));
								$Configrebleproduct->setWebsiteId(0);
                                $Configrebleproduct->setStoreId(0);
						}
                                if(isset($productitem['Short_Description']) && $productitem['Short_Description'] != '')
                                {
                                    $Configrebleproduct->setName(str_replace('�', "'", $productitem['Short_Description'])); 
                                    $Configrebleproduct->setShortDescription(str_replace('�', "'", $productitem['Short_Description']));
                                }
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
                                $Configrebleproduct->setCategoryIds($category_id);
                                $Configrebleproduct->setStatus(self::STATUS);
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
                                $Configrebleproduct->setCollecttion(array($getCollectionValue['value']));
                                $Configrebleproduct->setFeature($FeaturesValueArray);
                                $Configrebleproduct->setMaincolor(array($getParenColorValue['value']));
                                $Configrebleproduct->setStockData( array(
                                        'use_config_manage_stock' => 1,
                                        'manage_stock' => self::MANAGE_STOCK,
                                        'is_in_stock' => self::IS_IN_STOCK
                                        )
                                    );
                                if($productitem['Website_Size_Chart'] != '')
                                {
                                   $Configrebleproduct->setSizecharturl($productitem['Website_Size_Chart']);
                                     
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
                                        $this->RemoveImagesProduct($getConfigProductId);
                                    }
                                    $this->imapgeupload($Configrebleproduct,$allImagePath, $fp_log);
                                }
                                
                                try {
                                        
                                        $Configrebleproduct->save();
								        fwrite($fp_log, $productitem['Parent_Style'].' update product sucess.'."\n");
                                        $this->logger->info('Add Configreble Product : Product Id=='.$Configrebleproduct->getId());
                                    }
                                    catch (Exception $e) {

                                        fwrite($fp_log, $e->getMessage()."\n");
                                        $this->logger->info($e->getMessage());
                                    }
                        //}
                                $getConfigProductId = $Configrebleproduct->getId();
                                $undelteConfigrebleIds[] = $getConfigProductId;
                        if($configurable_product_id > 0)
                            {
								echo "==379==";
								echo PHP_EOL;
                                $this->simpleproductsettoconfigreble($productimportArray,$superAttirbuteids, $fp_log, $con_prd_update);
								echo PHP_EOL;
								echo "==379==";
								
                                $configurable_product_id = 0;
                                $productimportArray = [];
                                $productimportArray['configreble_id'] = 0;
                            }
                            if($getConfigProductId && $getConfigProductId != '')
                            {
                                $productimportArray['configreble_id'] = $getConfigProductId;
                                $configurable_product_id = $getConfigProductId;
                            }else{
                                $productimportArray['configreble_id'] = $Configrebleproduct->getId();
                                $configurable_product_id = $Configrebleproduct->getId();
                             }
                              $this->logger->info('Add Simpla (Assoiceated) Product for Configreble Product Product Id: '.$Configrebleproduct->getId());
                    }

                    $getProductId = $this->product->getIdBySku($productitem['SKU_Number']);
					$update_prd = false;
                    if($getProductId && $getProductId != '')
                    {
                        $product = $this->product->load($getProductId);
						$update_prd = true;
                    }else{
                         $product = $this->productFactory->create();
						 $product->setTypeId('simple');
                    }
					
					if($update_prd == false)
					{
						$product->setSku($productitem['SKU_Number']);
						$product->setAttributeSetId(self::ATTRIBUTE_SET_ID);
						$product->setVisibility(self::VISIBILITY);
						if( strtolower(trim($productitem['Color_Status'])) == 'Seasonal')
						{
							$product->setSeasonalcolors($getSeasonalColorsValue['value']);
						}
						else
						{
							$product->setSeasonalcolors(231);
						}
						$product->setColor($getColorValue['value']);   
						$product->setSize($getSizeValue['value']);
						$product->setCreatedAt(strtotime('now'));
						$product->setWebsiteId(0);
						$product->setStoreId(0);
					}
					if( strtolower(trim($productitem['Color_Status'])) == 'Seasonal')
						{
							$product->setSeasonalcolors($getSeasonalColorsValue['value']);
						}
						else
						{
							$product->setSeasonalcolors(231);
						}
						$product->setColor($getColorValue['value']);   
						$product->setSize($getSizeValue['value']);
						$product->setCreatedAt(strtotime('now'));
						$product->setWebsiteId(0);
						$product->setStoreId(0);				 
				
                    $product->setName(str_replace('�', "'", $productitem['Short_Description'].' '.$productitem['Color_Name'].' '.$productitem['Size']));
                    $product->setShortDescription(str_replace('�', "'", $productitem['Short_Description']));
                    $product->setDescription($discription);
                    $product->setBulletsdetails(str_replace('�', "'", $productitem['Bullets']));
                    $product->setFabriccontent($productitem['Fabric_Content']);
                    
                    $product->setCategoryIds($category_id);
                    $product->setFilterCategory($getCatageoryValue['value']);
                    if(isset($productitem['ISActive']) && $productitem['ISActive'] == 0)
                    {
                      $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    if(isset($productitem['ISActive']) && $productitem['ISActive'] == 1)
                    {
                      
                      $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    }
                    //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $product->setTaxClassId(self::TAXCLASSID);
                    $product->setWeight($productitem['Weight']);
                    
                   
                    
                    $product->setGender($getGenderValue['value']);
                    $product->setFit($getFitValue['value']);
                    $product->setcollecttion(array($getCollectionValue['value']));
					if(isset($getBrandValue['value']) && $getBrandValue['value'] !='')
					{
						 $product->setSttlBrand($getBrandValue['value']);
					}
                   
                    
                    $product->setFeature($FeaturesValueArray);
                    $product->setMaincolor(array($getParenColorValue['value']));
                    $product->setPrice(round($productitem['Items_Price_list']));
                    $product->setStockData( array(
                                'use_config_manage_stock' => self::USE_CONFIG_MANAGE_STOCK,
                                'manage_stock' => self::MANAGE_STOCK,
                                'is_in_stock' => self::IS_IN_STOCK,
                                'qty' => $productitem['Qty_Available']
                                )
                    );
                    $product->setSizecharturl($productitem['Website_Size_Chart']);
                    $product->setModelWears($productitem['Model_Wears']);
                    if(!empty($allImagePath) && isset($productitem['IsImgUpdate']) && $productitem['IsImgUpdate'] == 'Y')
                    {
                         if($getProductId)
                            {
                                    //$this->RemoveImagesProduct($getProductId);
                            }
                        //$this->imapgeupload($product,$allImagePath, $fp_log);

                    }

                    try 
                    {
                        $product->save();
						echo "=={";
						print_r($product->debug());
						echo "}==";
						echo PHP_EOL;
						print_r($product->getId());
						echo PHP_EOL;
                        fwrite($fp_log, $productitem['SKU_Number'].' Updated product sucess.'."\n");
                    }
                    catch (Exception $e) 
                    {
                        fwrite($fp_log, $e->getMessage()."\n");
                    }
                    
                    $productimportArray['simple_id'][]= $product->getId();
                    $this->logger->info('Add Simpla (Assoiceated) Product : Product Id=='.$product->getId());
                    $i++;
                    $cnt++;
                }
                echo trim($productitem['Parent_Style']);
                echo PHP_EOL;
				if(isset($productitem['ID']) && $productitem['ID'] !='')
				{
					$id = $productitem['ID'];
					$UpdateImagesStatus = 'update "dbo".product_details set IsImgUpdate = \'N\' where id ='.$id;
					//$UpdateImagesQuery = $adar_magento_obj->query($UpdateImagesStatus);
					$UpdateStatus = 'update "dbo".product_details set MagentoField = 1 where id ='.$id;
					$UpdateQuery = $adar_magento_obj->query($UpdateStatus);	
				}
                
            }
             /**if(!empty($undelteConfigrebleIds))
                {
                    $this->anotherProductDisble($undelteConfigrebleIds);
                }**/
                //echo "441";exit;
				echo "517";exit;
				echo PHP_EOL;
                $this->simpleproductsettoconfigreble($productimportArray,$superAttirbuteids, $fp_log,$con_prd_update);
				echo PHP_EOL;
				echo "517";
				
                echo "done";
        }catch (\Exception $e) {
                fwrite($fp_log, $e->getMessage()."\n");
                $this->logger->info('see the erreo '.$e);
            }
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
            $this->videoGalleryProcessor->addVideo($product,$videoData,['image', 'small_image', 'thumbnail'],false,false);
        }
        return $product;
       
    }
    public function imapgeupload($product,$allImagePath,$fp_log)
    {
    	    $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            $i = 0;
            foreach ($allImagePath as $key => $imagePath) {
                $newFileName = '';
                if($imagePath != ''  &&  $imagePath != ' ')
                {
                 if($this->is_image_exist($imagePath))
                 {
                    $newFileName = $tmpDir . baseName($imagePath);
                    $result = $this->file->read($imagePath, $newFileName);
                   
                 }else{
                    $this->logger->info('SUK number == '.$product->getSku().' images not exist '. $imagePath);
                    fwrite($fp_log, 'Image not exist for '.$product->getSku().' URL:'.$imagePath."\n");
                     $noimagePath = $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
                    $newFileName = $tmpDir . baseName($imagePath); 
                    $result = $this->file->read($noimagePath, $newFileName);
                  }
                    if ($result) 
                    {
                        //echo $newFileName."==";
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
                        $i++;
                    }
                }
            }
            /**$productVideos =array();
            $productVideos['url']= 'https://www.youtube.com/embed/WCuRrnXpl90';
            $productVideos['images']=  $this->directoryList->getPath(DirectoryList::MEDIA).DIRECTORY_SEPARATOR.'image-placeholder.jpg';
            $this->videosUpload($product,$productVideos);**/
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
    public function getColorAttibutevalue($AttributeOptionData,$attibuteoptionname,$fileUrl = '',$fp_log){
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
    
    
    
    public function SimpleProductImageRemove($AllProductArray)
    {
      $this->logger->info('===============Product Image Remove =====================');
      try{
          $i = 0;
          foreach($AllProductArray as $ProductData)
          {
              if(trim($ProductData['Parent_Style']) != '')
              {

                  $allImagePath = '';
                  $allImagePath[]= $ProductData['Main_Product_Image'];

                  $getProductId = $this->product->getIdBySku($ProductData['SKU_Number']);
                  $getProductId = 303;
                  if($getProductId && $getProductId != '')
                  {
                      $product = $this->product->load($getProductId);
                        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();

                        foreach ($existingMediaGalleryEntries as $key => $entry) {
                            if($key){
                                unset($existingMediaGalleryEntries[$key]);
                            }
                          }
                        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                        $this->productRepository->save($product);
                      //  $this->imapgeuploadRemove($product,$allImagePath);
                        $product->save();
                        exit;
                        $this->logger->info('Simple Product image Upload : Product Id == '.$product->getId());
                        echo "Product ID == ".trim($getProductId);
                        echo PHP_EOL;
                  }
                  $i++;
              }
          }
        }catch (\Exception $e) {
              $this->logger->info('see the erreo '.$e);
        }
        echo "all images upload";
    }
     public function imapgeuploadRemove($product,$allImagePath)
    {
            $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            $i = 0;
            foreach ($allImagePath as $key => $imagePath) {
               $newFileName = $tmpDir . baseName($imagePath);
                $result = $this->file->read($imagePath, $newFileName);
                if ($result) 
                {
                    $flags = '';
                    if($i == 0)
                    {
                        $flags = ['image','thumbnail','small_image'];        
                    }
                    $product->addImageToMediaGallery($newFileName,array('image', 'small_image', 'thumbnail'), false, false);
                    $i++;
                }

            }
           return true;
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
                            }
                        } 
                  }
                  return true;
    }
    public function SimpleProductImageUpload($AllProductArray)
    {
      $this->logger->info('===============Product Image upload =====================');
      try{
          $i = 0;
          foreach($AllProductArray as $ProductData)
          {
              if(trim($ProductData['Parent_Style']) != '')
              {
                    $ProductData['videos']['url']= 'https://www.youtube.com/embed/WCuRrnXpl90';
                    $ProductData['videos']['images']= '';
                    $allImagePath = '';
                    $allImagePath[]= $ProductData['image_1'];
                    $allImagePath[]= $ProductData['image_2'];
                    $allImagePath[]= $ProductData['image_3'];
                    $allImagePath[]= $ProductData['image_4'];
                    //$allImagePath[]= $ProductData['videos']; 
                    $allImagePath[]= $ProductData['image_5'];
                    $allImagePath[]= $ProductData['image_6'];  
                    $getProductId = '';
                    $getProductId = $this->product->getIdBySku($ProductData['SKU_Number']);

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
                                echo PHP_EOL;
                                $sql = "Delete FROM " . $tableName." Where value = '".$filename."'";
                            }
                        } 
                         
                        //$this->imapgeupload($product,$allImagePath);
                        $product->save();
                        $this->logger->info('Simple Product image Upload : Product Id == '.$product->getId());
                        echo "Product ID == ".trim($getProductId);
                        echo PHP_EOL;
                  }
                  $i++;
              }
          }
        }catch (\Exception $e) {
              $this->logger->info('see the erreo '.$e);
        }
        echo "all images upload";
    }
    
    public function anotherProductDisble($productids = '')
    {
       $collection = $this->_productCollectionFactory->create();
       $collection->addFieldToSelect(array('entity_id'));
       $collection->addFieldToFilter('status', ['eq' => self::STATUS]);
       $collection->addAttributeToFilter('entity_id', array('nin' => $productids));
       $collection->addFieldToFilter('type_id', ['eq' => 'configurable']);
          foreach($collection as $items)
          {
              $product = $this->productRepository->getById($items->getId());
               $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
              $this->productRepository->save($product);
          }
    }

    public function is_image_exist($url)
    {
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
}
