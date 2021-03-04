<?php

namespace DR\Gallery\Model\ResourceModel\Image;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collections extends AbstractCollection
{
    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('DR\Gallery\Model\Images','DR\Gallery\Model\ResourceModel\Images');
    }

   
}