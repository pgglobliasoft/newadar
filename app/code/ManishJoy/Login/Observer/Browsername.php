<?php
/**
* Copyright � 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

namespace ManishJoy\Login\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class Browsername implements ObserverInterface
{

    /**
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(EventObserver $observer)
    {
     
    
        $customer123 = $observer->getEvent()->getData("visitor");       
       
       

    }
}