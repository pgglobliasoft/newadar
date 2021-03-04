<?php

namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
class Ponumber extends \Magento\Framework\App\Action\Action
{
    protected $resultForwardFactory;
    protected $saphelper;
    protected $resultJsonFactory;
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Sttl\Adaruniforms\Helper\Sap $saphelper
    )
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->saphelper = $saphelper;
        parent::__construct($context);
    }

    public function execute()
    {

    	$post = $this->getRequest()->getParams();
    	$results = '';
    	// $post['custom_id'] = '234';
    	// $post['ponumber'] = '';
    	if(isset($post['custom_id']) && $post['custom_id'] != '')
    	{
    		$customdata = $this->saphelper->getCustomerDetailsbyid1($post['custom_id']);
			// print_r($customdata[0]);die;
			if(isset($customdata[0]) && $customdata[0] !='' && $post['ponumber'] == '')
			{
				$ponumberlist = $this->saphelper->getpolist($customdata[0]);
				// echo "<pre>";
				// print_r($ponumberlist);die;
				foreach($ponumberlist as $data)
					{
						 if($data['NumatCardPo'] !=''){
						 	$PO_number[] = ['ponumber' => $data['NumatCardPo'],
						 					'id' => $data['Id']
						 ];

						}
					}

          if(@!$PO_number){
            $PO_number = [];
          }

				$results = ['success' => 'true', "ponumberlist" => $PO_number];
			}else{
				if(isset($customdata[0]) && $customdata[0] !='' && $post['ponumber'] != '')
				{
					$ponumberdata = $this->saphelper->checkponumber($customdata[0],strtolower($post['ponumber']));
					if (!preg_match("/^[^a-zA-Z_ ]{4,}$/", $post['ponumber'])) {
						$results = ['success' => 'false','message' => 'PO Number must be a number or letter special character and at least 4 characters long'];
                       // $results->setData('error', 'PO Number must be a number or letter special character and at least 4 characters long');
                    }
					else if(empty($ponumberdata))
					{
						$results = ['success' => 'false','message' => 'used this po number'];
					}else{
						$results = ['success' => 'false','message' =>'PO Number already used, please enter unique PO Number.'];
					}


				}
			}
	   }else{
    		$results = ['success' => 'false','message' => 'custom session is expried'];
    	}
    	// print_r($results);die;
    	return $this->resultJsonFactory->create()->setData($results);
    }
}
