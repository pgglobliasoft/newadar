<?php

namespace Sttl\Feature\Model\Resource;

class Feature extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_store = null;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init('sttl_feature', 'feature_id');
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['feature_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('sttl_feature_product'), $condition);
        $this->getConnection()->delete($this->getTable('sttl_feature_store'), $condition);
        return parent::_beforeDelete($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('sttl_feature_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['feature_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['feature_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
		
		// Assign Products to item
		if($object->getProductIds()){
			$where = ['feature_id = ?' => (int)$object->getId()];
            $this->getConnection()->delete($this->getTable('sttl_feature_product'), $where);
			
			$productData = [];
			$productIds = (array)$object->getProductIds();
			if(count($productIds)>0){
				foreach($productIds as $productId => $pos){
					$position = $pos['position'];
					if($position==''){
						$position = 0;
					}
					$productData[] = ['feature_id' => (int)$object->getId(), 'product_id' => $productId, 'position' => $position];
				}
				
				$this->getConnection()->insertMultiple($this->getTable('sttl_feature_product'), $productData);
			}
		}
		
        return parent::_afterSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'url_key';
        }
        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['sttl_feature_store' => $this->getTable('sttl_feature_store')],
                $this->getMainTable() . '.feature_id = sttl_feature_store.feature_id',
                []
            )->where(
                'status = ?',
                1
            )->where(
                'sttl_feature_store.store_id IN (?)',
                $storeIds
            )->order(
                'sttl_feature_store.store_id DESC'
            )->limit(
                1
            );
        }
        return $select;
    }

    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('sttl_feature_store')],
            'cp.feature_id = cps.feature_id',
            []
        )->where(
            'cp.url_key = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $store
        );
        if (!is_null($isActive)) {
            $select->where('cp.status = ?', $isActive);
        }
        return $select;
    }

    protected function isNumericFeatureUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }

    protected function isValidFeatureUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
    }

    public function checkIdentifier($urlKey, $storeId)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($$urlKey, $stores, 1);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)->columns('cp.feature_id')->order('cps.store_id DESC')->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    public function lookupStoreIds($featureId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('sttl_feature_store'),
            'store_id'
        )->where(
            'feature_id = ?',
            (int)$featureId
        );
        return $connection->fetchCol($select);
    }

    public function lookupProductIds($featureId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('sttl_feature_product'),
            'product_id'
        )->where(
            'feature_id = ?',
            (int)$featureId
        );
        return $connection->fetchCol($select);
    }
}
