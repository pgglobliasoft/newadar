<?php
namespace Sttl\Proupdated\Controller\Adminhtml\Announcement;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action {
	public function __construct(
		Context $context,
		ForwardFactory $resultForwardFactory
	) {
		parent::__construct($context);
		$this->resultForwardFactory = $resultForwardFactory;
	}
	/**
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
	public function execute() {
		$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$resultPage->getConfig()->getTitle()->prepend(__('Edit Announcement B2B'));
		return $resultPage;
	}
}