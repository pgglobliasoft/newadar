<?php

namespace ManishJoy\ChildCustomer\Block\Adminhtml\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Permission extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $magepermmistion = $row->getData("permission");
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

                $html .= "<ul id='myUL'>";
                if(array_key_exists($key,$val_base)){
                    $html .= "<li><span class='caret' style='white-space: nowrap;'>".$val_base[$key]."</span>";
                }else{
                    $html .= "<li><span class='caret' style='white-space: nowrap;'>".$key."</span>";
                }

                $html .= "<ul class='nested'>";

                foreach ($parent_perm as $child_perm_key => $child_perm_value) {
                    // $temp_per = [$child_perm_value];
                    if(array_key_exists($child_perm_value,$val_base)){
                        $html .= "<li style='white-space: nowrap;'>".$val_base[$child_perm_value]."</li>";
                    }else{
                        $html .= "<li style='white-space: nowrap;'>".$child_perm_value."</li>";
                    }
                }
                $html .= "</ul>";
                $html .= "</li></ul>";

            }
        }
        return $html;
    }

    function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ;
    }
}