<?php

namespace Vendor\Rules\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Sttl\Adaruniforms\Helper\Sap;

class ProductList implements ArrayInterface
{

    public function __construct( Sap $saphelper )
    {
        $this->saphelper = $saphelper;
    }

  public function toOptionArray()
    {
        $collections = $this->saphelper->getproductsku();
        $this->_options[] = [   'label' => 'Select Products ',
                            'value' => '',];
          

          foreach ($collections as $manufacturer) {
                    $this->_options[] = [
                            'label' => __($manufacturer['style']),
                            'value' => $manufacturer['style'],
                        ];
                }


        return $this->_options;
    }
 

}