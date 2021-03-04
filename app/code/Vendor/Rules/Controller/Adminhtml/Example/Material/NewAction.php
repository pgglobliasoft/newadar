<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Material;

class NewAction extends \Vendor\Rules\Controller\Adminhtml\Example\Material {
	/**
	 * New action
	 *
	 * @return void
	 */
	public function execute() {
		$this->_forward('edit');
	}
}