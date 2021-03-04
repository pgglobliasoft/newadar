<?php
namespace DR\Gallery\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Imagelibrary extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;

	protected $session;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		PageFactory $resultPageFactory
		)
	{
		$this->session = $customerSession;
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
	public function execute()
	{
		if (!$this->session->isLoggedIn())
		{
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('customer/account/login');
			return $resultRedirect;
		}
		else
		{
			$resultPage = $this->resultPageFactory->create();
			$resultPage->getConfig()->getTitle()->set(__(''));
			return $resultPage;
		}
	}
}