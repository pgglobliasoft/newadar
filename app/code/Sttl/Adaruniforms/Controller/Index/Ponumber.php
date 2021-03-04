<?php

namespace Sttl\Adaruniforms\Controller\Index;

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
    	$results = new DataObject();
    	if(isset($post['custom_id']) && $post['custom_id'] != '')
    	{
    		$customdata = $this->saphelper->getCustomerDetailsbyid($post['custom_id']);
			
			if(isset($customdata[0]) && $customdata[0] !='' && $post['ponumber'] == '')
			{
				$ponumberlist = $this->saphelper->getponumberlist($customdata[0]);
			$html = '<option value="">Select Existing p.o.</option>';
					foreach($ponumberlist as $data)
					{
						 if($data['NumatCardPo'] !=''){
						 	$id = $data['Id'];
						 	$PO_number = $data['NumatCardPo']; 
						$html .= "<option value='$id'>$PO_number</option>";
						}
					}
				$results->setData('success', $html);
			}else{
				if(isset($customdata[0]) && $customdata[0] !='' && $post['ponumber'] != '')
				{
					$ponumberdata = $this->saphelper->checkponumber($customdata[0],strtolower($post['ponumber']));
					// if (!preg_match("/^[^a-zA-Z_ ]{4,}$/", $post['ponumber'])) {
						  if (!preg_match("/^[^-\s][a-zA-Z0-9!%*@#$&()\\-`.+,\-\s=\"]{3,}$/",$post['ponumber'] )){
                       $results->setData('error', 'PO Number must be a number or letter special character and at least 4 characters long');
                    }
					else if(empty($ponumberdata))
					{
						$results->setData('success', 'used this po number');
					}else{
						$results->setData('error', 'PO Number already used, please enter unique PO Number.');
					}


				}
			}
	   }else{
    		$results->setData('error', 'custom session is expried');
    	}
    	return $this->resultJsonFactory->create()->setData($results->getData());
    }
}