<?php
namespace Sttl\Collectionsilder\Model;
 
use Sttl\Collectionsilder\Model\ResourceModel\Post\CollectionFactory;
 
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
        $this->loadedData = array();
        foreach ($items as $contact) {
            // echo "<pre>";
            // print_r($contact->getData());die;
            $itemData = $contact->getData();
             if (isset($itemData['image'])) {
                    $imageName = explode('/', $itemData['image']);
                    // print_r($imageName);die;
                    $itemData['image'] = [
                        [
                            'name' => @$imageName[9],
                            'url' => $itemData['image'],
                        ],
                    ];
                }
            $itemData['allow_customer'] = json_decode($itemData["allow_customer"],true);
            $itemData['categories'] = json_decode($itemData["categories"],true);
            $itemData['hide_field'] = true;
            $this->loadedData[$contact->getId()]['collections'] = $itemData;
        }
        // echo "<pre>";
        // print_r($this->loadedData);die();

        return $this->loadedData;

    }
    public function getMeta()
    {   
        $meta = parent::getMeta();
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $id = $request->getParam('id');
        if($id){
            $meta['collections']['children']['categories']['arguments']['data']['config']['visible'] = 1;
        }else{
            $meta['collections']['children']['categories']['arguments']['data']['config']['visible'] = 0;
        }    
        return $meta;
    }
}