<?php

namespace DR\Gallery\Model\Image\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Category implements OptionSourceInterface
{
    const CATLOG = 1;
    const SWATCHCARD = 2;
    const IMAGELIBRARY = 3;
    const INVENTORYANDUPCFILES = 4;
    const LISTMAPPRICINGANDDISCOUNTFILES = 5;
    const AIOITEMSDATAFILES = 6;
    const DOCUMENTATIONPOLICIESANDMORE = 7;
    const SIZEFITGUIDE = 8;
	

    protected $options = null;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'label' => __('Catalog'),
                    'value' => static::CATLOG,
                ],
                [
                    'label' => __('Swatch Card'),
                    'value' => static::SWATCHCARD,
                ],
                [
                    'label' => __('Image Library'),
                    'value' => static::IMAGELIBRARY,
                ],
                [
                    'label' => __('Inventory and UPC files'),
                    'value' => static::INVENTORYANDUPCFILES,
                ],
                [
                    'label' => __('List/Map Pricing and Discount Files'),
                    'value' => static::LISTMAPPRICINGANDDISCOUNTFILES,
                ],
                [
                    'label' => __('AIO Items Data Files'),
                    'value' => static::AIOITEMSDATAFILES,
                ],
                [
                    'label' => __('Documentation,Policies and more'),
                    'value' => static::DOCUMENTATIONPOLICIESANDMORE,
                ],
                [
                    'label' => __('Size & Fit Guide'),
                    'value' => static::SIZEFITGUIDE,
                ]
            ];
        }

        return $this->options;
    }
}
