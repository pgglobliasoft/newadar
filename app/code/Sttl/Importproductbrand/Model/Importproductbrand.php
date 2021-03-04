<?php


namespace Sttl\Importproductbrand\Model;

use Magento\Framework\Api\DataObjectHelper;
use Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface;
use Sttl\Importproductbrand\Api\Data\ImportproductbrandInterfaceFactory;

class Importproductbrand extends \Magento\Framework\Model\AbstractModel
{

    protected $importproductbrandDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'sttl_importproductbrand_importproductbrand';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ImportproductbrandInterfaceFactory $importproductbrandDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand $resource
     * @param \Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ImportproductbrandInterfaceFactory $importproductbrandDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand $resource,
        \Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand\Collection $resourceCollection,
        array $data = []
    ) {
        $this->importproductbrandDataFactory = $importproductbrandDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve importproductbrand model with importproductbrand data
     * @return ImportproductbrandInterface
     */
    public function getDataModel()
    {
        $importproductbrandData = $this->getData();
        
        $importproductbrandDataObject = $this->importproductbrandDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $importproductbrandDataObject,
            $importproductbrandData,
            ImportproductbrandInterface::class
        );
        
        return $importproductbrandDataObject;
    }
}
