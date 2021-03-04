<?php

namespace Vendor\Rules\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Material extends AbstractDb {

	/**
	 * Initialize main table and table id field
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('materail_product', 'id');
	}
}