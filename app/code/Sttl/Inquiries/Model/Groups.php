<?php
namespace Sttl\Inquiries\Model;
class Groups extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'sttl_inquiries_groups';

	protected $_cacheTag = 'sttl_inquiries_groups';

	protected $_eventPrefix = 'sttl_inquiries_groups';

	protected function _construct()
	{
		$this->_init('Sttl\Inquiries\Model\ResourceModel\Groups');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}