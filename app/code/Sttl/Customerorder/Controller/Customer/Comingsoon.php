<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

class Comingsoon extends \Magento\Framework\App\Action\Action {

	/**
	 * @var Page render
	 */
	protected $_pageFactory;
	protected $resultJsonFactory;
	protected $session;
	/**
	 * @var String
	 */

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Session $session
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->session = $session;
		return parent::__construct($context);
	}

	public function execute() {
		 if ($this->session->isLoggedIn())
        {		
        		$resultPage = $this->resultPageFactory->create();
				$resultPage->getConfig()->getTitle()->set(__('Coming Soon'));
				return $resultPage;
		}
	}
}