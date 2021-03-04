<?php
namespace Vendor\Rules\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Vendor\Rules\Model\ResourceModel\Product');
    }
}