<?php

namespace Vendor\Rules\Controller\Adminhtml\Example;

abstract class Material extends \Magento\Backend\App\Action {
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $coreRegistry = null;

	/**
	 * @var \Magento\Framework\App\Response\Http\FileFactory
	 */
	protected $fileFactory;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
	 */
	protected $dateFilter;

	/**
	 * @var \Vendor\Rules\Model\MaterialFactory
	 */
	protected $MaterialFactory;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
	 * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
	 * @param \Vendor\Rules\Model\MaterialFactory $MaterialFactory
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
		\Vendor\Rules\Model\MaterialFactory $MaterialFactory,
		\Psr\Log\LoggerInterface $logger
	) {
		parent::__construct($context);
		$this->coreRegistry = $coreRegistry;
		$this->fileFactory = $fileFactory;
		$this->dateFilter = $dateFilter;
		$this->MaterialFactory = $MaterialFactory;
		$this->logger = $logger;
	}

	/**
	 * Initiate rule
	 *
	 * @return void
	 */
	protected function _initRule() {
		$rule = $this->MaterialFactory->create();
		$this->coreRegistry->register(
			'current_rule',
			$rule
		);
		$id = (int) $this->getRequest()->getParam('id');

		if (!$id && $this->getRequest()->getParam('id')) {
			$id = (int) $this->getRequest()->getParam('id');
		}

		if ($id) {
			$this->coreRegistry->registry('current_rule')->load($id);
		}
	}

	/**
	 * Initiate action
	 *
	 * @return Rule
	 */
	protected function _initAction() {
		$this->_view->loadLayout();
		$this->_setActiveMenu('Sttl_Inquiries::main')
			->_addBreadcrumb(__('Material Product'), __('Material Product'));
		return $this;
	}

	/**
	 * Returns result of current user permission check on resource and privilege
	 *
	 * @return bool
	 */
	protected function _isAllowed() {
		return $this->_authorization->isAllowed('Vendor_Rules::rules');
	}
}