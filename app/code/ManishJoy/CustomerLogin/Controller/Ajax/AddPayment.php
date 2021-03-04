<?php

namespace ManishJoy\CustomerLogin\Controller\Ajax;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

ini_set('memory_limit', '1000M');
ini_set('max_execution_time', -1);

class AddPayment extends \Magento\Framework\App\Action\Action
{
	protected $customerAccountManagement;
    protected $escaper;
    protected $session;
    protected $jsonHelper;
    protected $_ajaxLoginHelper;
	protected $dataHelper;
	protected $ebizHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
		\Sttl\Adaruniforms\Helper\Sap $dataHelper,
		\Sttl\Adaruniforms\Helper\Ebizcharge $ebizHelper,
        JsonHelper $jsonHelper
        
    )
    {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        $this->jsonHelper = $jsonHelper;
		$this->dataHelper = $dataHelper;
		$this->ebizHelper = $ebizHelper;
        parent::__construct($context);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $fullname = (string)$this->getRequest()->getPost('fullname');
		$card_type = (string)$this->getRequest()->getPost('card_type');
		$card_no = (string)$this->getRequest()->getPost('card_no');
        $security_code = (string)$this->getRequest()->getPost('security_code');
        $expiration_date = (string)$this->getRequest()->getPost('expiration_date');
        $cc_default = (string)$this->getRequest()->getPost('cc_default');
        $reutnhtml = false;
       	if(null !== $this->getRequest()->getPost('viewmode'))
       	{
       		$viewmode = $this->getRequest()->getPost('viewmode');
       		$returnhtml = true;
       	}
		
        /* $address1 = (string)$this->getRequest()->getPost('address1');
        $address2 = (string)$this->getRequest()->getPost('address2');
        $city = (string)$this->getRequest()->getPost('city');
        $state = (string)$this->getRequest()->getPost('state'); 
        $street = (string)$this->getRequest()->getPost('street');
        $zipcode = (string)$this->getRequest()->getPost('zipcode');*/
		
		$validate = 0;
		
		if (!\Zend_Validate::is($fullname, 'NotEmpty')) {
			$result['error'] = __('Please enter Full Name');
		} else if (!\Zend_Validate::is($card_type, 'NotEmpty')) {
			$result['error'] = __('Please select Card Type');
		} else if (!\Zend_Validate::is($card_no, 'NotEmpty')) {
			$result['error'] = __('Please enter Card No');
		} else if (!\Zend_Validate::is($security_code, 'NotEmpty')) {
			$result['error'] = __('Please enter Security Code');
		} else if (!\Zend_Validate::is($expiration_date, 'NotEmpty')) {
			$result['error'] = __('Please enter Expiration Date');
		} else {
			$validate = 1;
		}
		
        if ($validate) {
            
            try 
			{
				$getData = $this->session->getCustomer()->getData();
				
				if (!empty($getData)) {
					$card_code = $getData["customer_number"];
					$CardName = $getData["firstname"]." ".$getData["lastname"];
					$CustNum = $this->getRequest()->getPost('ebiz_customer_number');
					
					if (!empty($CustNum)) {
						$card_details = array();
						$card_details['MethodName'] = $this->getRequest()->getPost('fullname');
						$card_details['CardNumber'] = $this->getRequest()->getPost('card_no');
						$card_details['CardExpiration'] = str_replace("/", "", $this->getRequest()->getPost('expiration_date'));
						$card_details['CardType'] = $this->getRequest()->getPost('card_type');
						$card_details['CardCode'] = $this->getRequest()->getPost('security_code');
						//$card_details['AvsStreet'] = $this->getRequest()->getPost('street');
						//$card_details['AvsZip'] = $this->getRequest()->getPost('zipcode');
						$card_details['SecondarySort'] = 0;
						
						$cc_default = (!empty($cc_default)) ? true : false;

						$saved_card = $this->ebizHelper->saveCardByCustomerId($CustNum, $card_details, $cc_default, false);
						if (!is_numeric($saved_card))
							$result['error'] = $saved_card;
					} else {
						$result['error'] = __('eBiz Customer Number not exists.');
					}
				} else {
					$result['error'] = __('Invalid User');
				}
            } catch (NoSuchEntityException $e) {
                //$result['error'] = $e->getMessage();
                $result['error'] = __('We\'re unable to save card, please try again.');
            } catch (SecurityViolationException $exception) {
                $result['error'] = $exception->getMessage();
            } catch (\Exception $exception) {
                 $result['error'] = $exception->getMessage();
            }
        }

        if (!empty($result['error'])) {
            $htmlPopup = 'error';
            $result['html_popup'] = $htmlPopup;
        } else {
        	if(!empty($viewmode) && $returnhtml)
        	{
        		$html = $this->getpaymenthtml($saved_card);
        		$htmlPopup = 'done';
            	$result['html'] = $html;
            	$result['html_popup'] = $htmlPopup;
        	}else{
        		$htmlPopup = 'done';
            	$result['html_popup'] = $htmlPopup;
        	}
            
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }

    private function getpaymenthtml($saved_card = '')
    {
    	//$customer_number = $customerdata["customer_number"];
    	$getData = $this->session->getCustomer()->getData();
    	$customer_number = $getData["customer_number"];
    	$search_query=array(
		array(
		'Field'=>'CustomerID',  
		'Type'=>'eq',
		'Value'=>$customer_number)
		);
		if(isset($customer_number) && $customer_number != '')
		{
			$objCustomers = $this->ebizHelper->searchCustomerByParams($search_query,true,0,1);
		}
		$custNum = '';
		if(isset($objCustomers->Customers) && count($objCustomers->Customers) > 0)
		{
			$objCustomer = $objCustomers->Customers;
			$objCustomer = $objCustomer[0];
			if (isset($objCustomer->CustNum)) {
				$custNum = $objCustomer->CustNum;
				//$customerSession->getCustomer()->setCustNum($custNum);
			}
		}
    	$saved_cards = (isset($objCustomer->PaymentMethods) && count($objCustomer->PaymentMethods) > 0) ? $objCustomer->PaymentMethods : array();
    	$i = 0;
    	$html = '<option>Please Select</option>';
			foreach($saved_cards as $card)
			{ 
				$select = '';
				$card_expiry = (isset($card->CardExpiration) && !empty($card->CardExpiration)) ? date("m/Y", strtotime($card->CardExpiration)) : NULL;

				if($saved_card == $card->MethodID ) { $select = "selected" ;} 
				$html .='<option value="'.$card->MethodID.'" attr-ccno = "'.$card->CardNumber.'" attr-ccexpiry = "'.$card_expiry.'" attr-cctype = "'.$card->CardType.'" '.$select.'>'.$card->CardNumber.'</option>';
		 		$i++;
			}
			return $html;
    }
}