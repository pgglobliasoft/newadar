<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

class MassStatus extends \Sttl\Feature\Controller\Adminhtml\Feature
{
    public function execute()
    {
        $featureIds = $this->getRequest()->getParam('feature');
        if (!is_array($featureIds) || empty($featureIds)) {
            $this->messageManager->addError(__('Please select feature(s).'));
        } else {
            try {
                foreach ($featureIds as $id) {
                    $feature = $this->_objectManager->create('Sttl\Feature\Model\Feature')->load($id);
                    $feature->setStatus($this->getRequest()->getParam('status'))->save();
                }
                $this->messageManager->addSuccess(__('Total of %1 feature(s) were changed status.', count($featureIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Feature::save_feature');
    }
}
