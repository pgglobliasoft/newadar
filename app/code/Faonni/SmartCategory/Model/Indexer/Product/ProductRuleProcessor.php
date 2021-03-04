<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Indexer\Product;

use Magento\Framework\Indexer\AbstractProcessor;

/**
 * Product rule processor
 */
class ProductRuleProcessor extends AbstractProcessor
{
    /**
     * Indexer id
     */
    const INDEXER_ID = 'smartcategory_product';

    /**
     * Run Row reindex
     *
     * @param int $id
     * @param bool $forceReindex
     * @return void
     */
    public function reindexRow($id, $forceReindex = false)
    {
        // echo $id; echo '-'.$this->isIndexerScheduled(); 
        if (!$forceReindex && $this->isIndexerScheduled()) {
            // echo '31 process';
            $this->getIndexer()->invalidate();
            return;
        }
        parent::reindexRow($id, $forceReindex);
    }

    /**
     * Run List reindex
     *
     * @param int[] $ids
     * @param bool $forceReindex
     * @return void
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
        }
        parent::reindexList($ids, $forceReindex);
    }
}
