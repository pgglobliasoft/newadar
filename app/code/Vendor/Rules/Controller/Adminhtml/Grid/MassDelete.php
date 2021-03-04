<?php

namespace Vendor\Rules\Controller\Adminhtml\Grid;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Vendor\Rules\Model\ResourceModel\Grid\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    protected $_filter;

    protected $_collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Vendor\Rules\Block\Adminhtml\Grid\Featuredpro $featuredpro 
    ) {

        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->featuredpro = $featuredpro;
        parent::__construct($context);
    }

     public function cleanFlush()
    {       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try{
            $_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
            $_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
            $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
            foreach ($types as $type) {
                $_cacheTypeList->cleanType($type);
            }
            foreach ($_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $recordDeleted = 0;
        foreach ($collection->getItems() as $record) {
            $record->setId($record->getEntityId());
            $record->delete();
            $recordDeleted++;
        }
        $this->featuredpro->CreateJson();
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_Rules::row_data_delete');
    }
}
