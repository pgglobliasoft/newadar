<?php

namespace Vendor\Rules\Model;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\EncoderInterface;



class Configurable extends \Magento\Framework\View\Element\Template implements OptionSourceInterface
{

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        CollectionFactory $productCollectionFactory,
        \Vendor\Rules\Model\GridFactory $factoryCategory,
        array $data = []
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->factoryCategory = $factoryCategory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }


    public function getOptionArray()
    {
        $result = [];
        $categories = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku'])->addAttributeToFilter('type_id', 'configurable')->addAttributeToSort('sku', 'ASC');

        $category = $this->factoryCategory->create()->getCollection()->addFieldToFilter('is_active',array('in' => array(1,2)))->setOrder('sort_order', 'ASC')->getData();
        foreach ($category as $key => $value) {
            $categories[] = $value['sku'];
           
        }

        $items = array();
        foreach ($category as $value) {
          $items[] = $value['sku'];
        }

        $uniq = array_unique($items); 

        foreach ($collection as $index => $product) {
            if ($product->getTypeId() == "configurable") {
                $result[] = ['sku' => $product->getSku() , 'value' => $product->getSku()." , ".$product->getName(), 'label' => $product->getSku()." - ".$product->getName()];
            }
        }

//         function removeElementWithValue($array, $key, $value){
//             echo "<pre>";
//              foreach($array as $subKey => $subArray){
//                 // print_r(gettype($subArray[$key]));
//                 // print_r(gettype($value));
//                  unset($array[$subKey]);
//                   if($subArray[$key] == $value){
//                         print_r($value);
//                        unset($array[$subKey]);
//                   }else{
//                         echo "string";
//                   }
//              return $array;

//              }

//         }


//         for ($i=0; $i < count($uniq); $i++) { 
//             $array = removeElementWithValue($result, "sku", $uniq[$i]);
//         }

// echo "<pre>";
// print_r($result);
// die;
        return $result;
    }

   public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    public function getOptions()
    {
        $res = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }

    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
