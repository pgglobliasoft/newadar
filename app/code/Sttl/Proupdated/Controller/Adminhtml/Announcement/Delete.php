<?php

/**
 * @author Globalia Soft <globalia@gmail.com>
 *
 */

namespace Sttl\Proupdated\Controller\Adminhtml\Announcement;

class Delete extends \Magento\Backend\App\Action {
	/**
	 * @return void
	 */
	public function execute() {
		$bannerId = (int) $this->getRequest()->getParam('id');
		$this->_redirect('*/*/');
		if ($bannerId) {
			/** @var $bannerModel \Mageworld\SimpleNews\Model\News */
			$bannerModel = $this->_objectManager->create('Sttl\Proupdated\Model\Post');
			$bannerModel->load($bannerId);

			// Check this news exists or not
			if (!$bannerModel->getId()) {
				$this->messageManager->addError(__('This Announcement no longer exists.'));
			} else {
				try {
					// Delete news
					$bannerModel->delete();
					$this->messageManager->addSuccess(__('The Announcement has been deleted.'));

					// Redirect to grid page
					$this->_redirect('*/*/');
					return;
				} catch (\Exception $e) {
					$this->messageManager->addError($e->getMessage());
					$this->_redirect('*/*/edit', ['id' => $bannerId]);
				}
			}
		}
	}
}