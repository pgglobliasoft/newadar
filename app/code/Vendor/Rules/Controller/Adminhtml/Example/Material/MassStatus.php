<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Material;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Vendor\Rules\Model\ResourceModel\Material\CollectionFactory;

class MassStatus extends \Magento\Backend\App\Action {
	protected $filter;
	protected $collectionFactory;

	public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}

	public function execute() {
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		$collectionSize = $collection->getSize();
		foreach ($collection as $item) {

			$brand = $this->_objectManager->create('Vendor\Rules\Model\Material')->load($item->getId());
			$brand->setStatus($this->getRequest()->getParam('status'))->save();
		}
		$this->messageManager->addSuccess(__('A total of %1 record(s) have been upadted.', $collectionSize));
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		return $resultRedirect->setPath('*/*/');
	}

}
