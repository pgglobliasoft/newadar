<?php


namespace Sttl\Importproductbrand\Api\Data;

interface ImportproductbrandSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Importproductbrand list.
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface[]
     */
    public function getItems();

    /**
     * Set brand_id list.
     * @param \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
