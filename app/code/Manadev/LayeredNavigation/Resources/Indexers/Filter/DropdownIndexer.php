<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers\Filter;

use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Zend_Db_Expr;

class DropdownIndexer extends AttributeIndexer
{
    protected function getIndexedFields($changes) {
        $db = $this->getConnection();

        if (empty($changes['load_defaults'])) {
            return array_merge(parent::getIndexedFields($changes), [
                'type' => new Zend_Db_Expr("'dropdown'"),
                'template' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`template`, ?)",
                    $this->configuration->getDefaultDropdownTemplate())),
            ]);
        }
        else {
            return array_merge(parent::getIndexedFields($changes), [
                'type' => new Zend_Db_Expr("'dropdown'"),
                'template' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultDropdownTemplate())),
            ]);
        }
    }

    protected function select($fields) {
        return parent::select($fields)->where(
            "`a`.`backend_type` IN('int', 'varchar', 'text') AND " .
            "(`a`.`source_model` IS NULL OR `a`.`source_model` = ?)",
            Table::class
        );
    }
}
