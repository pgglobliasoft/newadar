<?php

namespace DR\Gallery\Block\Adminhtml\Image;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'image_id';
        $this->_blockGroup = 'DR_Gallery';
        $this->_controller = 'adminhtml_image';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Document'));
        $this->buttonList->update('delete', 'label', __('Delete Document'));

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            -100
        );
    }

    /**
     * Get edit form container header text
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('dr_gallery_image')->getId()) {
            return __("Edit Document '%1'", $this->escapeHtml($this->coreRegistry->registry('dr_gallery_image')->getName()));
        } else {
            return __('New image2wbmp(image)');
        }
    }
}