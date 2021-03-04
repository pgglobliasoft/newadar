<?php

namespace Sttl\Brand\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Sttl\Brand\Model\Resource\Product');
    }
}
