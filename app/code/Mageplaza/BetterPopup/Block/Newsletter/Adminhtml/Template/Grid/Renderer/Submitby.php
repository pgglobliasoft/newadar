<?php
namespace Mageplaza\BetterPopup\Block\Newsletter\Adminhtml\Template\Grid\Renderer;
use Magento\Framework\DataObject;
class Submitby extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        return ($row->getData('submitby')!=''?$row->getData('submitby'):'----');
    }
}