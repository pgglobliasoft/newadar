<?php
namespace ManishJoy\ChildCustomer\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	protected $_idFieldName = 'entity_id';

	protected $_eventPrefix = 'manishJoy_childCustomer_post_collection';

	protected $_eventObject = 'undercustomer_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('ManishJoy\ChildCustomer\Model\Post', 'ManishJoy\ChildCustomer\Model\ResourceModel\Post');
	}

	public function setcustomerFilter($parent_id = '') {

		$this->getSelect()->joinLeft(
			['au_customer_entity' => $this->getTable('au_customer_entity')],
			'main_table.c_id = au_customer_entity.entity_id',
			array('child_entity_id' => 'main_table.entity_id', 'au_customer_entity.*', 'main_table.*')
		)
		// if($parent_id)
			->where("main_table.parent_id =" . $parent_id);

		return $this;

		// return $this;
	}

}