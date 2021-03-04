<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Sttl\Adaruniforms\Helper\Sap;

class Inventorystatus extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;

	protected $invsession;
	
	protected $helpersap;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		Sap $helpersap
		)
	{
		$this->invsession = $customerSession;
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helpersap = $helpersap;
	}
	
	public function execute()
	{
		$resultJson = $this->resultJsonFactory->create();
		
		try {
			$getallitmesdata = $this->helpersap->getallitmescode();
			$allitmescode = $allInventoryItems = [];
			$DatabyStyle = array();
			foreach($getallitmesdata as $key => $data)
			{
				$styleInventory[$data['Style']] = $data;
				$allInventoryItems[$data['ItemCode']] = $data;
				$allitmescode[$key]['ItemCode'] = $data['ItemCode'];
				$DatabyStyle[$data['SizeGroup']][$data['SizeOrder']] = $data['Size'];
			}
			$allitmescode = array_column($allitmescode, 'ItemCode');
			//if(empty($this->session->getStyleInventory()))
			{
				$this->invsession->setStyleInventory($styleInventory);
			}
			//if(empty($this->session->getAllInventoryItems()))
			{
				$this->invsession->setAllInventoryItems($allInventoryItems);	
			}
			//if(empty($this->session->getAllitmescode()))
			{
				$this->invsession->setAllitmescode($allitmescode);	
			}
			//if(empty($this->session->getDatabyStyle()))
			{
				$this->invsession->setDatabyStyle($DatabyStyle);	
			}	
			
            $response = ['success' => 'true', "allitmescode" => $allitmescode, "allInventoryItems" => $allInventoryItems];
        } catch (\Exception $e) {
            $response = ['success' => 'false', 'message' => $e->getMessage()];
        }
		
		return $resultJson->setData($response);
	}	
}