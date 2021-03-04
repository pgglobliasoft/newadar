<?php

namespace Sttl\Collectionsilder\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Order extends \Magento\Ui\Component\Listing\Columns\Column
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
                $activestatus = $item['order'];
                if($activestatus){
                    $item['order'] = 'asc';
                }else{
                    $item['order'] = 'dsc';
                }
            }
        }

        return $dataSource;
    }
}