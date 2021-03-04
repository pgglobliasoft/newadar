<?php

namespace Vendor\Rules\Controller\Adminhtml\Grid;

class Save extends \Magento\Backend\App\Action
{

    var $gridFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Vendor\Rules\Model\GridFactory $gridFactory,
        \Vendor\Rules\Block\Adminhtml\Grid\Featuredpro $featuredpro 
    ) {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
        $this->featuredpro = $featuredpro;
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
        $data = $this->getRequest()->getPostValue();

        $myString = $data['detail'];
        $myArray = explode(',', $myString);

        $data['sku'] = $myArray[0];
        $data['name'] = $myArray[1];

        if (!$data) {
            $this->_redirect('vendor_rules/grid/massdelete/grid/addrow');
            return;
        }
        try {
            $rowData = $this->gridFactory->create();
            $rowData->setData($data);
            if (isset($data['id'])) {
                $rowData->setEntityId($data['id']);
            }
            $rowData->save();
            $this->featuredpro->CreateJson();
            $this->messageManager->addSuccess(__('Row data has been successfully saved.'));

        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('vendor_rules/grid/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_Rules::save');
    }
}
