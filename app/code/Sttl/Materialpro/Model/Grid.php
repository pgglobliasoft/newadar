<?php
namespace Sttl\Materialpro\Model;

class Grid extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

	const CACHE_TAG = 'Sttl_Materialpro';

	protected $_cacheTag = 'Sttl_Materialpro';

	protected $_eventPrefix = 'Sttl_Materialpro';

	protected function _construct() {
		$this->_init('Sttl\Materialpro\Model\ResourceModel\Grid');
	}

	public function getIdentities() {
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues() {
		$values = [];
		return $values;
	}
}