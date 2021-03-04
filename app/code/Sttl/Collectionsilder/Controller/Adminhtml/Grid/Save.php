<?php

/**
 * Grid Admin Cagegory Map Record Save Controller.
 * @category  Webkul
 * @package   Webkul_Grid
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Sttl\Collectionsilder\Controller\Adminhtml\Grid;

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
        \Sttl\Collectionsilder\Model\PostFactory $gridFactory,
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
            $this->_redirect('collectionsilder/grid/newcollection');
            return;
        }
            // echo "<pre>";
            // print_r($data);die;
        try {
            $rowData = $this->gridFactory->create();
            // $rowData->setData($data);

            $collectonname = $data['collections']['name'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();   
            $this->saphelper = $objectManager->create('Sttl\Adaruniforms\Helper\Sap'); 
            $collectionss = $this->saphelper->getproductcollection($collectonname);
            // foreach ($collectionss as $key => $value) {
            //     $value['U_WImage1'] = $this->getResizeImage($value['U_WImage1'],250,300);
            // }
            $result = [];
            // echo "<pre>";
            // print_r($collectionss);die;
            if (array_key_exists("categories",$data['collections']))
                {
                foreach ($collectionss as $cdata){
                        $j = 0;
                        if (in_array($cdata['GroupName'], $data['collections']['categories']))
                        {
                        foreach ($collectionss as $pro){
                            if($pro['GroupName'] == $cdata['GroupName']){
                                $result[$cdata['GroupName']][$j] = [
                                    'Style' => $pro['style'],
                                ];  
                                $j++;
                            }
                        }
                    }
                }
            }else{
                foreach ($collectionss as $cdata){
                    $j = 0;
                    foreach ($collectionss as $pro){
                        if($pro['GroupName'] == $cdata['GroupName']){
                            $result[$cdata['GroupName']][$j] = [
                                'Style' => $pro['style'],
                            ];  
                            $j++;
                        }
                    }
                }
                $categories = $this->saphelper->getCategories($collectonname);
                $gname = [];
                $j = 0;
                    foreach ($categories as $value) {
                        $gname[$j] = $value['GroupName'];
                        $j++;
                    }
                $data['collections']['categories'] = $gname;
            }               
                        // echo "<pre>";
            // print_r($data['collections']['allow_all_customer']);die;
                if (!array_key_exists('allow_customer',$data['collections'])){
                    $data['collections']['allow_customer'] = [""];
                }
                if (!array_key_exists('categories',$data['collections'])){
                    $data['collections']['categories'] = [""];
                }
            $procollection = json_encode($result);
            $rowData->addData([
            "name" => $data['collections']['name'],
            "image" => @$data['collections']['image'][0]['url'],
            "status" => @$data['collections']['status'],
            "orders" => @$data['collections']['orders'],
            "product_collection" => $procollection,
            "allow_all_customer" => $data['collections']['allow_all_customer'],
            "allow_customer" => json_encode($data['collections']['allow_customer']),
            "categories" => json_encode($data['collections']['categories']),
            ]);
            if (isset($data['collections']['entity_id'])) {
                $rowData->setEntityId($data['collections']['entity_id']);
                $rowData->save();
                $this->messageManager->addSuccess(__('Row data has been successfully Edited.'));
            }else{
                $rowData->save();
                $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('collectionsilder/grid/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sttl_Collectionsilder::save');
    }
        public function getResizeImage($imageName, $width = 258, $height = 200) {
        /* Real path of image from directory */
        $image_link_raw = $imageName;
        $p = parse_url($image_link_raw);
        $imagename = basename($imageName);
        // print_r($p);die;
        // $realPath = $this->_filesystem->getPath($p['path']);
        $realPath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($p['path']);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }

        /* Target directory path where our resized image will be save */
        $targetDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('resized/collection/' . $width . 'x' . $height);
        // print_r($targetDir);die;
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        /* If Directory not available, create it */
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->_imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {

            return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/collection/' . $width . 'x' . $height . '/' . $imagename;
        }
        return false;
    }
}