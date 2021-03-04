<?php

namespace DR\Gallery\Controller\Adminhtml\Image;

use DR\Gallery\Api\Data\ImageInterface;
use DR\Gallery\Controller\Adminhtml\Image;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Exception;
use Magento\Framework\File\Uploader;
use Magento\Framework\File\UploaderFactory;
use DR\Gallery\Api\ImageRepositoryInterface;
use DR\Gallery\Api\Data\ImageInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;

class Save extends Image
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ImageRepositoryInterface $imageRepository
     * @param ImageInterfaceFactory $imageFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ImageRepositoryInterface $imageRepository,
        ImageInterfaceFactory $imageFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    )
    {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context, $coreRegistry, $imageRepository, $imageFactory);
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        // print_r($data);die;
		//$model->setData($data);
        if ($data) {

            $id = $this->getRequest()->getParam('image_id');
            if ($id) {
                try {
                    $image = $this->imageRepository->getById($id);
                } catch (Exception $e) {
                    $this->messageManager->addError(__('This image no longer exists.'));
                    /** @var Redirect $resultRedirect */
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $image = $this->imageFactory->create(); 
            }

            $data = $this->removeFile($data);
            if(isset($_FILES['path']['name']) && !empty($_FILES['path']['name'])){
                $data = $this->uploadFile($data);
            }
            $data = $this->uploadSmallFile($data);
             if(is_array(@$data['path'])){                
                $data['path'] = $data['path']['value'];
            }
             if(is_array(@$data['small_image'])){  
             // echo "string2222";              
                $data['small_image'] = $data['small_image']['value'];
            }
			$data['publish']=implode(',',$data['publish']);
            $image->setData($data);

            try {
                $this->imageRepository->save($image);
                $this->messageManager->addSuccess(__('You saved the document.'));
                $this->_session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['image_id' => $image->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['image_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $data
     * @return $this
     */
    protected function uploadFile($data)
    {
        if (empty($_FILES)
            || !array_key_exists('path', $_FILES)
            || !array_key_exists('name', $_FILES['path'])
            || $_FILES['path']['name'] == ''
        ) {
            if ($data['path'] && is_array($data['path'])) {
                $data['path'] = $data['path']['value'];
            }

            return $data;
        }

        $uploadDirectory = implode(DIRECTORY_SEPARATOR, [
            BP,
            $this->filesystem->getUri('media'),
            ImageInterface::RELATIVE_PATH_FROM_MEDIA_TO_FILE
        ]);

        try {
            /** @var $uploader Uploader */
            $uploader = $this->uploaderFactory->create([
                'fileId' => [
                    'tmp_name' => $_FILES['path']['tmp_name'],
                    'name' => $_FILES['path']['name']
                ]
            ]);

            //$uploader->setAllowedExtensions(['jpg', 'jpeg', 'png','pdf','docx']);
            $uploader->setAllowRenameFiles(true);

            $result = $uploader->save($uploadDirectory);
            $data['path'] = ImageInterface::RELATIVE_PATH_FROM_MEDIA_TO_FILE . $result['file'];
        } catch (\Exception $e) {}

        return $data;
    }

    /**
     * @param $data
     * @return $this
     */
    protected function uploadSmallFile($data)
    {
        if (empty($_FILES)
            || !array_key_exists('small_image', $_FILES)
            || !array_key_exists('name', $_FILES['small_image'])
            || $_FILES['small_image']['name'] == ''
        ) {
            if ($data['small_image'] && is_array($data['small_image'])) {
                $data['small_image'] = $data['small_image']['value'];
            }

            return $data;
        }

        $uploadDirectory = implode(DIRECTORY_SEPARATOR, [
            BP,
            $this->filesystem->getUri('media'),
            ImageInterface::RELATIVE_PATH_FROM_MEDIA_TO_FILE_SMALL
        ]);

        try {
            /** @var $uploader Uploader */
            $uploader = $this->uploaderFactory->create([
                'fileId' => [
                    'tmp_name' => $_FILES['small_image']['tmp_name'],
                    'name' => $_FILES['small_image']['name']
                ]
            ]);

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png','JPG', 'JPEG', 'PNG']);
            $uploader->setAllowRenameFiles(true);

            $result = $uploader->save($uploadDirectory);
            $data['small_image'] = ImageInterface::RELATIVE_PATH_FROM_MEDIA_TO_FILE_SMALL . $result['file'];
        } catch (\Exception $e) {}

        return $data;
    }

    /**
     * @param $data
     * @return $this
     */
    protected function removeFile($data)
    {
        if (!array_key_exists('path', $data) || !array_key_exists('delete', $data['path']) || !$data['path']['delete']) {
            return $data;
        }
        if (!array_key_exists('small_image', $data) || !array_key_exists('delete', $data['small_image']) || !$data['small_image']['delete']) {
            return $data;
        }
        
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        try {
            $writer->delete(ImageInterface::RELATIVE_PATH_FROM_MEDIA_TO_FILE . $data['path']['value']);
            $writer->delete(ImageInterface::RELATIVE_PATH_FROM_MEDIA_TO_FILE_SMALL . $data['small_image']['value']);
            $data['path'] = null;
        } catch (\Exception $e) {}

        return $data;
    }
}