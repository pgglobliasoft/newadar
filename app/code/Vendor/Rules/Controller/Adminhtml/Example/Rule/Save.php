<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Rule;

class Save extends \Vendor\Rules\Controller\Adminhtml\Example\Rule
{

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
        \Vendor\Rules\Model\RuleFactory $ruleFactory,
        \Psr\Log\LoggerInterface $logger
    ) {

        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter, $ruleFactory, $logger);
    }

    /**
     * Rule save action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            $this->_redirect('vendor_rules/*/');
        }

        try {
            /** @var $model \Vendor\Rules\Model\Rule */
            $model = $this->ruleFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_vendor_rules_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();
            $inputFilter = new \Zend_Filter_Input(
                ['from_date' => $this->dateFilter, 'to_date' => $this->dateFilter],
                [],
                $data
            );
            $data = $inputFilter->getUnescaped();
            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                $model->load($id);
            }
            
            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('vendor_rules/*/edit', ['id' => $model->getId()]);
                return;
            }

            $data = $this->prepareData($data);

            $not_valid = false;
            $is_active = true;

            if(count($data['conditions']) <= 1 ){
                $data['is_active'] = 0;
                $is_active = false;
            }else{
                foreach ($data['conditions'] as $key => $item) {
                    if(isset($item['value'])){
                        if($item['value'] == ''){
                            /* ATTRIBUTE VALUE NOT ADDED */
                            unset($data['conditions'][$key]);
                            $not_valid = true;
                        }
                    }else if($item['attribute'] == 'product_sap'){
                        if(!isset($item['value'])){
                            /* SAP PRODUCT NOT ADDED */
                            unset($data['conditions'][$key]);
                            $not_valid = true;
                        }
                    }
                }
            }
            if($not_valid){
                $this->messageManager->addErrorMessage(__('Please add condition Properly.') );
            }
            if(!$is_active){
                $this->messageManager->addErrorMessage(__('Please add condition and active again rule.') );
            }
            $model->loadPost($data);
            $this->_session->setPageData($model->getData());

            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the rule.'));
            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('vendor_rules/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('vendor_rules/*/');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('rule_id');
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
            $this->_redirect('vendor_rules/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
            return;
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {

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