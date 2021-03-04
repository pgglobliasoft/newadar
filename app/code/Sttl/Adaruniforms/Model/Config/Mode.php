<?php

namespace Sttl\Adaruniforms\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    const LIVE = 1;
    const SENDBOX = 2;

    public function toOptionArray()
    {
        $options = [
            self::LIVE => __('LIVE'),
            self::SENDBOX => __('SENDBOX')
        ];
        return $options;
    }

    public static function getAvailableStatuses()
    {
        return [
            self::LIVE => __('LIVE')
            , self::SENDBOX => __('SENDBOX'),
        ];
    }
}
