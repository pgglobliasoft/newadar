<?php


namespace Sttl\Importproductbrand\Controller\Adminhtml\Importproductbrand;

class Delete extends \Sttl\Importproductbrand\Controller\Adminhtml\Importproductbrand
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    { echo "123456";exit;
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('importproductbrand_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Sttl\Importproductbrand\Model\Importproductbrand::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Importproductbrand.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['importproductbrand_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Importproductbrand to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
