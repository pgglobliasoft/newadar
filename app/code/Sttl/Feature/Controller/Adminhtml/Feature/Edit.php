<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

class Edit extends \Sttl\Feature\Controller\Adminhtml\Feature
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('feature_id');
        $model = $this->_objectManager->create('Sttl\Feature\Model\Feature');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This feature no longer exists.'));
                $this->_redirect('feature/*');
                return;
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_feature', $model);
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Feature') : __('Add New Feature'),
            $id ? __('Edit Feature') : __('Add New Feature')
        );
        $this->_view->getPage()->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('Add New Feature'));
        $this->_view->getLayout()->getBlock('feature_edit');
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Feature::edit_feature');
    }
}
