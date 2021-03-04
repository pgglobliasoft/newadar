<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Framework\App\Request\Http;

class prodata extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
	
	protected $helpersap;

	protected $_customerSession;

	protected $requestfacto;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Customer\Model\Session $customerSession,
		Sap $helpersap,
		Http $requestfacto
		)
	{
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $customerSession;
		$this->helpersap = $helpersap;
		$this->requestfacto = $requestfacto;
	}
	
	public function execute()
	{	
		
		$view_name = @$this->requestfacto->getParam('name');
			try{
				// $getallitmesdata = $this->helpersap->sapproductdata();
				// $getallitmesdata = $this->helpersap->getSapViewStructure($view_name);
				// $getallitmesdata = $this->helpersap->getcustomermysql();
				$getallitmesdata = $this->helpersap->getJoinFromSAP();

					// $list = $getallitmesdata;

					// $fp = fopen('MWEB_OINV.csv', 'w');

					// foreach ($list as $fields) {
					//     fputcsv($fp, $fields);
					// }

					// fclose($fp);
					echo "<pre>";
					print_r($getallitmesdata);die;
	           
				
	        } catch (\Exception $e) {
	           echo $e;die;
	        }        
			
		return 0;
	}	
}