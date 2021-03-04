<?php

namespace ManishJoy\ChildCustomer\Block\Adminhtml\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Active extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $mageactive = $row->getData("active");

        if($mageactive){
            return "<span style='color:green; font-weight:900;'>Online</span>";
        }else{
            return "<span style='color:#ce7b7b; font-weight:900;'>Offline</span>";
        }

        return $mageactive;
    }
}