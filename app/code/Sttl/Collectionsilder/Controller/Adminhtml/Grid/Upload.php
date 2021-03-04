<?php
/**
 * Created By : Rohan Hapani
 */
namespace Sttl\Collectionsilder\Controller\Adminhtml\Grid;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Backend\App\Action {
	/**
	 * Image uploader
	 * @var \Magento\Catalog\Model\ImageUploader
	 */
	protected $imageUploader;
	/**
	 * @var \Magento\Framework\Filesystem
	 */
	protected $filesystem;
	/**
	 * @var \Magento\Framework\Filesystem\Io\File
	 */
	protected $fileIo;
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	protected $_imageFactory;
	/**
	 * Upload constructor.
	 * @param \Magento\Backend\App\Action\Context        $context
	 * @param \Magento\Catalog\Model\ImageUploader       $imageUploader
	 * @param \Magento\Framework\Filesystem              $filesystem
	 * @param \Magento\Framework\Filesystem\Io\File      $fileIo
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Catalog\Model\ImageUploader $imageUploader,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Filesystem\Io\File $fileIo,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Image\AdapterFactory $imageFactory
	) {
		parent::__construct($context);
		$this->imageUploader = $imageUploader;
		$this->filesystem = $filesystem;
		$this->_imageFactory = $imageFactory;
		$this->fileIo = $fileIo;
		$this->storeManager = $storeManager;
		$this->_directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
	}
	/**
	 * Upload file controller action.
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute() {
		$imageUploadId = $this->getRequest()->getParam('param_name', 'image');
		try {
			$imageResult = $this->imageUploader->saveFileToTmpDir($imageUploadId);
			// Upload image folder wise
			$imageName = $imageResult['name'];
			$firstName = substr($imageName, 0, 1);
			$secondName = substr($imageName, 1, 1);
			$basePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'collection/image/';
			$mediaRootDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'collection/image/' . $firstName . '/' . $secondName . '/';
			if (!is_dir($mediaRootDir)) {
				$this->fileIo->mkdir($mediaRootDir, 0775);
			}
			// Set image name with new name, If image already exist
			$newImageName = $this->updateImageName($mediaRootDir, $imageName);
			$this->fileIo->mv($basePath . $imageName, $mediaRootDir . $newImageName);
			// Upload image folder wise
			$imageResult['cookie'] = [
				'name' => $this->_getSession()->getName(),
				'value' => $this->_getSession()->getSessionId(),
				'lifetime' => $this->_getSession()->getCookieLifetime(),
				'path' => $this->_getSession()->getCookiePath(),
				'domain' => $this->_getSession()->getCookieDomain(),
			];
			$mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$imageResult['name'] = $newImageName;
			$imageResult['file'] = $newImageName;
			$imageResult['url'] = $mediaUrl . 'collection/image/' . $firstName . '/' . $secondName . '/' . $newImageName;
			// $imageResult['url'] = $this->getResizeImage($imageResult['url'], 150, 150);

		} catch (\Exception $e) {
			$imageResult = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
		}
		return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($imageResult);
	}

	public function updateImageName($path, $file_name) {
		if ($position = strrpos($file_name, '.')) {
			$name = substr($file_name, 0, $position);
			$extension = substr($file_name, $position);
		} else {
			$name = $file_name;
		}
		$new_file_path = $path . '/' . $file_name;
		$new_file_name = $file_name;
		$count = 0;
		while (file_exists($new_file_path)) {
			$new_file_name = $name . '_' . $count . $extension;
			$new_file_path = $path . '/' . $new_file_name;
			$count++;
		}
		return $new_file_name;
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
		$targetDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('resized/' . $width . 'x' . $height);
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

			return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/' . $width . 'x' . $height . '/' . $imagename;
		}
		return false;
	}
}