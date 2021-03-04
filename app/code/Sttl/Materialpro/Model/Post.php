<?php
namespace Sttl\Materialpro\Model;

use Sttl\Materialpro\Model\ResourceModel\Post as RuleResource;

class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

	const RULE_ID = 'id';

	const CACHE_TAG = 'Sttl_Materialpro';

	protected $_cacheTag = self::CACHE_TAG;

	protected $_eventPrefix = 'Sttl_Materialpro';

	protected $_eventObject = 'rule';
 

	protected function _construct() {
		$this->_init(RuleResource::class);
	}

	public function getIdentities() {
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues() {
		$values = [];
		return $values;
	}
}