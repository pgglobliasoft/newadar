<?php

namespace Sttl\Brand\Model\Resource\Brand;

use \Sttl\Brand\Model\Resource\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'brand_id';
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('Sttl\Brand\Model\Brand', 'Sttl\Brand\Model\Resource\Brand');
        $this->_map['fields']['brand_id'] = 'main_table.brand_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];
        foreach ($this as $item) {
            $identifier = $item->getData('url_key');
            $data['value'] = $identifier;
            $data['label'] = $item->getData('name');
            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getData('brand_id');
            } else {
                $existingIdentifiers[] = $identifier;
            }
            $res[] = $data;
        }
        return $res;
    }

    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    protected function _afterLoad()
    {
        $this->performAfterLoad('sttl_brand_store', 'brand_id');
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('sttl_brand_store', 'brand_id');
    }
}
