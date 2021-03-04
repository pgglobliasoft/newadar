<?php
namespace Sttl\Childskus\Model;
 
use Sttl\Childskus\Model\ResourceModel\Post\CollectionFactory;
 
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $employeeCollectionFactory
     * @param array $meta
     * @param array $data
     */
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $employeeCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $employeeCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
       if (isset($this->loadedData)) {
        
            return $this->loadedData;
        }
        
        $items = $this->collection->getItems();
        foreach ($items as $blog) {
            $itemData = $blog->getData();
            $this->loadedData[$blog->getId()]['collections'] = $itemData;
        }
      
        return $this->loadedData;
    }
}