<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Rule;

class Index extends \Vendor\Rules\Controller\Adminhtml\Example\Rule
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Marketing Products Rules'), __('Marketing Products Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Marketing Products Rules'));
        $this->_view->renderLayout('root');
    }
}