<?php

namespace ManishJoy\ChildCustomer\Observer;

use ManishJoy\ChildCustomer\Model\PostFactory;

class Removechildcustomer implements \Magento\Framework\Event\ObserverInterface
{
	protected $_postFactory;

	public function __construct(
		PostFactory $postFactory
	) {
		$this->_postFactory = $postFactory;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$customerId = $observer->getEvent()->getCustomer()->getId();
		$model = $this->_postFactory->create()->load($customerId,"c_id");

		if($model->getId()){
			$model->delete();
		}

		return $this;
	}
}