<?php
namespace Vendor\Rules\Model;

use Magento\Framework\Model\AbstractModel;
use Vendor\Rules\Model\ResourceModel\Material as MaterialResourceModel;

class Material extends \Magento\Framework\Model\AbstractModel {

	protected function _construct() {
		$this->_init(MaterialResourceModel::class);
	}
}