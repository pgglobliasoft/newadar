<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Material;

class Edit extends \Vendor\Rules\Controller\Adminhtml\Example\Material {
	/**
	 * Rule edit action
	 *
	 * @return void
	 */
	public function execute() {
		$id = $this->getRequest()->getParam('id');
		/** @var \Vendor\Rules\Model\Rule $model */
		$model = $this->MaterialFactory->create();

		if ($id) {
			$model->load($id);
			if (!$model->getId()) {
				$this->messageManager->addErrorMessage(__('This Product is  no longer exists.'));
				$this->_redirect('vendor_rules/*');
				return;
			}
		}

		// set entered data if was error when we do save
		$data = $this->_session->getPageData(true);
		if (!empty($data)) {
			$model->addData($data);
		}
		$this->coreRegistry->register('current_rule', $model);

		$this->_initAction();
		$this->_view->getLayout()
			->getBlock('example_rule_edit')
			->setData('action', $this->getUrl('vendor_rules/*/save'));

		$this->_addBreadcrumb($id ? __('Edit Product') : __('New Product'), $id ? __('Edit Product') : __('New Product'));

		$this->_view->getPage()->getConfig()->getTitle()->prepend(
			$model->getRuleId() ? $model->getName() : __('New Product')
		);
		$this->_view->renderLayout();
	}
}