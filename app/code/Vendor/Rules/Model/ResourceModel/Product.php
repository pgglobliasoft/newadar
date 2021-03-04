<?php
namespace Vendor\Rules\Model\ResourceModel;

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('configurable_product','id');
    }
}