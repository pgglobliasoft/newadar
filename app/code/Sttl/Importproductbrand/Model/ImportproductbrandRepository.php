<?php


namespace Sttl\Importproductbrand\Model;

use Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand\CollectionFactory as ImportproductbrandCollectionFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Sttl\Importproductbrand\Api\Data\ImportproductbrandSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Sttl\Importproductbrand\Api\Data\ImportproductbrandInterfaceFactory;
use Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand as ResourceImportproductbrand;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Sttl\Importproductbrand\Api\ImportproductbrandRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class ImportproductbrandRepository implements ImportproductbrandRepositoryInterface
{

    protected $dataImportproductbrandFactory;

    protected $dataObjectHelper;

    private $collectionProcessor;

    protected $dataObjectProcessor;

    protected $importproductbrandCollectionFactory;

    protected $resource;

    protected $importproductbrandFactory;

    protected $extensibleDataObjectConverter;
    protected $searchResultsFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;


    /**
     * @param ResourceImportproductbrand $resource
     * @param ImportproductbrandFactory $importproductbrandFactory
     * @param ImportproductbrandInterfaceFactory $dataImportproductbrandFactory
     * @param ImportproductbrandCollectionFactory $importproductbrandCollectionFactory
     * @param ImportproductbrandSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceImportproductbrand $resource,
        ImportproductbrandFactory $importproductbrandFactory,
        ImportproductbrandInterfaceFactory $dataImportproductbrandFactory,
        ImportproductbrandCollectionFactory $importproductbrandCollectionFactory,
        ImportproductbrandSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->importproductbrandFactory = $importproductbrandFactory;
        $this->importproductbrandCollectionFactory = $importproductbrandCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataImportproductbrandFactory = $dataImportproductbrandFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface $importproductbrand
    ) {
        /* if (empty($importproductbrand->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $importproductbrand->setStoreId($storeId);
        } */
        
        $importproductbrandData = $this->extensibleDataObjectConverter->toNestedArray(
            $importproductbrand,
            [],
            \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface::class
        );
        
        $importproductbrandModel = $this->importproductbrandFactory->create()->setData($importproductbrandData);
        
        try {
            $this->resource->save($importproductbrandModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the importproductbrand: %1',
                $exception->getMessage()
            ));
        }
        return $importproductbrandModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($importproductbrandId)
    {
        $importproductbrand = $this->importproductbrandFactory->create();
        $this->resource->load($importproductbrand, $importproductbrandId);
        if (!$importproductbrand->getId()) {
            throw new NoSuchEntityException(__('Importproductbrand with id "%1" does not exist.', $importproductbrandId));
        }
        return $importproductbrand->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->importproductbrandCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface $importproductbrand
    ) {
        try {
            $importproductbrandModel = $this->importproductbrandFactory->create();
            $this->resource->load($importproductbrandModel, $importproductbrand->getImportproductbrandId());
            $this->resource->delete($importproductbrandModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Importproductbrand: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($importproductbrandId)
    {
        return $this->delete($this->getById($importproductbrandId));
    }
}
