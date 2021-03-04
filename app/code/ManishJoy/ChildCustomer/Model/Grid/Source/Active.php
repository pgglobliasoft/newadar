<?php
namespace ManishJoy\ChildCustomer\Model\Grid\Source;

class Active implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $emp;

    public function __construct(\ManishJoy\ChildCustomer\Model\Grid $emp)
    {
        $this->emp = $emp;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public static function getOptionArray()
    {
        return [1 => 'Online', 0 => 'Offline'];
    }
}