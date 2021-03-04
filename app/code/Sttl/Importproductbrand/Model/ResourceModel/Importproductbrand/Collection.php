<?php


namespace Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

        protected $_idFieldName = 'importproductbrand_id';
        protected $_eventPrefix = 'sttl_importproductbrand_importproductbrand';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Sttl\Importproductbrand\Model\Importproductbrand::class,
            \Sttl\Importproductbrand\Model\ResourceModel\Importproductbrand::class
        );
    }
}
