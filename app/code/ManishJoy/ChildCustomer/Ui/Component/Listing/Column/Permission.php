<?php

namespace ManishJoy\ChildCustomer\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Permission extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
         \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $html = '';
                $per_actual = '';

                if($this->is_valid_json($item['permission'])){
                    $perm = json_decode($item['permission']);
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

                        $html .= "<ul>";
                        if(array_key_exists($key,$val_base)){
                            $html .= "<li style='list-style-position:inside; white-space: nowrap;'><span class='caret'>".$val_base[$key]."</span>";
                        }else{
                            $html .= "<li style='list-style-position:inside; white-space: nowrap;'><span class='caret'>".$key."</span>";
                        }

                        $html .= "<ul style='margin-left:1rem; padding-left:1rem;'>";

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

                $item['permission'] = $html;

            }
        }

        return $dataSource;
    }


    function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ;
    }
}