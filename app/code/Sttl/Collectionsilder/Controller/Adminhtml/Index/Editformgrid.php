<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Collectionsilder\Controller\Adminhtml\Index;

class Editformgrid extends \Sttl\Collectionsilder\Controller\Adminhtml\Index
{
    /**
     * Customer orders grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // echo "<pre>";
        // print_r("test");

        $this->initCurrentSlider();
        // $sliderId = (int)$this->getRequest()->getParam('id');

        // echo "<pre>";
        // print_r($sliderId);

        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
