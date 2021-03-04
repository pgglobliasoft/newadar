<?php
namespace Sttl\Inquiries\Model;
class Inquiries extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'sttl_inquiries_inquiries';

	protected $_cacheTag = 'sttl_inquiries_inquiries';

	protected $_eventPrefix = 'sttl_inquiries_inquiries';

	protected function _construct()
	{
		$this->_init('Sttl\Inquiries\Model\ResourceModel\Inquiries');
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