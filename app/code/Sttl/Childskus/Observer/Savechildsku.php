<?php

namespace Sttl\Childskus\Observer;

use Magento\Framework\Event\ObserverInterface;

class Savechildsku implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();  // you will get product object
        $_sku=$_product->getChild_sku(); // for sku

    }   
}