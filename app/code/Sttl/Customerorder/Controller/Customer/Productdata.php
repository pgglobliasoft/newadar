<?php
namespace Sttl\Customerorder\Controller\Customer;


class Productdata extends \Magento\Framework\App\Action\Action
{
     protected $_productCollectionFactory;
    protected $resultJsonFactory;
    protected $_productVisibility;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productVisibility = $productVisibility;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute() {
        $resultJson = $this->resultJsonFactory->create();
        $collection = $this->_productCollectionFactory->create();
          $collection->addAttributeToSelect('name')->addAttributeToSort('sku', 'ASC');
        $result = [];
        foreach ($collection as $product) {
            if($product->getTypeId() == "configurable"){
                $result[] = [
                    'sku'      => $product->getSku(),
                    'name'        => $product->getName(),
                ];
            }
        }
        // echo count($result);die;
        return $resultJson->setData($result);
    }
}
