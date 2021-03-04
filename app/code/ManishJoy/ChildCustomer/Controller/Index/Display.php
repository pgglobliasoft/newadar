<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\ChildCustomer\Controller\Index;

class Display extends \Magento\Framework\App\Action\Action {

	protected $_pageFactory;

	protected $_postFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\ManishJoy\ChildCustomer\Model\PostFactory $postFactory,
		\ManishJoy\ChildCustomer\Model\ResourceModel\Post\CollectionFactory $reportCollectionFactory
	) {
		$this->_pageFactory = $pageFactory;
		$this->_postFactory = $postFactory;
		$this->_reportCollectionFactory = $reportCollectionFactory;
		// $this->_postFactoryCollection = $postFactoryCollection;
		return parent::__construct($context);
	}

	public function execute() {
		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('Create Child Customer'));
		return $resultPage;

	}
}