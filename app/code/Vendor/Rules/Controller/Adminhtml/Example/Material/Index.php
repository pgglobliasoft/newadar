<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Material;

class Index extends \Vendor\Rules\Controller\Adminhtml\Example\Material {
	/**
	 * Index action
	 *
	 * @return void
	 */
	public function execute() {
		$this->_initAction()->_addBreadcrumb(__('Marketing Products Grid'), __('Marketing Products Grid'));
		$this->_view->getPage()->getConfig()->getTitle()->prepend(__('Marketing Products Grid'));
		$this->_view->renderLayout('root');
	}
}