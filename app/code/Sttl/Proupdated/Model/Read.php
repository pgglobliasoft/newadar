<?php
namespace Sttl\Proupdated\Model;

use Magento\Framework\Model\AbstractModel;
use Sttl\Proupdated\Model\ResourceModel\Read as ReadResourceModel;

class Read extends \Magento\Framework\Model\AbstractModel {

	protected function _construct() {

		$this->_init(ReadResourceModel::class);
	}
}