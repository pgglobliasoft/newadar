<?php

namespace Globala\Customapi\Model\Api;



class Options implements \Globala\Customapi\Api\OptionsInterface
{

 protected $optionsvalue;
 public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Eav\Model\Config $optionsvalue
    ) {
        $this->optionsvalue = $optionsvalue;
        $this->_customerSession = $session;
    }
    /**
     * get test Api data.
     *
     * @api
     *
     * @param string $id
     *
     * @return \Globala\Customapi\Model\Api
     */
    public function getApiData($id)
    { 
        if ($this->_customerSession->isLoggedIn()) {
            $attribute = $this->optionsvalue->getAttribute('catalog_product', 'size');
            $options = $attribute->getSource()->getAllOptions();
            $responce[]  = ["option" => $options,"customerdata" => 1];
            return $responce;
        } else {
            $attribute = $this->optionsvalue->getAttribute('catalog_product', 'size');
            $options = $attribute->getSource()->getAllOptions();
            $responce[]  = ["option" => $options,"customerdata" => 0];
            return $responce;
        }
    }
}