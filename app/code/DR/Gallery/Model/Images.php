<?php

namespace DR\Gallery\Model;

class Images extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
    {
        parent::_construct();
        $this->_init('DR\Gallery\Model\ResourceModel\Images');
    }
}
 