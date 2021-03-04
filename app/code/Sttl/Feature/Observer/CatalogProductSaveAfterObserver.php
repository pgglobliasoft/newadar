<?php

namespace Sttl\Feature\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($product->getId()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fullActionName = $objectManager->get('Magento\Framework\App\Request\Http')->getFullActionName();
                if ($fullActionName != 'feature_feature_save') {
                    $productId = $product->getId();
                    $value = $product->getMgsFeature();
                    if ((int)$value) {
                        $featureCollection = $objectManager->create('Sttl\Feature\Model\Feature')->getCollection();
                        $featureCollection->addFieldToFilter('option_id', ['eq' => $value]);
                        if (count($featureCollection)) {
                            $p = $objectManager->create('Sttl\Feature\Model\Product');
                            $p->setFeatureId($featureCollection->getFirstItem()->getId());
                            $p->setProductId($productId);
                            $p->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }
}
