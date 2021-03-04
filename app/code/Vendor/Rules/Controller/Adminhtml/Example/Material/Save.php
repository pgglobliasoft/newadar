<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Material;

class Save extends \Vendor\Rules\Controller\Adminhtml\Example\Material {

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
	 * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
	 * @param \Vendor\Rules\Model\RuleFactory $ruleFactory
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

		parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter, $MaterialFactory, $logger);
	}

	/**
	 * Rule save action
	 *
	 * @return void
	 */
	public function execute() {
		if (!$this->getRequest()->getPostValue()) {
			$this->_redirect('vendor_rules/*/');
		}

		try {
			/** @var $model \Vendor\Rules\Model\Rule */
			$model = $this->MaterialFactory->create();
			$this->_eventManager->dispatch(
				'adminhtml_controller_vendor_rules_prepare_save',
				['request' => $this->getRequest()]
			);
			$data = $this->getRequest()->getPostValue();
			$id = $this->getRequest()->getParam('id');
			if ($id) {
				$model->load($id);
			}
			$data['create_at'] = date('Y-m-d');
			$model->setData($data);
			$model->save();
			$this->messageManager->addSuccessMessage(__('You saved the product.'));
			$this->_session->setPageData(false);
			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('vendor_rules/*/edit', ['id' => $model->getId()]);
				return;
			}
			$this->_redirect('vendor_rules/*/');
			return;
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			$id = (int) $this->getRequest()->getParam('id');
			if (!empty($id)) {
				$this->_redirect('vendor_rules/*/edit', ['id' => $id]);
			} else {
				$this->_redirect('vendor_rules/*/new');
			}
			return;
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage(
				__('Something went wrong while saving the rule data. Please review the error log.')
			);
			$this->logger->critical($e);
			$data = !empty($data) ? $data : [];
			$this->_session->setPageData($data);
			$this->_redirect('vendor_rules/*/edit', ['id' => $this->getRequest()->getParam('id')]);
			return;
		}
	}

	/**
	 * Prepares specific data
	 *
	 * @param array $data
	 * @return array
	 */
	protected function prepareData($data) {

		if (isset($data['rule']['conditions'])) {
			$data['conditions'] = $data['rule']['conditions'];
		}

		$data['products'] = json_encode($data['products']);
		// $data['Categorys'] = json_encode($data['Categorys']);

		unset($data['rule']);
		// unset($data['products']);

		return $data;
	}
}