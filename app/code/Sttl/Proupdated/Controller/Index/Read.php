<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sttl\Proupdated\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Read extends \Magento\Framework\App\Action\Action {

	protected $resultForwardFactory;
	protected $resultPageFactory;
	protected $saphelper;
	protected $resultJsonFactory;

	public function __construct(
		Context $context,
		JsonFactory $resultJsonFactory,
		Session $customerSession,
		\Magento\Framework\UrlInterface $urlInterface
	) {

		$this->resultJsonFactory = $resultJsonFactory;
		$this->session = $customerSession;
		$this->_urlInterface = $urlInterface;
		parent::__construct($context);
	}

	public function execute() {
		$result = $this->resultJsonFactory->create();
		$Post = $this->getRequest()->getParams('url');
		echo 'test';
		return 0;

	}

}
