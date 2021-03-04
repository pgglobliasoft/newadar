<?php
namespace Sttl\Adaruniforms\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
/**
 * Class Ranges
 */
class Ranges extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('cardcode', ['label' => __('CardCode'), 'class' => 'required-entry']);
        $this->addColumn('recent_Orders', ['label' => __('Recent Order Limit'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
