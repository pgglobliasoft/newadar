<?php

namespace ManishJoy\ChildCustomer\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Parentname extends \Magento\Ui\Component\Listing\Columns\Column
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
        array $data = []
    ) {
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
                $parentname = $item['firstname'];
                if($parentname != ''){
                    $item['firstname'] = $parentname." ".$item['lastname'];
                }elseif($item['parent_id'] == 0){
                    $item['firstname'] = '<span class="grid-severity-notice"><span>It Self</span></span>';
                }
            }
        }

        return $dataSource;
    }
}