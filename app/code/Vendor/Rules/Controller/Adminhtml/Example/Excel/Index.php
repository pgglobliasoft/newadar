<?php

namespace Vendor\Rules\Controller\Adminhtml\Example\Excel;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Vendor\Rules\Controller\Adminhtml\Example\Material {
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
        \Vendor\Rules\Model\MaterialFactory $MaterialFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context,$coreRegistry,$fileFactory,$dateFilter,$MaterialFactory,$logger);
        
    }

	public function execute()
    {
        
        $importExcelFile = $this->getRequest()->getFiles('import_rates_file');
        if ($this->getRequest()->isPost() && isset($importExcelFile['tmp_name'])) {
            try {
                
                /** @var $importHandler \Magento\TaxImportExport\Model\Rate\CsvImportHandler */
                $importHandler = $this->_objectManager->create(
                    \Vendor\Rules\Model\Excel\ExcelImportHandler::class
                );
                $uploadHandler = $this->_objectManager->create(
                    \Vendor\Rules\Model\Excel\ExcelUploadHandler::class
                );

                $file = $uploadHandler->upload(['fileId' => $importExcelFile]);
                $exceldata = $importHandler->readFile($file);
      			$newadded = 0;
      			$updated = 0;

      			// var_dump($exceldata);die;
                foreach ($exceldata as $key => $value) {
                	if($value[0] != "ItemCode" && $value[0] != ''){
                		try{
                			$collections = $this->MaterialFactory->create()->getCollection();
                			$itemcode = $collections->addFieldToSelect('id')->addFieldToFilter('item_code', array('eq' => $value[0]))->getData();
                			$rowData = $this->MaterialFactory->create();
                			$active = strtolower($value[5]) == "yes" ? 1 : 0;
                            if(array_key_exists('10',$value))
                            {
                                $shortorder=$value[10];
                            }
                            else
                            {
                                $shortorder=NULL;
                            }
                            if(array_key_exists('0',$itemcode)){
	                		 if($itemcode[0]['id']){
	                			$rowData->addData([
		                			"id" => $itemcode[0]['id'],
				                    "item_code" => $value[0],
				                    "item_name" => $value[1],
				                    "item_url" => $value[2],
				                    "file_url" => $value[3],
				                    "category" => $value[4],
				                    "is_active" => $active,
				                    "price" => $value[6],
				                    "minimum_order_amt" => $value[7],
				                    "maximum_pre_order" => $value[8],
				                    "free_after" => $value[9],
                                    "shortorder" => $shortorder,
				                    ]);
                               	$rowData->save();
	                			$updated++;
                			}else{
                				$rowData->addData([
				                    "item_code" => $value[0],
				                    "item_name" => $value[1],
				                    "item_url" => $value[2],
				                    "file_url" => $value[3],
				                    "category" => $value[4],
				                    "is_active" => $active,
				                    "price" => $value[6],
				                    "minimum_order_amt" => $value[7],
				                    "maximum_pre_order" => $value[8],
				                    "free_after" => $value[9],
                                    "shortorder" => $shortorder,
				                    ]);
                                $rowData->save();
	                			$newadded++;
                			}
                		  }
                		} catch (\Exception $e) {
			                $this->messageManager->addError($e->getMessage());
			            }
                	}
                }
                
                $successmessage = 'Marketing product(s) Added.';
                if($newadded > 0 && $updated > 0){
                  $successmessage = 'Total '.$newadded.' Marketing product(s) added and '.$updated.' Marketing product(s) Updated.';
                }else if($newadded == 0 && $updated > 0){
                  $successmessage = 'Total  '.$updated.' Marketing product(s) Updated.';
                }else if($newadded > 0 && $updated == 0){
                  $successmessage = 'Total '.$newadded.' Marketing product(s) added.';
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