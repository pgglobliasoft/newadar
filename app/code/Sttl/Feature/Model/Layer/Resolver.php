<?php

namespace Sttl\Feature\Model\Layer;

class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{
    public function get()
    {
        if (!isset($this->layer)) {
            $this->layer = $this->objectManager->create($this->layersPool['feature']);
        }
        return $this->layer;
    }
}