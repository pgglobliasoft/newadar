<?php
namespace Sttl\Materialpro\Model\ResourceModel\Post;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Sttl\Materialpro\Model\Post;
use Sttl\Materialpro\Model\ResourceModel\Post as RuleResource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'Sttl_Materialpro_post_collection';
	protected $_eventObject = 'Sttl_Materialpro';

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
        $this->_init('Sttl\Materialpro\Model\Post', 'Sttl\Materialpro\Model\ResourceModel\Post');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
	
    protected function _construct()
    {
        $this->_init(Post::class, RuleResource::class);
    }

}