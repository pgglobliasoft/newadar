<?php


namespace Sttl\Importproductbrand\Controller\Adminhtml\Importproductbrand;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$data = $this->getRequest()->getPostValue();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $csvDataHelper = $objectManager->create('Magento\Framework\File\Csv');
        $csvData = $insertData = array();
        $csvData = $csvDataHelper->getData($data['icon'][0]['path']. DIRECTORY_SEPARATOR .$data['icon'][0]['file']);
        //$count = 0;$count_1 = 0;$count2 = 0;
        $count = 0;
        $brandIds = array();
        $insertAllData = false;
        if(count($csvData) > 1)
        {
            foreach($csvData as $data)
            {
                if($count > 0)
                {
                    foreach($data as $key => $brand_url)
                    {
                        if($key == 0)
                        {
                            $sku = $brand_url;
                        }
                        else
                        {
                            $brands_product_urls = $objectManager->get('Sttl\Importproductbrand\Model\Importproductbrand')
                                ->getCollection()
                                ->addFieldToFilter('brand_id', array('eq' => $csvData[0][$key]))
                                ->addFieldToFilter('sku', array('eq' => $sku));
                                $get_data_brands_product = $brands_product_urls->getData();
                            if(count($get_data_brands_product > 0) && isset($get_data_brands_product[0]))
                            {   
                                $insertData['importproductbrand_id'] = $get_data_brands_product[0]['importproductbrand_id'];
                               
                            }
                                $insertData['brand_id'] = $csvData[0][$key];
                                $insertData['sku'] = $sku;
                                $insertData['brand_url'] = $brand_url;
                            
                            if ($insertData && $brand_url != "") {
                                $id = $this->getRequest()->getParam('importproductbrand_id');
                                $model = $this->_objectManager->create(\Sttl\Importproductbrand\Model\Importproductbrand::class)->load($id);
                                    if (!$model->getId() && $id) {
                                        $this->messageManager->addErrorMessage(__('This Importproductbrand no longer exists.'));
                                        return $resultRedirect->setPath('*/*/');
                                    }
                                    
                                    $model->setData($insertData);
                                    $model->save();
                                    $insertAllData = true;
                            }
                        }
                    }
                }
                $count ++;           
            }
		}
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($insertAllData) {
            try {
                $this->messageManager->addSuccessMessage(__('Import Successfully.'));
                $this->dataPersistor->clear('sttl_importproductbrand_importproductbrand');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['importproductbrand_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Importproductbrand.'));
            }
        
            $this->dataPersistor->set('sttl_importproductbrand_importproductbrand', $insertData);
            return $resultRedirect->setPath('*/*/edit', ['importproductbrand_id' => $this->getRequest()->getParam('importproductbrand_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
