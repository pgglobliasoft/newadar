<?php

namespace Sttl\Proupdated\Controller\Adminhtml;

abstract class Announcement extends \Sttl\Proupdated\Controller\Adminhtml\AbstractAction {

	const PARAM_CRUD_ID = 'id';

	/**
	 * Get back result redirect after add/edit.
	 *
	 * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
	 * @param null                                          $paramCrudId
	 *
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	protected function _getBackResultRedirect(\Magento\Framework\Controller\Result\Redirect $resultRedirect, $paramCrudId = null) {
		switch ($this->getRequest()->getParam('back')) {
		case 'edit':
			$resultRedirect->setPath(
				'*/*/edit',
				[
					static::PARAM_CRUD_ID => $paramCrudId,
					'_current' => true,
					'store' => $this->getRequest()->getParam('store'),
					'id' => $this->getRequest()->getParam('id'),
					'saveandclose' => $this->getRequest()->getParam('saveandclose'),
				]
			);
			break;
		case 'new':
			$resultRedirect->setPath('*/*/new', ['_current' => true]);
			break;
		default:
			$resultRedirect->setPath('*/*/');
		}

		return $resultRedirect;
	}
}
