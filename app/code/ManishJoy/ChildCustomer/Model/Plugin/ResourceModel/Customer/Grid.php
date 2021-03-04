<?php
namespace ManishJoy\ChildCustomer\Model\Plugin\ResourceModel\Customer;
 
class Grid
{
    public static $table = 'customer_grid_flat';
    public static $leftJoinTable = 'under_child_customer';
 
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
 
            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.parent_id = main_table.entity_id",
                    [
                        'c_id' => 'co.c_id'
                    ]
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where)->group('main_table.entity_id');
        }

        
        return $collection;
    }
}