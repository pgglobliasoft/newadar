<?php

namespace Sttl\Feature\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogAttributeSaveAfterObserver implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        try {
            $attribute = $observer->getEvent()->getAttribute();
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == 'feature') {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $attributeModel = $objectManager->create('Magento\Catalog\Api\ProductAttributeRepositoryInterface')->get($attributeCode);
                $options = $attributeModel->getOptions();
                foreach ($options as $option) {
                    $value = $option->getValue();
                    if ($value) {
                        $featureCollection = $objectManager->create('Sttl\Feature\Model\Feature')->getCollection();
                        $featureCollection->addFieldToFilter('option_id', ['eq' => $value]);
                        if (count($featureCollection)) {
                            $feature = $featureCollection->getFirstItem();
                            $feature->setName($option->getLabel());
                            $feature->setUrlKey($objectManager->get('Magento\Catalog\Model\Product\Url')->formatUrlKey($option->getLabel()));
                            $feature->save();
                        } else {
                            $feature = $objectManager->create('Sttl\Feature\Model\Feature');
                            $feature->setName($option->getLabel());
                            $feature->setUrlKey($objectManager->get('Magento\Catalog\Model\Product\Url')->formatUrlKey($option->getLabel()));
                            $feature->setOptionId($value);
                            $feature->save();
                        }
                    }
                }
                $options = $attribute->getOption();
                $deletes = $options['delete'];
                if (count($deletes)) {
                    foreach ($deletes as $optionId => $value) {
                        if ((int)$value == 1) {
                            $featureCollection = $objectManager->create('Sttl\Feature\Model\Feature')->getCollection();
                            $featureCollection->addFieldToFilter('option_id', ['eq' => $optionId]);
                            if (count($featureCollection)) {
                                $feature = $featureCollection->getFirstItem();
                                $feature->delete();
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
        return $this;
    }
}
