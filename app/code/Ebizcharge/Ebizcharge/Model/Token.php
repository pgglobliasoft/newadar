<?php
/**
* Instantiates the model, and initializes the corresponding resource model.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Model;

class Token extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Ebizcharge\Ebizcharge\Model\ResourceModel\Token');
    }
}