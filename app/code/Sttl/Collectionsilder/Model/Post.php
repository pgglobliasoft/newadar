<?php
namespace Sttl\Collectionsilder\Model;

class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

	const CACHE_TAG = 'collection_silder';

	protected $_cacheTag = 'collection_silder';

	protected $_eventPrefix = 'collection_silder';

	protected function _construct() {
		$this->_init('Sttl\Collectionsilder\Model\ResourceModel\Post');
	}

	public function getIdentities() {
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues() {
		$values = [];
		return $values;
	}
}