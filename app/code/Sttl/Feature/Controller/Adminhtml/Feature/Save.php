<?php

namespace Sttl\Feature\Controller\Adminhtml\Feature;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Sttl\Feature\Controller\Adminhtml\Feature
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $jsHelper = $this->_objectManager->create('Magento\Backend\Helper\Js');
                $model = $this->_objectManager->create('Sttl\Feature\Model\Feature');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('feature_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong feature is specified.'));
                    }
                }
                if (isset($_FILES['small_image']['name']) && $_FILES['small_image']['name'] != '') {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'small_image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $ext = pathinfo($_FILES['small_image']['name'], PATHINFO_EXTENSION);
                    if ($uploader->checkAllowedExtension($ext)) {
                        $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('sttl_feature/');
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        if ($fileName) {
                            $data['feature']['small_image'] = 'sttl_feature' . $fileName;
                        }
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        return $this->redirectToEdit($model, $data);
                    }
                } else {
                    if (isset($data['small_image']['delete']) && $data['small_image']['delete'] == 1) {
                        $data['feature']['small_image'] = '';
                    } else {
                        unset($data['small_image']);
                    }
                }
                if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    if ($uploader->checkAllowedExtension($ext)) {
                        $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('sttl_feature/');
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        if ($fileName) {
                            $data['feature']['image'] = 'sttl_feature' . $fileName;
                        }
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        return $this->redirectToEdit($model, $data);
                    }
                } else {
                    if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                        $data['feature']['image'] = '';
                    } else {
                        unset($data['image']);
                    }
                }
                if (!isset($data['feature']['url_key']) || $data['feature']['url_key'] == '') {
                    $data['feature']['url_key'] = $this->_objectManager->get('Magento\Catalog\Model\Product\Url')->formatUrlKey($data['feature']['name']);
                }
                $model->setData($data['feature'])
                    ->setId($this->getRequest()->getParam('feature_id'));
                $model->setStores($data['stores']);
                if (isset($data['product_ids']) && ($data['product_ids'] != '' || $data['product_ids'] != null)) {
                    $productIds = $jsHelper->decodeGridSerializedInput($data['product_ids']);
                    $model->setProductIds($productIds);
                } else {
                    if (isset($data['product_ids']) && ($data['product_ids'] == '' || $data['product_ids'] == null)) {
                        $model->setProductIds(array());
                    }
                }
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $feature = $this->_objectManager->create('Sttl\Feature\Model\Feature')->load($model->getId());
                $optionId = $this->saveOption('feature', $feature->getName(), (int)$feature->getOptionId());
                $feature->setOptionId($optionId);
                $feature->save();
                if (isset($data['product_ids']) && ($data['product_ids'] != '' || $data['product_ids'] != null)) {
                    $productIdsInFeature = array();
                    $productCollection = $this->_objectManager->create('Sttl\Feature\Model\Product')->getCollection();
                    $productCollection->addFieldToFilter('feature_id', ['eq' => $feature->getId()]);
                    foreach ($productCollection as $product) {
                        $productIdsInFeature[] = (int)$product->getProductId();
						$product->delete();
                    }
                    $arrAttributeEmpty = ['sttl_feature'=>''];
                    //print_r($productIdsInFeature); die();
                    if(count($productIdsInFeature)){
                        $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                                ->updateAttributes($productIdsInFeature,  $arrAttributeEmpty, 0);
                    }



                    $productIds = $jsHelper->decodeGridSerializedInput($data['product_ids']);
                    $productIdsInput = array();
                    foreach ($productIds as $key => $value) {
                        $productIdsInput[] = (int)$key;
                    }

					if(count($productIdsInput)>0){

						$arrAttributeData = ['sttl_feature'=>$optionId];
						foreach($productIdsInput as $productId){
							$featureProduct = $this->_objectManager->create('Sttl\Feature\Model\Product');
							$featureProduct->setFeatureId($feature->getId())->setProductId($productId)->save();
						}



						$this->_objectManager->get('Magento\Catalog\Model\Product\Action')
							->updateAttributes($productIdsInput,  $arrAttributeData, 0);
					}

                }

				if (isset($data['product_ids']) && ($data['product_ids'] == '' || $data['product_ids'] == null)) {
					$arrProductIds = [];
					$productCollection = $this->_objectManager->create('Sttl\Feature\Model\Product')->getCollection();
					$productCollection->addFieldToFilter('feature_id', ['eq' => $feature->getId()]);
					foreach ($productCollection as $product) {
						$arrProductIds[] = $product->getProductId();
						$product->delete();
					}
					$arrAttributeEmpty = ['sttl_feature'=>''];
					$this->_objectManager->get('Magento\Catalog\Model\Product\Action')
						->updateAttributes($arrProductIds,  $arrAttributeEmpty, 0);
				}


                $this->messageManager->addSuccess(__('You saved the feature.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('feature/*/edit', ['feature_id' => $model->getId()]);
                    return;
                }
                $this->_redirect('feature/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('feature_id');
                if (!empty($id)) {
                    $this->_redirect('feature/*/edit', ['feature_id' => $id]);
                } else {
                    $this->_redirect('feature/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the feature data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('feature/*/edit', ['feature_id' => $this->getRequest()->getParam('feature_id')]);
                return;
            }
        }
        $this->_redirect('feature/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Feature::save_feature');
    }

    protected function saveOption($attributeCode, $label, $value)
    {
        $attribute = $this->_objectManager->create('Magento\Catalog\Api\ProductAttributeRepositoryInterface')->get($attributeCode);
        $options = $attribute->getOptions();
        $values = array();
        foreach ($options as $option) {
            if ($option->getValue() != '') {
                $values[] = (int)$option->getValue();
            }
        }
        if (!in_array($value, $values)) {
            return $this->addAttributeOption(
                [
                    'attribute_id' => $attribute->getAttributeId(),
                    'order' => [0],
                    'value' => [
                        [
                            0 => $label,
                        ],
                    ],
                ]
            );
        } else {
            return $this->updateAttributeOption($value, $label);
        }
    }

    protected function addAttributeOption($option)
    {
        $oId = 0;
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
        $optionTable = $setup->getTable('eav_attribute_option');
        $optionValueTable = $setup->getTable('eav_attribute_option_value');
        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = (int)$optionId;
                if (!$intOptionId) {
                    $data = [
                        'attribute_id' => $option['attribute_id'],
                        'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    ];
                    $setup->getConnection()->insert($optionTable, $data);
                    $intOptionId = $setup->getConnection()->lastInsertId($optionTable);
                    $oId = $intOptionId;
                }
                $condition = ['option_id =?' => $intOptionId];
                $setup->getConnection()->delete($optionValueTable, $condition);
                foreach ($values as $storeId => $value) {
                    $data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                    $setup->getConnection()->insert($optionValueTable, $data);
                }
            }
        }
        return $oId;
    }

    protected function updateAttributeOption($optionId, $value)
    {
        $oId = $optionId;
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
        $optionValueTable = $setup->getTable('eav_attribute_option_value');
        $data = ['value' => $value];
        $setup->getConnection()->update($optionValueTable, $data, ['option_id=?' => $optionId]);
        return $oId;
    }
}
