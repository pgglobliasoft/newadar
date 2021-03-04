<?php

namespace Sttl\Feature\Block\Adminhtml\Feature;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'feature_id';
        $this->_controller = 'adminhtml_feature';
        $this->_blockGroup = 'Sttl_Feature';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
    }

    public function getHeaderText()
    {
        $feature = $this->_coreRegistry->registry('current_feature');
        if ($feature->getId()) {
            return __("Edit Feature '%1'", $this->escapeHtml($feature->getName()));
        } else {
            return __('New New Feature');
        }
    }
}
