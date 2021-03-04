<?php
namespace Sttl\Childskus\Model;

class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

	const CACHE_TAG = 'childskus';

	protected $_cacheTag = 'childskus';

	protected $_eventPrefix = 'childskus';

	protected function _construct() {
		$this->_init('Sttl\Childskus\Model\ResourceModel\Post');
	}

	public function getIdentities() {
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues() {
		$values = [];
		return $values;
	}
}