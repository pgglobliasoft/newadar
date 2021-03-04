<?php
namespace ManishJoy\ChildCustomer\Model;

class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

	const CACHE_TAG = 'under_child_customer';

	protected $_cacheTag = 'under_child_customer';

	protected $_eventPrefix = 'under_child_customer';

	protected function _construct() {
		$this->_init('ManishJoy\ChildCustomer\Model\ResourceModel\Post');
	}

	public function getIdentities() {
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues() {
		$values = [];
		return $values;
	}
}