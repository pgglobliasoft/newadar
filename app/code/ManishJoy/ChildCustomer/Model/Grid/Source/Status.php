<?php
namespace ManishJoy\ChildCustomer\Model\Grid\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
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
        return [0 => 'Yes', 1 => 'No'];
    }
}