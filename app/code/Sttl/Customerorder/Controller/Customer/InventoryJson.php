<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class InventoryJson extends \Magento\Framework\App\Action\Action {

	protected $resultJsonFactory;

	protected $helpersap;

	protected $_filesystem;

	protected $_customerSession;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		EncoderInterface $jsonEncoder,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		Sap $helpersap
	) {
		parent::__construct($context);
		$this->jsonEncoder = $jsonEncoder;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helpersap = $helpersap;
		$this->_filesystem = $filesystem;
		$this->_directory =  $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);       
		$this->_storeManager = $storeManager;

	}

	public function execute() {

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$resultJson = $this->resultJsonFactory->create();
		$allSapIds = $this->helpersap->getJsAllInventoryItems();
		$baseurl = $this->_storeManager->getStore()->getUrl();
			foreach ($allSapIds as $key => $value) {
				if ($value["ColorSwatch"]) {
					$p = parse_url($value["ColorSwatch"]);
					$varlue = explode('/', $p['path']);
					$imagename = basename($p['path']);
					$imageurl = $baseurl . $varlue[1] . '/resized/80x80/' . $varlue[2] . '/' . $varlue[3] . '/' . $imagename; 
				    $path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/80x80/'.$varlue[2].'/'.$varlue[3].'/'.$imagename);
						$allSapIds[$key]["ColorSwatch"] = $imageurl;
				}
				if ($value["U_WImage1"]) {
					$p = parse_url($value["U_WImage1"]);
					$varlue = explode('/', $p['path']);
					$imagename = basename($p['path']);
					$imageUrl = $baseurl . $varlue[1] . '/resized/350x500/' . $varlue[2] . '/' . $varlue[3] . '/' . $imagename;
					$path = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($varlue[1].'/resized/350x500/'.$varlue[2].'/'.$varlue[3].'/'.$imagename);
						$allSapIds[$key]["U_WImage1"] = $imageUrl;
				}

			}

		$Json = $this->jsonEncoder->encode($allSapIds);	
		$collectionJson = $this->jsonEncoder->encode($this->helpersap->Slidercollection());
		try {
		    $media = $this->_filesystem->getDirectoryWrite(DirectoryList::APP);
		    $media1 = $this->_filesystem->getDirectoryWrite(DirectoryList::PUB);
		    try {
		    	$media->writeFile("code/Sttl/Customerorder/view/frontend/web/template/Inventory.json",$Json);
		    	$media->writeFile("code/Sttl/Customerorder/view/frontend/web/template/collectionJson.json",$collectionJson);
		    	$media1->writeFile("static/frontend/sttl/adaruniforms/en_US/Sttl_Customerorder/template/Inventory.json",$Json);
		    	$media1->writeFile("static/frontend/sttl/adaruniforms_mobile/en_US/Sttl_Customerorder/template/Inventory.json",$Json);
	        }
	        catch(Exception $e) {
	        	$logger->info($e->getMessage());
	    	}
	        finally {
	            $logger->info("Inventory Json File is Gendered");
	        }
	    } catch(Exception $e) {
	        $logger->info($e->getMessage());
	    }
	}

}