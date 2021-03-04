<?php

namespace Sttl\Adaruniforms\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
class Ponumbernew extends \Magento\Framework\App\Action\Action
{
    protected $resultForwardFactory;
    protected $saphelper;
    protected $resultJsonFactory;
    protected $customerSession;
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Customer\Model\Session $customerSession,
    \Sttl\Adaruniforms\Helper\Sap $saphelper
    )
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->saphelper = $saphelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {

    	$post = $this->getRequest()->getParams();
    	$results = new DataObject();
    	$cid = $this->customerSession->getCustomer()->getId();

    	if(isset($cid) && $cid != '')
    	{
    		$customdata = $this->saphelper->getCustomerDetailsbyid($cid);
			
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