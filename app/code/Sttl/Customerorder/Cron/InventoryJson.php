<?php

namespace Sttl\Customerorder\Cron;

use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class InventoryJson
{

	protected $helpersap;

	protected $_filesystem;

	public function __construct(
		EncoderInterface $jsonEncoder,
		\Magento\Framework\Filesystem $filesystem,
		Sap $helpersap
	) {
		$this->jsonEncoder = $jsonEncoder;
		$this->helpersap = $helpersap;
		$this->_filesystem = $filesystem;

	}
	public function execute()
	{

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info("Done");
		$getallitmesdata = $this->helpersap->getJsAllInventoryItemshalf();
		$Json = $this->jsonEncoder->encode($getallitmesdata);	
		try {
		    $media = $this->_filesystem->getDirectoryWrite(DirectoryList::APP);
		    try {
		    	$media->writeFile("design/frontend/sttl/adaruniforms/Sttl_Customerorder/web/template/Inventory.json",$Json);
	        }
	        finally {
	        	$logger->info("Inventory Json File is Gendered");
	            
	        }
	    } catch(Exception $e) {
	    	$logger->info($e->getMessage());
	    }
		return $this;

	}
}