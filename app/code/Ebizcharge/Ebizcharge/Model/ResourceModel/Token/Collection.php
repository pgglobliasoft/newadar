<?php
/**
* Resource model which collects multiple 'Token' models.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Token;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ebizcharge\Ebizcharge\Model\Token', 'Ebizcharge\Ebizcharge\Model\ResourceModel\Token');
    }
}