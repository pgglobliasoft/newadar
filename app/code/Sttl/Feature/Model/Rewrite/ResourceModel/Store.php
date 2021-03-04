<?php

namespace Sttl\Feature\Model\Rewrite\ResourceModel;

class Store extends \Magento\Store\Model\ResourceModel\Store {

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object) {
        $table = $this->getTable('sttl_feature');
        $delete = $this->lookupFeatureIds($object->getId());
        if ($delete) {
            $where = ['feature_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        return parent::_beforeDelete($object);
    }

    public function lookupFeatureIds($storeId) {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
                        $this->getTable('sttl_feature_store'), 'feature_id'
                )->where(
                'store_id = ?', (int) $storeId
        );
        return $connection->fetchCol($select);
    }

}
