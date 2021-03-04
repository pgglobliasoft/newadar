<?php
namespace DR\Gallery\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Imageszip extends \Magento\Framework\App\Action\Action
{

	protected $resultPageFactory;
	protected $session;
	protected $dataObjectFactory;
	protected $resultJsonFactory;
	protected $_storeManager;
	protected $directoryList;
    protected $driver;
    protected $fileFactory;
	public function __construct(
	    context $context,
	    \Magento\Customer\Model\Session $customerSession,
	    PageFactory $resultPageFactory,
	    \Magento\Framework\DataObjectFactory $dataObjectFactory,
	    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	    \Sttl\Adaruniforms\Helper\Sap $saphelper,
	    \Magento\Store\Model\StoreManagerInterface $storeManager,
	     \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Shell\Driver $driver,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
	    )
	{
	    $this->session = $customerSession;
	    parent::__construct($context);
	    $this->resultPageFactory = $resultPageFactory;
	    $this->dataObjectFactory = $dataObjectFactory;
	    $this->resultJsonFactory = $resultJsonFactory;
	    $this->saphelper = $saphelper;
	    $this->_storeManager = $storeManager;
	     $this->directoryList = $directoryList;
        $this->driver = $driver;
        $this->fileFactory = $fileFactory;
	}
	public function execute()
	{
		//error_reporting(0);
			$resultJson = $this->resultJsonFactory->create();
		try {
			$post = $this->getRequest()->getParams('imagespath');
		if(!empty($post['imagespath']))
		{
			if (!class_exists('\ZipArchive')) {
            	die('ZipArchive class not found');
        	}
        	$dir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        	$zipname = strtotime("now").'.zip';
        	$zip = new \ZipArchive;
			$zip->open($zipname, \ZipArchive::CREATE);
			foreach ($post['imagespath'] as $fileurl)
	        {
	           if ($fileurl != '')
	            {
					$baseurl = $this->_storeManager->getStore()->getBaseUrl();
					$abpath = str_replace($baseurl, $dir.DIRECTORY_SEPARATOR, $fileurl);
	            	$new_filename = substr($abpath,strrpos($abpath,'/') + 1);
	            	$zip->addFile($abpath,$new_filename);
	            }
	        }
	    $zip->close();
	    header('Content-Type: application/zip');
		header('Content-Type: application/force-download');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		//header('Content-Length: ' . filesize($zipname));
		$response = [
                    'errors' => false,
                    'message' => __(''),
                    'filename' => __($baseurl.$zipname)
                ];
			}
		}catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = $e->getMessage();
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            } 
        //print_r($response);exit;
        return $resultJson->setData($response);
    }
}
