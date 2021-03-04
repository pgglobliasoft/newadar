<?php
namespace Sttl\Proupdated\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Sttl\Proupdated\Model\Post as PostModel;
use Sttl\Proupdated\Model\ResourceModel\Post as PostResourceModel;

class Collection extends AbstractCollection {
	protected $_idFieldName = 'id';
	protected function _construct() {
		$this->_init(PostModel::class, postResourceModel::class);
	}
}