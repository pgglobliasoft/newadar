<?php

namespace ManishJoy\ChildCustomer\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class ChildCustomer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
         \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \ManishJoy\ChildCustomer\Model\ResourceModel\Grid\CollectionFactory $collectionFactory
    ) {
        $this->_permissionCollectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }   

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $collection = $this->_permissionCollectionFactory->create()->addFieldToFilter('parent_id',$item['entity_id']);
                // print_r($collection->getData());die;
                $html = "<ul>";
                foreach ($collection as $key => $value) {
                        if($value['email']){
                            $html .= "<li style='list-style-position:inside; white-space: nowrap;'><span class='caret'>".$value['email']."</span>";
                        }
                }
                $html .= "</ul>";
                $item['child_customer'] = $html;
            }
        }
        return $dataSource;
    }
}


