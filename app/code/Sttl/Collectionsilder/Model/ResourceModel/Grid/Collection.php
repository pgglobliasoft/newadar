<?php
namespace Sttl\Collectionsilder\Model\ResourceModel\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'entity_id';
	protected $_eventPrefix = 'Sttl_Collectionsilder_post_collection';
	protected $_eventObject = 'collectionsilder_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */

	public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init('Sttl\Collectionsilder\Model\Grid', 'Sttl\Collectionsilder\Model\ResourceModel\Grid');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
	
	protected function _construct()
	{
		$this->_init('Sttl\Collectionsilder\Model\Grid', 'Sttl\Collectionsilder\Model\ResourceModel\Grid');
	}

	protected function _initSelect() {
        parent::_initSelect();



        $this->getSelect()->joinLeft(
            ['MWEB_InventoryStatus' => $this->getTable('MWEB_InventoryStatus')],
            'main_table.name = MWEB_InventoryStatus.Collection',
            ["ItemName","Style"]
        )->group('MWEB_InventoryStatus.Style');;

        $this->addFilterToMap('ItemName', 'MWEB_InventoryStatus.ItemName');
        return $this;
    }

}