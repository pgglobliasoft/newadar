<?php

namespace Sttl\Feature\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Sttl\Feature\Model\Resource\Product');
    }
}
