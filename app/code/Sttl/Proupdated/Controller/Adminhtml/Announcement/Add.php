<?php

namespace Sttl\Proupdated\Controller\Adminhtml\Announcement;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Exception\NotFoundException;
use Sttl\Proupdated\Controller\Adminhtml\Announcement;

class Add extends Announcement {

	/**
	 * Dispatch request
	 *
	 * @return ResultInterface|ResponseInterface
	 * @throws NotFoundException
	 */
	public function execute() {
		/** @var Forward $resultForward */
		$resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
		return $resultForward->forward('edit');
	}
}