<?php

/**
 * Grid Admin Cagegory Map Record Save Controller.
 * @category  Webkul
 * @package   Webkul_Grid
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Sttl\Childskus\Controller\Adminhtml\Grid;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\Grid\Model\GridFactory
     */
    var $gridFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\Grid\Model\GridFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Sttl\Childskus\Model\PostFactory $gridFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory
    ) {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
        $this->filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->_directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

            $data = $this->getRequest()->getPostValue();
  
           
                if (!$data) {
                    $this->_redirect('childskus/grid/newcollection');
                    return;
                }
                try {
                    $rowData = $this->gridFactory->create();
                  
                    $rowData->setData($data['collections']);


                    if (isset($data['collections']['entity_id'])) {

                        $rowData->setEntityId($data['collections']['entity_id']);
                    }
                    $rowData->save();
                    $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }
                $this->_redirect('childskus/grid/index');


    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Childskus::save');
    }

}