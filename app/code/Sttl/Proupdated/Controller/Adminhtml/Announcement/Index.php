<?php
namespace Sttl\Proupdated\Controller\Adminhtml\Announcement;
// /Sttl/Proupdated/Controller/Adminhtml/Announcement

class Index extends \Magento\Backend\App\Action {
	/**
	 * Hello test controller page.
	 *
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
	public function execute() {
		$this->_view->loadLayout();
		$this->_setActiveMenu('Sttl_Proupdated::core');
		$title = __('Announcement Import B2B');
		$this->_view->getPage()->getConfig()->getTitle()->prepend($title);
		$this->_addBreadcrumb($title, $title);
		$this->_view->renderLayout();
	}

	/**
	 * Check Permission.
	 *
	 * @return bool
	 */
	protected function _isAllowed() {
		return $this->_authorization->isAllowed('Sttl_Proupdated::core');
	}
}