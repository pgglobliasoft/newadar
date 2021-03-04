<?php
namespace ManishJoy\ChildCustomer\Model\ResourceModel\User;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	protected $_idFieldName = 'entity_id';

	protected $_eventPrefix = 'manishJoy_childCustomer_user_collection';

	protected $_eventObject = 'user_undercustomer_collection';

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
        $this->_init('ManishJoy\ChildCustomer\Model\User', 'ManishJoy\ChildCustomer\Model\ResourceModel\User');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    protected function _initSelect() {
        parent::_initSelect();
        $this->addFieldToFilter('admin_custom', 1);

        return $this;
    }

}