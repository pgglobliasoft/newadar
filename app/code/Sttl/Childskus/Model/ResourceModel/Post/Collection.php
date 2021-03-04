<?php
namespace Sttl\Childskus\Model\ResourceModel\Post;

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
	protected $_eventPrefix = 'Sttl_Childskus_post_collection';
	protected $_eventObject = 'childskus';

	/**
	 * Define resource model
	 *
	 * @return void
	 */

	// public function __construct(
 //        EntityFactoryInterface $entityFactory,
 //        LoggerInterface $logger,
 //        FetchStrategyInterface $fetchStrategy,
 //        ManagerInterface $eventManager,
 //        StoreManagerInterface $storeManager,
 //        AdapterInterface $connection = null,
 //        AbstractDb $resource = null
 //    ) {
 //        $this->_init('Sttl\Childskus\Model\Post', 'Sttl\Childskus\Model\ResourceModel\Post');
 //        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
 //        $this->storeManager = $storeManager;
 //    }
	
	protected function _construct()
	{
		$this->_init('Sttl\Childskus\Model\Post', 'Sttl\Childskus\Model\ResourceModel\Post');
	}

}