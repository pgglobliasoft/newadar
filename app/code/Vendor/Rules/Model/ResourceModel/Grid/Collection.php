<?php

namespace Vendor\Rules\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            'Vendor\Rules\Model\Grid',
            'Vendor\Rules\Model\ResourceModel\Grid'
        );
    }
}
