<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Brand\Controller\Adminhtml\Excel;

use Magento\Framework\Controller\ResultFactory;


class ImportPost extends \Sttl\Brand\Controller\Adminhtml\Excel
{
    /**
     * import action from import/export tax
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

     protected $productFactory;

     protected $productRepository;

    public function execute()
    {
        $importExcelFile = $this->getRequest()->getFiles('import_rates_file');


        if ($this->getRequest()->isPost() && isset($importExcelFile['tmp_name'])) {
            try {
                /** @var $importHandler \Magento\TaxImportExport\Model\Rate\CsvImportHandler */
                $importHandler = $this->_objectManager->create(
                    \Sttl\Brand\Model\Excel\ExcelImportHandler::class
                );
                $uploadHandler = $this->_objectManager->create(
                    \Sttl\Brand\Model\Excel\ExcelUploadHandler::class
                );

                $productRepository = $this->_objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface');


                $file = $uploadHandler->upload(['fileId' => $importExcelFile]);
                $exceldata = $importHandler->readFile($file);

                // $excelvalue_with_column_name = $this->getMaincolumn($exceldata);

                $idArray = array();
                $fabricUrlData = array();
                $fabricUrlDataexist =array();
                $newadded = 0;
                $updated = 0;

                $productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
              	$collection = $productCollection->addAttributeToSelect('*')
                      ->load();
              	// $value = "464"; //amount
                $productActionObject = $this->_objectManager->create('Magento\Catalog\Model\Product\Action');
                // $productActionObject->updateAttributes($idArray, array('ufabriccareurl' => $value), 0);


                foreach ($exceldata as $key => $value) {
                  if($value[0] != "SKU Number"){
                    $product = '';
                    try{
                      $product = $productRepository->get($value[0]);
                    }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
                      $product = "";
                    }
                    if($product){
                      if($product->getParentStyle() == $value[1]){
                        if($product->getData("ufabriccareurl") != ""){
                          // echo $product->getParentStyle();
                          // $fabricUrlDataexist[$product->getId()] = $product->getData("ufabriccareurl");
                          $updated++;
                        }else{
                          $newadded++;
                        }
                        $idArray[] = $product->getId();
                        $fabricUrlData[$product->getId()] = $value[7];
                      }
                    }
                  }
                }



                // For Remove Value from Attribute Start
                // $idArray1 = array();
                // $fabricUrlData1 = array();
                // $a = 11584;
                // while ($a <= 11590) {
                //   $idArray1[] = $a;
                //   $fabricUrlData1[$a] = "";
                //   $a++;
                // }
                // For Remove Value from Attribute End



                foreach ($idArray as $productid){
                  foreach ($fabricUrlData as $keyss => $valuess){
                    if($keyss == $productid){
                      $productActionObject->updateAttributes([$productid], array('ufabriccareurl' => $valuess), 0);
                    }
                  }
                }

                $successmessage = 'The Fabric Url added.';
                if($newadded > 0 && $updated > 0){
                  $successmessage = 'The Fabric Url added on '.$newadded.' product(s) and Updated on '.$updated.' product(s).';
                }else if($newadded == 0 && $updated > 0){
                  $successmessage = 'The Fabric Url Updated on '.$updated.' product(s).';
                }else if($newadded > 0 && $updated == 0){
                  $successmessage = 'The Fabric Url added on '.$newadded.' product(s).';
                }else if($newadded == 0 && $updated == 0){
                  $successmessage = 'Not Found any Product in Store.';
                }

                $this->messageManager->addSuccess(__($successmessage));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt'));
            }
        } else {

            $this->messageManager->addError(__('Invalid file upload attempt'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }


    public function getMaincolumn($sheetdata){

      $maincolumn = array();
      foreach ($sheetdata as $key => $value) {
        if($key == 1){
          foreach ($value as $keys => $values) {
            $maincolumn[] = $values;
          }
        }
      }

      $excelvalue_tmp = array();
      foreach ($sheetdata as $keysheet => $valuesheet) {
        if($keysheet != 1){
          foreach ($valuesheet as $keysheetsheet => $valuesheetsheet) {
              $excelvalue_tmp[$keysheet][$maincolumn[$keysheetsheet]] = $valuesheetsheet;
          }
        }
      }

      return $excelvalue_tmp;
    }
}
