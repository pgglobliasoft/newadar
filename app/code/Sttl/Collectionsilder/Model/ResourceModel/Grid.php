<?php
namespace Sttl\Collectionsilder\Model\ResourceModel;

class Grid extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	) {
		parent::__construct($context);
	}

	protected function _construct() {
		$this->_init('collection_silder', 'entity_id');
	}

}