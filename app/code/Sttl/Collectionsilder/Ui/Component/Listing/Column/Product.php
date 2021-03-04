<?php

namespace Sttl\Collectionsilder\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Product extends \Magento\Ui\Component\Listing\Columns\Column
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
                $products = json_decode($item['product_collection'], true);
                $html = '';
                $html.= "<ul>";
                 foreach ($products as $key => $value) {
                    $html.= "<li style='list-style-position:inside; white-space: nowrap;'><span class='caret'>".$key."</span><ul style='margin-left:1rem; padding-left:1rem;'>";
                    foreach ($value as $key => $gname) {

                        $html .= "<li style='white-space: nowrap;'>".$gname['Style']."</li>";
                    }   
                     $html.= "</ul></li>";
                }
                $html.= "</ul></li></ul>";   
            $item['product_collection'] = $html;
            }
        }
        return $dataSource;
    }
}