<?php
namespace Vendor\Rules\Model\ResourceModel\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _consturt()
    {
        $this->_init('Vendor\Rules\Model\Product', 'Vendor\Rules\Model\ResourceModel\Product');
    }
}