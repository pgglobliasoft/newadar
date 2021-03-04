<?php
namespace ManishJoy\ChildCustomer\Model\Grid\Source;
class Permission implements \Magento\Framework\Option\ArrayInterface
{
    protected $collectionFactory;

    public function __construct(
        \ManishJoy\ChildCustomer\Model\ResourceModel\Grid\CollectionFactory $collectionFactory
    ) {
        $this->_permissionCollectionFactory = $collectionFactory;
 
    }
    public function toOptionArray()
    {
 
        $collection = $this->_permissionCollectionFactory->create();
        $collection->addFieldToSelect('permission');
        $options = [];

        $count = 0;

        foreach ($collection as $row) {
            $magepermmistion = $row->getData('permission');
            $html = '';

            $per_actual = '';
            
            if($this->is_valid_json($magepermmistion)){
                $perm = json_decode($magepermmistion);
                $temp_per = array();
                $val_base = array(
                    "view_customer" => "View Customer Statement",
                    "payment_info" => "Manage Payment Info",
                    "shipping_info" => "Manage Shipping Addresses",
                    "user_manage" => "Manage User Management",
                    "place_oder" => "Place Orders",
                    "view_order" => "View Order History",
                    "pay_invoice" => "Pay Invoices",
                    "view_invoice" => "View Invoice History",
                    "view_catalog" => "View Catalogs, Image Library",
                    "view_inventory" => "View Inventory files",
                    "view_product" => "View Price List and Product Data files",
                    "accountinfo" => "Account Info",
                    "order" => "Orders",
                    "invoice" => "View & Pay Invoices",
                    "downlaod_library" => "Downlaod Library" 
                );

                foreach ($perm as $key => $parent_perm) {
                    foreach ($parent_perm as $child_perm_key => $child_perm_value) {
                        if($count != 0){
                            $tml_valexist = 0;
                            foreach ($options as $options_value) {
                                if($options_value['value'] == $child_perm_value){
                                    $tml_valexist = 1;
                                }
                            }
                            if(array_key_exists($child_perm_value,$val_base) && $tml_valexist == 0){
                                $options[] = ['label' => $val_base[$child_perm_value], 'value' => $child_perm_value];
                                $count++;
                            }elseif($tml_valexist == 0){
                                $options[] = ['label' => $child_perm_value, 'value' => $child_perm_value];
                                $count++;
                            }
                        }else{
                            if(array_key_exists($child_perm_value,$val_base)){
                                $options[] = ['label' => $val_base[$child_perm_value], 'value' => $child_perm_value];
                                $count++;
                            }else{
                                $options[] = ['label' => $child_perm_value, 'value' => $child_perm_value];
                                $count++;
                            }
                        }
                    }

                }
            }
        }


        return $options;
    }

    public static function getOptionArray()
    {
        return [1 => 'Active', 0 => 'inActive'];
    }

    function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ;
    }
}