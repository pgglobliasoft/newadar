<?php


namespace Sttl\Importproductbrand\Controller\Adminhtml\Importproductbrand;

class Edit extends \Sttl\Importproductbrand\Controller\Adminhtml\Importproductbrand
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('importproductbrand_id');
        $model = $this->_objectManager->create(\Sttl\Importproductbrand\Model\Importproductbrand::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Importproductbrand no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('sttl_importproductbrand_importproductbrand', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Import CSV') : __('Import CSV'),
            $id ? __('Import CSV') : __('Import CSV')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Importproductbrands'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Importproductbrand %1', $model->getId()) : __('Import CSV'));
        return $resultPage;
    }
}
