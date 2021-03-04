<?php

namespace WeltPixel\Quickview\Controller\Catalog\Product;


use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$product = $objectManager->create('Magento\Catalog\Model\Product')->load('7151');
			echo "<pre>";
			print_r($product->getData());
    }
}

