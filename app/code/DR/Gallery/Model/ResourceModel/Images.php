<?php

namespace DR\Gallery\Model\ResourceModel;

use DR\Gallery\Api\Data\ImageInterface;

class Images extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('dr_gallery_image', ImageInterface::ID);
    }
}
