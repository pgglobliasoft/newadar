<?php
namespace Vendor\Rules\Model\ResourceModel\Material;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\Rules\Model\Material as MaterialModel;
use Vendor\Rules\Model\ResourceModel\Material as MaterialResourceModel;

class Collection extends AbstractCollection {

	protected $_idFieldName = 'id';

	protected function _construct() {

		$this->_init(MaterialModel::class, MaterialResourceModel::class);
	}
}