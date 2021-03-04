<?php
namespace ManishJoy\ChildCustomer\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

class Redirect extends \Magento\Framework\App\Action\Action {

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
			$post = $this->getRequest()->getParams();
			// print_r($post);die;
				$resultPage = $this->resultPageFactory->create();
			if($post['target'] != 'adaruniforms/index/productinv'){
				$resultPage->getConfig()->getTitle()->set(__('Access Denied'));
				return $resultPage;
			}else{
				$resultJson = $this->resultJsonFactory->create();
				 $renderDataPart = $resultPage->getLayout()
	                                    ->createBlock('Sttl\Adaruniforms\Block\View')
	                                    ->setTemplate('ManishJoy_ChildCustomer::redirect.phtml')
	                                    ->toHtml();
				$response = [
	                            'errors' => false,
	                            'html'   => $renderDataPart,
	                            'message' => __("Success.")
	                            ];
	            return $resultJson->setData($response);
			}
		}
	}
}