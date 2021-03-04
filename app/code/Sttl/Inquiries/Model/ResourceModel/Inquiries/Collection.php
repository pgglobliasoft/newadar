<?php
namespace Sttl\Inquiries\Model\ResourceModel\Inquiries;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'sttl_inquiries_inquiries_collection';
	protected $_eventObject = 'inquiries_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Sttl\Inquiries\Model\Inquiries', 'Sttl\Inquiries\Model\ResourceModel\Inquiries');
	}

}