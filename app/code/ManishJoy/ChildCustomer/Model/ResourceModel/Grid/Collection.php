<?php
namespace ManishJoy\ChildCustomer\Model\ResourceModel\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	protected $_idFieldName = 'entity_id';

	protected $_eventPrefix = 'manishJoy_childCustomer_post_collection';

	protected $_eventObject = 'undercustomer_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('ManishJoy\ChildCustomer\Model\Post', 'ManishJoy\ChildCustomer\Model\ResourceModel\Post');
	}

	public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init('ManishJoy\ChildCustomer\Model\Grid', 'ManishJoy\ChildCustomer\Model\ResourceModel\Grid');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    protected function _initSelect() {
        parent::_initSelect();



        $this->getSelect()->joinLeft(
            ['au_customer_entity' => $this->getTable('au_customer_entity')],
            'main_table.parent_id = au_customer_entity.entity_id',
            ["firstname","lastname","parent_email" => "au_customer_entity.email"]
        )->joinLeft(
            ['au_customer' => $this->getTable('au_customer_entity')],
            'main_table.c_id = au_customer.entity_id',
            ["email"]
        );

        $this->addFilterToMap('firstname', 'au_customer_entity.firstname');
        $this->addFilterToMap('email', 'au_customer.firstname');
        return $this;
    }

}