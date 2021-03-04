<?php
namespace Sttl\Proupdated\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Read extends AbstractDb {
	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('sttl_note_read', 'id');
	}
}
