<?php
namespace Sttl\Proupdated\Block\Adminhtml\Index\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

class Save extends Generic implements ButtonProviderInterface {
	/**
	 * Get button data
	 *
	 * @return array
	 */
	 public function getButtonData()
    {
        return [
            'label' => __('Save Announcement'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
	
}