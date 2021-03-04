<?php
namespace ManishJoy\ChildCustomer\Model\ResourceModel;

class User extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	) {
		parent::__construct($context);
	}

	protected function _construct() {
		$this->_init('customer_entity', 'entity_id');
	}

}