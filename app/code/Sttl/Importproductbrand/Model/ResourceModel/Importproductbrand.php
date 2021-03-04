<?php


namespace Sttl\Importproductbrand\Model\ResourceModel;

class Importproductbrand extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sttl_importproductbrand_importproductbrand', 'importproductbrand_id');
    }
}
