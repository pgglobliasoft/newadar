<?php
namespace Sttl\Inquiries\Model\ResourceModel\Groups;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'sttl_inquiries_groups_collection';
	protected $_eventObject = 'groups_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Sttl\Inquiries\Model\Groups', 'Sttl\Inquiries\Model\ResourceModel\Groups');
	}

}