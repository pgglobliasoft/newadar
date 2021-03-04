<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;

class Imageresize extends \Magento\Framework\App\Action\Action {

	protected $sapHelper;

	protected $_filesystem ;

	protected $imageFactory;

	private $storeManager;

	public function __construct(
		Context $context,
		Sap $sapHelper,
		StoreManagerInterface $storeManager,
		AdapterFactory $imageFactory,
		Filesystem $filesystem,
		array $data = []
	) {
		parent::__construct($context);
		$this->sapHelper = $sapHelper;
		$this->storeManager = $storeManager;
		$this->_filesystem = $filesystem;
		$this->imageFactory = $imageFactory;
		$this->_directory =  $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
	}
	public function execute()
	{
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('ftp_images/resized');
        $deleteDirectory = $this->_directory->delete($path);
        if($deleteDirectory){
		    $allSapIds = $this->sapHelper->getJsAllInventoryItems();
		    $data = [];
		    $count = 0;
            $total = 0;
    		foreach ($allSapIds as $key => $value) {	
    			if($value["ColorSwatch"] != ""){
    				$p = parse_url($value["ColorSwatch"]);
    	        	$this->getResizeImage($p['path'],80,80); 
    	        	$count++;  	
    			}
                if($value["U_WImage1"] != ""){
                    $p = parse_url($value["U_WImage1"]);
                    $this->getResizeImage($p['path'],350,500);
                    $total++;
                }	
    		}
            echo "Total ".$count." Swatcher images and ".$total." product image resized";
        }
	}
    public function getResizeImage($imageName,$width = 80,$height = 80)
    {
    	// print_r($imageName);die;
        /* Real path of image from directory */
        $image_link_raw = $imageName;
        $imagename =  basename($imageName);
        $varlue = explode('/', $imageName);
        $realPath = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($imageName);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/'.$width.'x'.$height.'/'.$varlue[2].'/'.$varlue[3].'/'.$imagename);

        if ($this->_directory->isFile($path) || $this->_directory->isExist($path)) {
            return false;
        }
        /* Target directory path where our resized image will be save */
        $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/'.$width.'x'.$height.'/'.$varlue[2].'/'.$varlue[3]);


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
        	
            return $this->storeManager->getStore()->getBaseUrl().$varlue[1].'/resized/'.$width.'x'.$height.'/'.$varlue[2].'/'.$varlue[3].'/'.$imagename;
        }
        return false;
    }
 }

