<?php
namespace ManishJoy\ChildCustomer\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action {

	/**
	 * @var Page render
	 */
	protected $_pageFactory;

	/**
	 * @var String
	 */
	protected $session;

	public function __construct(
		Context $context,
		Session $customerSession,
		PageFactory $resultPageFactory
	) {
		$this->session = $customerSession;
		$this->resultPageFactory = $resultPageFactory;

		return parent::__construct($context);
	}

	public function execute() {

		if (!$this->session->isLoggedIn()) {
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('/login');
			return $resultRedirect;
		} else {
			$resultPage = $this->resultPageFactory->create();
			$resultPage->getConfig()->getTitle()->set(__('Create Child Customer'));
			return $resultPage;
		}
	}
}