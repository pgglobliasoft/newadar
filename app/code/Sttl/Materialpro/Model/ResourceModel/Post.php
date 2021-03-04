<?php
namespace Sttl\Materialpro\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;

class Post extends AbstractResource {

	
    protected $_isPkAutoIncrement = false;

	protected function _construct() {
		$this->_init('Materialpro_rules', 'id');
	}

}