<?php
namespace Sttl\Customerorder\Controller\Customer;

class Searchproductdata extends \Magento\Framework\App\Action\Action
{
     protected $_productCollectionFactory;
    protected $resultJsonFactory;
    private $cacheId = 'searchprodata';

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->_productCollectionFactory = $productCollectionFactory; 
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cache = $cache;
        parent::__construct($context);
    }

    public function execute() {
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->cache->load($this->cacheId);
        if (!$data) {
                $collection = $this->_productCollectionFactory->create();   
                  $collection->addAttributeToSelect('name')->addAttributeToSort('sku', 'ASC')->addAttributeToSelect('color');
                  // $collection->groupByAttribute('color');
                $result = [];
                foreach ($collection as $product) {

                    if($product->getTypeId() == "configurable"){
                        $result[] = [
                            'sku'      => $product->getSku(),
                        ];
                    }
                    $color[] = $product->getAttributeText('color');
                }
                $cor = array_unique($color);
                foreach ($cor as $key => $value) {
                    if($value != ''){
                        $color1[] = [ "color" => $value,];
                    }
                }
                $response = [ "result" => $result , "color" => $color1 ];
                $data = json_encode($response,true);
                $this->cache->save($data,$this->cacheId);
        }else{
            $response = json_decode($data,true);
        }
        return $resultJson->setData($response);
    }
}