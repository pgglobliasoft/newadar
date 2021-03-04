<?php

namespace Sttl\Proupdated\Controller\Adminhtml\Announcement;

use Sttl\Proupdated\Controller\Adminhtml\Announcement;

class Save extends Announcement {
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function execute() {

		$resultRedirect = $this->resultRedirectFactory->create();
		$_PostCollectionFactory = $this->_objectManager->create('Sttl\Proupdated\Model\PostFactory');
		if ($data = $this->getRequest()->getPostValue()) {
			// echo "<pre>";
			// print_r($data);die;
			$data['customer']['banners'] = isset($data['customer']['banners']) ? json_encode($data['customer']['banners']) : '';

			// $data['customer']['banners'] = json_encode($data['customer']['banners']);
			$model = $_PostCollectionFactory->create();
			$post_data = $this->getRequest()->getPostValue();
			if ($id = $this->getRequest()->getParam('id')) {
				$model->load($id);
			}
			$model->setData($data['customer']);

			try {
				$model->save();
				$this->messageManager->addSuccess(__('The Announcement has been saved.'));
				$this->_getSession()->setFormData(false);

				return $this->_getBackResultRedirect($resultRedirect, $model->getId());
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
				$this->messageManager->addException($e, __('Something went wrong while saving the Announcement.'));
			}

			$this->_getSession()->setFormData($data);

			return $resultRedirect->setPath(
				'*/*/edit',
				['id' => $this->getRequest()->getParam('id')]
			);
		}

		return $resultRedirect->setPath('*/*/');
	}
}
