<?php

namespace Vendor\Rules\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Sttl\Adaruniforms\Helper\Sap;

class CategoryList implements ArrayInterface
{

    public function __construct( Sap $saphelper )
    {
        $this->saphelper = $saphelper;
    }

  public function toOptionArray()
    {
        $collections = $this->saphelper->getproductsku();
        // $this->_options[] = [   'label' => 'Select Products ',
        //                     'value' => '',];
        $this->_options= 
                [
                    [
                        'label' => __('Select Category'),
                        'value' => '',
                    ],
                    [
                        'label' => __('Catalogs'),
                        'value' => 'Catalogs',
                    ],
                    [
                        'label' => __('Signage'),
                        'value' => 'Signage',
                    ],
                    [
                        'label' => __('Marketing items'),
                        'value' => 'Marketing items',
                    ]
                ];
        return $this->_options;
    }
 

}