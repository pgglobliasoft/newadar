<?php
namespace Mageplaza\BetterPopup\Block\Newsletter\Adminhtml\Template\Grid\Renderer;
use Magento\Framework\DataObject;
class Createddate extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    { 
    	$change_status_date = $row->getData();
        return ($change_status_date['change_status_at']!=''? date("m/d/Y H:i:s",strtotime($change_status_date['change_status_at'])):'----');
    }
}