<?php
namespace Sttl\Proupdated\Model\ResourceModel\Read;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Sttl\Proupdated\Model\Read as ReadModel;
use Sttl\Proupdated\Model\ResourceModel\Read as ReadResourceModel;

class Collection extends AbstractCollection {
	protected $_idFieldName = 'id';
	protected function _construct() {
		$this->_init(ReadModel::class, ReadResourceModel::class);
	}
}