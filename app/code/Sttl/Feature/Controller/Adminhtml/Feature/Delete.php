<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

class Delete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('feature_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('Sttl\Feature\Model\Feature');
                $model->load($id);
                $optionManagement = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\OptionManagement');
                $optionManagement->delete(\Magento\Catalog\Model\Product::ENTITY, 'feature', $model->getOptionId());
                $model->delete();
                $this->messageManager->addSuccess(__('The feature has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['feature_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a feature to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Feature::delete_feature');
    }
}
