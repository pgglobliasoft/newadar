<?php

namespace Sttl\Adaruniforms\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageResize extends Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->imageFactory = $imageFactory;
        $this->_filesystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @param $imageName string imagename only(abc.jpg)
     * @param $width int
     * @param $height int
     * @return string
     */
    public function getResizeImage($imageName,$width = 258,$height = 200)
    {
        /* Real path of image from directory */
        $image_link_raw = $imageName;
        $p=parse_url($image_link_raw);
        $imagename =  basename($imageName);
        print_r($pdf);die;
        // print_r($p);die;
        // $realPath = $this->_filesystem->getPath($p['path']);
        $realPath = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($p['path']);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }

        /* Target directory path where our resized image will be save */
        $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'x'.$height);
        // print_r($targetDir);die;
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        /* If Directory not available, create it */
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width,$height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {

            return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'x'.$height.'/'.$imagename;
        }
        return false;
    }
}


