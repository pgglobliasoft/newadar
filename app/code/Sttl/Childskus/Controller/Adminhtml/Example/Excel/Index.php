<?php

namespace Sttl\Childskus\Controller\Adminhtml\Example\Excel;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action {
    /**
     * Index action
     *
     * @return void


     */
     public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Sttl\Childskus\Model\PostFactory $gridFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->gridFactory = $gridFactory;
        $this->logger = $logger;
        
    }

    public function execute()
    {
        
        $importExcelFile = $this->getRequest()-> getFiles('import_rates_file');
        if ($this->getRequest()->isPost() && isset($importExcelFile['tmp_name'])) {
            try {
                
                /** @var $importHandler \Magento\TaxImportExport\Model\Rate\CsvImportHandler */
                 $importHandler = $this->_objectManager->create(
                    \Sttl\Childskus\Model\Excel\ExcelImportHandler::class
                );
                $uploadHandler = $this->_objectManager->create(
                    \Sttl\Childskus\Model\Excel\ExcelUploadHandler::class
                );

                $file = $uploadHandler->upload(['fileId' => $importExcelFile]);
                $exceldata = $importHandler->readFile($file);
                $newadded = 0;
                $updated = 0;

                foreach ($exceldata as $key => $value) {

                    if($value[0] != "parantsku" && $value[0] != ''){
                        try{
                             
                            $collections = $this->gridFactory->create()->getCollection();                            
                            $itemcode = $collections->addFieldToSelect('entity_id')->addFieldToFilter('parantsku',array('eq'=>$value[0]))->getData();
                            $rowData = $this->gridFactory->create();

                            if(array_key_exists('0',$itemcode)){                         
                             if($itemcode[0]['entity_id']){
                                 
                                $rowData->addData([
                                    "entity_id" => $itemcode[0]['entity_id'],
                                    "parantsku" => $value[0],
                                    "childsku1" => $value[1],
                                    "childsku2" => $value[2],
                                    ]);
                                $rowData->save();
                                $updated++;
                             }

                            }else{
                                $rowData->addData([
                                    "parantsku" => $value[0],
                                    "childsku1" => $value[1],
                                    "childsku2" => $value[2],
                                    ]);
                                $rowData->save();
                                $newadded++;
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addError($e->getMessage());
                        }
                    }
                }
                 $successmessage = 'Sku Added...';

                if($newadded > 0 && $updated > 0){
                  $successmessage = 'Total '.$newadded.' sku added and '.$updated.' sku Updated.';
                }else if($newadded == 0 && $updated > 0){
                  $successmessage = 'Total  '.$updated.' sku Updated.';
                }else if($newadded > 0 && $updated == 0){
                  $successmessage = 'Total '.$newadded.' sku added.';
                }else if($newadded == 0 && $updated == 0){
                  $successmessage = 'Not Found any Product in Store.';
                }
               

                $this->messageManager->addSuccess(__($successmessage));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt2'));
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt3'));
        }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
            return $resultRedirect;
    }
}